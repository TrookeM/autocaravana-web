<?php
// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

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
     * Define los tipos de datos de los atributos.
     * Esto asegura que 'start_date' y 'end_date' son objetos Carbon.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function campervan()
    {
        return $this->belongsTo(Campervan::class);
    }
}