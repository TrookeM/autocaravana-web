<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Services\PriceCalculatorService; // <-- 1. Importar el servicio

class PublicBookingController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * Inyectamos el servicio de precios
     */
    public function __invoke(Request $request, PriceCalculatorService $priceCalculator, $token)
    {
        // 2. Buscamos la reserva por su token
        $booking = Booking::where('public_token', $token)
                        ->with('campervan.guides', 'inventoryItems') // Cargamos las relaciones
                        ->firstOrFail();

        // ==========================================================
        // 3. AÑADIMOS LA LÓGICA DE DESGLOSE DE PRECIO
        // (Copiada de BookingController@confirmation)
        // ==========================================================
        $priceBreakdown = $priceCalculator->getPriceBreakdown(
            $booking->campervan, 
            $booking->start_date, 
            $booking->end_date
        );
        
        $extrasPrice = $booking->inventoryItems->sum('pivot.precio_cobrado');
        $baseSeasonalPrice = $booking->original_price - $extrasPrice;
        $durationDiscountAmount = $priceBreakdown['duration_discount_amount'];
        $couponDiscountAmount = ($booking->discount_amount ?? 0) - $durationDiscountAmount;
        // ==========================================================

        // 4. Pasamos las nuevas variables a la vista
        return view('booking.public_status', [
            'booking' => $booking,
            'base_seasonal_price' => $baseSeasonalPrice,
            'extras_price' => $extrasPrice,
            'duration_discount_amount' => $durationDiscountAmount,
            'coupon_discount_amount' => $couponDiscountAmount,
        ]);
    }
}