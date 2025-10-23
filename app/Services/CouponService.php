<?php

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;

class CouponService
{
    /**
     * Valida si un código de cupón es aplicable.
     * @param string $code
     * @return Coupon|null
     * @throws \Exception
     */
    public function validate(string $code): ?Coupon
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            throw new \Exception("El cupón introducido no existe.");
        }

        if (!$coupon->is_active) {
            throw new \Exception("El cupón '{$code}' no está activo.");
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw new \Exception("El cupón '{$code}' ha expirado el {$coupon->expires_at->format('d/m/Y')}.");
        }

        if ($coupon->max_uses !== null && $coupon->uses >= $coupon->max_uses) {
            throw new \Exception("El cupón '{$code}' ha alcanzado su límite máximo de usos.");
        }

        return $coupon;
    }

    /**
     * Calcula el nuevo precio total después de aplicar el cupón.
     * @param Coupon $coupon
     * @param float $originalPrice
     * @return float
     */
    public function calculateDiscountedPrice(Coupon $coupon, float $originalPrice): float
    {
        $discountAmount = 0;

        if ($coupon->type === 'fixed') {
            // Descuento de cantidad fija (€)
            $discountAmount = $coupon->value;
        } elseif ($coupon->type === 'percentage') {
            // Descuento porcentual (%)
            $discountAmount = $originalPrice * ($coupon->value / 100);
        }

        // Asegurarse de que el descuento no sea mayor que el precio original
        $discountAmount = min($discountAmount, $originalPrice);

        return $originalPrice - $discountAmount;
    }

    /**
     * Aplica el cupón al precio total.
     * @param string $code
     * @param float $originalPrice
     * @return float
     * @throws \Exception
     */
    public function applyCoupon(string $code, float $originalPrice): float
    {
        $coupon = $this->validate($code);
        
        return $this->calculateDiscountedPrice($coupon, $originalPrice);
    }
}