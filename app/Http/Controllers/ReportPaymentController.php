<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleNote;
use Illuminate\Support\Carbon;

class ReportPaymentController extends Controller
{
    public function index() {
        return view('admin.reports.payments.index');
    }

    public function getSalesByPaymentMethod(Request $request) {
        $signo = $this->signo_pais();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
    
        $sales = SaleNote::join('detail_payments', 'sale_notes.id', '=', 'detail_payments.idfactura')
            ->join('pay_modes', 'detail_payments.idpago', '=', 'pay_modes.id') // Suponiendo que "idpago" es el método de pago
            ->whereBetween('sale_notes.fecha_emision', [$startDate, $endDate])
            ->selectRaw('
                pay_modes.descripcion as metodo_pago,
                COUNT(sale_notes.id) as cantidad_transacciones,
                SUM(detail_payments.monto) as total_recaudado
            ')
            ->groupBy('pay_modes.id', 'pay_modes.descripcion')
            ->orderByDesc('total_recaudado')
            ->get();
    
        return response()->json([
            'sales' => $sales,
            'signo' => $signo
        ]);
    }
    
    
}
