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
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('facturacion_automatica_delivery')->default(true)->after('instancia_wpp');
            $table->string('tipo_documento_delivery_defecto', 10)->default('auto')->after('facturacion_automatica_delivery');
            $table->decimal('boleta_umbral_identidad', 12, 2)->default(700.00)->after('tipo_documento_delivery_defecto');
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->string('sunat_status', 30)->default('pendiente')->after('estado_cpe');
            $table->unsignedInteger('sunat_intentos')->default(0)->after('sunat_status');
            $table->timestamp('sunat_ultimo_intento_at')->nullable()->after('sunat_intentos');
            $table->string('estado_whatsapp', 30)->default('pendiente')->after('sunat_ultimo_intento_at');
            $table->text('whatsapp_error')->nullable()->after('estado_whatsapp');
            $table->unsignedInteger('whatsapp_intentos')->default(0)->after('whatsapp_error');
            $table->timestamp('whatsapp_enviado_at')->nullable()->after('whatsapp_intentos');
        });

        Schema::table('sale_notes', function (Blueprint $table) {
            $table->string('estado_whatsapp', 30)->default('pendiente')->after('observaciones');
            $table->text('whatsapp_error')->nullable()->after('estado_whatsapp');
            $table->unsignedInteger('whatsapp_intentos')->default(0)->after('whatsapp_error');
            $table->timestamp('whatsapp_enviado_at')->nullable()->after('whatsapp_intentos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_notes', function (Blueprint $table) {
            $table->dropColumn([
                'estado_whatsapp',
                'whatsapp_error',
                'whatsapp_intentos',
                'whatsapp_enviado_at',
            ]);
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn([
                'sunat_status',
                'sunat_intentos',
                'sunat_ultimo_intento_at',
                'estado_whatsapp',
                'whatsapp_error',
                'whatsapp_intentos',
                'whatsapp_enviado_at',
            ]);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'facturacion_automatica_delivery',
                'tipo_documento_delivery_defecto',
                'boleta_umbral_identidad',
            ]);
        });
    }
};
