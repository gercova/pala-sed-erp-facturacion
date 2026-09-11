<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('ruc', 11)->nullable()->after('id');
            $table->string('nombre_comercial')->nullable()->after('razon_social');
            $table->string('codigo_pais', 2)->nullable()->after('direccion');
            $table->string('ubigeo', 6)->nullable()->after('codigo_pais');
            $table->string('urbanizacion')->nullable()->after('ubigeo');
            $table->string('local')->nullable()->after('urbanizacion');
            $table->string('url_api')->nullable()->after('telefono');
            $table->date('vencimiento_certificado')->nullable()->after('url_api');
            $table->string('usuario_sunat')->nullable()->after('vencimiento_certificado');
            $table->string('clave_sunat')->nullable()->after('usuario_sunat');
            $table->string('clave_certificado')->nullable()->after('clave_sunat');
            $table->string('certificado')->nullable()->after('clave_certificado');
            $table->string('servidor_sunat', 1)->nullable()->after('certificado');
            $table->string('instancia_wpp')->nullable()->after('servidor_sunat');
            $table->boolean('cobrar_igv')->default(false)->after('instancia_wpp');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'ruc',
                'nombre_comercial',
                'codigo_pais',
                'ubigeo',
                'urbanizacion',
                'local',
                'url_api',
                'vencimiento_certificado',
                'usuario_sunat',
                'clave_sunat',
                'clave_certificado',
                'certificado',
                'servidor_sunat',
                'instancia_wpp',
                'cobrar_igv',
            ]);
        });
    }
};
