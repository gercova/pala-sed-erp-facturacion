<?php

namespace App\Http\Controllers;

use App\Models\DetailPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $saleNotesQuery = $this->saleNotesByCurrentWarehouse();
        $billingsQuery = $this->billingsByCurrentWarehouse()->whereIn('billings.idtipo_comprobante', function ($query) {
            $query->select('id')
                ->from('type_documents')
                ->whereIn('codigo', ['01', '03']);
        });
        $stockQuery = $this->stockProductsByCurrentWarehouse();
        $transferOrdersQuery = $this->transferOrdersByCurrentWarehouse();

        $totalVentasNotas = (clone $saleNotesQuery)->whereDate('fecha_emision', Carbon::today())->sum('total');
        $totalVentasComprobantes = (clone $billingsQuery)->whereDate('fecha_emision', Carbon::today())->sum('total');
        $data["totalVentas"] = $totalVentasNotas + $totalVentasComprobantes;
        $data["totalStock"] = (clone $stockQuery)->sum('stock_actual');
        $data["totalTransferencias"] = (clone $transferOrdersQuery)->whereDate('fecha_emision', Carbon::today())->count();
        $data["signo"] = $this->signo_pais();

        $ventasHoyNotas = (clone $saleNotesQuery)->whereDate('fecha_emision', today())->count();
        $ventasHoyComprobantes = (clone $billingsQuery)->whereDate('fecha_emision', today())->count();
        $data["ventasHoy"] = $ventasHoyNotas + $ventasHoyComprobantes;
        $data["totalStock"] = (clone $stockQuery)->sum('stock_actual');
        $data["ordenesPendientes"] = (clone $transferOrdersQuery)->where('estado', 'pendiente')->count();
        $presupuestoNotas = (clone $saleNotesQuery)->whereYear('fecha_emision', Carbon::now()->year)->sum('total');
        $presupuestoComprobantes = (clone $billingsQuery)->whereYear('fecha_emision', Carbon::now()->year)->sum('total');
        $data["presupuestoAnual"] = $presupuestoNotas + $presupuestoComprobantes;
        $metaPresupuestoAnual = 100000;
        $data["porcentajePresupuesto"] = $metaPresupuestoAnual > 0 ? ($data["presupuestoAnual"] / $metaPresupuestoAnual) * 100 : 0;
        $data["porcentajePresupuesto"] = min($data["porcentajePresupuesto"], 100);

        return view('admin.home', $data);
    }

    public function ventasMensuales()
    {
        $signo = $this->signo_pais();
        $ventasNotas = $this->saleNotesByCurrentWarehouse()
            ->selectRaw('MONTH(fecha_emision) as mes, SUM(total) as total')
            ->whereYear('fecha_emision', Carbon::now()->year)
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $ventasComprobantes = $this->billingsByCurrentWarehouse()
            ->whereIn('billings.idtipo_comprobante', function ($query) {
                $query->select('id')
                    ->from('type_documents')
                    ->whereIn('codigo', ['01', '03']);
            })
            ->selectRaw('MONTH(fecha_emision) as mes, SUM(total) as total')
            ->whereYear('fecha_emision', Carbon::now()->year)
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $ventasPorMes = [];
        for ($i = 1; $i <= 12; $i++) {
            $ventasPorMes[] = ($ventasNotas[$i] ?? 0) + ($ventasComprobantes[$i] ?? 0);
        }

        return response()->json([
            'signo' => $signo,
            'ventas' => $ventasPorMes
        ]);
    }

    public function reporteIngresos()
    {
        $signo = $this->signo_pais();

        $ingresosNotas = $this->saleNotesByCurrentWarehouse()
            ->selectRaw('MONTH(fecha_emision) as mes, SUM(total) as total')
            ->whereYear('fecha_emision', Carbon::now()->year)
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $ingresosComprobantes = $this->billingsByCurrentWarehouse()
            ->whereIn('billings.idtipo_comprobante', function ($query) {
                $query->select('id')
                    ->from('type_documents')
                    ->whereIn('codigo', ['01', '03']);
            })
            ->selectRaw('MONTH(fecha_emision) as mes, SUM(total) as total')
            ->whereYear('fecha_emision', Carbon::now()->year)
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $ingresosPorMes = [];
        for ($i = 1; $i <= 12; $i++) {
            $ingresosPorMes[] = ($ingresosNotas[$i] ?? 0) + ($ingresosComprobantes[$i] ?? 0);
        }

        return response()->json([
            'signo' => $signo,
            'ingresos' => $ingresosPorMes
        ]);
    }

    public function metodosPagoVentas()
    {
        $warehouseId = $this->currentWarehouseId();

        $metodosPago = DetailPayment::select('pay_modes.descripcion', DB::raw('SUM(detail_payments.monto) as total'))
            ->join('pay_modes', 'detail_payments.idpago', '=', 'pay_modes.id')
            ->leftJoin('sale_notes', function ($join) {
                $join->on('detail_payments.idfactura', '=', 'sale_notes.id')
                    ->whereColumn('detail_payments.idtipo_comprobante', 'sale_notes.idtipo_comprobante');
            })
            ->leftJoin('billings', function ($join) {
                $join->on('detail_payments.idfactura', '=', 'billings.id')
                    ->whereColumn('detail_payments.idtipo_comprobante', 'billings.idtipo_comprobante');
            })
            ->whereYear('detail_payments.created_at', Carbon::now()->year);

        if ($warehouseId > 0) {
            $metodosPago->where(function ($query) use ($warehouseId) {
                $query->where('billings.idalmacen', $warehouseId)
                    ->orWhereExists(function ($subquery) use ($warehouseId) {
                        $subquery->selectRaw('1')
                            ->from('detail_sale_notes')
                            ->whereColumn('detail_sale_notes.idnotaventa', 'sale_notes.id')
                            ->where('detail_sale_notes.idalmacen', $warehouseId);
                    });
            });
        }

        $metodosPago = $metodosPago
            ->groupBy('pay_modes.descripcion')
            ->get();

        $signo = $this->signo_pais();

        return response()->json([
            'labels' => $metodosPago->pluck('descripcion'),
            'data' => $metodosPago->pluck('total'),
            'signo' => $signo
        ]);
    }
}
