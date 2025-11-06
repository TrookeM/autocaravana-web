<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking; // <-- AÑADIDO

class PublicBookingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($token) // <-- Aceptamos el token de la ruta
    {
        // 1. Buscar la reserva usando el token público
        // Usamos firstOrFail para que falle (404) si el token no existe
        $booking = Booking::where('public_token', $token)->firstOrFail();

        // 2. Cargar las relaciones necesarias para la vista
        $booking->load('campervan.guides', 'inventoryItems');

        // 3. Devolver la vista (que crearemos en el siguiente archivo)
        return view('booking.public_status', compact('booking'));
    }
}