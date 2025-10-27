<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser; // Interfaz de Filament
use Illuminate\Support\Facades\Hash; 

class User extends Authenticatable implements FilamentUser // Implementación de la interfaz
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =======================================================
    // MÉTODO DE ACCESO DE FILAMENT (CORREGIDO PARA v3/v4)
    // =======================================================

    /**
     * Define si este usuario puede acceder a UN panel de Filament específico.
     *
     * @param string $panel
     * @return bool
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Esta es la validación que faltaba y que Filament está buscando.
        // Usa tu email de administrador para que puedas entrar.
        return $this->email === 'migatoyorch@gmail.com'; 
    }
}
