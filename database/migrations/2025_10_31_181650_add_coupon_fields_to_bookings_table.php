<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // En el método up() del nuevo archivo de migración

    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {

            // Comprueba si la columna NO existe antes de añadirla
            if (!Schema::hasColumn('bookings', 'original_price')) {
                $table->decimal('original_price', 8, 2)->nullable()->after('total_price');
            }

            // Comprueba si la columna NO existe antes de añadirla
            if (!Schema::hasColumn('bookings', 'discount_amount')) {
                // Asegúrate de que 'original_price' existe antes de ponerla 'after'
                // Si 'original_price' ya existía, 'after' funciona bien.
                $table->decimal('discount_amount', 8, 2)->nullable()->after('original_price');
            }

            // Comprueba si la columna NO existe antes de añadirla
            if (!Schema::hasColumn('bookings', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('discount_amount');
            }
        });
    }

    // (Opcional pero recomendado) Añade esto al método down() para poder revertir
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'discount_amount', 'coupon_code']);
        });
    }
};
