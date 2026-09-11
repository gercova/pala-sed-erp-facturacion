<?php

namespace App\Http\Controllers;

use App\Exports\BillingReportExport;
use App\Models\Business;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BillingReportController extends Controller
{
    public function salesRegister()
    {
        return view('admin.reports.billings.sales_register', $this->reportViewContext([
            'title' => 'Registro de ventas',
            'subtitle' => 'Consulta boletas, facturas y notas de credito emitidas en el almacen activo, con filtros contables y exportacion.',
        ]));
    }

    public function billingDocuments()
    {
        return view('admin.reports.billings.billing_documents', $this->reportViewContext([
            'title' => 'Documentos emitidos',
            'subtitle' => 'Revisa boletas y facturas emitidas con sus montos, estado interno y respuesta SUNAT.',
        ]));
    }

    public function creditNotes()
    {
        return view('admin.reports.billings.credit_notes', $this->reportViewContext([
            'title' => 'Notas de credito',
            'subtitle' => 'Consulta anulaciones y ajustes vinculados a comprobantes emitidos en el almacen activo.',
        ]));
    }

    public function getSalesRegister(Request $request)
    {
        return $this->salesRegisterDatatable($this->salesRegisterQuery($request));
    }

    public function getBillingDocuments(Request $request)
    {
        return $this->billingDocumentsDatatable($this->billingDocumentsQuery($request));
    }

    public function getCreditNotes(Request $request)
    {
        return $this->creditNotesDatatable($this->creditNotesQuery($request));
    }

    public function salesRegisterPdf(Request $request)
    {
        return $this->downloadPdf(
            'Registro de ventas',
            'reporte-registro-ventas-' . now()->format('Ymd_His') . '.pdf',
            $this->salesRegisterHeadings(),
            $this->mapSalesRegisterRows($this->salesRegisterQuery($request)->get())
        );
    }

    public function salesRegisterExcel(Request $request)
    {
        return $this->downloadExcel(
            'Registro de ventas',
            'reporte-registro-ventas-' . now()->format('Ymd_His') . '.xlsx',
            $this->salesRegisterHeadings(),
            $this->mapSalesRegisterRows($this->salesRegisterQuery($request)->get())
        );
    }

    public function billingDocumentsPdf(Request $request)
    {
        return $this->downloadPdf(
            'Documentos emitidos',
            'reporte-documentos-emitidos-' . now()->format('Ymd_His') . '.pdf',
            $this->billingDocumentsHeadings(),
            $this->mapBillingDocumentsRows($this->billingDocumentsQuery($request)->get())
        );
    }

    public function billingDocumentsExcel(Request $request)
    {
        return $this->downloadExcel(
            'Documentos emitidos',
            'reporte-documentos-emitidos-' . now()->format('Ymd_His') . '.xlsx',
            $this->billingDocumentsHeadings(),
            $this->mapBillingDocumentsRows($this->billingDocumentsQuery($request)->get())
        );
    }

    public function creditNotesPdf(Request $request)
    {
        return $this->downloadPdf(
            'Notas de credito',
            'reporte-notas-credito-' . now()->format('Ymd_His') . '.pdf',
            $this->creditNotesHeadings(),
            $this->mapCreditNotesRows($this->creditNotesQuery($request)->get())
        );
    }

    public function creditNotesExcel(Request $request)
    {
        return $this->downloadExcel(
            'Notas de credito',
            'reporte-notas-credito-' . now()->format('Ymd_His') . '.xlsx',
            $this->creditNotesHeadings(),
            $this->mapCreditNotesRows($this->creditNotesQuery($request)->get())
        );
    }

    private function reportViewContext(array $extra = []): array
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange(request());

        $clients = $this->billingsByCurrentWarehouse()
            ->join('clients', 'billings.idcliente', '=', 'clients.id')
            ->select('clients.id', 'clients.nombres', 'clients.nro_documento')
            ->distinct()
            ->orderBy('clients.nombres')
            ->get();

        $users = $this->billingsByCurrentWarehouse()
            ->join('users', 'billings.idusuario', '=', 'users.id')
            ->select('users.id', 'users.nombres')
            ->distinct()
            ->orderBy('users.nombres')
            ->get();

        return array_merge([
            'defaultDateFrom' => $dateFrom,
            'defaultDateTo' => $dateTo,
            'clients' => $clients,
            'users' => $users,
            'currentWarehouse' => auth()->user()?->activeWarehouse,
            'signo' => $this->signo_pais(),
        ], $extra);
    }

    private function reportBaseQuery(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $query = $this->billingsByCurrentWarehouse()
            ->selectRaw("
                billings.id,
                billings.fecha_emision,
                billings.serie,
                billings.correlativo,
                billings.sunat_forma_pago,
                billings.modo_pago,
                billings.gravada,
                billings.exonerada,
                billings.inafecta,
                billings.igv,
                billings.total,
                billings.anulado,
                billings.cdr,
                billings.estado_cpe,
                billings.motivo,
                type_documents.codigo as tipo_comprobante_codigo,
                type_documents.descripcion as tipo_comprobante,
                clients.nombres as cliente,
                clients.nro_documento as cliente_documento,
                users.nombres as usuario,
                warehouses.descripcion as almacen,
                credit_note_types.descripcion as motivo_credito,
                related_type.descripcion as tipo_relacionado,
                related_billing.serie as serie_relacionada,
                related_billing.correlativo as correlativo_relacionado,
                COALESCE(NULLIF(billings.sunat_forma_pago, ''), NULLIF(billings.modo_pago, ''), pay_modes.descripcion, '-') as pago
            ")
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->join('clients', 'billings.idcliente', '=', 'clients.id')
            ->leftJoin('users', 'billings.idusuario', '=', 'users.id')
            ->leftJoin('warehouses', 'billings.idalmacen', '=', 'warehouses.id')
            ->leftJoin('pay_modes', 'billings.idpago', '=', 'pay_modes.id')
            ->leftJoin('credit_note_types', 'billings.id_tipo_nota_credito', '=', 'credit_note_types.id')
            ->leftJoin('billings as related_billing', 'billings.idfactura_anular', '=', 'related_billing.id')
            ->leftJoin('type_documents as related_type', 'related_billing.idtipo_comprobante', '=', 'related_type.id')
            ->whereBetween('billings.fecha_emision', [$dateFrom, $dateTo]);

        $this->applyCommonFilters($query, $request);

        return $query->orderByDesc('billings.fecha_emision')->orderByDesc('billings.id');
    }

    private function salesRegisterQuery(Request $request)
    {
        return $this->reportBaseQuery($request)
            ->whereIn('type_documents.codigo', ['01', '03', '07']);
    }

    private function billingDocumentsQuery(Request $request)
    {
        return $this->reportBaseQuery($request)
            ->whereIn('type_documents.codigo', ['01', '03']);
    }

    private function creditNotesQuery(Request $request)
    {
        return $this->reportBaseQuery($request)
            ->where('type_documents.codigo', '07');
    }

    private function applyCommonFilters($query, Request $request): void
    {
        if ($request->filled('filter_document_type')) {
            $query->where('type_documents.codigo', trim((string) $request->input('filter_document_type')));
        }

        if ($request->filled('filter_status')) {
            $status = trim((string) $request->input('filter_status'));
            if ($status === 'vigente') {
                $query->where('billings.anulado', false);
            } elseif ($status === 'anulado') {
                $query->where('billings.anulado', true);
            }
        }

        if ($request->filled('filter_sunat_status')) {
            $sunatStatus = trim((string) $request->input('filter_sunat_status'));
            if ($sunatStatus === 'accepted') {
                $query->where('billings.anulado', false)
                    ->where('billings.cdr', 1)
                    ->where('billings.estado_cpe', 0);
            } elseif ($sunatStatus === 'annulled') {
                $query->where('billings.anulado', true);
            } elseif ($sunatStatus === 'pending') {
                $query->where('billings.anulado', false)
                    ->whereNull('billings.cdr');
            } elseif ($sunatStatus === 'rejected') {
                $query->where('billings.anulado', false)
                    ->whereNotNull('billings.cdr')
                    ->where(function ($inner) {
                        $inner->where('billings.cdr', '<>', 1)
                            ->orWhere('billings.estado_cpe', '<>', 0);
                    });
            }
        }

        if ($request->filled('filter_pay_mode')) {
            $payMode = mb_strtolower(trim((string) $request->input('filter_pay_mode')), 'UTF-8');
            $query->where(function ($inner) use ($payMode) {
                $inner->whereRaw("LOWER(COALESCE(billings.modo_pago, '')) = ?", [$payMode])
                    ->orWhereRaw("LOWER(COALESCE(billings.sunat_forma_pago, '')) = ?", [$payMode]);
            });
        }

        if ($request->filled('filter_client_id')) {
            $query->where('billings.idcliente', (int) $request->input('filter_client_id'));
        }

        if ($request->filled('filter_series')) {
            $series = trim((string) $request->input('filter_series'));
            $query->where(function ($inner) use ($series) {
                $inner->where('billings.serie', 'like', '%' . $series . '%')
                    ->orWhere('billings.correlativo', 'like', '%' . $series . '%')
                    ->orWhereRaw("CONCAT(billings.serie, '-', billings.correlativo) like ?", ['%' . $series . '%']);
            });
        }

        if ($request->filled('filter_user')) {
            $query->where('billings.idusuario', (int) $request->input('filter_user'));
        }
    }

    private function salesRegisterDatatable($query)
    {
        $summary = $this->buildSummary(clone $query, true);

        return datatables()
            ->of($query)
            ->editColumn('fecha_emision', fn ($row) => Carbon::parse($row->fecha_emision)->format('d/m/Y'))
            ->addColumn('comprobante', fn ($row) => $this->renderDocumentCell($row))
            ->addColumn('cliente_info', fn ($row) => $this->renderClientCell($row))
            ->addColumn('cpe_relacionado', fn ($row) => $this->renderRelatedCell($row))
            ->editColumn('igv', fn ($row) => $this->renderAmountChip($this->signedAmount($row, $row->igv)))
            ->editColumn('total', fn ($row) => $this->renderTotalChip($this->signedAmount($row, $row->total)))
            ->addColumn('sunat_badge', fn ($row) => $this->renderSunatBadge($row))
            ->addColumn('estado_badge', fn ($row) => $this->renderStatusBadge($row))
            ->editColumn('sunat_forma_pago', fn ($row) => e($this->normalizePaymentLabel($row->pago)))
            ->with(['summary' => $summary])
            ->rawColumns(['comprobante', 'cliente_info', 'cpe_relacionado', 'igv', 'total', 'sunat_badge', 'estado_badge'])
            ->toJson();
    }

    private function billingDocumentsDatatable($query)
    {
        $summary = $this->buildSummary(clone $query, false);

        return datatables()
            ->of($query)
            ->editColumn('fecha_emision', fn ($row) => Carbon::parse($row->fecha_emision)->format('d/m/Y'))
            ->addColumn('comprobante', fn ($row) => $this->renderDocumentCell($row))
            ->addColumn('cliente_info', fn ($row) => $this->renderClientCell($row))
            ->editColumn('gravada', fn ($row) => $this->renderAmountChip($row->gravada))
            ->editColumn('igv', fn ($row) => $this->renderAmountChip($row->igv))
            ->editColumn('total', fn ($row) => $this->renderTotalChip($row->total))
            ->addColumn('sunat_badge', fn ($row) => $this->renderSunatBadge($row))
            ->addColumn('estado_badge', fn ($row) => $this->renderStatusBadge($row))
            ->editColumn('sunat_forma_pago', fn ($row) => e($this->normalizePaymentLabel($row->pago)))
            ->with(['summary' => $summary])
            ->rawColumns(['comprobante', 'cliente_info', 'gravada', 'igv', 'total', 'sunat_badge', 'estado_badge'])
            ->toJson();
    }

    private function creditNotesDatatable($query)
    {
        $summary = $this->buildSummary(clone $query, true);

        return datatables()
            ->of($query)
            ->editColumn('fecha_emision', fn ($row) => Carbon::parse($row->fecha_emision)->format('d/m/Y'))
            ->addColumn('comprobante', fn ($row) => $this->renderDocumentCell($row))
            ->addColumn('cliente_info', fn ($row) => $this->renderClientCell($row))
            ->addColumn('cpe_relacionado', fn ($row) => $this->renderRelatedCell($row))
            ->editColumn('motivo_credito', fn ($row) => e($row->motivo_credito ?: ($row->motivo ?: '-')))
            ->editColumn('gravada', fn ($row) => $this->renderAmountChip($this->signedAmount($row, $row->gravada)))
            ->editColumn('igv', fn ($row) => $this->renderAmountChip($this->signedAmount($row, $row->igv)))
            ->editColumn('total', fn ($row) => $this->renderTotalChip($this->signedAmount($row, $row->total)))
            ->addColumn('sunat_badge', fn ($row) => $this->renderSunatBadge($row))
            ->addColumn('estado_badge', fn ($row) => $this->renderStatusBadge($row))
            ->with(['summary' => $summary])
            ->rawColumns(['comprobante', 'cliente_info', 'cpe_relacionado', 'gravada', 'igv', 'total', 'sunat_badge', 'estado_badge'])
            ->toJson();
    }

    private function buildSummary($query, bool $signed = false): array
    {
        $rows = $query->get(['billings.id', 'billings.gravada', 'billings.igv', 'billings.total', 'type_documents.codigo as tipo_comprobante_codigo']);

        $summary = [
            'count' => $rows->count(),
            'gravada' => 0,
            'igv' => 0,
            'total' => 0,
        ];

        foreach ($rows as $row) {
            $summary['gravada'] += $signed ? $this->signedAmount($row, $row->gravada) : (float) $row->gravada;
            $summary['igv'] += $signed ? $this->signedAmount($row, $row->igv) : (float) $row->igv;
            $summary['total'] += $signed ? $this->signedAmount($row, $row->total) : (float) $row->total;
        }

        return $summary;
    }

    private function resolveDateRange(Request $request): array
    {
        $from = $request->input('filter_date_from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->input('filter_date_to', Carbon::now()->endOfMonth()->toDateString());

        return [$from, $to];
    }

    private function renderDocumentCell($row): string
    {
        return '<div class="report-doc-cell">'
            . '<div class="report-doc-code">' . e(trim($row->serie . '-' . $row->correlativo)) . '</div>'
            . '<small class="report-doc-type">' . e((string) $row->tipo_comprobante) . '</small>'
            . '</div>';
    }

    private function renderClientCell($row): string
    {
        return '<div class="report-client-cell">'
            . '<div class="report-client-name">' . e((string) $row->cliente) . '</div>'
            . '<small class="report-client-doc">' . e((string) ($row->cliente_documento ?: 'Sin documento')) . '</small>'
            . '</div>';
    }

    private function renderRelatedCell($row): string
    {
        if (empty($row->serie_relacionada) || empty($row->correlativo_relacionado)) {
            return '<span class="text-muted">-</span>';
        }

        $label = trim(($row->tipo_relacionado ?: 'CPE') . ' ' . $row->serie_relacionada . '-' . $row->correlativo_relacionado);

        return '<span class="report-chip">' . e($label) . '</span>';
    }

    private function renderAmountChip($value): string
    {
        return '<span class="report-chip">' . e(number_format((float) $value, 2, '.', '')) . '</span>';
    }

    private function renderTotalChip($value): string
    {
        return '<div class="report-total-chip">' . e(trim($this->signo_pais() . ' ' . number_format((float) $value, 2, '.', ''))) . '</div>';
    }

    private function renderSunatBadge($row): string
    {
        $label = $this->resolveSunatStatusLabel($row);
        $class = match ($label) {
            'Aceptado' => 'bg-success-subtle text-success',
            'Pendiente' => 'bg-light text-dark border',
            'Anulado' => 'bg-dark-subtle text-dark',
            default => 'bg-danger-subtle text-danger',
        };

        return '<span class="badge ' . $class . '">' . e($label) . '</span>';
    }

    private function renderStatusBadge($row): string
    {
        if ((bool) $row->anulado) {
            return '<span class="badge bg-danger-subtle text-danger">Anulado</span>';
        }

        return '<span class="badge bg-success-subtle text-success">Vigente</span>';
    }

    private function resolveSunatStatusLabel($row): string
    {
        if ((bool) $row->anulado) {
            return 'Anulado';
        }

        if ($row->cdr === null) {
            return 'Pendiente';
        }

        if ((int) $row->cdr === 1 && (int) $row->estado_cpe === 0) {
            return 'Aceptado';
        }

        return (int) $row->cdr === 1 ? 'Rechazado' : 'Observado';
    }

    private function normalizePaymentLabel(?string $value): string
    {
        $label = trim((string) $value);
        if ($label === '') {
            return '-';
        }

        return match (mb_strtolower($label, 'UTF-8')) {
            'contado', 'cash', 'efectivo' => 'Contado',
            'credito', 'crédito' => 'Credito',
            default => ucfirst($label),
        };
    }

    private function signedAmount($row, $value): float
    {
        $sign = (string) $row->tipo_comprobante_codigo === '07' ? -1 : 1;

        return $sign * (float) $value;
    }

    private function salesRegisterHeadings(): array
    {
        return ['Fecha', 'Comprobante', 'Tipo', 'Cliente', 'Documento', 'CPE relacionado', 'IGV', 'Total', 'SUNAT', 'Estado', 'Pago', 'Usuario', 'Almacen'];
    }

    private function billingDocumentsHeadings(): array
    {
        return ['Fecha', 'Comprobante', 'Tipo', 'Cliente', 'Documento', 'Gravada', 'IGV', 'Total', 'SUNAT', 'Estado', 'Pago', 'Usuario', 'Almacen'];
    }

    private function creditNotesHeadings(): array
    {
        return ['Fecha', 'Nota', 'Cliente', 'Documento', 'CPE relacionado', 'Motivo', 'Base', 'IGV', 'Total', 'SUNAT', 'Estado', 'Usuario', 'Almacen'];
    }

    private function mapSalesRegisterRows($rows): array
    {
        return $rows->map(function ($row) {
            return [
                Carbon::parse($row->fecha_emision)->format('d/m/Y'),
                trim($row->serie . '-' . $row->correlativo),
                (string) $row->tipo_comprobante,
                (string) $row->cliente,
                (string) ($row->cliente_documento ?: '-'),
                $this->relatedPlainValue($row),
                number_format($this->signedAmount($row, $row->igv), 2, '.', ''),
                number_format($this->signedAmount($row, $row->total), 2, '.', ''),
                $this->resolveSunatStatusLabel($row),
                (bool) $row->anulado ? 'Anulado' : 'Vigente',
                $this->normalizePaymentLabel($row->pago),
                (string) ($row->usuario ?: '-'),
                (string) ($row->almacen ?: '-'),
            ];
        })->all();
    }

    private function mapBillingDocumentsRows($rows): array
    {
        return $rows->map(function ($row) {
            return [
                Carbon::parse($row->fecha_emision)->format('d/m/Y'),
                trim($row->serie . '-' . $row->correlativo),
                (string) $row->tipo_comprobante,
                (string) $row->cliente,
                (string) ($row->cliente_documento ?: '-'),
                number_format((float) $row->gravada, 2, '.', ''),
                number_format((float) $row->igv, 2, '.', ''),
                number_format((float) $row->total, 2, '.', ''),
                $this->resolveSunatStatusLabel($row),
                (bool) $row->anulado ? 'Anulado' : 'Vigente',
                $this->normalizePaymentLabel($row->pago),
                (string) ($row->usuario ?: '-'),
                (string) ($row->almacen ?: '-'),
            ];
        })->all();
    }

    private function mapCreditNotesRows($rows): array
    {
        return $rows->map(function ($row) {
            return [
                Carbon::parse($row->fecha_emision)->format('d/m/Y'),
                trim($row->serie . '-' . $row->correlativo),
                (string) $row->cliente,
                (string) ($row->cliente_documento ?: '-'),
                $this->relatedPlainValue($row),
                (string) ($row->motivo_credito ?: ($row->motivo ?: '-')),
                number_format($this->signedAmount($row, $row->gravada), 2, '.', ''),
                number_format($this->signedAmount($row, $row->igv), 2, '.', ''),
                number_format($this->signedAmount($row, $row->total), 2, '.', ''),
                $this->resolveSunatStatusLabel($row),
                (bool) $row->anulado ? 'Anulado' : 'Vigente',
                (string) ($row->usuario ?: '-'),
                (string) ($row->almacen ?: '-'),
            ];
        })->all();
    }

    private function relatedPlainValue($row): string
    {
        if (empty($row->serie_relacionada) || empty($row->correlativo_relacionado)) {
            return '-';
        }

        return trim(($row->tipo_relacionado ?: 'CPE') . ' ' . $row->serie_relacionada . '-' . $row->correlativo_relacionado);
    }

    private function downloadPdf(string $title, string $filename, array $headings, array $rows)
    {
        $pdf = Pdf::loadView('admin.reports.billings.pdf', [
            'title' => $title,
            'headings' => $headings,
            'rows' => $rows,
            'business' => Business::query()->find(1),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    private function downloadExcel(string $title, string $filename, array $headings, array $rows)
    {
        return Excel::download(new BillingReportExport($title, $headings, $rows), $filename);
    }
}
