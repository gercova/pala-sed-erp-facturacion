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
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('idarqueocaja')->nullable()->after('idalmacen');
            $table->dateTime('liquidado_at')->nullable()->after('fecha_entrega');
            $table->string('motivo_liquidacion', 50)->nullable()->after('estado_pago');

            $table->foreign('idarqueocaja')->references('id')->on('arching_cashes')->nullOnDelete();
        });

        Schema::create('delivery_order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('iddelivery_order');
            $table->unsignedBigInteger('idusuario')->nullable();
            $table->string('estado_anterior', 30);
            $table->string('estado_nuevo', 30);
            $table->string('motivo', 100)->nullable();
            $table->text('notas')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('iddelivery_order')->references('id')->on('delivery_orders')->cascadeOnDelete();
            $table->foreign('idusuario')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_status_logs');

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['idarqueocaja']);
            $table->dropColumn(['idarqueocaja', 'liquidado_at', 'motivo_liquidacion']);
        });
    }
};
