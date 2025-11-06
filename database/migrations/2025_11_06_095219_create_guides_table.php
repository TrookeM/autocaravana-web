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
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campervan_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->longText('content')->nullable(); // Permitimos nulo si prefieren subir solo PDF
            $table->string('pdf_path')->nullable(); // Opcional, como dice la tarea
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};
