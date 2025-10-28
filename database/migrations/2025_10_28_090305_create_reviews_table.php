<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // Clave foránea a la reserva
            $table->foreignId('booking_id')
                  ->constrained('bookings')
                  ->onDelete('cascade');

            // Guardamos el nombre del autor directamente
            $table->string('customer_name'); 

            $table->unsignedTinyInteger('rating'); // 1 a 5
            $table->text('comment')->nullable();
            $table->timestamps();

            // ¡Importante! Solo una reseña por reserva
            $table->unique('booking_id'); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};