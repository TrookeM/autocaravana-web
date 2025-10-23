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
        Schema::table('bookings', function (Blueprint $table) {
            // Monto ya pagado por el cliente
            $table->decimal('amount_paid', 10, 2)->default(0)->after('total_price');
            
            // Estado actual del pago: 'pending_deposit', 'deposit_paid', 'full_paid', 'refunded'
            $table->string('payment_status')->default('pending_deposit')->after('amount_paid');
            
            // Fecha límite para pagar el resto del importe (usado para notificaciones)
            $table->date('payment_due_date')->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'payment_status', 'payment_due_date']);
        });
    }
};