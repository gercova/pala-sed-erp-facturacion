<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 2)->unique();
            $table->string('descripcion');
            $table->string('descripcion_documento');
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_document_types');
    }
};
