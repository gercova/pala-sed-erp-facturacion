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
        Schema::create('loyalty_promotion_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idpromocion');
            $table->unsignedBigInteger('idusuario')->nullable();
            $table->integer('meta_compras_anterior')->nullable();
            $table->integer('meta_compras_nueva');
            $table->integer('bonificacion_anterior')->nullable();
            $table->integer('bonificacion_nueva');
            $table->unsignedBigInteger('idproducto_objetivo_anterior')->nullable();
            $table->unsignedBigInteger('idproducto_objetivo_nuevo')->nullable();
            $table->unsignedBigInteger('idproducto_bonificado_anterior')->nullable();
            $table->unsignedBigInteger('idproducto_bonificado_nuevo')->nullable();
            $table->boolean('activo_anterior')->nullable();
            $table->boolean('activo_nuevo')->default(true);
            $table->string('motivo', 255)->nullable();
            $table->timestamps();

            $table->foreign('idpromocion')->references('id')->on('loyalty_promotions')->cascadeOnDelete();
            $table->foreign('idusuario')->references('id')->on('users')->nullOnDelete();
            $table->foreign('idproducto_objetivo_nuevo')->references('id')->on('products')->nullOnDelete();
            $table->foreign('idproducto_bonificado_nuevo')->references('id')->on('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_promotion_logs');
    }
};
