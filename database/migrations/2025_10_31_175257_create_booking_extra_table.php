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
        Schema::create('booking_extra', function (Blueprint $table) {
            // Clave foránea para la reserva
            $table->foreignId('booking_id')
                  ->constrained() // Asume que la tabla es 'bookings'
                  ->onDelete('cascade'); // Si se borra la reserva, se borra esta fila

            // Clave foránea para el extra
            $table->foreignId('extra_id')
                  ->constrained() // Asume que la tabla es 'extras'
                  ->onDelete('cascade');

            // El precio que se cobró por este extra en esta reserva
            $table->decimal('precio_cobrado', 8, 2);

            // Definimos una clave primaria compuesta para evitar duplicados
            $table->primary(['booking_id', 'extra_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_extra');
    }
};