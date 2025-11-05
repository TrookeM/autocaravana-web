<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Campervan;
use App\Models\Coupon;
// use App\Models\Extra; // <-- ELIMINADO
use App\Models\InventoryItem; // <-- ¡AÑADIDO!
use App\Services\PriceCalculatorService;
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

        $request->validate([
            'campervan_id' => 'required|exists:campervans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_price' => 'required|numeric|min:0',
        ]);

        $campervan = Campervan::findOrFail($request->campervan_id);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $nights = $startDate->diffInDays($endDate);

        $totalPrice = (float) $request->total_price;
        $paymentDueDate = $startDate->copy()->subDays(30);
        $isDepositAllowed = $campervan->allows_deposit && $paymentDueDate->gt(now());
        $defaultOption = $isDepositAllowed ? 'deposit' : 'full';
        
        // ==========================================================
        // --- ¡LÓGICA DE EXTRAS CORREGIDA! (RF9.3 Refactor - Trello #11) ---
        // ==========================================================
        // Cargamos los items de la tabla pivote que están marcados
        // como opcionales PARA ESTA camper.
        $extras = $campervan->inventoryItems()
                            ->wherePivot('es_opcional', true)
                            ->get();
        // ==========================================================

        return view('booking.create', [
            'campervan' => $campervan,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'base_price' => $totalPrice,
            'nights' => $nights,
            'deposit_amount' => $priceCalculator->calculateDepositAmount($totalPrice),
            'remaining_amount' => $totalPrice - $priceCalculator->calculateDepositAmount($totalPrice),
            'due_date' => $paymentDueDate->format('d/m/Y'),
            'isDepositAllowed' => $isDepositAllowed,
            'defaultOption' => $defaultOption,
            'extras' => $extras, // <-- Ahora pasamos los items de inventario opcionales
        ]);
    }

    /**
     * Procesa la solicitud final de reserva, aplica el cupón y guarda la información.
     */
    public function store(Request $request, PriceCalculatorService $priceCalculator, CouponService $couponService)
    {
        // 1. VALIDACIÓN
        $validated = $request->validate([
            'campervan_id' => 'required|exists:campervans,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_price' => 'required|numeric|min:0',
            'payment_option' => 'required|in:deposit,full',
            'coupon_code' => 'nullable|string|max:255',
            'final_price_after_coupon' => 'required|numeric|min:0',
            'extras' => 'nullable|array',
            'extras.*' => 'nullable|integer|exists:inventory_items,id', // <-- ¡CORREGIDO!
        ]);
        
        // --- CÁLCULO DE PRECIOS ---
        $campervan = Campervan::findOrFail($validated['campervan_id']);
        $nights = Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date']));
        $originalPrice = (float) $validated['total_price'];
        $couponCode = $validated['coupon_code'] ?? null;
        $finalPrice = (float) $validated['final_price_after_coupon'];
        $discountAmount = $originalPrice - $finalPrice;
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        
        // ==========================================================
        // --- 3. VALIDACIÓN DE STOCK DE EXTRAS (RF9.3 Refactor - Trello #12) ---
        // ==========================================================
        if (!empty($validated['extras'])) {
            
            // Carga los items globales que el cliente ha solicitado
            $selectedItems = InventoryItem::find($validated['extras']);

            foreach ($selectedItems as $item) {
                // (No necesitamos 'es_opcional' aquí, el controlador 'create' ya lo filtró)

                $totalStock = (int)$item->total_stock; // Stock Global (ej: 2 portabicis)
                $requestedCount = 1; // Asumimos 1 de cada por reserva

                // Contamos cuántos de este item global están en uso
                // en TODAS las reservas que se solapan
                $inUseCount = Booking::whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_ACTIVE])
                    ->where('start_date', '<', $endDate)
                    ->where('end_date', '>', $startDate)
                    // Buscamos en la nueva tabla pivote de reservas
                    ->whereHas('inventoryItems', function ($query) use ($item) {
                        $query->where('inventory_item_id', $item->id);
                    })
                    ->count();

                // Comparamos
                if (($inUseCount + $requestedCount) > $totalStock) {
                    return back()->withErrors([
                        'db_error' => 'Error de stock: El extra "' . $item->name . '" no está disponible para las fechas seleccionadas. (Stock total: ' . $totalStock . ', ya en uso: ' . $inUseCount . ')'
                    ])->withInput();
                }
            }
        }
        // ==========================================================

        // 2. PREPARAR EXTRAS PARA LA DB
        $itemsToSync = [];
        if (!empty($validated['extras'])) {
             if (!isset($selectedItems)) {
                 $selectedItems = InventoryItem::find($validated['extras']);
             }
             
             // Cargamos la relación de ESTA camper para obtener el precio correcto
             $campervan->load('inventoryItems');

             foreach ($selectedItems as $item) {
                 // Buscamos el precio específico de este item PARA ESTA CAMPER
                 $pivotData = $campervan->inventoryItems->find($item->id);
                 
                 if ($pivotData) {
                    $precio = (float)$pivotData->pivot->precio;
                    $esPorDia = (bool)$pivotData->pivot->es_por_dia;
                    $costoExtra = $esPorDia ? ($precio * $nights) : $precio;
                    // Añadimos la cantidad reservada (quantity_booked)
                    $itemsToSync[$item->id] = ['precio_cobrado' => $costoExtra, 'quantity_booked' => 1];
                 }
             }
        }
        
        // 4. CÁLCULO DE PAGOS Y ESTADO INICIAL
        // ... (Esta lógica se queda igual) ...
        $depositAmount = $priceCalculator->calculateDepositAmount($finalPrice);
        $paymentDueDate = null;
        if ($validated['payment_option'] === 'deposit') {
            $paymentDueDate = Carbon::parse($validated['start_date'])->subDays(30);
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
                'total_price' => $finalPrice,
                'original_price' => $originalPrice,
                'discount_amount' => $discountAmount,
                'coupon_code' => $couponCode,
                'amount_paid' => $initialAmountPaid,
                'payment_status' => $paymentStatus,
                'payment_due_date' => $paymentDueDate,
                'status' => 'confirmed', 
            ]);
            
            if (!empty($itemsToSync)) {
                $booking->inventoryItems()->attach($itemsToSync); // <-- ¡CORREGIDO!
            }
            
            if ($couponCode) {
                Coupon::where('code', $couponCode)->increment('uses');
            }
            
            $booking->transactions()->create([
                'gateway_id' => 'INIT_PAY_' . $booking->id . '_' . time(), 
                'type' => $paymentStatus === 'full_paid' ? 'full_payment' : 'deposit_payment',
                'amount' => $initialAmountPaid,
                'status' => 'completed', 
                'notes' => $couponCode ? "Pago inicial con cupón: {$couponCode}" : "Pago inicial sin cupón.",
            ]);

            DB::commit();

            Session::forget(['coupon_code', 'coupon_discount_amount', 'final_price', 'coupon_error', 'coupon_success']);

            // Mail::to($booking->customer_email)->send(new BookingConfirmed($booking)); // Comentado por ahora

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
    public function confirmation($id)
    {
        $booking = Booking::with('campervan', 'inventoryItems')->findOrFail($id); // <-- ¡CORREGIDO!
        
        return view('booking.confirmation', compact('booking'));
    }
    
    /**
     * Genera y descarga el contrato en PDF.
     */
    public function downloadContract(Booking $booking)
    {
        $booking->load('campervan', 'inventoryItems'); // <-- ¡CORREGIDO!

        $pdf = Pdf::loadView('pdf.contract', [
            'booking' => $booking
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        $fileName = 'contrato_reserva_' . $booking->id . '.pdf';
        
        return $pdf->download($fileName);
    }
}