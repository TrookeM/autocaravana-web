<?php
// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str; // <-- AÑADIDO: Para generar el token

// (Asegúrate de que este Enum existe si lo estás usando)
// use App\Enums\BookingStatus; 

class Booking extends Model
{
    use HasFactory;

    // --- Constantes de Estado de la Reserva ---
    // public const STATUS_PENDING_BOOKING = 'pending'; // ¡ELIMINADA! Estaba duplicada
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_ACTIVE = 'active'; 
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    
    // --- Constantes de Estado de Pago ---
    public const DEPOSIT_PERCENTAGE = 0.30;
    public const STATUS_PENDING = 'pending'; // Este 'pending' sirve para ambos
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
        'reminder_sent',
        'km_salida',
        'km_llegada',
        'original_price',
        'discount_amount',
        'coupon_code',
        'fuel_level_out',
        'fuel_level_in',
        'checkout_notes',
        'extra_charge_km',
        'extra_charge_fuel',
        'extra_charge_other',
        'inventory_checklist_out', 
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'payment_due_date' => 'date',
        'total_price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'extra_charge_km' => 'decimal:2',
        'extra_charge_fuel' => 'decimal:2',
        'extra_charge_other' => 'decimal:2',
        'inventory_checklist_out' => 'array', 
        // 'status' => BookingStatus::class, // (Si usaste el Enum)
    ];

    /**
     * ==========================================================
     * AÑADIDO: Generar Token Público (RF10.1)
     * ==========================================================
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Hook para el evento 'creating' (antes de guardar en DB)
        static::creating(function ($booking) {
            // Generar un token único y seguro
            do {
                $booking->public_token = Str::random(40);
            } while (static::where('public_token', $booking->public_token)->exists());
        });
    }

    public function campervan(): BelongsTo
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

    /**
     * ==========================================================
     * ¡RELACIÓN ACTUALIZADA! (RF9.3 Refactor)
     * ==========================================================
     * Una reserva tiene muchos items de inventario (los extras que contrató).
     */
    public function inventoryItems(): BelongsToMany
    {
        return $this->belongsToMany(InventoryItem::class, 'booking_inventory_item')
                                ->withPivot('precio_cobrado', 'quantity_booked')
                                ->withTimestamps();
    }

    public function invoice()
    {
        // Una reserva (Booking) tiene una factura (Invoice)
        return $this->hasOne(\App\Models\Invoice::class);
    }
}