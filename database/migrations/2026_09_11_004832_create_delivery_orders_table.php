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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_orden', 30)->unique();
            $table->unsignedBigInteger('idcliente');
            $table->unsignedBigInteger('idrepartidor')->nullable();
            $table->unsignedBigInteger('idusuario_registro')->nullable();
            $table->unsignedBigInteger('idalmacen')->nullable();
            $table->unsignedBigInteger('idnotaventa')->nullable();
            $table->unsignedBigInteger('idfactura')->nullable();
            $table->string('origen', 30)->default('qr'); // qr, manual, pos, whatsapp
            $table->string('estado', 30)->default('pendiente'); // pendiente, en_ruta, entregado, reprogramado, cancelado
            $table->string('direccion_entrega', 255);
            $table->string('referencia', 255)->nullable();
            $table->string('telefono_contacto', 30)->nullable();
            $table->string('coordenadas', 100)->nullable();
            $table->date('fecha_programada');
            $table->string('franja_horaria', 50)->nullable(); // manana, tarde, noche, flexible
            $table->dateTime('fecha_entrega')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('metodo_pago', 50)->default('contraentrega'); // efectivo, yape, plin, transferencia, contraentrega, credito
            $table->string('estado_pago', 30)->default('pendiente'); // pendiente, pagado
            $table->integer('bidones_a_entregar')->default(0);
            $table->integer('bidones_vacios_recibidos')->default(0);
            $table->integer('bidones_danados_recibidos')->default(0);
            $table->decimal('cobro_envases_danados', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('idcliente')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('idrepartidor')->references('id')->on('users')->nullOnDelete();
            $table->foreign('idusuario_registro')->references('id')->on('users')->nullOnDelete();
            $table->foreign('idalmacen')->references('id')->on('warehouses')->nullOnDelete();
            $table->foreign('idnotaventa')->references('id')->on('sale_notes')->nullOnDelete();
            $table->foreign('idfactura')->references('id')->on('billings')->nullOnDelete();
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('iddelivery_order');
            $table->unsignedBigInteger('idproducto');
            $table->string('descripcion', 255);
            $table->string('tipo_item', 50)->default('recarga'); // recarga, con_envase, producto, bonificacion_fidelidad
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('iddelivery_order')->references('id')->on('delivery_orders')->cascadeOnDelete();
            $table->foreign('idproducto')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
    }
};
