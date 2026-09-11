<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_guides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idcliente');
            $table->unsignedBigInteger('idalmacen')->nullable();
            $table->unsignedBigInteger('idtipo_comprobante')->nullable();
            $table->string('serie', 10);
            $table->string('correlativo', 20);
            $table->date('fecha_emision');
            $table->date('fecha_inicio_traslado');
            $table->string('motivo_traslado_codigo', 4);
            $table->string('motivo_traslado_descripcion', 150);
            $table->string('modo_transporte', 2)->default('02');
            $table->decimal('peso_total', 12, 3)->default(0);
            $table->string('unidad_peso', 3)->default('KGM');
            $table->string('partida_ubigeo', 6)->nullable();
            $table->string('partida_direccion', 255)->nullable();
            $table->string('llegada_ubigeo', 6)->nullable();
            $table->string('llegada_direccion', 255)->nullable();
            $table->string('transportista_documento_tipo', 4)->nullable();
            $table->string('transportista_documento', 20)->nullable();
            $table->string('transportista_nombre', 255)->nullable();
            $table->string('conductor_documento_tipo', 4)->nullable();
            $table->string('conductor_documento', 20)->nullable();
            $table->string('conductor_nombre', 255)->nullable();
            $table->string('placa_vehiculo', 20)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('xml', 255)->nullable();
            $table->string('cdr', 255)->nullable();
            $table->tinyInteger('estado_cpe')->default(0);
            $table->string('ticket', 255)->nullable();
            $table->text('errores')->nullable();
            $table->timestamps();

            $table->foreign('idcliente')->references('id')->on('clients');
            $table->foreign('idalmacen')->references('id')->on('warehouses');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_guides');
    }
};
