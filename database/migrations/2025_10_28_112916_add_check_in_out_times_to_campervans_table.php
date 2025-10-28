<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campervans', function (Blueprint $table) {
            // Añade la hora de check-in (ej: 15:00)
            $table->string('check_in_time')
                  ->default('15:00')
                  ->after('price_per_night'); // O después de la columna que prefieras

            // Añade la hora de check-out (ej: 12:00)
            $table->string('check_out_time')
                  ->default('12:00')
                  ->after('check_in_time');
        });
    }

    public function down(): void
    {
        Schema::table('campervans', function (Blueprint $table) {
            $table->dropColumn(['check_in_time', 'check_out_time']);
        });
    }
};