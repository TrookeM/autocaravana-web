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
     * Valida el cupón y guarda el descuento en la sesión.
     */
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'total_price' => 'required|numeric|min:0',
            // No es necesario validar los campos del cliente aquí,
            // pero vendrán en el $request
        ]);

        $code = strtoupper($validated['code']);
        $originalPrice = (float) $validated['total_price'];

        // Limpiamos la sesión de errores de cupones anteriores
        Session::forget([
            'coupon_code',
            'coupon_discount_amount',
            'final_price',
            'coupon_error',
            'coupon_success'
        ]);

        try {
            // 1. Validar y obtener el cupón
            $coupon = $this->couponService->validate($code);

            // 2. Calcular el precio final
            $finalPrice = $this->couponService->calculateDiscountedPrice($coupon, $originalPrice);

            // 3. Guardar en la sesión
            Session::put('coupon_code', $coupon->code);
            Session::put('coupon_discount_amount', $originalPrice - $finalPrice);
            Session::put('final_price', $finalPrice);
            Session::put('coupon_success', "Cupón '{$coupon->code}' aplicado con éxito. ¡Has ahorrado " . number_format($originalPrice - $finalPrice, 2) . "€!");

            // Redirigir de vuelta al formulario conservando TODOS los datos
            return back()->withInput();

        } catch (\Exception $e) {
            // Manejar errores de validación del servicio
            Session::put('coupon_error', $e->getMessage());
            
            // --- CAMBIO CLAVE AQUÍ ---
            // Devuelve TODOS los inputs (incluyendo nombre, email...) 
            // para que no se borren.
            return back()->withInput();
        }
    }
}
