<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController; 
use App\Models\Campervan;
use App\Http\Controllers\BookingController; 
use App\Http\Controllers\CouponController; // Añadido para gestionar cupones
use App\Http\Controllers\PublicBookingController; // <-- AÑADIDO

// Ruta de inicio (Homepage)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Ruta de la página de detalle que usa el modelo Campervan
Route::get('/caravanas/{campervan}', function (Campervan $campervan) {
    return view('campervan_detail_page', [
        'campervan' => $campervan,
    ]);
})->name('campervan.show');

// --- RUTAS DE CONTACTO ---
Route::get('/contacto', [HomeController::class, 'contact'])->name('contact');
Route::post('/contacto', [HomeController::class, 'storeContactForm'])->name('contact.store');

// --- RUTAS DE RESERVA ---
Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/confirmation/{id}', [BookingController::class, 'confirmation'])->name('booking.confirmation');

// --- RUTA PARA EL CUPÓN (RF5.1) ---
Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');

// ¡¡NUEVA RUTA AÑADIDA!! --
Route::post('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');

// Ruta para descargar el contrato en PDF
Route::get('/booking/{booking}/contract', [BookingController::class, 'downloadContract'])
    ->name('booking.contract.download');

// ==========================================================
// NUEVA RUTA: PORTAL PÚBLICO DE RESERVA (RF10.1)
// ==========================================================
Route::get('/reserva/{token}', PublicBookingController::class)
    ->name('public.booking.show');