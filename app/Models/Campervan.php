<?php

// app/Models/Campervan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Guide;

class Campervan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_per_night',
        'allows_deposit',
        'is_visible',
        'main_image_path',
        'secondary_images_json',
        'no_checkout_booking',
        'check_in_time',
        'check_out_time',
        'km_limit',         
        'price_per_extra_km',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'allows_deposit' => 'boolean',
        'secondary_images_json' => 'array',
        'no_checkout_booking' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getSecondaryImagesAttribute(): array
    {
        return $this->secondary_images_json ?? [];
    }

    public function blockings(): HasMany
    {
        return $this->hasMany(Blocking::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Booking::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * ==========================================================
     * ¡RELACIÓN ACTUALIZADA! (RF9.3 Refactor)
     * ==========================================================
     * Una camper puede tener muchos items de inventario (incluidos).
     */
    public function inventoryItems(): BelongsToMany
    {
        return $this->belongsToMany(InventoryItem::class, 'campervan_inventory_item')
                    // Le decimos a Eloquent que también cargue estos campos de la pivote
                    ->withPivot('quantity', 'es_opcional', 'precio', 'es_por_dia')
                    ->withTimestamps();
    }

    /**
     * ==========================================================
     * NUEVA RELACIÓN: GUÍAS Y MANUALES (RF9.2.b)
     * ==========================================================
     * Una camper tiene muchas guías.
     */
    public function guides(): HasMany
    {
        return $this->hasMany(Guide::class);
    }
}