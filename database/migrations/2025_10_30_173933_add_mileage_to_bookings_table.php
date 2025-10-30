<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Kilometraje al recoger la caravana
            $table->integer('km_salida')->nullable()->after('status');
            // Kilometraje al devolver la caravana
            $table->integer('km_llegada')->nullable()->after('km_salida');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['km_salida', 'km_llegada']);
        });
    }
};