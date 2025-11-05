<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InventoryItem extends Model
{
    use HasFactory;

    /**
     * Define los campos que son "globales" para un item.
     */
    protected $fillable = [
        'name',
        'total_stock',   // Stock físico total que posees
        'notes',
    ];

    /**
     * Casts globales
     */
    protected $casts = [
        'total_stock' => 'integer',
    ];

    /**
     * Un item de inventario puede estar incluido en MUCHAS campers.
     */
    public function campervans(): BelongsToMany
    {
        return $this->belongsToMany(Campervan::class, 'campervan_inventory_item')
                    // Le decimos a Eloquent que también cargue estos campos de la pivote
                    ->withPivot('quantity', 'es_opcional', 'precio', 'es_por_dia')
                    ->withTimestamps();
    }

    /**
     * Un item de inventario puede estar en muchas reservas
     * (si es un extra opcional contratado).
     */
    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_inventory_item')
                        ->withPivot('precio_cobrado', 'quantity_booked')
                        ->withTimestamps();
    }
}