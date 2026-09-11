<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->foreignId('id_tipo_nota_debito')
                ->nullable()
                ->after('id_tipo_nota_credito')
                ->constrained('debit_note_types');
        });
    }

    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_tipo_nota_debito');
        });
    }
};
