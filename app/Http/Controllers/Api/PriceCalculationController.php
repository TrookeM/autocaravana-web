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
     */
    public function calculate(Request $request, PriceCalculatorService $priceCalculator)
    {
        // 1. Validar la entrada del cliente
        $validated = $request->validate([
            'campervan_id' => 'required|exists:campervans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $campervan = Campervan::findOrFail($validated['campervan_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // 2. Usar el servicio para obtener el precio final con reglas
        $totalPrice = $priceCalculator->calculateTotalPrice($campervan, $startDate, $endDate);
        
        // 3. Calcular la SEÑAL (Depósito) usando el nuevo método
        $depositAmount = $priceCalculator->calculateDepositAmount($totalPrice);
        
        // 4. Calcular el RESTANTE a pagar
        $remainingAmount = $totalPrice - $depositAmount;
        
        // 5. Devolver la respuesta JSON con la división de pagos
        return response()->json([
            'total_price' => $totalPrice,
            'deposit_amount' => $depositAmount,       // <- Monto de la señal (30%)
            'remaining_amount' => $remainingAmount,   // <- Monto restante
        ]);
    }
}
