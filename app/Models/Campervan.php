<?php
// app/Models/Campervan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campervan extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price_per_night',
        'is_visible',
    ];
    /**
     * Una autocaravana tiene muchas reservas.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
