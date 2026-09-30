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
        Schema::table('businesses', function (Blueprint $table) {
            if (! Schema::hasColumn('businesses', 'auth_cliente_metodo')) {
                $table->string('auth_cliente_metodo', 20)->default('password')->after('servidor_sunat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (Schema::hasColumn('businesses', 'auth_cliente_metodo')) {
                $table->dropColumn('auth_cliente_metodo');
            }
        });
    }
};
