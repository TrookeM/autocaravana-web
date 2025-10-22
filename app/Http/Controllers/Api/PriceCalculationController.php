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
     * Calcula el precio total de una reserva dado un rango de fechas y una autocaravana.
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
        
        // 3. Devolver la respuesta JSON
        return response()->json([
            'total_price' => $totalPrice,
            // (Opcional) Puedes devolver el precio por noche si lo necesitas en el front-end
        ]);
    }
}