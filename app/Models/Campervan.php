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
     */
    protected $fillable = [
        'name',
        'description',
        'price_per_night',
        'is_visible',
        'main_image_path', 
        'secondary_images_json',
    ];
    
    /**
     * Define los tipos de datos de los atributos.
     */
    protected $casts = [
        'is_visible' => 'boolean',
        // El campo JSON se convierte automáticamente en un array/Collection de PHP
        'secondary_images_json' => 'array', 
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
}