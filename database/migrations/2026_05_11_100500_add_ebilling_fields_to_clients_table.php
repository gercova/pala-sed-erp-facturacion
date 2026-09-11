<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('iddoc')->nullable()->after('id')->constrained('identity_document_types');
            $table->string('codigo_pais', 2)->nullable()->after('direccion');
            $table->string('ubigeo', 6)->nullable()->after('codigo_pais');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('iddoc');
            $table->dropColumn(['codigo_pais', 'ubigeo']);
        });
    }
};
