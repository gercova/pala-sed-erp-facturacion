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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_interno')->nullable();
            $table->string('codigo_barras')->nullable();
            $table->string('descripcion');
            $table->integer('idunidad')->nullable();
            $table->integer('idcategoria')->nullable();
            $table->integer('igv');
            $table->integer('opcion')->nullable();
            $table->decimal('precio_compra', 18, 2)->nullable();
            $table->decimal('precio_venta', 18, 2)->nullable();
            $table->integer('stock_actual')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
