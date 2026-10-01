<?php

namespace App\Services\Inventory;

use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\StockProduct;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    /**
     * Regla de costeo de compras soportadas:
     * - 'last_cost': Último costo de adquisición registrado en la compra.
     * - 'weighted_average': Costo promedio ponderado en base al stock existente y la nueva entrada.
     */
    public const COST_RULE_LAST = 'last_cost';

    public const COST_RULE_WEIGHTED_AVERAGE = 'weighted_average';

    /**
     * Verifica si un producto gestiona stock físico (opcion == 1).
     */
    public function isPhysicalProduct(Product|int $product): bool
    {
        if (is_int($product)) {
            $product = Product::find($product);
        }

        return $product !== null && (int) $product->opcion === 1;
    }

    /**
     * Incrementa stock en un almacén específico para un producto físico.
     * Los servicios (opcion != 1) no alteran el stock físico.
     */
    public function increaseStock(
        int $productId,
        int $warehouseId,
        float $quantity,
        string $movementType = 'entrada',
        ?string $documentType = null,
        ?string $documentNumber = null,
        ?float $unitCost = null,
        ?string $observations = null,
        ?int $userId = null
    ): ?StockProduct {
        if ($quantity <= 0) {
            return null;
        }

        $product = Product::find($productId);
        if (! $product || ! $this->isPhysicalProduct($product)) {
            return null;
        }

        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $unitCost) {
            $stockProduct = StockProduct::where('idproducto', $productId)
                ->where('idalmacen', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (! $stockProduct) {
                $stockProduct = StockProduct::create([
                    'idproducto' => $productId,
                    'idalmacen' => $warehouseId,
                    'stock_minimo' => 10,
                    'stock_actual' => $quantity,
                    'stock_entrada' => $quantity,
                    'precio_compra' => $unitCost ?? 0,
                    'precio_venta' => 0,
                    'fecha_registro' => now()->toDateString(),
                ]);
            } else {
                $stockProduct->stock_actual = (float) $stockProduct->stock_actual + $quantity;
                if ($unitCost !== null && $unitCost > 0) {
                    $stockProduct->precio_compra = $unitCost;
                }
                $stockProduct->save();
            }

            $this->syncProductStock($productId);

            return $stockProduct;
        });
    }

    /**
     * Decrementa stock en un almacén específico para un producto físico.
     * Garantiza que el stock no caiga por debajo de cero salvo que se indique explícitamente.
     */
    public function decreaseStock(
        int $productId,
        int $warehouseId,
        float $quantity,
        string $movementType = 'salida',
        ?string $documentType = null,
        ?string $documentNumber = null,
        ?float $unitCost = null,
        ?string $observations = null,
        ?int $userId = null,
        bool $allowNegative = false
    ): ?StockProduct {
        if ($quantity <= 0) {
            return null;
        }

        $product = Product::find($productId);
        if (! $product || ! $this->isPhysicalProduct($product)) {
            return null;
        }

        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $allowNegative) {
            $stockProduct = StockProduct::where('idproducto', $productId)
                ->where('idalmacen', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (! $stockProduct) {
                $stockProduct = StockProduct::create([
                    'idproducto' => $productId,
                    'idalmacen' => $warehouseId,
                    'stock_minimo' => 10,
                    'stock_actual' => 0,
                    'stock_entrada' => 0,
                    'precio_compra' => 0,
                    'precio_venta' => 0,
                    'fecha_registro' => now()->toDateString(),
                ]);
            }

            $currentStock = (float) $stockProduct->stock_actual;
            $newStock = $allowNegative ? ($currentStock - $quantity) : max(0.0, $currentStock - $quantity);

            $stockProduct->stock_actual = $newStock;
            $stockProduct->save();

            $this->syncProductStock($productId);

            return $stockProduct;
        });
    }

    /**
     * Registra un ajuste de inventario manual o por conteo físico.
     * Registra la auditoría en la tabla inventory_adjustments y sincroniza stocks.
     */
    public function adjustStock(
        int $productId,
        int $warehouseId,
        float $newStock,
        string $reason,
        string $adjustmentType = 'manual',
        ?int $userId = null,
        ?string $referenceDocument = null
    ): InventoryAdjustment {
        $product = Product::findOrFail($productId);
        if (! $this->isPhysicalProduct($product)) {
            throw new InvalidArgumentException('Los servicios no manejan stock físico ni ajustes de almacén.');
        }

        if ($newStock < 0) {
            throw new InvalidArgumentException('El nuevo stock no puede ser negativo.');
        }

        return DB::transaction(function () use ($product, $warehouseId, $newStock, $reason, $adjustmentType, $userId, $referenceDocument) {
            $stockProduct = StockProduct::where('idproducto', $product->id)
                ->where('idalmacen', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (! $stockProduct) {
                $stockProduct = StockProduct::create([
                    'idproducto' => $product->id,
                    'idalmacen' => $warehouseId,
                    'stock_minimo' => 10,
                    'stock_actual' => 0,
                    'stock_entrada' => 0,
                    'precio_compra' => $product->precio_compra ?? 0,
                    'precio_venta' => $product->precio_venta ?? 0,
                    'fecha_registro' => now()->toDateString(),
                ]);
            }

            $stockAnterior = (float) $stockProduct->stock_actual;
            $diferencia = round($newStock - $stockAnterior, 2);

            $stockProduct->stock_actual = $newStock;
            $stockProduct->save();

            $this->syncProductStock($product->id);

            return InventoryAdjustment::create([
                'idalmacen' => $warehouseId,
                'idproducto' => $product->id,
                'idusuario' => $userId,
                'tipo_ajuste' => $adjustmentType,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $newStock,
                'cantidad_diferencia' => $diferencia,
                'costo_unitario' => (float) ($stockProduct->precio_compra ?? $product->precio_compra ?? 0),
                'motivo' => $reason,
                'documento_referencia' => $referenceDocument,
            ]);
        });
    }

    /**
     * Registra el ingreso por compra y actualiza el costo de adquisición
     * según la regla de negocio (Último Costo / Last Cost por defecto).
     */
    public function recordPurchase(
        int $productId,
        int $warehouseId,
        float $quantity,
        float $purchaseCost,
        string $documentNumber,
        string $costRule = self::COST_RULE_LAST,
        ?int $userId = null
    ): void {
        $product = Product::find($productId);
        if (! $product || ! $this->isPhysicalProduct($product)) {
            // Servicios comprados no afectan inventario
            return;
        }

        DB::transaction(function () use ($product, $warehouseId, $quantity, $purchaseCost, $costRule) {
            $stockProduct = StockProduct::where('idproducto', $product->id)
                ->where('idalmacen', $warehouseId)
                ->lockForUpdate()
                ->first();

            $currentStock = $stockProduct ? (float) $stockProduct->stock_actual : 0.0;
            $currentCost = $stockProduct ? (float) $stockProduct->precio_compra : (float) $product->precio_compra;

            $newCost = $purchaseCost;
            if ($costRule === self::COST_RULE_WEIGHTED_AVERAGE && ($currentStock + $quantity) > 0) {
                $newCost = round((($currentStock * $currentCost) + ($quantity * $purchaseCost)) / ($currentStock + $quantity), 4);
            }

            if (! $stockProduct) {
                StockProduct::create([
                    'idproducto' => $product->id,
                    'idalmacen' => $warehouseId,
                    'stock_minimo' => 10,
                    'stock_actual' => $quantity,
                    'stock_entrada' => $quantity,
                    'precio_compra' => $newCost,
                    'precio_venta' => $product->precio_venta ?? 0,
                    'fecha_registro' => now()->toDateString(),
                ]);
            } else {
                $stockProduct->stock_actual = $currentStock + $quantity;
                $stockProduct->precio_compra = $newCost;
                $stockProduct->save();
            }

            $product->update([
                'precio_compra' => $newCost,
            ]);

            $this->syncProductStock($product->id);
        });
    }

    /**
     * Transfiere stock entre almacenes de forma atómica.
     */
    public function transferStock(
        int $productId,
        int $fromWarehouseId,
        int $toWarehouseId,
        float $quantity,
        ?string $documentNumber = null,
        ?int $userId = null
    ): void {
        $product = Product::findOrFail($productId);
        if (! $this->isPhysicalProduct($product)) {
            throw new InvalidArgumentException('No se pueden transferir productos de tipo servicio.');
        }

        DB::transaction(function () use ($product, $fromWarehouseId, $toWarehouseId, $quantity) {
            $sourceStock = StockProduct::where('idproducto', $product->id)
                ->where('idalmacen', $fromWarehouseId)
                ->lockForUpdate()
                ->first();

            if (! $sourceStock || (float) $sourceStock->stock_actual < $quantity) {
                $disp = $sourceStock ? (float) $sourceStock->stock_actual : 0;
                throw new InvalidArgumentException("Stock insuficiente en almacén de despacho para {$product->descripcion}. Disponible: {$disp}, Requerido: {$quantity}");
            }

            $sourceStock->stock_actual = (float) $sourceStock->stock_actual - $quantity;
            $sourceStock->save();

            $destStock = StockProduct::where('idproducto', $product->id)
                ->where('idalmacen', $toWarehouseId)
                ->lockForUpdate()
                ->first();

            if (! $destStock) {
                StockProduct::create([
                    'idproducto' => $product->id,
                    'idalmacen' => $toWarehouseId,
                    'stock_minimo' => $sourceStock->stock_minimo ?? 10,
                    'stock_actual' => $quantity,
                    'stock_entrada' => $quantity,
                    'precio_compra' => $sourceStock->precio_compra,
                    'precio_venta' => $sourceStock->precio_venta,
                    'fecha_registro' => now()->toDateString(),
                ]);
            } else {
                $destStock->stock_actual = (float) $destStock->stock_actual + $quantity;
                $destStock->save();
            }

            $this->syncProductStock($product->id);
        });
    }

    /**
     * Sincroniza el valor derivado products.stock_actual a partir de stock_products.
     * Retorna el nuevo stock sumado.
     */
    public function syncProductStock(int $productId): ?float
    {
        $product = Product::find($productId);
        if (! $product) {
            return null;
        }

        if (! $this->isPhysicalProduct($product)) {
            $product->update(['stock_actual' => null]);

            return null;
        }

        $total = (float) StockProduct::where('idproducto', $productId)->sum('stock_actual');
        $product->update(['stock_actual' => $total]);

        return $total;
    }

    /**
     * Concilia todos los productos físicos del sistema asegurando
     * que SUM(stock_products.stock_actual) == products.stock_actual.
     */
    public function reconcileAll(): array
    {
        $results = [];
        $products = Product::where('opcion', 1)->get();

        foreach ($products as $product) {
            $previousStock = $product->stock_actual;
            $calculatedStock = (float) StockProduct::where('idproducto', $product->id)->sum('stock_actual');
            $discrepancy = round((float) $previousStock - $calculatedStock, 2);

            if ($discrepancy !== 0.0 || $previousStock === null) {
                $product->update(['stock_actual' => $calculatedStock]);
                $status = 'corregido';
            } else {
                $status = 'ok';
            }

            $results[] = [
                'id' => $product->id,
                'descripcion' => $product->descripcion,
                'stock_anterior' => $previousStock,
                'stock_almacenes' => $calculatedStock,
                'stock_conciliado' => $calculatedStock,
                'diferencia' => $discrepancy,
                'estado' => $status,
            ];
        }

        // Servicios no deben tener stock_actual
        Product::where('opcion', '!=', 1)->update(['stock_actual' => null]);

        return $results;
    }
}
