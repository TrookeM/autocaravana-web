<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Campervan;
use App\Models\Coupon;
use App\Models\Extra;
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
        // LIMPIEZA DE SESIÓN: Evitar que el cupón se arrastre.
        // CORRECCIÓN: Solo limpiamos si NO es una recarga por validación de errores
        // y si NO se está pasando un 'code' en la URL.
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
        $extras = Extra::all();

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
            'extras' => $extras,
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
            'extras.*' => 'nullable|integer|exists:extras,id',
        ]);
        
        // --- CÁLCULO DE PRECIOS ---
        $campervan = Campervan::findOrFail($validated['campervan_id']);
        $nights = Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date']));

        $originalPrice = (float) $validated['total_price'];
        $couponCode = $validated['coupon_code'] ?? null;
        $finalPrice = (float) $validated['final_price_after_coupon'];
        $discountAmount = $originalPrice - $finalPrice;

        // 2. PREPARAR EXTRAS PARA LA DB
        $extrasToSync = [];
        if (!empty($validated['extras'])) {
             $selectedExtras = Extra::find($validated['extras']);
             foreach ($selectedExtras as $extra) {
                 $costoExtra = $extra->es_por_dia ? ($extra->precio * $nights) : $extra->precio;
                 $extrasToSync[$extra->id] = ['precio_cobrado' => $costoExtra];
             }
        }
        
        // 4. CÁLCULO DE PAGOS Y ESTADO INICIAL (Usando $finalPrice)
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
        } else { // full payment
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
            
            if (!empty($extrasToSync)) {
                $booking->extras()->attach($extrasToSync);
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

            // 6. Limpiamos la sesión del cupón DESPUÉS del guardado exitoso
            Session::forget(['coupon_code', 'coupon_discount_amount', 'final_price', 'coupon_error', 'coupon_success']);

            // 7. ENVIAR EMAIL Y REDIRIGIR
            try {
                Mail::to($booking->customer_email)->send(new BookingConfirmed($booking));
            } catch (\Exception $e) {
                Log::error('Fallo al enviar email de confirmación: ' . $e->getMessage());
            }

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
        $booking = Booking::with('campervan', 'extras')->findOrFail($id);
        
        return view('booking.confirmation', compact('booking'));
    }
    
    /**
     * Genera y descarga el contrato en PDF.
     */
    public function downloadContract(Booking $booking)
    {
        $booking->load('campervan', 'extras');

        $pdf = Pdf::loadView('pdf.contract', [
            'booking' => $booking
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        $fileName = 'contrato_reserva_' . $booking->id . '.pdf';
        
        return $pdf->download($fileName);
    }
}