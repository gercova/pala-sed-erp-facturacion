<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idfacturacion')->constrained('billings')->cascadeOnDelete();
            $table->foreignId('idproducto')->constrained('products');
            $table->decimal('cantidad', 18, 2);
            $table->decimal('descuento', 18, 2)->default(0);
            $table->decimal('igv', 18, 2)->default(0);
            $table->decimal('icbper', 18, 2)->default(0);
            $table->decimal('factor_icbper', 18, 4)->nullable();
            $table->decimal('cantidad_bolsas', 18, 2)->default(0);
            $table->foreignId('id_afectacion_igv')->constrained('igv_type_affections');
            $table->decimal('precio_unitario', 18, 2);
            $table->decimal('valor_unitario', 18, 10)->default(0);
            $table->decimal('valor_total', 18, 2)->default(0);
            $table->decimal('precio_total', 18, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_billings');
    }
};
