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
        Schema::create('loyalty_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('meta_compras')->default(4); // e.g. 4
            $table->integer('bonificacion')->default(1);  // e.g. 1 gratis
            $table->unsignedBigInteger('idproducto_objetivo')->nullable();
            $table->unsignedBigInteger('idproducto_bonificado')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->foreign('idproducto_objetivo')->references('id')->on('products')->nullOnDelete();
            $table->foreign('idproducto_bonificado')->references('id')->on('products')->nullOnDelete();
        });

        Schema::create('client_loyalty', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idcliente');
            $table->unsignedBigInteger('idpromocion');
            $table->integer('compras_acumuladas')->default(0);
            $table->integer('premios_reclamados')->default(0);
            $table->dateTime('ultimo_canje')->nullable();
            $table->timestamps();

            $table->foreign('idcliente')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('idpromocion')->references('id')->on('loyalty_promotions')->cascadeOnDelete();
            $table->unique(['idcliente', 'idpromocion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_loyalty');
        Schema::dropIfExists('loyalty_promotions');
    }
};
