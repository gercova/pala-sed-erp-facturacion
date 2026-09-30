<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportPaymentController extends Controller
{
    public function index()
    {
        return view('admin.reports.payments.index');
    }

    public function getSalesByPaymentMethod(Request $request)
    {
        $signo = $this->signo_pais();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Pagos provenientes de Notas de Venta (documentos internos código '02')
        $saleNotePayments = DB::table('detail_payments')
            ->join('sale_notes', 'detail_payments.idfactura', '=', 'sale_notes.id')
            ->join('type_documents', 'detail_payments.idtipo_comprobante', '=', 'type_documents.id')
            ->where('type_documents.codigo', '02')
            ->where('sale_notes.estado', 1)
            ->where('detail_payments.estado', 1)
            ->whereBetween('sale_notes.fecha_emision', [$startDate, $endDate])
            ->select(
                'detail_payments.idpago',
                'detail_payments.monto',
                DB::raw("'sale_note' as doc_type"),
                'sale_notes.id as doc_id'
            );

        // Pagos provenientes de Comprobantes Electrónicos SUNAT (Boletas '03' y Facturas '01')
        $billingPayments = DB::table('detail_payments')
            ->join('billings', 'detail_payments.idfactura', '=', 'billings.id')
            ->join('type_documents', 'detail_payments.idtipo_comprobante', '=', 'type_documents.id')
            ->whereIn('type_documents.codigo', ['01', '03'])
            ->where('billings.anulado', false)
            ->where('detail_payments.estado', 1)
            ->whereBetween('billings.fecha_emision', [$startDate, $endDate])
            ->select(
                'detail_payments.idpago',
                'detail_payments.monto',
                DB::raw("'billing' as doc_type"),
                'billings.id as doc_id'
            );

        $combinedQuery = $saleNotePayments->unionAll($billingPayments);

        $sales = DB::table(DB::raw("({$combinedQuery->toSql()}) as combined_payments"))
            ->mergeBindings($combinedQuery)
            ->join('pay_modes', 'combined_payments.idpago', '=', 'pay_modes.id')
            ->selectRaw('
                pay_modes.descripcion as metodo_pago,
                COUNT(combined_payments.doc_id) as cantidad_transacciones,
                ROUND(SUM(combined_payments.monto), 2) as total_recaudado
            ')
            ->groupBy('pay_modes.id', 'pay_modes.descripcion')
            ->orderByDesc('total_recaudado')
            ->get();

        return response()->json([
            'sales' => $sales,
            'signo' => $signo,
        ]);
    }
}
