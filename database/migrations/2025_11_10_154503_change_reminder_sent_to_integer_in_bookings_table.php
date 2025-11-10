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
            // Cambiamos la columna de boolean a integer
            // default(0) = 0 recordatorios enviados
            $table->integer('reminder_sent')->default(0)->comment('Contador de recordatorios enviados')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Vuelve a dejarlo como estaba (boolean)
            $table->boolean('reminder_sent')->default(false)->change();
        });
    }
};