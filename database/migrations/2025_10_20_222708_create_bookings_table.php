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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Usamos constrained() para crear la relación con la tabla 'campervans'
            $table->foreignId('campervan_id')->constrained()->onDelete('cascade');

            // Aquí podríamos relacionar con un usuario, pero para empezar
            // guardaremos los datos del cliente directamente.
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');

            $table->date('start_date'); // Fecha de inicio
            $table->date('end_date');   // Fecha de fin

            $table->decimal('total_price', 10, 2); // Precio total calculado

            $table->string('status')->default('pending'); // Ej: pending, confirmed, cancelled

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
