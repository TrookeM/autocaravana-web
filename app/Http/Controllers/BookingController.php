<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Campervan;
use App\Models\Coupon; // Necesario para incrementar el uso del cupón
use App\Services\PriceCalculatorService;
use App\Services\CouponService; // Servicio para validar y aplicar cupones (RF5.1)
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB; // Usaremos transacciones de DB para mayor seguridad
use Illuminate\Support\Facades\Session; // Para limpiar la sesión de cupones

class BookingController extends Controller
{
    /**
     * Muestra la página de confirmación de reserva (checkout).
     */
    public function create(Request $request, PriceCalculatorService $priceCalculator) 
    {
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
        
        // 1. CÁLCULO DE PAGOS Y FECHAS
        $totalPrice = $request->total_price;
        $depositAmount = $priceCalculator->calculateDepositAmount($totalPrice);
        $remainingAmount = $totalPrice - $depositAmount;
        
        // Fecha límite para el pago restante (30 días antes del check-in)
        $paymentDueDate = $startDate->copy()->subDays(30);

        // 2. COMPROBACIÓN DE VIABILIDAD DEL DEPÓSITO (RF6.1)
        // Solo se permite si el campervan lo permite Y la fecha límite de pago restante no ha pasado.
        $isDepositAllowed = $campervan->allows_deposit && $paymentDueDate->gt(now());
        
        // 3. OPCIÓN POR DEFECTO
        $defaultOption = $isDepositAllowed ? 'deposit' : 'full';

        return view('booking.create', [
            'campervan' => $campervan,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_price' => $totalPrice,
            'nights' => $nights,
            
            // Variables de pago parcial
            'deposit_amount' => $depositAmount,
            'remaining_amount' => $remainingAmount,
            'due_date' => $paymentDueDate->format('d/m/Y'),
            'isDepositAllowed' => $isDepositAllowed,
            'defaultOption' => $defaultOption,
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
            'total_price' => 'required|numeric|min:0', // Precio original sin descuento
            'payment_option' => 'required|in:deposit,full', 
            
            // --- NUEVOS CAMPOS DE CUPÓN (RF5.1) ---
            'coupon_code' => 'nullable|string|max:255',
            'final_price_after_coupon' => 'nullable|numeric|min:0', // Precio con descuento
            // ------------------------------------
        ]);
        
        $campervan = Campervan::findOrFail($validated['campervan_id']);
        $originalPrice = (float) $validated['total_price'];
        
        // Determinar el precio final a usar para el pago (con descuento si existe)
        $finalPrice = (float) ($validated['final_price_after_coupon'] ?? $originalPrice);
        $couponCode = $validated['coupon_code'] ?? null;
        $discountAmount = $originalPrice - $finalPrice; 

        // ----------------------------------------------------
        // ** INICIO RF6.4: Bloquear Reserva en Día de Checkout si la regla está activa **
        // ----------------------------------------------------
        if ($campervan->no_checkout_booking) {
            
            $isCheckoutDay = Booking::where('campervan_id', $campervan->id)
                ->where('end_date', $validated['start_date'])
                ->whereIn('status', ['confirmed', 'paid', 'deposit_paid'])
                ->exists();

            if ($isCheckoutDay) {
                // Si hay conflicto y la regla está ACTIVA, detenemos la reserva.
                return redirect()->back()->withErrors([
                    'start_date' => 'La fecha de inicio seleccionada coincide con un día de check-out existente para esta caravana. Por favor, selecciona el día siguiente.',
                ])->withInput();
            }
        }
        // ----------------------------------------------------
        // ** FIN RF6.4 **
        // ----------------------------------------------------
        
        // 2. VERIFICACIÓN DE DISPONIBILIDAD (Lógica de solapamiento)
        $existingBooking = Booking::where('campervan_id', $validated['campervan_id'])
            ->where('start_date', '<', $validated['end_date']) 
            ->where('end_date', '>', $validated['start_date'])
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'failed_payment')
            ->first();

        if ($existingBooking) {
            return back()->withErrors(['error' => 'Lo sentimos, las fechas seleccionadas ya están reservadas.'])->withInput();
        }
        
        // 3. CÁLCULO DE PAGOS Y ESTADO INICIAL (Usando $finalPrice)
        
        $depositAmount = $priceCalculator->calculateDepositAmount($finalPrice);
        $paymentDueDate = null;
        
        // Lógica de Estado Inicial (RF6.1)
        if ($validated['payment_option'] === 'deposit') {
            $paymentDueDate = Carbon::parse($validated['start_date'])->subDays(30);
            
            if ($paymentDueDate->lt(now())) {
                // Si la fecha límite ha pasado, forzamos el pago total
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

        // 4. CREAR LA RESERVA (dentro de una transacción de DB)
        try {
            DB::beginTransaction();

            $booking = Booking::create([
                'campervan_id' => $validated['campervan_id'],
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                
                'total_price' => $finalPrice, // PRECIO FINAL CON DESCUENTO
                
                // --- CAMPOS DE CUPÓN (RF5.1) ---
                'original_price' => $originalPrice, 
                'discount_amount' => $discountAmount, 
                'coupon_code' => $couponCode, 
                // ---------------------------------
                
                'amount_paid' => $initialAmountPaid,
                'payment_status' => $paymentStatus,
                'payment_due_date' => $paymentDueDate,
                'status' => 'confirmed',
            ]);
            
            // RF5.1: Incrementar uso del cupón
            if ($couponCode) {
                // Buscamos e incrementamos el contador de usos del cupón
                Coupon::where('code', $couponCode)->increment('uses');
            }
            
            // RF6.3: Registro de la Transacción Inicial
            $booking->transactions()->create([
                'gateway_id' => 'INIT_PAY_' . $booking->id . '_' . time(), 
                'type' => $paymentStatus === 'full_paid' ? 'full_payment' : 'deposit_payment',
                'amount' => $initialAmountPaid, 
                'status' => 'completed',
                'notes' => $couponCode ? "Pago inicial con cupón: {$couponCode}" : "Pago inicial sin cupón.",
            ]);

            DB::commit();

            // 5. Limpiamos la sesión del cupón DESPUÉS de un guardado exitoso
            Session::forget(['coupon_code', 'coupon_discount_amount', 'final_price', 'coupon_error', 'coupon_success']);

            // 6. ENVIAR EMAIL Y REDIRIGIR
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
        $booking = Booking::with('campervan')->findOrFail($id);
        
        return view('booking.confirmation', compact('booking'));
    }
}
