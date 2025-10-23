<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade la columna 'allows_deposit' a la tabla 'campervans'.
     */
    public function up(): void
    {
        Schema::table('campervans', function (Blueprint $table) {
            // Campo booleano para activar/desactivar la opción de pagar solo la señal
            $table->boolean('allows_deposit')
                  ->default(true) // Por defecto, permitimos el depósito
                  ->after('price_per_night'); // Lo colocamos después del precio
        });
    }

    /**
     * Reverse the migrations.
     * Quita la columna si se deshace la migración.
     */
    public function down(): void
    {
        Schema::table('campervans', function (Blueprint $table) {
            $table->dropColumn('allows_deposit');
        });
    }
};
