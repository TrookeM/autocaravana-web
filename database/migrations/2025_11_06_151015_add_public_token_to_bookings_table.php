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
            // Añadimos el token público. Lo hacemos nullable para que no falle
            // y unique para asegurar que no haya colisiones.
            // Lo añadimos después de la columna 'status'.
            $table->string('public_token', 60)->nullable()->unique()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Eliminamos la columna si hacemos rollback
            $table->dropColumn('public_token');
        });
    }
};
