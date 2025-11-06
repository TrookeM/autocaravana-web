<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campervan;
use App\Services\PriceCalculatorService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PriceCalculationController extends Controller
{
    /**
     * Calcula el precio total, el monto de la señal y la cantidad restante de una reserva.
     * Implementa lógica para RF6.1
     * MODIFICADO para RF12.2 (Descuentos por Duración)
     */
    public function calculate(Request $request, PriceCalculatorService $priceCalculator)
    {
        // 1. Validar la entrada del cliente (Sin cambios)
        $validated = $request->validate([
            'campervan_id' => 'required|exists:campervans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $campervan = Campervan::findOrFail($validated['campervan_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // ==========================================================
        // LÓGICA ACTUALIZADA (RF12.2)
        // ==========================================================
        
        // 2. Usar el servicio para obtener el DESGLOSE completo
        $priceBreakdown = $priceCalculator->getPriceBreakdown($campervan, $startDate, $endDate);
        
        // 3. Calcular la SEÑAL (Depósito) usando el PRECIO FINAL
        $depositAmount = $priceCalculator->calculateDepositAmount($priceBreakdown['final_price']);
        
        // 4. Calcular el RESTANTE a pagar
        $remainingAmount = $priceBreakdown['final_price'] - $depositAmount;
        
        // 5. Devolver la respuesta JSON con el desglose completo
        // (Esto cumple el Paso 6: Frontend, al darle los datos que necesita)
        return response()->json([
            
            // Mantenemos 'total_price' apuntando al precio final
            // por compatibilidad con tu BookingController
            'total_price' => $priceBreakdown['final_price'], 
            
            'deposit_amount' => $depositAmount,
            'remaining_amount' => $remainingAmount,
            
            // Añadimos el nuevo desglose para que el frontend 
            // pueda mostrar: "Precio: 100€, Descuento: -10€, Total: 90€"
            'price_breakdown' => [
                'base_price' => $priceBreakdown['base_price'],
                'duration_discount_percentage' => $priceBreakdown['discount_percentage'],
                'duration_discount_amount' => $priceBreakdown['discount_amount'],
                'final_price' => $priceBreakdown['final_price'],
            ]
        ]);
    }
}