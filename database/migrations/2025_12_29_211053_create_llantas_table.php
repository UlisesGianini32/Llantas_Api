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
        Schema::create('llantas', function (Blueprint $table) {
        $table->id();

        $table->string('sku')->unique();
        $table->string('marca');
        $table->string('medida');
        $table->string('descripcion');

        $table->decimal('costo', 10, 2);
        $table->decimal('precio_ML', 10, 2)->nullable();

        $table->string('title_familyname')->nullable();
        $table->string('MLM')->nullable(); // ID o código de MercadoLibre

        $table->integer('stock')->default(0);

        $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llantas');
    }
};
