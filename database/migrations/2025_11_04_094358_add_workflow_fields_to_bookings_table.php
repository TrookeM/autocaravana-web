<?php
// En el archivo database/migrations/xxxx_add_workflow_fields_...

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // ¡Añadir esto!

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modificar la columna 'status' para añadir el nuevo estado 'active'
        // Usamos DB::statement para modificar un enum existente de forma segura
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");

        // 2. Añadir los nuevos campos para el workflow
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('fuel_level_out')->nullable()->after('km_llegada');
            $table->string('fuel_level_in')->nullable()->after('fuel_level_out');
            $table->text('checkout_notes')->nullable()->after('fuel_level_in');
            $table->decimal('extra_charge_km', 10, 2)->default(0.00)->after('total_price');
            $table->decimal('extra_charge_fuel', 10, 2)->default(0.00)->after('extra_charge_km');
            $table->decimal('extra_charge_other', 10, 2)->default(0.00)->after('extra_charge_fuel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'fuel_level_out',
                'fuel_level_in',
                'checkout_notes',
                'extra_charge_km',
                'extra_charge_fuel',
                'extra_charge_other'
            ]);
        });
        
        // Revertir el ENUM a su estado original
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};