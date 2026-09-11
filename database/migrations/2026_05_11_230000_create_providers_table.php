<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iddoc')->nullable()->constrained('identity_document_types');
            $table->string('nro_documento');
            $table->string('nombres');
            $table->string('direccion');
            $table->string('codigo_pais', 2)->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
