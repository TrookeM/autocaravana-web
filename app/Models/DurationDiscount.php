<?php

namespace App\Models; // <-- ¡ESTA ES LA LÍNEA CORREGIDA!

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DurationDiscount extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'min_nights',
        'max_nights',
        'percentage_discount',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_nights' => 'integer',
        'max_nights' => 'integer',
        'percentage_discount' => 'float',
    ];
}