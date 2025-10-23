<?php

namespace App\Services;

use App\Models\Campervan;
use App\Models\PriceRule;
use Carbon\Carbon;

class PriceCalculatorService
{
    // --- NUEVA CONSTANTE RF6.1 ---
    const DEPOSIT_PERCENTAGE = 0.30; // 30% del precio total como señal
    // -----------------------------

    /**
     * Calcula el precio final por noche para una autocaravana y una fecha dada,
     * aplicando todas las reglas de precios.
     */
    public function getPriceForDate(Campervan $campervan, Carbon $date): float
    {
        // 1. Empezar con el precio base
        $finalPrice = $campervan->price_per_night;

        // 2. Obtener las reglas activas que aplican a esta fecha y autocaravana
        $rules = PriceRule::where('is_active', true)
            ->where(function ($query) use ($campervan) {
                // Aplica a esta autocaravana o es global (campervan_id is NULL)
                $query->whereNull('campervan_id')
                    ->orWhere('campervan_id', $campervan->id);
            })
            ->get();

        // 3. Iterar y aplicar las reglas
        foreach ($rules as $rule) {
            if ($this->ruleAppliesToDate($rule, $date)) {
                $finalPrice = $this->applyRule($finalPrice, $rule);
            }
        }

        // Devolver el precio final, asegurándose de que no sea negativo y redondeando a 2 decimales
        return max(0, round($finalPrice, 2));
    }

    /**
     * Calcula el precio total de una reserva para un rango de fechas.
     */
    public function calculateTotalPrice(Campervan $campervan, Carbon $startDate, Carbon $endDate): float
    {
        $totalPrice = 0;
        $currentDate = $startDate->copy();

        // Iterar por cada noche (el final no cuenta como noche de alquiler)
        while ($currentDate->lessThan($endDate)) {
            $pricePerNight = $this->getPriceForDate($campervan, $currentDate);
            $totalPrice += $pricePerNight;
            $currentDate->addDay();
        }

        return round($totalPrice, 2);
    }

    // --- NUEVO MÉTODO RF6.1 ---
    /**
     * Calcula el monto del depósito (señal) basado en el precio total.
     * @param float $totalPrice
     * @return float
     */
    public function calculateDepositAmount(float $totalPrice): float
    {
        $deposit = $totalPrice * self::DEPOSIT_PERCENTAGE;
        
        // El depósito también debe redondearse a dos decimales
        return round($deposit, 2);
    }
    // -----------------------------
    
    // --- Lógica Auxiliar de Aplicación de Reglas (Sin cambios) ---

    /**
     * Comprueba si una regla de precio aplica a una fecha específica.
     */
    protected function ruleAppliesToDate(PriceRule $rule, Carbon $date): bool
    {
        // Regla 1: Validar las fechas de inicio/fin de la regla
        if ($rule->start_date && $date->lt($rule->start_date)) return false;
        if ($rule->end_date && $date->gte($rule->end_date)) return false;

        // Regla 2: Validar el período (Fin de semana, entre semana, etc.)
        $dayOfWeekIso = $date->dayOfWeekIso;

        if ($rule->period === 'weekends') {
            // FIN DE SEMANA: Viernes (5), Sábado (6), Domingo (7)
            if (in_array($dayOfWeekIso, [5, 6])) {
                return true;
            }
        }
        
        if ($rule->period === 'weekdays') {
            // ENTRE SEMANA: Lunes (1), Martes (2), Miércoles (3), Jueves (4)
            if (in_array($dayOfWeekIso, [1, 2, 3, 4, 7])) {
                return true;
            }
        }
        
        // La regla 'all' o 'custom_dates' aplica por defecto si pasa la validación de fechas
        return $rule->period === 'all' || $rule->period === 'custom_dates';
    }

    /**
     * Aplica el ajuste de la regla de precio al precio base.
     */
    protected function applyRule(float $price, PriceRule $rule): float
    {
        $value = (float)$rule->value;

        switch ($rule->type) {
            case 'percentage_increase':
                // Aumenta el precio en un porcentaje (ej: 10% -> * 1.10)
                return $price * (1 + $value / 100);
            case 'percentage_decrease':
                // Disminuye el precio en un porcentaje (ej: 10% -> * 0.90)
                return $price * (1 - $value / 100);
            case 'fixed_increase':
                // Suma un valor fijo
                return $price + $value;
            case 'fixed_decrease':
                // Resta un valor fijo
                return $price - $value;
            default:
                return $price;
        }
    }
}
