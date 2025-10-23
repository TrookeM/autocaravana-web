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
        // Añadimos las tres columnas necesarias para la lógica de cupones.
        Schema::table('bookings', function (Blueprint $table) {
            // original_price: El precio antes de aplicar cualquier descuento de cupón.
            $table->decimal('original_price', 10, 2)->after('total_price')->default(0.00)->comment('Precio total de la reserva antes de aplicar el cupón.');
            
            // discount_amount: La cantidad de descuento aplicado.
            $table->decimal('discount_amount', 10, 2)->after('original_price')->default(0.00)->comment('Cantidad descontada por el cupón.');
            
            // coupon_code: El código de cupón utilizado (si lo hay).
            $table->string('coupon_code', 50)->after('discount_amount')->nullable()->comment('Código de cupón aplicado.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertimos la migración eliminando las tres columnas.
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'discount_amount', 'coupon_code']);
        });
    }
};
