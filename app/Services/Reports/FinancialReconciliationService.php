<?php

namespace App\Services\Reports;

use App\Models\Billing;
use App\Models\SaleNote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialReconciliationService
{
    /**
     * Obtiene los IDs de notas de venta que fueron convertidas a comprobante formal (Factura/Boleta)
     * en repartos o POS, para evitar duplicación en los reportes financieros.
     *
     * @return array<int>
     */
    public function getConvertedSaleNoteIds(): array
    {
        return DB::table('delivery_orders')
            ->whereNotNull('idnotaventa')
            ->whereNotNull('idfactura')
            ->pluck('idnotaventa')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Resumen consolidado de ventas.
     * Regla:
     * - Comprobantes formales: Factura ('01') y Boleta ('03') suman, ND ('08') suma, NC ('07') resta.
     * - Notas de venta: solo válidas (estado=1) y NO convertidas a comprobante formal.
     */
    public function getSalesSummary(?string $startDate = null, ?string $endDate = null, ?int $warehouseId = null): array
    {
        $convertedSaleNoteIds = $this->getConvertedSaleNoteIds();

        $billingsQuery = Billing::query()
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->where('billings.anulado', false)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('billings.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, fn ($q) => $q->where('billings.idalmacen', $warehouseId));

        $billingsTotals = (clone $billingsQuery)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type_documents.codigo IN ('01', '03') THEN billings.total ELSE 0 END), 0) as total_facturas_boletas,
                COALESCE(SUM(CASE WHEN type_documents.codigo = '07' THEN billings.total ELSE 0 END), 0) as total_notas_credito,
                COALESCE(SUM(CASE WHEN type_documents.codigo = '08' THEN billings.total ELSE 0 END), 0) as total_notas_debito,
                COALESCE(SUM(CASE WHEN type_documents.codigo = '07' THEN -billings.total ELSE billings.total END), 0) as total_documentos_neto,
                COALESCE(SUM(CASE WHEN type_documents.codigo = '07' THEN -billings.igv ELSE billings.igv END), 0) as igv_documentos_neto,
                COUNT(billings.id) as cantidad_documentos
            ")
            ->first();

        $saleNotesQuery = SaleNote::query()
            ->where('sale_notes.estado', 1)
            ->when(! empty($convertedSaleNoteIds), fn ($q) => $q->whereNotIn('sale_notes.id', $convertedSaleNoteIds))
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('sale_notes.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, function ($q) use ($warehouseId) {
                $q->whereExists(function ($ex) use ($warehouseId) {
                    $ex->select(DB::raw(1))
                        ->from('detail_sale_notes')
                        ->whereColumn('detail_sale_notes.idnotaventa', 'sale_notes.id')
                        ->where('detail_sale_notes.idalmacen', $warehouseId);
                });
            });

        $saleNotesTotals = (clone $saleNotesQuery)
            ->selectRaw('
                COALESCE(SUM(sale_notes.total), 0) as total_notas_venta,
                COALESCE(SUM(sale_notes.igv), 0) as igv_notas_venta,
                COUNT(sale_notes.id) as cantidad_notas_venta
            ')
            ->first();

        $convertedNotesTotals = SaleNote::query()
            ->whereIn('id', $convertedSaleNoteIds)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('fecha_emision', [$startDate, $endDate]))
            ->selectRaw('COALESCE(SUM(total), 0) as total, COUNT(id) as cantidad')
            ->first();

        $totalDocumentosNeto = (float) ($billingsTotals->total_documentos_neto ?? 0);
        $totalNotasVenta = (float) ($saleNotesTotals->total_notas_venta ?? 0);
        $totalVentas = round($totalDocumentosNeto + $totalNotasVenta, 2);
        $totalImpuestos = round((float) ($billingsTotals->igv_documentos_neto ?? 0) + (float) ($saleNotesTotals->igv_notas_venta ?? 0), 2);
        $totalTransacciones = (int) ($billingsTotals->cantidad_documentos ?? 0) + (int) ($saleNotesTotals->cantidad_notas_venta ?? 0);

        return [
            'total_sales' => $totalVentas,
            'total_taxes' => $totalImpuestos,
            'total_transactions' => $totalTransacciones,
            'total_documents_net' => round($totalDocumentosNeto, 2),
            'total_facturas_boletas' => round((float) ($billingsTotals->total_facturas_boletas ?? 0), 2),
            'total_credit_notes' => round((float) ($billingsTotals->total_notas_credito ?? 0), 2),
            'total_debit_notes' => round((float) ($billingsTotals->total_notas_debito ?? 0), 2),
            'total_sale_notes' => round($totalNotasVenta, 2),
            'converted_notes_excluded_count' => (int) ($convertedNotesTotals->cantidad ?? 0),
            'converted_notes_excluded_amount' => round((float) ($convertedNotesTotals->total ?? 0), 2),
        ];
    }

    /**
     * Ventas agrupadas por fecha.
     */
    public function getSalesByDate(?string $startDate = null, ?string $endDate = null, ?int $warehouseId = null): Collection
    {
        $convertedSaleNoteIds = $this->getConvertedSaleNoteIds();

        // 1. Facturas, Boletas, NC y ND
        $billingQuery = DB::table('billings')
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->where('billings.anulado', false)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('billings.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, fn ($q) => $q->where('billings.idalmacen', $warehouseId))
            ->selectRaw("
                DATE(billings.fecha_emision) as fecha,
                COUNT(billings.id) as cantidad_ventas,
                SUM(CASE WHEN type_documents.codigo = '07' THEN -billings.total ELSE billings.total END) as total,
                SUM(CASE WHEN type_documents.codigo = '07' THEN -billings.igv ELSE billings.igv END) as total_impuestos
            ")
            ->groupBy(DB::raw('DATE(billings.fecha_emision)'));

        // 2. Notas de venta válidas no convertidas
        $saleNoteQuery = DB::table('sale_notes')
            ->where('sale_notes.estado', 1)
            ->when(! empty($convertedSaleNoteIds), fn ($q) => $q->whereNotIn('sale_notes.id', $convertedSaleNoteIds))
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('sale_notes.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, function ($q) use ($warehouseId) {
                $q->whereExists(function ($ex) use ($warehouseId) {
                    $ex->select(DB::raw(1))
                        ->from('detail_sale_notes')
                        ->whereColumn('detail_sale_notes.idnotaventa', 'sale_notes.id')
                        ->where('detail_sale_notes.idalmacen', $warehouseId);
                });
            })
            ->selectRaw('
                DATE(sale_notes.fecha_emision) as fecha,
                COUNT(sale_notes.id) as cantidad_ventas,
                SUM(sale_notes.total) as total,
                SUM(sale_notes.igv) as total_impuestos
            ')
            ->groupBy(DB::raw('DATE(sale_notes.fecha_emision)'));

        $combined = $billingQuery->unionAll($saleNoteQuery);

        $results = DB::table(DB::raw("({$combined->toSql()}) as combined_sales"))
            ->mergeBindings($billingQuery)
            ->selectRaw('
                fecha,
                SUM(cantidad_ventas) as cantidad_ventas,
                ROUND(SUM(total), 2) as total,
                ROUND(SUM(total_impuestos), 2) as total_impuestos,
                CASE WHEN SUM(cantidad_ventas) > 0 THEN ROUND(SUM(total) / SUM(cantidad_ventas), 2) ELSE 0 END as ticket_promedio
            ')
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();

        return $results;
    }

    /**
     * Ventas agrupadas por producto.
     */
    public function getSalesByProduct(?string $startDate = null, ?string $endDate = null, ?int $warehouseId = null): Collection
    {
        $convertedSaleNoteIds = $this->getConvertedSaleNoteIds();

        // 1. Items de Comprobantes formales (NC resta)
        $billingItems = DB::table('detail_billings')
            ->join('billings', 'detail_billings.idfacturacion', '=', 'billings.id')
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->join('products', 'detail_billings.idproducto', '=', 'products.id')
            ->where('billings.anulado', false)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('billings.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, fn ($q) => $q->where('billings.idalmacen', $warehouseId))
            ->select(
                'products.id as product_id',
                'products.descripcion as producto',
                DB::raw("CASE WHEN type_documents.codigo = '07' THEN -detail_billings.cantidad ELSE detail_billings.cantidad END as cantidad"),
                DB::raw("CASE WHEN type_documents.codigo = '07' THEN -detail_billings.precio_total ELSE detail_billings.precio_total END as total")
            );

        // 2. Items de Notas de Venta válidas no convertidas
        $saleNoteItems = DB::table('detail_sale_notes')
            ->join('sale_notes', 'detail_sale_notes.idnotaventa', '=', 'sale_notes.id')
            ->join('products', 'detail_sale_notes.idproducto', '=', 'products.id')
            ->where('sale_notes.estado', 1)
            ->when(! empty($convertedSaleNoteIds), fn ($q) => $q->whereNotIn('sale_notes.id', $convertedSaleNoteIds))
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('sale_notes.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, fn ($q) => $q->where('detail_sale_notes.idalmacen', $warehouseId))
            ->select(
                'products.id as product_id',
                'products.descripcion as producto',
                'detail_sale_notes.cantidad as cantidad',
                'detail_sale_notes.precio_total as total'
            );

        $combined = $billingItems->unionAll($saleNoteItems);

        $results = DB::table(DB::raw("({$combined->toSql()}) as combined_items"))
            ->mergeBindings($billingItems)
            ->selectRaw('
                product_id,
                producto,
                ROUND(SUM(cantidad), 2) as cantidad_vendida,
                ROUND(SUM(total), 2) as total_ventas,
                CASE WHEN SUM(cantidad) > 0 THEN ROUND(SUM(total) / SUM(cantidad), 2) ELSE 0 END as precio_promedio
            ')
            ->groupBy('product_id', 'producto')
            ->orderByDesc('total_ventas')
            ->get();

        return $results;
    }

    /**
     * Ventas agrupadas por cliente.
     */
    public function getSalesByCustomer(?string $startDate = null, ?string $endDate = null, ?int $warehouseId = null): Collection
    {
        $convertedSaleNoteIds = $this->getConvertedSaleNoteIds();

        $billingCustomers = DB::table('billings')
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->join('clients', 'billings.idcliente', '=', 'clients.id')
            ->where('billings.anulado', false)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('billings.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, fn ($q) => $q->where('billings.idalmacen', $warehouseId))
            ->select(
                'clients.id as client_id',
                'clients.nro_documento as documento_cliente',
                'clients.nombres as cliente',
                DB::raw("CASE WHEN type_documents.codigo = '07' THEN -billings.total ELSE billings.total END as total"),
                DB::raw('1 as transaccion')
            );

        $saleNoteCustomers = DB::table('sale_notes')
            ->join('clients', 'sale_notes.idcliente', '=', 'clients.id')
            ->where('sale_notes.estado', 1)
            ->when(! empty($convertedSaleNoteIds), fn ($q) => $q->whereNotIn('sale_notes.id', $convertedSaleNoteIds))
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('sale_notes.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, function ($q) use ($warehouseId) {
                $q->whereExists(function ($ex) use ($warehouseId) {
                    $ex->select(DB::raw(1))
                        ->from('detail_sale_notes')
                        ->whereColumn('detail_sale_notes.idnotaventa', 'sale_notes.id')
                        ->where('detail_sale_notes.idalmacen', $warehouseId);
                });
            })
            ->select(
                'clients.id as client_id',
                'clients.nro_documento as documento_cliente',
                'clients.nombres as cliente',
                'sale_notes.total as total',
                DB::raw('1 as transaccion')
            );

        $combined = $billingCustomers->unionAll($saleNoteCustomers);

        $results = DB::table(DB::raw("({$combined->toSql()}) as combined_customers"))
            ->mergeBindings($billingCustomers)
            ->selectRaw('
                client_id,
                documento_cliente,
                cliente,
                COUNT(transaccion) as cantidad_compras,
                ROUND(SUM(total), 2) as total_compras
            ')
            ->groupBy('client_id', 'documento_cliente', 'cliente')
            ->orderByDesc('total_compras')
            ->get();

        return $results;
    }

    /**
     * Ventas agrupadas por tipo de comprobante.
     */
    public function getSalesByDocumentType(?string $startDate = null, ?string $endDate = null, ?int $warehouseId = null): Collection
    {
        $convertedSaleNoteIds = $this->getConvertedSaleNoteIds();

        $billings = DB::table('billings')
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->where('billings.anulado', false)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('billings.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, fn ($q) => $q->where('billings.idalmacen', $warehouseId))
            ->select(
                'type_documents.codigo as codigo_tipo',
                'type_documents.descripcion as tipo_comprobante',
                'billings.total as total_monto',
                DB::raw("CASE WHEN type_documents.codigo = '07' THEN -billings.total ELSE billings.total END as neto_monto")
            );

        $saleNotes = DB::table('sale_notes')
            ->where('sale_notes.estado', 1)
            ->when(! empty($convertedSaleNoteIds), fn ($q) => $q->whereNotIn('sale_notes.id', $convertedSaleNoteIds))
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('sale_notes.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, function ($q) use ($warehouseId) {
                $q->whereExists(function ($ex) use ($warehouseId) {
                    $ex->select(DB::raw(1))
                        ->from('detail_sale_notes')
                        ->whereColumn('detail_sale_notes.idnotaventa', 'sale_notes.id')
                        ->where('detail_sale_notes.idalmacen', $warehouseId);
                });
            })
            ->select(
                DB::raw("'02' as codigo_tipo"),
                DB::raw("'NOTA DE VENTA' as tipo_comprobante"),
                'sale_notes.total as total_monto',
                'sale_notes.total as neto_monto'
            );

        $combined = $billings->unionAll($saleNotes);

        $results = DB::table(DB::raw("({$combined->toSql()}) as combined_types"))
            ->mergeBindings($billings)
            ->selectRaw('
                codigo_tipo,
                tipo_comprobante,
                COUNT(*) as cantidad_documentos,
                ROUND(SUM(total_monto), 2) as total_bruto,
                ROUND(SUM(neto_monto), 2) as total_neto
            ')
            ->groupBy('codigo_tipo', 'tipo_comprobante')
            ->orderBy('codigo_tipo')
            ->get();

        return $results;
    }

    /**
     * Ventas agrupadas por método de pago.
     * Cuadre con los pagos reales de comprobantes válidos y notas de venta no convertidas.
     */
    public function getSalesByPaymentMethod(?string $startDate = null, ?string $endDate = null, ?int $warehouseId = null): Collection
    {
        $convertedSaleNoteIds = $this->getConvertedSaleNoteIds();

        // 1. Pagos de notas de venta (excluyendo notas convertidas)
        $saleNotePayments = DB::table('detail_payments')
            ->join('sale_notes', 'detail_payments.idfactura', '=', 'sale_notes.id')
            ->where('detail_payments.idtipo_comprobante', 7) // o código '02'
            ->where('sale_notes.estado', 1)
            ->where('detail_payments.estado', 1)
            ->when(! empty($convertedSaleNoteIds), fn ($q) => $q->whereNotIn('sale_notes.id', $convertedSaleNoteIds))
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('sale_notes.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, function ($q) use ($warehouseId) {
                $q->whereExists(function ($ex) use ($warehouseId) {
                    $ex->select(DB::raw(1))
                        ->from('detail_sale_notes')
                        ->whereColumn('detail_sale_notes.idnotaventa', 'sale_notes.id')
                        ->where('detail_sale_notes.idalmacen', $warehouseId);
                });
            })
            ->select(
                'detail_payments.idpago',
                'detail_payments.monto',
                DB::raw("'sale_note' as doc_type"),
                'sale_notes.id as doc_id'
            );

        // 2. Pagos de facturación electrónica (Boletas '03', Facturas '01', ND '08', NC '07')
        $billingPayments = DB::table('detail_payments')
            ->join('billings', 'detail_payments.idfactura', '=', 'billings.id')
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->whereIn('type_documents.codigo', ['01', '03', '07', '08'])
            ->where('billings.anulado', false)
            ->where('detail_payments.estado', 1)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('billings.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, fn ($q) => $q->where('billings.idalmacen', $warehouseId))
            ->select(
                'detail_payments.idpago',
                DB::raw("CASE WHEN type_documents.codigo = '07' THEN -detail_payments.monto ELSE detail_payments.monto END as monto"),
                DB::raw("'billing' as doc_type"),
                'billings.id as doc_id'
            );

        $combined = $saleNotePayments->unionAll($billingPayments);

        $results = DB::table(DB::raw("({$combined->toSql()}) as combined_payments"))
            ->mergeBindings($saleNotePayments)
            ->join('pay_modes', 'combined_payments.idpago', '=', 'pay_modes.id')
            ->selectRaw('
                pay_modes.id as idpago,
                pay_modes.descripcion as metodo_pago,
                COUNT(combined_payments.doc_id) as cantidad_transacciones,
                ROUND(SUM(combined_payments.monto), 2) as total_recaudado
            ')
            ->groupBy('pay_modes.id', 'pay_modes.descripcion')
            ->orderByDesc('total_recaudado')
            ->get();

        return $results;
    }

    /**
     * Stock por almacén con validación de consistencia.
     * Criterio de aceptación: SUM(stock_products) = products.stock_actual
     */
    public function getStockByWarehouse(?int $warehouseId = null): array
    {
        $rows = DB::table('stock_products')
            ->join('products', 'stock_products.idproducto', '=', 'products.id')
            ->join('warehouses', 'stock_products.idalmacen', '=', 'warehouses.id')
            ->where('products.opcion', 1) // Solo productos físicos
            ->when($warehouseId > 0, fn ($q) => $q->where('stock_products.idalmacen', $warehouseId))
            ->select(
                'stock_products.idalmacen',
                'warehouses.descripcion as almacen',
                'products.id as idproducto',
                'products.codigo_interno',
                'products.descripcion as producto',
                'stock_products.stock_actual as stock_almacen',
                'stock_products.stock_minimo',
                'stock_products.precio_compra',
                'stock_products.precio_venta',
                DB::raw('(stock_products.stock_actual * stock_products.precio_compra) as total_valorizado')
            )
            ->orderBy('warehouses.descripcion')
            ->orderBy('products.descripcion')
            ->get();

        // Verificación de integridad: SUM(stock_products.stock_actual) == products.stock_actual
        $integrityCheck = DB::table('products')
            ->where('products.opcion', 1)
            ->leftJoin('stock_products', 'products.id', '=', 'stock_products.idproducto')
            ->selectRaw('
                products.id,
                products.descripcion,
                products.stock_actual as stock_en_products,
                COALESCE(SUM(stock_products.stock_actual), 0) as suma_stock_products,
                (products.stock_actual - COALESCE(SUM(stock_products.stock_actual), 0)) as diferencia
            ')
            ->groupBy('products.id', 'products.descripcion', 'products.stock_actual')
            ->havingRaw('(products.stock_actual - COALESCE(SUM(stock_products.stock_actual), 0)) != 0')
            ->get();

        return [
            'items' => $rows,
            'total_stock' => round((float) $rows->sum('stock_almacen'), 2),
            'total_valorizado' => round((float) $rows->sum('total_valorizado'), 2),
            'is_fully_consistent' => $integrityCheck->isEmpty(),
            'discrepancies' => $integrityCheck,
        ];
    }

    /**
     * Compras agrupadas por proveedor.
     */
    public function getPurchasesBySupplier(?string $startDate = null, ?string $endDate = null): Collection
    {
        return DB::table('buys')
            ->join('clients', 'buys.idproveedor', '=', 'clients.id')
            ->where('buys.estado', 1)
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('buys.fecha_emision', [$startDate, $endDate]))
            ->selectRaw('
                clients.id as supplier_id,
                clients.nro_documento as ruc_proveedor,
                clients.nombres as proveedor,
                COUNT(buys.id) as cantidad_compras,
                ROUND(SUM(buys.total), 2) as total_comprado,
                ROUND(SUM(buys.igv), 2) as total_igv
            ')
            ->groupBy('clients.id', 'clients.nro_documento', 'clients.nombres')
            ->orderByDesc('total_comprado')
            ->get();
    }

    /**
     * Documentos y estados (Facturas, Boletas, NC, ND, Notas de Venta).
     */
    public function getDocumentsAndStatuses(?string $startDate = null, ?string $endDate = null, ?int $warehouseId = null): Collection
    {
        $convertedSaleNoteIds = $this->getConvertedSaleNoteIds();

        $billings = DB::table('billings')
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->join('clients', 'billings.idcliente', '=', 'clients.id')
            ->leftJoin('warehouses', 'billings.idalmacen', '=', 'warehouses.id')
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('billings.fecha_emision', [$startDate, $endDate]))
            ->when($warehouseId > 0, fn ($q) => $q->where('billings.idalmacen', $warehouseId))
            ->select(
                DB::raw("'billing' as origen"),
                'billings.id',
                'type_documents.codigo as tipo_codigo',
                'type_documents.descripcion as tipo_descripcion',
                DB::raw("CONCAT(billings.serie, '-', billings.correlativo) as numero_documento"),
                'billings.fecha_emision',
                'billings.hora',
                'clients.nro_documento as cliente_doc',
                'clients.nombres as cliente_nombre',
                'billings.total',
                DB::raw("CASE WHEN type_documents.codigo = '07' THEN -billings.total ELSE billings.total END as total_neto"),
                'billings.anulado',
                'billings.estado_cpe',
                'billings.cdr',
                'warehouses.descripcion as almacen',
                DB::raw('0 as is_converted_delivery')
            );

        $saleNotes = DB::table('sale_notes')
            ->join('clients', 'sale_notes.idcliente', '=', 'clients.id')
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('sale_notes.fecha_emision', [$startDate, $endDate]))
            ->select(
                DB::raw("'sale_note' as origen"),
                'sale_notes.id',
                DB::raw("'02' as tipo_codigo"),
                DB::raw("'NOTA DE VENTA' as tipo_descripcion"),
                DB::raw("CONCAT(sale_notes.serie, '-', sale_notes.correlativo) as numero_documento"),
                'sale_notes.fecha_emision',
                'sale_notes.hora',
                'clients.nro_documento as cliente_doc',
                'clients.nombres as cliente_nombre',
                'sale_notes.total',
                'sale_notes.total as total_neto',
                DB::raw('CASE WHEN sale_notes.estado = 2 THEN 1 ELSE 0 END as anulado'),
                DB::raw('NULL as estado_cpe'),
                DB::raw('NULL as cdr'),
                DB::raw("'' as almacen"),
                DB::raw('CASE WHEN sale_notes.id IN ('.(empty($convertedSaleNoteIds) ? '0' : implode(',', $convertedSaleNoteIds)).') THEN 1 ELSE 0 END as is_converted_delivery')
            );

        $combined = $billings->unionAll($saleNotes);

        return DB::table(DB::raw("({$combined->toSql()}) as all_docs"))
            ->mergeBindings($billings)
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Cuadre y reconciliación financiera para un día de prueba / arqueo de caja.
     * Verifica la regla central:
     * TOTAL REPORTES = TOTAL CAJA / PAGOS = TOTAL DOCUMENTOS + NOTAS DE VENTA
     */
    public function reconcileDay(string $date, ?int $warehouseId = null): array
    {
        $salesSummary = $this->getSalesSummary($date, $date, $warehouseId);
        $paymentsSummary = $this->getSalesByPaymentMethod($date, $date, $warehouseId);

        $totalCash = 0.0;
        $totalDigital = 0.0;
        foreach ($paymentsSummary as $pay) {
            $label = mb_strtolower((string) $pay->metodo_pago);
            if (str_contains($label, 'efectivo')) {
                $totalCash += (float) $pay->total_recaudado;
            } else {
                $totalDigital += (float) $pay->total_recaudado;
            }
        }

        $totalPayments = round($totalCash + $totalDigital, 2);
        $totalReports = $salesSummary['total_sales'];
        $totalDocumentsPlusNotes = round($salesSummary['total_documents_net'] + $salesSummary['total_sale_notes'], 2);

        $diffReportsVsPayments = round(abs($totalReports - $totalPayments), 2);
        $diffReportsVsDocs = round(abs($totalReports - $totalDocumentsPlusNotes), 2);
        $isBalanced = ($diffReportsVsPayments < 0.01 && $diffReportsVsDocs < 0.01);

        return [
            'date' => $date,
            'warehouse_id' => $warehouseId,
            'total_reports' => $totalReports,
            'total_payments' => $totalPayments,
            'total_cash' => round($totalCash, 2),
            'total_digital' => round($totalDigital, 2),
            'total_documents_net' => $salesSummary['total_documents_net'],
            'total_sale_notes' => $salesSummary['total_sale_notes'],
            'total_documents_plus_notes' => $totalDocumentsPlusNotes,
            'total_facturas_boletas' => $salesSummary['total_facturas_boletas'],
            'total_credit_notes' => $salesSummary['total_credit_notes'],
            'total_debit_notes' => $salesSummary['total_debit_notes'],
            'converted_notes_excluded_count' => $salesSummary['converted_notes_excluded_count'],
            'converted_notes_excluded_amount' => $salesSummary['converted_notes_excluded_amount'],
            'is_balanced' => $isBalanced,
            'discrepancy' => max($diffReportsVsPayments, $diffReportsVsDocs),
        ];
    }
}
