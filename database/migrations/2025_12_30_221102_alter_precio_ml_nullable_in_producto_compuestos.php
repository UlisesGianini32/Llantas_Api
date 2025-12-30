<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('producto_compuestos', function (Blueprint $table) {
            $table->decimal('precio_ML', 10, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('producto_compuestos', function (Blueprint $table) {
            $table->decimal('precio_ML', 10, 2)->nullable(false)->change();
        });
    }
};
