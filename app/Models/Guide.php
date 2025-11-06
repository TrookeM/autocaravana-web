<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar la relación

class Guide extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campervan_id',
        'title',
        'content',
        'pdf_path',
    ];

    /**
     * Define la relación inversa (una guía pertenece a una campervan).
     */
    public function campervan(): BelongsTo
    {
        return $this->belongsTo(Campervan::class);
    }
}