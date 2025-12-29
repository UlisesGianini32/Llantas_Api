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
        Schema::create('producto_compuestos', function (Blueprint $table) {
        $table->id();

        // Relación con llantas
        $table->foreignId('llanta_id')
              ->constrained('llantas')
              ->cascadeOnDelete();

        // Identificación del producto compuesto
        $table->string('sku')->unique();
        $table->string('tipo'); // par | juego4

        // Cantidad que representa el compuesto (2, 4, etc.)
        $table->integer('stock');

        // Costos y precios
        $table->decimal('costo', 10, 2);
        $table->decimal('precio_ML', 10, 2)->nullable();

        // MercadoLibre
        $table->string('title_familyname')->nullable();
        $table->string('MLM')->nullable(); // ID publicación MercadoLibre

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_compuestos');
    }
};
