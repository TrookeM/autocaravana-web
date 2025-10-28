<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'customer_name', // <-- Nuevo
        'rating',
        'comment',
    ];

    // Una reseña pertenece a UNA reserva
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}