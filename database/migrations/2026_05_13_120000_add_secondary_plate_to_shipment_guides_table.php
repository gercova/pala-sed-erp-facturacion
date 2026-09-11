<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipment_guides', function (Blueprint $table) {
            $table->string('placa_secundaria', 20)->nullable()->after('placa_vehiculo');
        });
    }

    public function down(): void
    {
        Schema::table('shipment_guides', function (Blueprint $table) {
            $table->dropColumn('placa_secundaria');
        });
    }
};
