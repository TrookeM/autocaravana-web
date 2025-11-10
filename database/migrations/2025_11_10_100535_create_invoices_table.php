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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Clave foránea para la reserva
            $table->foreignId('booking_id')
                  ->constrained('bookings')
                  ->onDelete('cascade'); // Si se borra la reserva, se borra la factura

            // Datos de la factura
            $table->string('invoice_number')->unique(); // Ej: "FRA-2025-0001"
            $table->date('invoice_date');

            // Almacenamos importes en céntimos (enteros) para evitar decimales
            $table->integer('total_amount'); // Importe total (IVA incl.)
            $table->integer('tax_amount');   // Parte de IVA/impuestos
            
            // Guardamos los datos del cliente (NIF, dirección...)
            $table->json('customer_details');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};