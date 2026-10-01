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
            $table->boolean('puntos_fidelidad_acumulados')->default(false)->after('motivo_liquidacion');
            $table->dateTime('fecha_acumulacion_fidelidad')->nullable()->after('puntos_fidelidad_acumulados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn(['puntos_fidelidad_acumulados', 'fecha_acumulacion_fidelidad']);
        });
    }
};
