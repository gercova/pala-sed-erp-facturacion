<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('idcliente')->nullable()->after('idalmacen');
            $table->string('tipo', 20)->default('admin')->after('idcliente'); // admin | cliente
            $table->foreign('idcliente')->references('id')->on('clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['idcliente']);
            $table->dropColumn(['idcliente', 'tipo']);
        });
    }
};
