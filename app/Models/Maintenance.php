<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'campervan_id',
        'date',
        'service_type',
        'cost',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'cost' => 'decimal:2',
    ];

    /**
     * Un registro de mantenimiento pertenece a una Campervan.
     */
    public function campervan(): BelongsTo
    {
        return $this->belongsTo(Campervan::class);
    }
}