<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Campervan;
use App\Models\Coupon;
use App\Models\InventoryItem;
use App\Services\PriceCalculatorService; // <-- Importante
use App\Services\CouponService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingController extends Controller
{
    /**
     * Muestra la página de confirmación de reserva (checkout).
     */
    public function create(Request $request, PriceCalculatorService $priceCalculator)
    {
        if (!session()->has('errors') && !$request->has('code')) {
            Session::forget([
                'coupon_code',
                'coupon_discount_amount',
                'final_price',
                'coupon_error',
                'coupon_success'
            ]);
        }

        // ==========================================================
        // VALIDACIÓN ACTUALIZADA (RF12.2)
        // ==========================================================
        $validated = $request->validate([
            'campervan_id' => 'required|exists:campervans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_price' => 'required|numeric|min:0', // Precio final (Base - Descuento Duración)
            'base_price_before_discount' => 'required|numeric|min:0', // Precio ANTES del descuento
            'duration_discount_amount' => 'required|numeric|min:0', // El descuento
        ]);

        $campervan = Campervan::findOrFail($validated['campervan_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $nights = $startDate->diffInDays($endDate);

        // --- PRECIOS CON DESGLOSE ---
        $totalPrice = (float) $validated['total_price']; // Precio ya descontado (Subtotal)
        $basePriceBeforeDiscount = (float) $validated['base_price_before_discount'];
        $durationDiscountAmount = (float) $validated['duration_discount_amount'];
        // -----------------------------

        $paymentDueDate = $startDate->copy()->subDays(30);
        $isDepositAllowed = $campervan->allows_deposit && $paymentDueDate->gt(now());
        $defaultOption = $isDepositAllowed ? 'deposit' : 'full';
        
        $extras = $campervan->inventoryItems()
                            ->wherePivot('es_opcional', true)
                            ->get();

        // ==========================================================
        // VISTA ACTUALIZADA (RF12.2)
        // ==========================================================
        return view('booking.create', [
            'campervan' => $campervan,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'nights' => $nights,
            
            // Pasamos el desglose de precios a la vista
            'base_price' => $totalPrice, // Este es el subtotal (con descuento de duración aplicado)
            'base_price_before_discount' => $basePriceBeforeDiscount,
            'duration_discount_amount' => $durationDiscountAmount,
            
            'deposit_amount' => $priceCalculator->calculateDepositAmount($totalPrice),
            'remaining_amount' => $totalPrice - $priceCalculator->calculateDepositAmount($totalPrice),
            'due_date' => $paymentDueDate->format('d/m/Y'),
            'isDepositAllowed' => $isDepositAllowed,
            'defaultOption' => $defaultOption,
            'extras' => $extras,
        ]);
    }

    /**
     * Procesa la solicitud final de reserva, aplica el cupón y guarda la información.
     */
    public function store(Request $request, PriceCalculatorService $priceCalculator, CouponService $couponService)
    {
        // 1. VALIDACIÓN (MODIFICADA)
        $validated = $request->validate([
            'campervan_id' => 'required|exists:campervans,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_option' => 'required|in:deposit,full',
            'coupon_code' => 'nullable|string|max:255',
            'final_price_after_coupon' => 'required|numeric|min:0', 
            'extras' => 'nullable|array',
            'extras.*' => 'nullable|integer|exists:inventory_items,id',
        ]);
        
        $campervan = Campervan::findOrFail($validated['campervan_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $nights = $startDate->diffInDays($endDate);
        
        // ==========================================================
        // --- 2. RE-CÁLCULO DE PRECIOS EN SERVIDOR (RF12.2) ---
        // ==========================================================
        $priceBreakdown = $priceCalculator->getPriceBreakdown($campervan, $startDate, $endDate);
        $baseSeasonalPrice = $priceBreakdown['base_price'];
        $durationDiscountAmount = $priceBreakdown['duration_discount_amount'];

        // --- VALIDACIÓN DE STOCK DE EXTRAS (Movido aquí) ---
        $itemsToSync = [];
        $extrasPrice = 0; 

        if (!empty($validated['extras'])) {
            $selectedItems = InventoryItem::find($validated['extras']);
            $campervan->load('inventoryItems'); 

            foreach ($selectedItems as $item) {
                $totalStock = (int)$item->total_stock;
                $requestedCount = 1;
                $inUseCount = Booking::whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_ACTIVE])
                    ->where('start_date', '<', $endDate)
                    ->where('end_date', '>', $startDate)
                    ->whereHas('inventoryItems', function ($query) use ($item) {
                        $query->where('inventory_item_id', $item->id);
                    })
                    ->count();

                if (($inUseCount + $requestedCount) > $totalStock) {
                    return back()->withErrors([
                        'db_error' => 'Error de stock: El extra "' . $item->name . '" no está disponible.'
                    ])->withInput();
                }

                // --- PREPARAR EXTRAS PARA LA DB (Movido aquí) ---
                $pivotData = $campervan->inventoryItems->find($item->id);
                if ($pivotData) {
                    $precio = (float)$pivotData->pivot->precio;
                    $esPorDia = (bool)$pivotData->pivot->es_por_dia;
                    $costoExtra = $esPorDia ? ($precio * $nights) : $precio;
                    
                    $itemsToSync[$item->id] = ['precio_cobrado' => $costoExtra, 'quantity_booked' => 1];
                    $extrasPrice += $costoExtra;
                }
            }
        }

        // --- CÁLCULO DE PRECIOS FINALES ---
        $originalPrice = $baseSeasonalPrice + $extrasPrice;
        $priceBeforeCoupon = $originalPrice - $durationDiscountAmount;
        $finalPrice = (float) $validated['final_price_after_coupon'];
        $couponCode = $validated['coupon_code'] ?? null;
        $couponDiscountAmount = $priceBeforeCoupon - $finalPrice;
        $totalDiscountAmount = $durationDiscountAmount + $couponDiscountAmount;
        // ==========================================================
        // FIN DE RE-CÁLCULO
        // ==========================================================


        // 4. CÁLCULO DE PAGOS Y ESTADO INICIAL
        $depositAmount = $priceCalculator->calculateDepositAmount($finalPrice); 
        $paymentDueDate = null;
        if ($validated['payment_option'] === 'deposit') {
            $paymentDueDate = $startDate->copy()->subDays(30);
            if ($paymentDueDate->lt(now())) {
                $initialAmountPaid = $finalPrice;
                $paymentStatus = 'full_paid';
                $paymentDueDate = null;
            } else {
                $initialAmountPaid = $depositAmount;
                $paymentStatus = 'deposit_paid';
            }
        } else {
            $initialAmountPaid = $finalPrice;
            $paymentStatus = 'full_paid';
            $paymentDueDate = null;
        }


        // 5. CREAR LA RESERVA
        try {
            DB::beginTransaction();

            $booking = Booking::create([
                'campervan_id' => $validated['campervan_id'],
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                
                // --- CAMPOS DE PRECIO CORREGIDOS (RF12.2) ---
                'total_price' => $finalPrice, 
                'original_price' => $originalPrice, 
                'discount_amount' => $totalDiscountAmount, 
                'coupon_code' => $couponCode,
                // --- FIN DE CAMPOS CORREGIDOS ---
                
                'amount_paid' => $initialAmountPaid,
                'payment_status' => $paymentStatus,
                'payment_due_date' => $paymentDueDate,
                'status' => 'confirmed', 
            ]);
            
            if (!empty($itemsToSync)) {
                $booking->inventoryItems()->attach($itemsToSync);
            }
            
            if ($couponCode) {
                if ($couponDiscountAmount > 0) {
                    Coupon::where('code', $couponCode)->increment('uses');
                }
            }
            
            $booking->transactions()->create([
                'gateway_id' => 'INIT_PAY_' . $booking->id . '_' . time(), 
                'type' => $paymentStatus === 'full_paid' ? 'full_payment' : 'deposit_payment',
                'amount' => $initialAmountPaid,
                'status' => 'completed', 
                'notes' => $couponCode ? "Pago inicial con cupón: {$couponCode}" : "Pago inicial.",
            ]);

            DB::commit();

            Session::forget(['coupon_code', 'coupon_discount_amount', 'final_price', 'coupon_error', 'coupon_success']);

            Mail::to($booking->customer_email)->queue(new BookingConfirmed($booking));

            return redirect()->route('booking.confirmation', $booking->id)
                ->with('success', 'Reserva creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error crítico al crear la reserva: ' . $e->getMessage());
            return back()->withErrors(['db_error' => 'Error al procesar la reserva. Por favor, inténtelo de nuevo.'])->withInput();
        }
    }

    /**
     * Muestra la página de confirmación final.
     */
    // ==========================================================
    // MÉTODO MODIFICADO (RF12.2)
    // ==========================================================
    public function confirmation($id, PriceCalculatorService $priceCalculator)
    {
        $booking = Booking::with('campervan', 'inventoryItems')->findOrFail($id);
        
        // 1. Recalculamos el precio base (con temporada)
        $priceBreakdown = $priceCalculator->getPriceBreakdown(
            $booking->campervan, 
            $booking->start_date, 
            $booking->end_date
        );
        
        // 2. Recalculamos el precio de los extras
        $extrasPrice = $booking->inventoryItems->sum('pivot.precio_cobrado');
        
        // 3. Calculamos el precio base de temporada (Original - Extras)
        $baseSeasonalPrice = $booking->original_price - $extrasPrice;

        // 4. Calculamos el descuento por duración
        $durationDiscountAmount = $priceBreakdown['duration_discount_amount'];

        // 5. Calculamos el descuento por cupón
        $couponDiscountAmount = $booking->discount_amount - $durationDiscountAmount;

        return view('booking.confirmation', [
            'booking' => $booking,
            'base_seasonal_price' => $baseSeasonalPrice, // Base (temporada)
            'extras_price' => $extrasPrice, // Total Extras
            'duration_discount_amount' => $durationDiscountAmount, // Descuento Duración
            'coupon_discount_amount' => $couponDiscountAmount, // Descuento Cupón
        ]);
    }
    
    /**
     * Genera y descarga el contrato en PDF.
     */
    public function downloadContract(Booking $booking, PriceCalculatorService $priceCalculator) // <-- Inyectar servicio
    {
        $booking->load('campervan', 'inventoryItems');

        // ==========================================================
        // RECALCULAMOS EL DESGLOSE PARA EL PDF (RF12.2)
        // ==========================================================
        $priceBreakdown = $priceCalculator->getPriceBreakdown(
            $booking->campervan, 
            $booking->start_date, 
            $booking->end_date
        );
        
        $extrasPrice = $booking->inventoryItems->sum('pivot.precio_cobrado');
        $baseSeasonalPrice = $booking->original_price - $extrasPrice;
        $durationDiscountAmount = $priceBreakdown['duration_discount_amount'];
        $couponDiscountAmount = $booking->discount_amount - $durationDiscountAmount;
        // ==========================================================

        $pdf = Pdf::loadView('pdf.contract', [
            'booking' => $booking,
            'base_seasonal_price' => $baseSeasonalPrice, // <-- Pasamos variables
            'extras_price' => $extrasPrice,
            'duration_discount_amount' => $durationDiscountAmount,
            'coupon_discount_amount' => $couponDiscountAmount,
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        $fileName = 'contrato_reserva_' . $booking->id . '.pdf';
        
        return $pdf->download($fileName);
    }
}