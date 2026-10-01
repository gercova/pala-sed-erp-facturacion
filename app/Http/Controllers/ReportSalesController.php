<?php

namespace App\Http\Controllers;

use App\Services\Reports\FinancialReconciliationService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportSalesController extends Controller
{
    public function __construct(
        protected FinancialReconciliationService $reconciliationService
    ) {}

    public function index(): View
    {
        return view('admin.reports.sales.index');
    }

    public function getSalesReport(Request $request): JsonResponse
    {
        $signo = $this->signo_pais();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $warehouseId = (int) $request->input('warehouse_id', 0);
        $sales = $this->reconciliationService->getSalesByDate($startDate, $endDate, $warehouseId > 0 ? $warehouseId : null);
        $summary = $this->reconciliationService->getSalesSummary($startDate, $endDate, $warehouseId > 0 ? $warehouseId : null);

        return response()->json([
            'sales' => $sales,
            'summary' => $summary,
            'signo' => $signo,
        ]);
    }

    public function salesByProductIndex(): View
    {
        return view('admin.reports.sales.sales_product');
    }

    public function getSalesByProduct(Request $request): JsonResponse
    {
        $signo = $this->signo_pais();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $warehouseId = (int) $request->input('warehouse_id', 0);
        $sales = $this->reconciliationService->getSalesByProduct($startDate, $endDate, $warehouseId > 0 ? $warehouseId : null);

        return response()->json([
            'sales' => $sales,
            'signo' => $signo,
        ]);
    }

    public function getSalesByCustomer(Request $request): JsonResponse
    {
        $signo = $this->signo_pais();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $warehouseId = (int) $request->input('warehouse_id', 0);
        $sales = $this->reconciliationService->getSalesByCustomer($startDate, $endDate, $warehouseId > 0 ? $warehouseId : null);

        return response()->json([
            'sales' => $sales,
            'signo' => $signo,
        ]);
    }

    public function getSalesByDocumentType(Request $request): JsonResponse
    {
        $signo = $this->signo_pais();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $warehouseId = (int) $request->input('warehouse_id', 0);
        $sales = $this->reconciliationService->getSalesByDocumentType($startDate, $endDate, $warehouseId > 0 ? $warehouseId : null);

        return response()->json([
            'sales' => $sales,
            'signo' => $signo,
        ]);
    }

    public function getReconciliationReport(Request $request): JsonResponse
    {
        $signo = $this->signo_pais();
        $date = $request->input('date', Carbon::today()->toDateString());
        $warehouseId = (int) $request->input('warehouse_id', 0);
        $reconciliation = $this->reconciliationService->reconcileDay($date, $warehouseId > 0 ? $warehouseId : null);

        return response()->json([
            'reconciliation' => $reconciliation,
            'signo' => $signo,
        ]);
    }
}
