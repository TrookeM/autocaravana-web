<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Extra extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     * ESTO ES LO QUE ARREGLA TU ERROR:
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'es_por_dia',
        'es_por_alquiler',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'precio' => 'decimal:2',
        'es_por_dia' => 'boolean',
        'es_por_alquiler' => 'boolean',
    ];

    /**
     * Define la relación "Muchos a Muchos" con Booking.
     */
    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_extra')
                    ->withPivot('precio_cobrado'); // ¡Importante para acceder al precio!
    }
}