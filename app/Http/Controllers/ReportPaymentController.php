<?php

namespace App\Http\Controllers;

use App\Services\Reports\FinancialReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportPaymentController extends Controller
{
    public function __construct(
        protected FinancialReconciliationService $reconciliationService
    ) {}

    public function index()
    {
        return view('admin.reports.payments.index');
    }

    public function getSalesByPaymentMethod(Request $request)
    {
        $signo = $this->signo_pais();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $warehouseId = (int) $request->input('warehouse_id', 0);

        $sales = $this->reconciliationService->getSalesByPaymentMethod(
            $startDate,
            $endDate,
            $warehouseId > 0 ? $warehouseId : null
        );

        return response()->json([
            'sales' => $sales,
            'signo' => $signo,
        ]);
    }
}
