<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController; // Asumimos que existe
use App\Models\Campervan;
use App\Http\Controllers\BookingController; // O el controlador que uses
use App\Livewire\CampervanCalendar; // Importa el componente Livewire

// Ruta de inicio (Homepage)
Route::get('/', [HomeController::class, 'index']);

// Ruta de la página de detalle que usa el modelo Campervan
Route::get('/caravanas/{campervan}', function (Campervan $campervan) {
    // Aquí es donde usas la vista que creamos antes
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