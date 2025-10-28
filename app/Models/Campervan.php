<?php

// app/Models/Campervan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Campervan extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'description',
        'price_per_night',
        'allows_deposit',
        'is_visible',
        'main_image_path',
        'secondary_images_json',
        'no_checkout_booking',
        'check_in_time',
        'check_out_time',
    ];

    /**
     * Define los tipos de datos de los atributos.
     */
    protected $casts = [
        'is_visible' => 'boolean',
        'allows_deposit' => 'boolean',
        // El campo JSON se convierte automáticamente en un array/Collection de PHP
        'secondary_images_json' => 'array',
        'no_checkout_booking' => 'boolean',
    ];

    /**
     * Una autocaravana tiene muchas reservas.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Método de acceso para obtener las imágenes secundarias de forma más limpia
    public function getSecondaryImagesAttribute(): array
    {
        return $this->secondary_images_json ?? [];
    }

    public function blockings(): HasMany
    {
        return $this->hasMany(Blocking::class);
    }

    /**
     * Obtiene todas las reseñas para esta caravana a través de las reservas.
     */
    public function reviews(): HasManyThrough
    {
        // Parámetros:
        // 1. Modelo final (Review)
        // 2. Modelo intermedio (Booking)
        return $this->hasManyThrough(Review::class, Booking::class);
    }
}
