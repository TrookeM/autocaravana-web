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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // Enlace a la reserva a la que pertenece el pago
            $table->foreignId('booking_id')->constrained()->onDelete('cascade'); 
            
            // ID del pago en la pasarela (ej. Stripe Payment Intent ID)
            $table->string('gateway_id')->nullable(); 
            
            // Tipo de movimiento: 'deposit', 'final_payment', 'extra_charge', 'refund'
            $table->string('type'); 
            
            // Monto de la transacción (puede ser negativo para reembolsos)
            $table->decimal('amount', 10, 2); 
            
            // Estado: 'completed', 'failed', 'pending', 'refunded'
            $table->string('status'); 
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};