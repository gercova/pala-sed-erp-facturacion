<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los clientes registrados desde el portal no tienen caja ni almacén asignados.
     * Se vuelven nullable para no depender de un valor por defecto.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('idcaja')->nullable()->change();
            $table->unsignedBigInteger('idalmacen')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertir solo si no hay NULLs en la columna (prevención)
            $table->unsignedBigInteger('idcaja')->nullable(false)->change();
            $table->unsignedBigInteger('idalmacen')->nullable(false)->change();
        });
    }
};
