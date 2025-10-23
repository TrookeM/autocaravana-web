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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Código del cupón
            $table->decimal('value', 8, 2); // Valor del descuento
            
            // Tipo de descuento: 'percentage' (%) o 'fixed' (€)
            $table->enum('type', ['percentage', 'fixed'])->default('fixed'); 
            
            $table->unsignedInteger('max_uses')->nullable(); // Límite de usos totales (null = ilimitado)
            $table->unsignedInteger('uses')->default(0); // Contador de usos actual
            $table->boolean('is_active')->default(true); // Estado (activo/inactivo)
            
            $table->timestamp('expires_at')->nullable(); // Fecha de expiración (opcional)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
// Ejecutar: php artisan migrate