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
        Schema::create('duration_discounts', function (Blueprint $table) {
            $table->id();
            
            // Noches mínimas para aplicar el descuento (ej. 7)
            $table->integer('min_nights'); 
            
            // Noches máximas. Si es nulo, significa "X noches o más" (ej. 21+)
            $table->integer('max_nights')->nullable(); 
            
            // El porcentaje a descontar (ej. 5.00 para 5%, 10.50 para 10.5%)
            $table->decimal('percentage_discount', 5, 2); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duration_discounts');
    }
};
