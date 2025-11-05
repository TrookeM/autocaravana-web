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
        // 1. Borrar tablas antiguas si existen (para que sea repetible)
        Schema::dropIfExists('booking_inventory_item');
        Schema::dropIfExists('campervan_inventory_item');
        Schema::dropIfExists('booking_extra');
        Schema::dropIfExists('extras');

        // 2. MODIFICAR 'inventory_items' PARA QUE SEA EL CATÁLOGO GLOBAL
        // Quitamos todos los campos que no sean globales.
        Schema::table('inventory_items', function (Blueprint $table) {
            
            if (Schema::hasColumn('inventory_items', 'campervan_id')) {
                $table->dropForeign(['campervan_id']);
                $table->dropColumn('campervan_id');
            }
            if (Schema::hasColumn('inventory_items', 'quantity')) {
                $table->dropColumn('quantity'); // La cantidad incluida depende de la camper
            }
            if (Schema::hasColumn('inventory_items', 'es_opcional')) {
                $table->dropColumn('es_opcional');
            }
            if (Schema::hasColumn('inventory_items', 'precio')) {
                $table->dropColumn('precio');
            }
            if (Schema::hasColumn('inventory_items', 'es_por_dia')) {
                $table->dropColumn('es_por_dia');
            }
            
            // Nos aseguramos de que 'total_stock' existe (lo añadimos si no)
            if (!Schema::hasColumn('inventory_items', 'total_stock')) {
                 $table->integer('total_stock')->default(1)->after('name');
            }
        });

        // 3. CREAR LA TABLA PIVOTE (Campervan <-> Inventario)
        // Aquí está la lógica de negocio que pediste
        Schema::create('campervan_inventory_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campervan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            
            // CAMPOS DE TU LÓGICA:
            $table->integer('quantity')->default(1); // Cantidad INCLUIDA (ej: 4 sillas)
            $table->boolean('es_opcional')->default(false); // ¿Es un extra de pago?
            $table->decimal('precio', 10, 2)->nullable(); // Precio si es opcional
            $table->boolean('es_por_dia')->default(false); // Tipo de precio
            
            $table->timestamps();
        });

        // 4. CREAR LA TABLA PIVOTE DE RESERVAS (no cambia)
        Schema::create('booking_inventory_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('precio_cobrado', 10, 2);
            $table->integer('quantity_booked')->default(1); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hacemos lo inverso
        Schema::dropIfExists('booking_inventory_item');
        Schema::dropIfExists('campervan_inventory_item');

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['total_stock']);
            $table->boolean('es_opcional')->default(false);
            $table->decimal('precio', 10, 2)->nullable();
            $table->boolean('es_por_dia')->default(false);
            $table->integer('quantity')->default(1);
            $table->foreignId('campervan_id')->nullable()->constrained()->cascadeOnDelete();
        });
        
        // (Recrear 'extras' por si acaso)
        Schema::create('extras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });
    }
};