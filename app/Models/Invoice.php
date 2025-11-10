<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Es bueno tenerlo
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory; // <-- Añade esto si usas factories

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'tax_amount',
        'customer_details',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_date' => 'date', // Convierte la fecha a un objeto Carbon
        'customer_details' => 'array', // Convierte el JSON a un array
    ];

    /**
     * Define la relación inversa: una factura pertenece a una reserva.
     */
    public function booking()
    {
        // Una factura (Invoice) pertenece a una reserva (Booking)
        return $this->belongsTo(Booking::class); // No necesitas el namespace completo si estás en el mismo.
    }
}