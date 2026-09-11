<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('codigo_sunat', 16)->nullable()->after('codigo_barras');
            $table->foreignId('idcodigo_igv')->nullable()->after('igv')->constrained('igv_type_affections');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('idcodigo_igv');
            $table->dropColumn('codigo_sunat');
        });
    }
};
