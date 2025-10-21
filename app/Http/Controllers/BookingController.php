<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Campervan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        // Validar parámetros de la URL
        $request->validate([
            'campervan_id' => 'required|exists:campervans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_price' => 'required|numeric|min:0',
        ]);

        $campervan = Campervan::findOrFail($request->campervan_id);

        // Calcular número de noches
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $nights = $startDate->diffInDays($endDate);

        return view('booking.create', [
            'campervan' => $campervan,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_price' => $request->total_price,
            'nights' => $nights,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'campervan_id' => 'required|exists:campervans,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_price' => 'required|numeric|min:0',
        ]);

        // Verificar disponibilidad antes de crear la reserva
        $existingBooking = Booking::where('campervan_id', $validated['campervan_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                      ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                      ->orWhere(function ($query) use ($validated) {
                          $query->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                      });
            })
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingBooking) {
            return back()->withErrors([
                'error' => 'Lo sentimos, las fechas seleccionadas ya no están disponibles.'
            ])->withInput();
        }

        // Crear la reserva
        $booking = Booking::create([
            'campervan_id' => $validated['campervan_id'],
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_price' => $validated['total_price'],
            'status' => 'confirmed',
        ]);

        // Redirigir a una página de confirmación
        return redirect()->route('booking.confirmation', $booking->id)
                         ->with('success', 'Reserva creada exitosamente');
    }

    public function confirmation($id)
    {
        $booking = Booking::with('campervan')->findOrFail($id);
        
        return view('booking.confirmation', compact('booking'));
    }
}