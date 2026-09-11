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
        Schema::create('stock_products', function (Blueprint $table) {
            $table->id();
            $table->integer('idalmacen');
            $table->integer('idproducto');
            $table->integer('stock_minimo')->nullable();
            $table->integer('stock_actual')->nullable();
            $table->decimal('precio_compra', 18, 2);
            $table->decimal('precio_venta', 18, 2);
            $table->date('fecha_registro')->nullable();
            $table->integer('stock_entrada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_products');
    }
};
