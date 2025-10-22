<?php

// app/Models/PriceRule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'value',
        'period',
        'start_date',
        'end_date',
        'is_active',
        'campervan_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'value' => 'decimal:2',
    ];
    
    // Relación opcional con Campervan
    public function campervan()
    {
        return $this->belongsTo(Campervan::class);
    }
}
