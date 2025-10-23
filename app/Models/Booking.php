<?php
// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Asegúrate de que Carbon esté disponible si usas helpers de fecha

class Booking extends Model
{
    use HasFactory;

    // Constante para el porcentaje de la señal (30% para el ejemplo)
    public const DEPOSIT_PERCENTAGE = 0.30;

    // --- CONSTANTES DE ESTADO DE PAGO (LAS QUE DAN ERROR) ---
    public const STATUS_PENDING = 'pending';
    public const STATUS_DEPOSIT_PAID = 'deposit_paid';
    public const STATUS_FULL_PAID = 'full_paid';

    protected $fillable = [
        'campervan_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'start_date',
        'end_date',
        'total_price',
        'status',

        // --- CAMPOS RF6.1 (Pago Parcial) AÑADIDOS ---
        'amount_paid',          // Monto total ya pagado (decimal)
        'payment_status',       // Estado del pago ('pending_deposit', 'deposit_paid', 'full_paid', etc.)
        'payment_due_date',     // Fecha límite para el pago restante (date)
        // ---------------------------------------------
    ];

    /**
     * Define los tipos de datos de los atributos.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'payment_due_date' => 'date', // Castear la nueva fecha límite
        'total_price' => 'decimal:2', // Aseguramos que el precio total sea decimal
        'amount_paid' => 'decimal:2', // Aseguramos que el monto pagado sea decimal
    ];

    /**
     * Relación con Campervan.
     */
    public function campervan()
    {
        return $this->belongsTo(Campervan::class);
    }

    /**
     * Relación con las Transacciones (para auditoría RF6.3).
     */
    public function transactions()
    {
        // Asumiendo que has creado el modelo Transaction.php
        return $this->hasMany(Transaction::class);
    }

    // --- NUEVOS MÉTODOS AYUDANTES ---

    /**
     * Calcula la cantidad restante que el cliente debe pagar.
     */
    public function getAmountDueAttribute(): float
    {
        return $this->total_price - $this->amount_paid;
    }
}
