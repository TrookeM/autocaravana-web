<?php

namespace App\Http\Controllers;

use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CouponController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Valida el cupón y devuelve el descuento en JSON.
     */
    public function apply(Request $request)
    {
        // Validación de la petición AJAX
        if (!$request->input('code')) {
            return response()->json(['error' => 'El código de cupón es obligatorio.'], 422);
        }
        if (!$request->has('price_for_coupon')) {
             return response()->json(['error' => 'Error de precio. Recargue la página.'], 422);
        }

        $code = strtoupper($request->input('code'));
        $originalPrice = (float) $request->input('price_for_coupon'); 

        // Limpiamos la sesión de errores anteriores antes de intentar aplicar
        Session::forget(['coupon_code', 'coupon_discount_amount', 'final_price', 'coupon_error', 'coupon_success']);

        try {
            // 1. Validar y obtener el cupón
            $coupon = $this->couponService->validate($code);

            // 2. Calcular el precio final
            $finalPrice = $this->couponService->calculateDiscountedPrice($coupon, $originalPrice);

            $discountAmount = $originalPrice - $finalPrice;

            // 3. GUARDAMOS EN SESIÓN (Para que BookingController@store lo lea)
            Session::put('coupon_code', $coupon->code);
            Session::put('coupon_discount_amount', $discountAmount);
            Session::put('final_price', $finalPrice);
            Session::put('coupon_success', "Cupón '{$coupon->code}' aplicado con éxito. ¡Has ahorrado " . number_format($discountAmount, 2) . "€!");
            
            // 4. RESPONDEMOS CON JSON (Para que Alpine lo lea sin recargar)
            return response()->json([
                'success' => true,
                'coupon_code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'message' => "Cupón '{$coupon->code}' aplicado con éxito. ¡Has ahorrado " . number_format($discountAmount, 2) . "€!"
            ]);

        } catch (\Exception $e) {
            // Guardamos el error en la sesión (por si acaso)
            Session::put('coupon_error', $e->getMessage()); 
            
            // Devolvemos el error en JSON
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Limpia el cupón de la sesión y responde con éxito.
     */
    public function remove(Request $request)
    {
        // Limpia TODAS las claves de sesión relacionadas con el cupón
        Session::forget([
            'coupon_code', 'coupon_discount_amount', 'final_price', 'coupon_error', 'coupon_success'
        ]);

        // Responde con JSON para que Alpine lo sepa y actualice la UI
        return response()->json(['success' => true]);
    }
}