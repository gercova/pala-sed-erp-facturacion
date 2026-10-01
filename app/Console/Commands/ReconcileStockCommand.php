<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Inventory\StockService;
use Illuminate\Console\Command;

class ReconcileStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:reconcile {--product= : ID específico del producto a conciliar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Concilia y sincroniza products.stock_actual con la suma real de stock_products por almacén.';

    public function __construct(public StockService $stockService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $productId = $this->option('product');

        if ($productId) {
            $product = Product::find((int) $productId);
            if (! $product) {
                $this->error("Producto con ID {$productId} no encontrado.");

                return self::FAILURE;
            }

            $newStock = $this->stockService->syncProductStock($product->id);
            $this->info("Producto #{$product->id} ({$product->descripcion}) conciliado con éxito. Stock actual: ".($newStock ?? 'N/A (Servicio)'));

            return self::SUCCESS;
        }

        $this->info('Iniciando conciliación integral de stock físico...');

        $results = $this->stockService->reconcileAll();

        $rows = array_map(function ($item) {
            return [
                $item['id'],
                $item['descripcion'],
                number_format((float) $item['stock_almacenes'], 2, '.', ''),
                $item['stock_anterior'] !== null ? number_format((float) $item['stock_anterior'], 2, '.', '') : 'NULL',
                number_format((float) $item['stock_conciliado'], 2, '.', ''),
                $item['estado'] === 'corregido' ? '<fg=yellow>Corregido</>' : '<fg=green>OK</>',
            ];
        }, $results);

        $this->table(
            ['ID', 'Producto', 'Stock Almacenes', 'Stock Anterior', 'Stock Conciliado', 'Estado'],
            $rows
        );

        $corregidos = count(array_filter($results, fn ($r) => $r['estado'] === 'corregido'));
        $total = count($results);

        $this->info("Conciliación finalizada. Total productos físicos verificados: {$total}. Discrepancias corregidas: {$corregidos}.");

        return self::SUCCESS;
    }
}
