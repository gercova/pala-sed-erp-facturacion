<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_guide_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_guide_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('codigo', 60)->nullable();
            $table->string('descripcion', 255);
            $table->string('unidad', 20)->default('NIU');
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->timestamps();

            $table->foreign('shipment_guide_id')->references('id')->on('shipment_guides')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_guide_items');
    }
};
