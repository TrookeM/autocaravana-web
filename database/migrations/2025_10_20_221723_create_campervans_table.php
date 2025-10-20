<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campervans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // <-- El nombre (ej: "La Furgoneta Azul")
            $table->text('description')->nullable(); // <-- Descripción larga
            $table->decimal('price_per_night', 8, 2); // <-- Precio (ej: 120.50)
            $table->boolean('is_visible')->default(true); // <-- Para ocultarla si no está disponible
            $table->timestamps(); // <-- (Déjalo, crea created_at y updated_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campervans');
    }
};
