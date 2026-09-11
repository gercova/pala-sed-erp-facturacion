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
        Schema::create('detail_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('idtipo_comprobante');
            $table->integer('idfactura');
            $table->integer('idpago');
            $table->decimal('monto', 16, 2)->nullable();
            $table->integer('idarqueocaja');
            $table->integer('estado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_payments');
    }
};
