<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleNote;
use Carbon\Carbon;

class ReportSalesController extends Controller
{
    public function index()
    {
        return view('admin.reports.sales.index');
    }

    public function getSalesReport(Request $request)
    {
        $signo      = $this->signo_pais();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $sales = SaleNote::whereBetween('fecha_emision', [$startDate, $endDate])
            ->selectRaw('
                        DATE(fecha_emision) as fecha, 
                        COUNT(id) as cantidad_ventas, 
                        SUM(total) as total, 
                        SUM(igv) as total_impuestos, 
                        SUM(total) / COUNT(id) as ticket_promedio
                    ')
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();

        return response()->json([
            'sales' => $sales,
            'signo' => $signo
        ]);
    }

    public function salesByProductIndex()
    {
        return view('admin.reports.sales.sales_product');
    }

    public function getSalesByProduct(Request $request)
    {
        $signo      = $this->signo_pais();
        $startDate  = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate    = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $sales = SaleNote::join('detail_sale_notes', 'sale_notes.id', '=', 'detail_sale_notes.idnotaventa')
            ->join('products', 'detail_sale_notes.idproducto', '=', 'products.id')
            ->whereBetween('sale_notes.fecha_emision', [$startDate, $endDate])
            ->selectRaw('
                products.descripcion as producto,
                SUM(detail_sale_notes.cantidad) as cantidad_vendida,
                SUM(detail_sale_notes.precio_total) as total_ventas,
                SUM(detail_sale_notes.precio_total) / SUM(detail_sale_notes.cantidad) as precio_promedio
            ')
            ->groupBy('products.id', 'products.descripcion')
            ->orderByDesc('cantidad_vendida')
            ->get();

        return response()->json([
            'sales' => $sales,
            'signo' => $signo
        ]);
    }
}
