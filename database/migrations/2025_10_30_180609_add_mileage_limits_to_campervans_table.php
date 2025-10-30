<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campervans', function (Blueprint $table) {
            // Límite de KM. Nulo o 0 significa ilimitado.
            $table->unsignedInteger('km_limit')->nullable()->default(null)->after('price_per_night');

            // Precio por KM extra (ej: 0.50)
            $table->decimal('price_per_extra_km', 8, 2)->nullable()->default(null)->after('km_limit');
        });
    }

    public function down(): void
    {
        Schema::table('campervans', function (Blueprint $table) {
            $table->dropColumn(['km_limit', 'price_per_extra_km']);
        });
    }
};