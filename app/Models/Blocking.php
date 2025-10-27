<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blocking extends Model
{
    use HasFactory;

    protected $fillable = [
        'campervan_id',
        'start_date',
        'end_date',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function campervan(): BelongsTo
    {
        return $this->belongsTo(Campervan::class);
    }
}