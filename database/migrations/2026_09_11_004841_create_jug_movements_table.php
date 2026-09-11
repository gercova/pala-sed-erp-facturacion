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
        if (!Schema::hasColumn('clients', 'saldo_envases')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->integer('saldo_envases')->default(0)->after('telefono');
                $table->string('referencia', 255)->nullable()->after('direccion');
                $table->string('coordenadas', 100)->nullable()->after('referencia');
            });
        }

        Schema::create('jug_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idcliente');
            $table->unsignedBigInteger('iddelivery_order')->nullable();
            $table->unsignedBigInteger('idusuario')->nullable();
            $table->unsignedBigInteger('idalmacen')->nullable();
            $table->string('tipo_movimiento', 50); // entrega_recarga, nuevo_comodato, devolucion_intactos, devolucion_danados, ajuste
            $table->integer('entregados_llenos')->default(0);
            $table->integer('devueltos_intactos')->default(0);
            $table->integer('devueltos_danados')->default(0);
            $table->decimal('costo_dano', 12, 2)->default(0);
            $table->integer('saldo_anterior')->default(0);
            $table->integer('saldo_nuevo')->default(0);
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha');
            $table->timestamps();

            $table->foreign('idcliente')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('iddelivery_order')->references('id')->on('delivery_orders')->nullOnDelete();
            $table->foreign('idusuario')->references('id')->on('users')->nullOnDelete();
            $table->foreign('idalmacen')->references('id')->on('warehouses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jug_movements');

        if (Schema::hasColumn('clients', 'saldo_envases')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn(['saldo_envases', 'referencia', 'coordenadas']);
            });
        }
    }
};
