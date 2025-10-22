<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Models\Campervan;
use App\Http\Controllers\BookingController;
use App\Livewire\CampervanCalendar;

// Health check route (add this first)
Route::get('/health', function () {
    try {
        // Test database connection
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        
        return response()->json([
            'status' => 'ok',
            'database' => 'connected',
            'timestamp' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// Ruta de inicio (Homepage)
Route::get('/', [HomeController::class, 'index']);

// Ruta de la página de detalle que usa el modelo Campervan
Route::get('/caravanas/{campervan}', function (Campervan $campervan) {
    return view('campervan_detail_page', [
        'campervan' => $campervan,
    ]);
})->name('campervan.show');

// Ruta para mostrar el formulario de reserva
Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');

// Ruta para procesar la reserva
Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');

// Ruta de confirmacion de reserva
Route::get('/booking/confirmation/{id}', [BookingController::class, 'confirmation'])->name('booking.confirmation');