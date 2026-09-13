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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 11)->nullable();
            $table->string('razon_social')->nullable();
            $table->string('nombre_comercial')->nullable();
            $table->string('logo')->nullable();
            $table->string('yape_qr')->nullable();
            $table->string('plin_qr')->nullable();
            $table->integer('idpais')->nullable();
            $table->string('direccion')->nullable();
            $table->string('codigo_pais', 2)->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->string('urbanizacion')->nullable();
            $table->string('local')->nullable();
            $table->string('telefono')->nullable();
            $table->string('url_api')->nullable();
            $table->date('vencimiento_certificado')->nullable();
            $table->string('usuario_sunat')->nullable();
            $table->string('clave_sunat')->nullable();
            $table->string('clave_certificado')->nullable();
            $table->string('certificado')->nullable();
            $table->string('servidor_sunat', 1)->nullable();
            $table->string('gre_client_id', 100)->nullable();
            $table->string('gre_client_secret', 150)->nullable();
            $table->string('instancia_wpp')->nullable();
            $table->boolean('cobrar_igv')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
