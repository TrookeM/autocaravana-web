<?php
// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
// ¡Añade esto!
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    // ... (tus constantes están bien) ...
    public const DEPOSIT_PERCENTAGE = 0.30;
    public const STATUS_PENDING = 'pending';
    public const STATUS_DEPOSIT_PAID = 'deposit_paid';
    public const STATUS_FULL_PAID = 'full_paid';

    protected $fillable = [
        'campervan_id',
        'user_id', 
        'customer_name',
        'customer_email',
        'customer_phone',
        'start_date',
        'end_date',
        'total_price',
        'status',
        'amount_paid',
        'payment_status',
        'payment_due_date',
        'reminder_sent', // <-- AÑADIR ESTA LÍNEA
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'payment_due_date' => 'date',
        'total_price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function campervan(): BelongsTo // <-- Es bueno tipar el retorno
    {
        return $this->belongsTo(Campervan::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getAmountDueAttribute(): float
    {
        return $this->total_price - $this->amount_paid;
    }
}
