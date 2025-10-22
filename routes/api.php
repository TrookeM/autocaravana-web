<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Campervan; // <-- Asegúrate de que esto está
use App\Http\Controllers\Api\PriceCalculationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Nueva ruta para calcular el precio dinámicamente
Route::post('/calculate-price', [PriceCalculationController::class, 'calculate']);