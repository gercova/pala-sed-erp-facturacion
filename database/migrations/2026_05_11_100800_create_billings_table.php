<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idtipo_comprobante')->constrained('type_documents');
            $table->string('serie', 4);
            $table->string('correlativo', 8);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->time('hora');
            $table->foreignId('idcliente')->constrained('clients');
            $table->foreignId('idmoneda')->constrained('currencies');
            $table->foreignId('idpago')->constrained('pay_modes');
            $table->integer('modo_pago')->default(1);
            $table->string('sunat_forma_pago', 20)->default('Contado');
            $table->decimal('exonerada', 18, 2)->default(0);
            $table->decimal('inafecta', 18, 2)->default(0);
            $table->decimal('gravada', 18, 2)->default(0);
            $table->decimal('anticipo', 18, 2)->default(0);
            $table->decimal('igv', 18, 2)->default(0);
            $table->decimal('icbper', 18, 2)->default(0);
            $table->decimal('gratuita', 18, 2)->default(0);
            $table->decimal('otros_cargos', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->decimal('monto_credito', 18, 2)->default(0);
            $table->json('cuotas')->nullable();
            $table->json('payment_breakdown')->nullable();
            $table->string('observaciones')->nullable();
            $table->integer('cdr')->nullable();
            $table->boolean('anulado')->default(false);
            $table->foreignId('id_tipo_nota_credito')->nullable()->constrained('credit_note_types');
            $table->foreignId('idfactura_anular')->nullable()->constrained('billings');
            $table->string('motivo')->nullable();
            $table->integer('estado_cpe')->nullable();
            $table->longText('errores')->nullable();
            $table->string('nticket')->nullable();
            $table->foreignId('idusuario')->constrained('users');
            $table->foreignId('idarqueocaja')->nullable()->constrained('arching_cashes');
            $table->decimal('vuelto', 18, 2)->nullable();
            $table->string('qr')->nullable();
            $table->foreignId('idalmacen')->nullable()->constrained('warehouses');
            $table->timestamps();

            $table->unique(['idtipo_comprobante', 'serie', 'correlativo'], 'billings_document_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
