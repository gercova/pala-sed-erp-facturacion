<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de ajustes de inventario para trazabilidad auditable y Kardex
        if (! Schema::hasTable('inventory_adjustments')) {
            Schema::create('inventory_adjustments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('idalmacen')->constrained('warehouses')->cascadeOnDelete();
                $table->foreignId('idproducto')->constrained('products')->cascadeOnDelete();
                $table->foreignId('idusuario')->nullable()->constrained('users')->nullOnDelete();
                $table->string('tipo_ajuste', 50)->default('manual'); // manual, conteo_fisico, merma, ingreso_extra, correccion
                $table->decimal('stock_anterior', 18, 2)->default(0);
                $table->decimal('stock_nuevo', 18, 2)->default(0);
                $table->decimal('cantidad_diferencia', 18, 2)->default(0);
                $table->decimal('costo_unitario', 18, 2)->nullable();
                $table->string('motivo', 500);
                $table->string('documento_referencia', 50)->nullable();
                $table->timestamps();

                $table->index(['idalmacen', 'idproducto']);
                $table->index('created_at');
            });
        }

        // 2. Prevenir duplicados de compras en nivel de base de datos
        // idproveedor + idtipo_comprobante + serie + correlativo
        if (Schema::hasTable('buys')) {
            // Verificar si el índice no existe ya antes de crearlo
            $indexExists = collect(DB::select("SHOW INDEXES FROM buys WHERE Key_name = 'buys_provider_doc_unique'"))->isNotEmpty();
            if (! $indexExists) {
                Schema::table('buys', function (Blueprint $table) {
                    $table->unique(
                        ['idproveedor', 'idtipo_comprobante', 'serie', 'correlativo'],
                        'buys_provider_doc_unique'
                    );
                });
            }
        }

        // 3. Reconciliación de datos inicial: sincronizar products.stock_actual con SUM(stock_products.stock_actual)
        // La fuente de la verdad por almacén es stock_products.
        if (Schema::hasTable('products') && Schema::hasTable('stock_products')) {
            DB::statement('
                UPDATE products p
                SET p.stock_actual = (
                    SELECT COALESCE(SUM(sp.stock_actual), 0)
                    FROM stock_products sp
                    WHERE sp.idproducto = p.id
                )
                WHERE p.opcion = 1
            ');

            DB::statement('
                UPDATE products p
                SET p.stock_actual = NULL
                WHERE p.opcion != 1
            ');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('buys')) {
            $indexExists = collect(DB::select("SHOW INDEXES FROM buys WHERE Key_name = 'buys_provider_doc_unique'"))->isNotEmpty();
            if ($indexExists) {
                Schema::table('buys', function (Blueprint $table) {
                    $table->dropUnique('buys_provider_doc_unique');
                });
            }
        }

        Schema::dropIfExists('inventory_adjustments');
    }
};
