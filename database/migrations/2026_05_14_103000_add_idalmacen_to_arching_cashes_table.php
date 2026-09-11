<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arching_cashes', function (Blueprint $table) {
            $table->unsignedBigInteger('idalmacen')->nullable()->after('idusuario');
        });

        DB::statement('
            UPDATE arching_cashes
            INNER JOIN users ON users.id = arching_cashes.idusuario
            SET arching_cashes.idalmacen = users.idalmacen
            WHERE arching_cashes.idalmacen IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('arching_cashes', function (Blueprint $table) {
            $table->dropColumn('idalmacen');
        });
    }
};
