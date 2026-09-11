<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (! Schema::hasColumn('businesses', 'gre_client_id')) {
                $table->string('gre_client_id', 100)->nullable()->after('servidor_sunat');
            }

            if (! Schema::hasColumn('businesses', 'gre_client_secret')) {
                $table->string('gre_client_secret', 150)->nullable()->after('gre_client_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('businesses', 'gre_client_id')) {
                $dropColumns[] = 'gre_client_id';
            }

            if (Schema::hasColumn('businesses', 'gre_client_secret')) {
                $dropColumns[] = 'gre_client_secret';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
