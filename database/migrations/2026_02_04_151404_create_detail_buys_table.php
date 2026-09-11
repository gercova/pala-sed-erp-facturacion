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
        Schema::create('detail_buys', function (Blueprint $table) {
            $table->id();
            $table->integer('idcompra');
            $table->integer('idproducto');
            $table->decimal('cantidad', 18, 2);
            $table->decimal('descuento', 18, 2);
            $table->decimal('igv', 18, 2);
            $table->integer('id_afectacion_igv')->nullable();
            $table->decimal('precio_unitario', 18, 2);
            $table->decimal('precio_total', 18, 2);
            $table->integer('idalmacen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_buys');
    }
};
