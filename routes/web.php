<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController; 
use App\Models\Campervan;
use App\Http\Controllers\BookingController; 
use App\Livewire\CampervanCalendar; 

// Ruta de inicio (Homepage)
Route::get('/', [HomeController::class, 'index'])->name('home'); // Aseguramos el nombre 'home'

// Ruta de la página de detalle que usa el modelo Campervan
Route::get('/caravanas/{campervan}', function (Campervan $campervan) {
    // Aquí es donde usas la vista que creamos antes
    return view('campervan_detail_page', [
        'campervan' => $campervan,
    ]);
})->name('campervan.show');

// --- RUTAS DE CONTACTO ---

// 1. Ruta GET: Muestra el formulario de contacto
Route::get('/contacto', [HomeController::class, 'contact'])->name('contact');

// 2. Ruta POST: Procesa el envío del formulario
Route::post('/contacto', [HomeController::class, 'storeContactForm'])->name('contact.store');

// --- RUTAS DE RESERVA ---

// Ruta para mostrar el formulario de reserva
Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');

// Ruta para procesar la reserva
Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');

// Ruta de confirmacion de reserva
Route::get('/booking/confirmation/{id}', [BookingController::class, 'confirmation'])->name('booking.confirmation');
