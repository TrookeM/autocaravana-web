<?php
// database/migrations/XXXX_XX_XX_XXXXXX_add_image_fields_to_campervans_table.php

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
        Schema::table('campervans', function (Blueprint $table) {
            // Imagen principal (para la lista y la principal en detalle)
            $table->string('main_image_path')->nullable()->after('price_per_night');
            
            // Rutas de imágenes secundarias (almacenadas como JSON)
            $table->text('secondary_images_json')->nullable()->after('main_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campervans', function (Blueprint $table) {
            $table->dropColumn('main_image_path');
            $table->dropColumn('secondary_images_json');
        });
    }
};