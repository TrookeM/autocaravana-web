<?php
// En el nuevo archivo de migración xxxx_add_inventory_checklist_...

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Esta columna guardará un array JSON con los nombres
            // de los items que se marcaron en el check-in.
            $table->json('inventory_checklist_out')->nullable()->after('checkout_notes');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('inventory_checklist_out');
        });
    }
};