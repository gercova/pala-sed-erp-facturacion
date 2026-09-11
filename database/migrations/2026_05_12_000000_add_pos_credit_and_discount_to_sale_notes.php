<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_notes', 'modo_pago')) {
                $table->integer('modo_pago')->default(1)->after('idcliente');
            }

            if (! Schema::hasColumn('sale_notes', 'monto_credito')) {
                $table->decimal('monto_credito', 18, 2)->default(0)->after('total');
            }

            if (! Schema::hasColumn('sale_notes', 'cuotas')) {
                $table->json('cuotas')->nullable()->after('monto_credito');
            }

            if (! Schema::hasColumn('sale_notes', 'payment_breakdown')) {
                $table->json('payment_breakdown')->nullable()->after('cuotas');
            }
        });

        Schema::table('detail_sale_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_sale_notes', 'descuento')) {
                $table->decimal('descuento', 18, 2)->default(0)->after('cantidad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_sale_notes', function (Blueprint $table) {
            if (Schema::hasColumn('detail_sale_notes', 'descuento')) {
                $table->dropColumn('descuento');
            }
        });

        Schema::table('sale_notes', function (Blueprint $table) {
            $columns = [];

            foreach (['modo_pago', 'monto_credito', 'cuotas', 'payment_breakdown'] as $column) {
                if (Schema::hasColumn('sale_notes', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
