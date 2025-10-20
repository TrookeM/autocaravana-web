<?php
// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    // Añadimos los campos que se pueden rellenar
    protected $fillable = [
        'campervan_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'start_date',
        'end_date',
        'total_price',
        'status',
    ];

    /**
     * Una reserva pertenece a una autocaravana.
     */
    public function campervan()
    {
        return $this->belongsTo(Campervan::class);
    }
}