<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();

            // Checklist Item 1: caravan_id
            $table->foreignId('campervan_id')->constrained()->onDelete('cascade');

            // Checklist Item 1: fecha
            $table->date('date');

            // Descripción: tipo de servicio
            $table->string('service_type');

            // Checklist Item 1: coste
            $table->decimal('cost', 10, 2)->nullable()->default(0.00);

            // Checklist Item 1: notas
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};