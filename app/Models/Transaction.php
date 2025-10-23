<?php
// app/Models/Transaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'gateway_id',
        'type',
        'amount',
        'status',
        'notes',
    ];

    /**
     * Define los tipos de datos de los atributos.
     */
    protected $casts = [
        'amount' => 'decimal:2', // Aseguramos que el monto sea decimal con 2 dígitos
    ];

    /**
     * Relación: Una transacción pertenece a una Reserva (Booking).
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
