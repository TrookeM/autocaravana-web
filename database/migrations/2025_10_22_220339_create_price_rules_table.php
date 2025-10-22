<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['percentage_increase', 'percentage_decrease', 'fixed_increase', 'fixed_decrease']);
            $table->decimal('value', 8, 2); // Valor del ajuste (ej: 10.00 para 10%, 15.00€ fijos)
            $table->enum('period', ['weekends', 'weekdays', 'all', 'custom_dates'])->default('all');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Opcional: Relacionar la regla a una autocaravana específica (Polimórfica en el futuro si tienes otros modelos)
            $table->foreignId('campervan_id')->nullable()->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_rules');
    }
};
