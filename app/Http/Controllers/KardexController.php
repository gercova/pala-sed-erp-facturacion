<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Buy;
use App\Models\Product;
use App\Models\SaleNote;
use App\Models\StockProduct;
use App\Models\TransferOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KardexController extends Controller
{
    public function index(Request $request)
    {
        $warehouseId = $this->currentWarehouseId();
        $selectedProductId = (int) $request->input('product_id', 0);
        $selectedType = trim((string) $request->input('movement_type', ''));
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        $products = Product::query()
            ->select('products.id', 'products.descripcion')
            ->join('stock_products', 'stock_products.idproducto', '=', 'products.id')
            ->when($warehouseId > 0, fn ($query) => $query->where('stock_products.idalmacen', $warehouseId))
            ->distinct()
            ->orderBy('products.descripcion')
            ->get();

        $movements = $this->buildMovements($warehouseId, $selectedProductId, $selectedType, $dateTo);
        $runningBalances = [];
        $rows = collect();

        foreach ($movements as $movement) {
            $productId = (int) $movement->product_id;
            $runningBalances[$productId] = round(($runningBalances[$productId] ?? 0) + (float) $movement->entrada - (float) $movement->salida, 2);
            $movement->saldo = $runningBalances[$productId];

            if ($dateFrom !== '' && (string) $movement->fecha < $dateFrom) {
                continue;
            }

            $rows->push($movement);
        }

        $summary = [
            'entries' => round((float) $rows->sum('entrada'), 2),
            'exits' => round((float) $rows->sum('salida'), 2),
            'closing_balance' => round(array_sum($runningBalances), 2),
            'movement_count' => $rows->count(),
        ];

        $currentWarehouse = $warehouseId > 0 ? Warehouse::query()->find($warehouseId) : null;

        $displayRows = $rows->sort(function ($left, $right) {
            $leftDate = (string) $left->fecha;
            $rightDate = (string) $right->fecha;

            if ($leftDate !== $rightDate) {
                return $rightDate <=> $leftDate;
            }

            $leftDocument = (string) $left->documento;
            $rightDocument = (string) $right->documento;
            if ($leftDocument !== $rightDocument) {
                return $rightDocument <=> $leftDocument;
            }

            return ((int) $right->row_id) <=> ((int) $left->row_id);
        })->values();

        return view('admin.kardex.index', [
            'rows' => $displayRows,
            'products' => $products,
            'summary' => $summary,
            'currentWarehouse' => $currentWarehouse,
            'filters' => [
                'product_id' => $selectedProductId,
                'movement_type' => $selectedType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'signo' => $this->signo_pais(),
        ]);
    }

    private function buildMovements(int $warehouseId, int $productId, string $movementType, string $dateTo): Collection
    {
        $queries = [
            $this->initialStockMovementsQuery($warehouseId, $productId),
            $this->purchaseMovementsQuery($warehouseId, $productId),
            $this->saleNoteMovementsQuery($warehouseId, $productId),
            $this->billingMovementsQuery($warehouseId, $productId),
            $this->transferOutMovementsQuery($warehouseId, $productId),
            $this->transferInMovementsQuery($warehouseId, $productId),
        ];

        $union = array_shift($queries);
        foreach ($queries as $query) {
            $union->unionAll($query);
        }

        $rows = DB::query()
            ->fromSub($union, 'kardex')
            ->when($movementType !== '', fn ($query) => $query->where('movement_type', $movementType))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('fecha', '<=', $dateTo))
            ->orderBy('product_name')
            ->orderBy('fecha')
            ->orderBy('documento')
            ->get();

        return collect($rows);
    }

    private function initialStockMovementsQuery(int $warehouseId, int $productId)
    {
        return StockProduct::query()
            ->selectRaw("
                stock_products.id as row_id,
                COALESCE(stock_products.fecha_registro, DATE(stock_products.created_at)) as fecha,
                'initial_stock' as movement_type,
                'Stock inicial' as movement_label,
                'SALDO INICIAL' as documento,
                stock_products.idproducto as product_id,
                products.descripcion as product_name,
                warehouses.descripcion as warehouse_name,
                stock_products.stock_entrada as entrada,
                0 as salida,
                stock_products.precio_compra as costo_unitario,
                (COALESCE(stock_products.stock_entrada, 0) * COALESCE(stock_products.precio_compra, 0)) as total_movimiento
            ")
            ->join('products', 'products.id', '=', 'stock_products.idproducto')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'stock_products.idalmacen')
            ->whereNotNull('stock_products.stock_entrada')
            ->where('stock_products.stock_entrada', '>', 0)
            ->when($warehouseId > 0, fn ($query) => $query->where('stock_products.idalmacen', $warehouseId))
            ->when($productId > 0, fn ($query) => $query->where('stock_products.idproducto', $productId))
            ->whereNotExists(function ($subquery) {
                $subquery->selectRaw('1')
                    ->from('transfer_orders')
                    ->join('detail_transfer_orders', 'detail_transfer_orders.idorden_traslado', '=', 'transfer_orders.id')
                    ->whereColumn('detail_transfer_orders.idproducto', 'stock_products.idproducto')
                    ->whereColumn('transfer_orders.idalmacen_receptor', 'stock_products.idalmacen')
                    ->where('transfer_orders.estado', 1)
                    ->whereRaw('transfer_orders.fecha_emision <= COALESCE(stock_products.fecha_registro, DATE(stock_products.created_at))');
            });
    }

    private function purchaseMovementsQuery(int $warehouseId, int $productId)
    {
        return Buy::query()
            ->selectRaw("
                detail_buys.id as row_id,
                buys.fecha_emision as fecha,
                'buy' as movement_type,
                'Compra' as movement_label,
                CONCAT(buys.serie, '-', buys.correlativo) as documento,
                detail_buys.idproducto as product_id,
                products.descripcion as product_name,
                warehouses.descripcion as warehouse_name,
                detail_buys.cantidad as entrada,
                0 as salida,
                detail_buys.precio_unitario as costo_unitario,
                detail_buys.precio_total as total_movimiento
            ")
            ->join('detail_buys', 'detail_buys.idcompra', '=', 'buys.id')
            ->join('products', 'products.id', '=', 'detail_buys.idproducto')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'detail_buys.idalmacen')
            ->where('buys.estado', 1)
            ->when($warehouseId > 0, fn ($query) => $query->where('detail_buys.idalmacen', $warehouseId))
            ->when($productId > 0, fn ($query) => $query->where('detail_buys.idproducto', $productId));
    }

    private function saleNoteMovementsQuery(int $warehouseId, int $productId)
    {
        return SaleNote::query()
            ->selectRaw("
                detail_sale_notes.id as row_id,
                sale_notes.fecha_emision as fecha,
                'sale_note' as movement_type,
                'Nota de venta' as movement_label,
                CONCAT(sale_notes.serie, '-', sale_notes.correlativo) as documento,
                detail_sale_notes.idproducto as product_id,
                products.descripcion as product_name,
                warehouses.descripcion as warehouse_name,
                0 as entrada,
                detail_sale_notes.cantidad as salida,
                detail_sale_notes.precio_unitario as costo_unitario,
                detail_sale_notes.precio_total as total_movimiento
            ")
            ->join('detail_sale_notes', 'detail_sale_notes.idnotaventa', '=', 'sale_notes.id')
            ->join('products', 'products.id', '=', 'detail_sale_notes.idproducto')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'detail_sale_notes.idalmacen')
            ->where('sale_notes.estado', '!=', 2)
            ->when($warehouseId > 0, fn ($query) => $query->where('detail_sale_notes.idalmacen', $warehouseId))
            ->when($productId > 0, fn ($query) => $query->where('detail_sale_notes.idproducto', $productId));
    }

    private function billingMovementsQuery(int $warehouseId, int $productId)
    {
        return Billing::query()
            ->selectRaw("
                detail_billings.id as row_id,
                billings.fecha_emision as fecha,
                'billing' as movement_type,
                type_documents.descripcion as movement_label,
                CONCAT(billings.serie, '-', billings.correlativo) as documento,
                detail_billings.idproducto as product_id,
                products.descripcion as product_name,
                warehouses.descripcion as warehouse_name,
                0 as entrada,
                detail_billings.cantidad as salida,
                detail_billings.precio_unitario as costo_unitario,
                detail_billings.precio_total as total_movimiento
            ")
            ->join('detail_billings', 'detail_billings.idfacturacion', '=', 'billings.id')
            ->join('products', 'products.id', '=', 'detail_billings.idproducto')
            ->join('type_documents', 'type_documents.id', '=', 'billings.idtipo_comprobante')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'billings.idalmacen')
            ->where('billings.anulado', false)
            ->when($warehouseId > 0, fn ($query) => $query->where('billings.idalmacen', $warehouseId))
            ->when($productId > 0, fn ($query) => $query->where('detail_billings.idproducto', $productId));
    }

    private function transferOutMovementsQuery(int $warehouseId, int $productId)
    {
        return TransferOrder::query()
            ->selectRaw("
                detail_transfer_orders.id as row_id,
                transfer_orders.fecha_emision as fecha,
                'transfer_out' as movement_type,
                'Traslado salida' as movement_label,
                CONCAT(transfer_orders.serie, '-', transfer_orders.correlativo) as documento,
                detail_transfer_orders.idproducto as product_id,
                products.descripcion as product_name,
                warehouses.descripcion as warehouse_name,
                0 as entrada,
                detail_transfer_orders.cantidad as salida,
                stock_products.precio_compra as costo_unitario,
                0 as total_movimiento
            ")
            ->join('detail_transfer_orders', 'detail_transfer_orders.idorden_traslado', '=', 'transfer_orders.id')
            ->join('products', 'products.id', '=', 'detail_transfer_orders.idproducto')
            ->leftJoin('stock_products', function ($join) {
                $join->on('stock_products.idproducto', '=', 'detail_transfer_orders.idproducto')
                    ->on('stock_products.idalmacen', '=', 'transfer_orders.idalmacen_despacho');
            })
            ->leftJoin('warehouses', 'warehouses.id', '=', 'transfer_orders.idalmacen_despacho')
            ->where('transfer_orders.estado', 1)
            ->when($warehouseId > 0, fn ($query) => $query->where('transfer_orders.idalmacen_despacho', $warehouseId))
            ->when($productId > 0, fn ($query) => $query->where('detail_transfer_orders.idproducto', $productId));
    }

    private function transferInMovementsQuery(int $warehouseId, int $productId)
    {
        return TransferOrder::query()
            ->selectRaw("
                detail_transfer_orders.id as row_id,
                transfer_orders.fecha_emision as fecha,
                'transfer_in' as movement_type,
                'Traslado ingreso' as movement_label,
                CONCAT(transfer_orders.serie, '-', transfer_orders.correlativo) as documento,
                detail_transfer_orders.idproducto as product_id,
                products.descripcion as product_name,
                warehouses.descripcion as warehouse_name,
                detail_transfer_orders.cantidad as entrada,
                0 as salida,
                stock_products.precio_compra as costo_unitario,
                0 as total_movimiento
            ")
            ->join('detail_transfer_orders', 'detail_transfer_orders.idorden_traslado', '=', 'transfer_orders.id')
            ->join('products', 'products.id', '=', 'detail_transfer_orders.idproducto')
            ->leftJoin('stock_products', function ($join) {
                $join->on('stock_products.idproducto', '=', 'detail_transfer_orders.idproducto')
                    ->on('stock_products.idalmacen', '=', 'transfer_orders.idalmacen_receptor');
            })
            ->leftJoin('warehouses', 'warehouses.id', '=', 'transfer_orders.idalmacen_receptor')
            ->where('transfer_orders.estado', 1)
            ->when($warehouseId > 0, fn ($query) => $query->where('transfer_orders.idalmacen_receptor', $warehouseId))
            ->when($productId > 0, fn ($query) => $query->where('detail_transfer_orders.idproducto', $productId));
    }
}
