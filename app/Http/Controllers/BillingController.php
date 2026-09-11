<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Business;
use App\Models\CreditNoteType;
use App\Models\DebitNoteType;
use App\Models\DetailBilling;
use App\Models\DetailPayment;
use App\Models\Serie;
use App\Models\TypeDocument;
use App\Models\Warehouse;
use App\Services\Ebilling\Payload\BillingPayloadBuilder;
use App\Services\Ebilling\SunatDispatchService;
use App\Services\Ebilling\Support\BusinessStoragePath;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BillingController extends Controller
{
    public function __construct(
        private readonly SunatDispatchService $dispatchService,
        private readonly BillingPayloadBuilder $payloadBuilder,
        private readonly BusinessStoragePath $storagePath,
    ) {
    }

    public function index()
    {
        $query = $this->salesBillingsQuery();

        return view('admin.billings.list', [
            'kpi_today_count' => (clone $query)->whereDate('fecha_emision', Carbon::today())->count(),
            'kpi_today_total' => (clone $query)->whereDate('fecha_emision', Carbon::today())->sum('total'),
            'kpi_sunat_pending' => (clone $query)->whereNull('cdr')->count(),
            'signo' => $this->signo_pais(),
            'creditNoteTypes' => CreditNoteType::query()
                ->where('estado', true)
                ->whereIn('codigo', ['01', '02'])
                ->orderBy('codigo')
                ->get(),
            'debitNoteTypes' => $this->activeDebitNoteTypes(),
        ]);
    }

    public function credit_notes_index()
    {
        $query = $this->creditNotesBillingsQuery();

        return view('admin.billings.credit_notes_list', [
            'kpi_today_count' => (clone $query)->whereDate('fecha_emision', Carbon::today())->count(),
            'kpi_today_total' => (clone $query)->whereDate('fecha_emision', Carbon::today())->sum('total'),
            'kpi_sunat_pending' => (clone $query)->whereNull('cdr')->count(),
            'signo' => $this->signo_pais(),
        ]);
    }

    public function debit_notes_index()
    {
        $query = $this->debitNotesBillingsQuery();

        return view('admin.billings.debit_notes_list', [
            'kpi_today_count' => (clone $query)->whereDate('fecha_emision', Carbon::today())->count(),
            'kpi_today_total' => (clone $query)->whereDate('fecha_emision', Carbon::today())->sum('total'),
            'kpi_sunat_pending' => (clone $query)->whereNull('cdr')->count(),
            'signo' => $this->signo_pais(),
        ]);
    }

    public function get()
    {
        $billings = $this->salesBillingsQuery()
            ->select(
                'billings.*',
                'clients.nro_documento as dni_ruc',
                'clients.nombres as cliente',
                'type_documents.descripcion as tipo_comprobante',
                'type_documents.codigo as tipo_comprobante_codigo',
                'warehouses.descripcion as almacen'
            )
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->join('clients', 'billings.idcliente', '=', 'clients.id')
            ->leftJoin('warehouses', 'billings.idalmacen', '=', 'warehouses.id')
            ->whereIn('type_documents.codigo', ['01', '03'])
            ->orderByDesc('billings.id');

        return $this->billingDatatableResponse($billings, true);
    }

    public function get_credit_notes()
    {
        $billings = $this->creditNotesBillingsQuery()
            ->select(
                'billings.*',
                'clients.nro_documento as dni_ruc',
                'clients.nombres as cliente',
                'type_documents.descripcion as tipo_comprobante',
                'type_documents.codigo as tipo_comprobante_codigo',
                'warehouses.descripcion as almacen'
            )
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->join('clients', 'billings.idcliente', '=', 'clients.id')
            ->leftJoin('warehouses', 'billings.idalmacen', '=', 'warehouses.id')
            ->orderByDesc('billings.id');

        return $this->billingDatatableResponse($billings, false);
    }

    public function get_debit_notes()
    {
        $billings = $this->debitNotesBillingsQuery()
            ->select(
                'billings.*',
                'clients.nro_documento as dni_ruc',
                'clients.nombres as cliente',
                'type_documents.descripcion as tipo_comprobante',
                'type_documents.codigo as tipo_comprobante_codigo',
                'warehouses.descripcion as almacen'
            )
            ->join('type_documents', 'billings.idtipo_comprobante', '=', 'type_documents.id')
            ->join('clients', 'billings.idcliente', '=', 'clients.id')
            ->leftJoin('warehouses', 'billings.idalmacen', '=', 'warehouses.id')
            ->orderByDesc('billings.id');

        return $this->billingDatatableResponse($billings, false, false);
    }

    protected function billingDatatableResponse($billings, bool $allowCreditNoteAction = true, bool $allowDebitNoteAction = true)
    {
        if (request()->has('columns')) {
            $dateSearch = trim((string) request()->input('columns.0.search.value'));
            $voucherSearch = trim((string) request()->input('columns.1.search.value'));
            $customerSearch = trim((string) request()->input('columns.2.search.value'));
            $totalSearch = trim((string) request()->input('columns.4.search.value'));

            if ($voucherSearch !== '') {
                if (strpos($voucherSearch, '-') !== false) {
                    [$serie, $correlativo] = array_pad(explode('-', $voucherSearch, 2), 2, '');
                    $billings->where('billings.serie', 'like', '%' . trim($serie) . '%')
                        ->where('billings.correlativo', 'like', '%' . trim($correlativo) . '%');
                } else {
                    $billings->where(function ($query) use ($voucherSearch) {
                        $query->where('billings.serie', 'like', '%' . $voucherSearch . '%')
                            ->orWhere('billings.correlativo', 'like', '%' . $voucherSearch . '%')
                            ->orWhere('type_documents.descripcion', 'like', '%' . $voucherSearch . '%');
                    });
                }
            }

            if ($dateSearch !== '') {
                $billings->whereDate('billings.fecha_emision', $dateSearch);
            }

            if ($customerSearch !== '') {
                $billings->where(function ($query) use ($customerSearch) {
                    $query->where('clients.nombres', 'like', '%' . $customerSearch . '%')
                        ->orWhere('clients.nro_documento', 'like', '%' . $customerSearch . '%');
                });
            }

            if ($totalSearch !== '') {
                $normalizedTotal = str_replace(',', '.', $totalSearch);
                $billings->where('billings.total', 'like', '%' . $normalizedTotal . '%');
            }
        }

        return datatables()
            ->of($billings)
            ->editColumn('fecha_emision', function ($billing) {
                return Carbon::parse((string) $billing->fecha_emision)->format('Y-m-d');
            })
            ->addColumn('comprobante', function ($billing) {
                $documento = e($billing->serie . '-' . $billing->correlativo);
                $tipo = e(mb_strtoupper((string) $billing->tipo_comprobante));

                return '<div class="text-center">'
                    . '<div class="fw-semibold">' . $documento . '</div>'
                    . '<small class="text-muted">' . $tipo . '</small>'
                    . '</div>';
            })
            ->addColumn('cliente_info', function ($billing) {
                return '<div class="billing-customer-cell">'
                    . '<div class="billing-customer-name">' . e((string) $billing->cliente) . '</div>'
                    . '<small class="billing-customer-doc">' . e((string) ($billing->dni_ruc ?: 'Sin documento')) . '</small>'
                    . '</div>';
            })
            ->addColumn('almacen_badge', function ($billing) {
                return '<span class="badge bg-light text-dark border">' . e((string) ($billing->almacen ?: 'Sin almacén')) . '</span>';
            })
            ->addColumn('total', fn ($billing) => '<div class="billing-total-chip">' . e($this->signo_pais() . ' ' . number_format((float) $billing->total, 2, '.', '')) . '</div>')
            ->addColumn('xml', function ($billing) {
                $exists = $this->billingXmlExists($billing);

                if (! $exists) {
                    return '<span class="text-muted">-</span>';
                }

                return '<a href="' . route('admin.billing_xml', $billing->id) . '" class="billing-file-link text-primary" target="_blank" title="Ver XML"><i class="fas fa-file-code"></i></a>';
            })
            ->addColumn('cdr_archivo', function ($billing) {
                $exists = $this->billingCdrExists($billing);

                if (! $exists) {
                    return '<span class="text-muted">-</span>';
                }

                return '<a href="' . route('admin.billing_cdr', $billing->id) . '" class="billing-file-link text-primary" target="_blank" title="Ver CDR"><i class="fas fa-file-invoice"></i></a>';
            })
            ->addColumn('sunat_badge', function ($billing) {
                if ((bool) $billing->anulado) {
                    return '<span class="badge bg-dark-subtle text-dark">Anulado</span>';
                }

                if ($billing->cdr === null) {
                    return '<span class="badge bg-light text-dark border">Pendiente</span>';
                }

                if ((int) $billing->cdr === 1 && (int) $billing->estado_cpe === 0) {
                    return '<span class="badge bg-success-subtle text-success">Aceptado</span>';
                }

                return (int) $billing->cdr === 1
                    ? '<span class="badge bg-danger-subtle text-danger">Rechazado</span>'
                    : '<span class="badge bg-danger-subtle text-danger">Observado</span>';
            })
            ->addColumn('estado_badge', function ($billing) {
                if ((bool) $billing->anulado) {
                    return '<span class="badge bg-danger-subtle text-danger">Anulado</span>';
                }

                return '<span class="badge bg-success-subtle text-success">Vigente</span>';
            })
            ->addColumn('acciones', function ($billing) use ($allowCreditNoteAction, $allowDebitNoteAction) {
                $menu = '<div class="dropdown">
                            <a href="#" role="button" id="dropdownBilling' . (int) $billing->id . '" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownBilling' . (int) $billing->id . '">
                                <a class="dropdown-item btn-a4" data-id="' . (int) $billing->id . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4H15V8H19V20H5V4ZM3.9985 2C3.44749 2 3 2.44405 3 2.9918V21.0082C3 21.5447 3.44476 22 3.9934 22H20.0066C20.5551 22 21 21.5489 21 20.9925L20.9997 7L16 2H3.9985ZM10.4999 7.5C10.4999 9.07749 10.0442 10.9373 9.27493 12.6534C8.50287 14.3757 7.46143 15.8502 6.37524 16.7191L7.55464 18.3321C10.4821 16.3804 13.7233 15.0421 16.8585 15.49L17.3162 13.5513C14.6435 12.6604 12.4999 9.98994 12.4999 7.5H10.4999ZM11.0999 13.4716C11.3673 12.8752 11.6042 12.2563 11.8037 11.6285C12.2753 12.3531 12.8553 13.0182 13.5101 13.5953C12.5283 13.7711 11.5665 14.0596 10.6352 14.4276C10.7999 14.1143 10.9551 13.7948 11.0999 13.4716Z"></path></svg>
                                    <span> A4</span>
                                </a>
                                <a class="dropdown-item btn-ticket" data-id="' . (int) $billing->id . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M20 22H4C3.44772 22 3 21.5523 3 21V3C3 2.44772 3.44772 2 4 2H20C20.5523 2 21 2.44772 21 3V21C21 21.5523 20.5523 22 20 22ZM19 20V4H5V20H19ZM7 6H11V10H7V6ZM7 12H17V14H7V12ZM7 16H17V18H7V16ZM13 7H17V9H13V7Z"></path></svg>
                                    <span> Ticket</span>
                                </a>';

                if (! ((int) $billing->cdr === 1 && (int) $billing->estado_cpe === 0) && ! (bool) $billing->anulado) {
                    $menu .= '<a class="dropdown-item btn-dispatch" data-id="' . (int) $billing->id . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3.4 20.4L21 12L3.4 3.6L3.4 10.2L16 12L3.4 13.8L3.4 20.4Z"></path></svg>
                                    <span> Enviar a SUNAT</span>
                                </a>';
                }

                if ((int) $billing->cdr === 1 && (int) $billing->estado_cpe === 0 && ! (bool) $billing->anulado) {
                    if ($allowCreditNoteAction && in_array((string) $billing->tipo_comprobante_codigo, ['01', '03'], true)) {
                        $menu .= '<a class="dropdown-item btn-credit-note-billing" data-id="' . (int) $billing->id . '" data-document="' . e(trim($billing->serie . '-' . $billing->correlativo)) . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6H8L12.5 1.5L11.08 0.08L4.17 7L11.08 13.92L12.5 12.5L8 8H20V18H4V10H2V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V8C22 6.9 21.1 6 20 6Z"></path></svg>
                                    <span> Anular con nota de credito</span>
                                </a>';
                    }

                    if ($allowDebitNoteAction && in_array((string) $billing->tipo_comprobante_codigo, ['01', '03'], true)) {
                        $menu .= '<a class="dropdown-item btn-debit-note-billing" data-id="' . (int) $billing->id . '" data-document="' . e(trim($billing->serie . '-' . $billing->correlativo)) . '" href="javascript:void(0);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="menu-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z"></path></svg>
                                    <span> Nota de debito</span>
                                </a>';
                    }
                }

                $menu .= '</div></div>';

                return $menu;
            })
            ->rawColumns(['comprobante', 'cliente_info', 'almacen_badge', 'total', 'xml', 'cdr_archivo', 'sunat_badge', 'estado_badge', 'acciones'])
            ->toJson();
    }

    public function print_ticket(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $billing = $this->accessibleBillingsQuery()
            ->with(['customer.tipoDocumento', 'user', 'currency', 'typeDocument'])
            ->find($request->input('id'));

        if (! $billing) {
            return response()->json([
                'status' => false,
                'msg' => 'El comprobante no existe.',
                'type' => 'warning',
            ]);
        }

        $baseName = trim((string) ($billing->nticket ?: $billing->typeDocument?->codigo . '-' . $billing->serie . '-' . $billing->correlativo));
        $ticket = $this->buildBillingTicket($billing, $baseName);

        return response()->json([
            'status' => true,
            'pdf' => basename($ticket['path']),
        ]);
    }

    public function print_a4(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning',
            ]);
        }

        $billing = $this->accessibleBillingsQuery()
            ->with(['customer.tipoDocumento', 'user', 'currency', 'typeDocument'])
            ->find($request->input('id'));

        if (! $billing) {
            return response()->json([
                'status' => false,
                'msg' => 'El comprobante no existe.',
                'type' => 'warning',
            ]);
        }

        $baseName = trim((string) ($billing->nticket ?: $billing->typeDocument?->codigo . '-' . $billing->serie . '-' . $billing->correlativo));
        $pdf = $this->buildBillingA4($billing, $baseName);

        return response()->json([
            'status' => true,
            'pdf' => basename($pdf['path']),
        ]);
    }

    public function download_xml(int $id)
    {
        $billing = $this->findBillingOrFail($id);
        $xmlPath = $this->billingXmlPath($billing);

        abort_unless(is_file($xmlPath), 404);

        return response()->file($xmlPath);
    }

    public function download_cdr(int $id)
    {
        $billing = $this->findBillingOrFail($id);
        $cdrPath = $this->billingCdrViewPath($billing);

        abort_unless(is_file($cdrPath), 404);

        return response()->file($cdrPath);
    }

    public function dispatch(int $id)
    {
        $billing = $this->findBillingOrFail($id);

        if ((bool) $billing->anulado) {
            return response()->json([
                'status' => false,
                'msg' => 'No se puede enviar a SUNAT un comprobante anulado.',
                'type' => 'warning',
            ], 422);
        }

        try {
            $result = $this->dispatchService->dispatch($billing);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'status' => false,
                'msg' => $exception->getMessage(),
                'type' => 'warning',
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => false,
                'msg' => $exception->getMessage() ?: 'No se pudo procesar el envío a SUNAT.',
                'type' => 'warning',
            ], 422);
        }

        $billing->refresh();
        $baseName = trim((string) ($billing->nticket ?: $billing->typeDocument?->codigo . '-' . $billing->serie . '-' . $billing->correlativo));
        $this->buildBillingTicket($billing, $baseName);

        return response()->json([
            'status' => (bool) ($result['ok'] ?? false),
            'msg' => (string) ($result['message'] ?? 'Se procesó el envío a SUNAT.'),
            'type' => (bool) ($result['ok'] ?? false) ? 'success' : 'warning',
        ], (bool) ($result['ok'] ?? false) ? 200 : 422);
    }

    public function create_credit_note(Request $request, int $id)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo.',
                'type' => 'warning',
            ], 422);
        }

        $request->validate([
            'credit_note_type_id' => ['required', 'integer', 'exists:credit_note_types,id'],
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'credit_note_type_id.required' => 'Debe seleccionar el motivo de la nota de credito.',
            'reason.required' => 'Debe indicar el motivo de la anulacion.',
        ]);

        $original = $this->salesBillingsQuery()
            ->with(['typeDocument', 'customer', 'currency', 'payMode', 'user', 'archingCash', 'warehouse', 'details'])
            ->find($id);

        if (! $original) {
            return response()->json([
                'status' => false,
                'msg' => 'El comprobante no existe.',
                'type' => 'warning',
            ], 404);
        }

        if (! in_array((string) $original->typeDocument?->codigo, ['01', '03'], true)) {
            return response()->json([
                'status' => false,
                'msg' => 'Solo se puede emitir nota de credito sobre facturas o boletas.',
                'type' => 'warning',
            ], 422);
        }

        if ((int) ($original->cdr ?? 0) !== 1 || (int) ($original->estado_cpe ?? -1) !== 0) {
            return response()->json([
                'status' => false,
                'msg' => 'El comprobante original debe estar aceptado por SUNAT antes de anularlo con nota de credito.',
                'type' => 'warning',
            ], 422);
        }

        if ((bool) $original->anulado) {
            return response()->json([
                'status' => false,
                'msg' => 'El comprobante ya se encuentra anulado.',
                'type' => 'warning',
            ], 422);
        }

        $creditNoteType = CreditNoteType::query()
            ->where('estado', true)
            ->whereIn('codigo', ['01', '02'])
            ->find((int) $request->input('credit_note_type_id'));

        if (! $creditNoteType) {
            return response()->json([
                'status' => false,
                'msg' => 'Solo se permiten motivos de anulacion total en esta etapa.',
                'type' => 'warning',
            ], 422);
        }

        $existingCreditNote = $this->billingsByCurrentWarehouse()
            ->with(['typeDocument'])
            ->where('idfactura_anular', $original->id)
            ->whereHas('typeDocument', fn ($query) => $query->where('codigo', '07'))
            ->latest('id')
            ->first();

        if ($existingCreditNote && (int) ($existingCreditNote->cdr ?? 0) === 1 && (int) ($existingCreditNote->estado_cpe ?? -1) === 0) {
            return response()->json([
                'status' => false,
                'msg' => 'Ya existe una nota de credito aceptada para este comprobante: ' . $existingCreditNote->serie . '-' . $existingCreditNote->correlativo . '.',
                'type' => 'warning',
            ], 422);
        }

        $reason = trim((string) $request->input('reason'));
        $creditNote = $existingCreditNote;

        if (! $creditNote) {
            try {
                $creditNote = $this->storeCreditNoteFromBilling($original, $creditNoteType, $reason);
            } catch (\Throwable $exception) {
                return response()->json([
                    'status' => false,
                    'msg' => $exception->getMessage() ?: 'No se pudo generar la nota de credito.',
                    'type' => 'warning',
                ], 422);
            }
        } elseif (blank($creditNote->motivo)) {
            $creditNote->update(['motivo' => $reason]);
        }

        try {
            $result = $this->dispatchService->dispatch($creditNote->fresh([
                'typeDocument',
                'parentBilling.typeDocument',
                'noteType',
                'customer.tipoDocumento',
                'currency',
                'payMode',
                'details',
            ]));

            if ((bool) ($result['ok'] ?? false)) {
                $original->forceFill(['anulado' => true])->saveQuietly();
            }

            return response()->json([
                'status' => (bool) ($result['ok'] ?? false),
                'msg' => (bool) ($result['ok'] ?? false)
                    ? 'Nota de credito emitida correctamente. SUNAT acepto la anulacion.'
                    : 'La nota de credito se registro, pero quedo pendiente de envio a SUNAT.',
                'detail' => (string) ($result['message'] ?? ''),
                'documento' => trim((string) ($creditNote->serie . '-' . $creditNote->correlativo)),
                'type' => (bool) ($result['ok'] ?? false) ? 'success' : 'warning',
            ], (bool) ($result['ok'] ?? false) ? 200 : 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => false,
                'msg' => 'La nota de credito se registro, pero no pudo enviarse a SUNAT en este momento.',
                'detail' => $exception->getMessage(),
                'documento' => trim((string) ($creditNote->serie . '-' . $creditNote->correlativo)),
                'type' => 'warning',
            ], 422);
        }
    }

    public function create_debit_note(Request $request, int $id)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo.',
                'type' => 'warning',
            ], 422);
        }

        $request->validate([
            'debit_note_type_id' => ['required', 'integer', 'exists:debit_note_types,id'],
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'debit_note_type_id.required' => 'Debe seleccionar el motivo de la nota de debito.',
            'reason.required' => 'Debe indicar el motivo del ajuste.',
        ]);

        $original = $this->salesBillingsQuery()
            ->with(['typeDocument', 'customer', 'currency', 'payMode', 'user', 'archingCash', 'warehouse', 'details'])
            ->find($id);

        if (! $original) {
            return response()->json([
                'status' => false,
                'msg' => 'El comprobante no existe.',
                'type' => 'warning',
            ], 404);
        }

        if (! in_array((string) $original->typeDocument?->codigo, ['01', '03'], true)) {
            return response()->json([
                'status' => false,
                'msg' => 'Solo se puede emitir nota de debito sobre facturas o boletas.',
                'type' => 'warning',
            ], 422);
        }

        if ((int) ($original->cdr ?? 0) !== 1 || (int) ($original->estado_cpe ?? -1) !== 0) {
            return response()->json([
                'status' => false,
                'msg' => 'El comprobante original debe estar aceptado por SUNAT antes de emitir una nota de debito.',
                'type' => 'warning',
            ], 422);
        }

        if ((bool) $original->anulado) {
            return response()->json([
                'status' => false,
                'msg' => 'El comprobante original se encuentra anulado.',
                'type' => 'warning',
            ], 422);
        }

        $debitNoteType = DebitNoteType::query()
            ->where('estado', true)
            ->find((int) $request->input('debit_note_type_id'));

        if (! $debitNoteType) {
            return response()->json([
                'status' => false,
                'msg' => 'El motivo de nota de debito no es valido.',
                'type' => 'warning',
            ], 422);
        }

        $reason = trim((string) $request->input('reason'));

        try {
            $debitNote = $this->storeDebitNoteFromBilling($original, $debitNoteType, $reason);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => false,
                'msg' => $exception->getMessage() ?: 'No se pudo generar la nota de debito.',
                'type' => 'warning',
            ], 422);
        }

        try {
            $result = $this->dispatchService->dispatch($debitNote->fresh([
                'typeDocument',
                'parentBilling.typeDocument',
                'debitNoteType',
                'customer.tipoDocumento',
                'currency',
                'payMode',
                'details',
            ]));

            return response()->json([
                'status' => (bool) ($result['ok'] ?? false),
                'msg' => (bool) ($result['ok'] ?? false)
                    ? 'Nota de debito emitida correctamente. SUNAT acepto el documento.'
                    : 'La nota de debito se registro, pero quedo pendiente de envio a SUNAT.',
                'detail' => (string) ($result['message'] ?? ''),
                'documento' => trim((string) ($debitNote->serie . '-' . $debitNote->correlativo)),
                'type' => (bool) ($result['ok'] ?? false) ? 'success' : 'warning',
            ], (bool) ($result['ok'] ?? false) ? 200 : 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => false,
                'msg' => 'La nota de debito se registro, pero no pudo enviarse a SUNAT en este momento.',
                'detail' => $exception->getMessage(),
                'documento' => trim((string) ($debitNote->serie . '-' . $debitNote->correlativo)),
                'type' => 'warning',
            ], 422);
        }
    }

    protected function buildBillingTicket(Billing $billing, string $name): array
    {
        $billing->loadMissing(['customer.tipoDocumento', 'user', 'currency', 'typeDocument', 'warehouse']);

        $business = $this->resolveBusinessForWarehouse(Business::find(1), $billing->warehouse);
        $payments = DetailPayment::select('detail_payments.*', 'pay_modes.descripcion as modo_pago')
            ->join('pay_modes', 'detail_payments.idpago', '=', 'pay_modes.id')
            ->where('idfactura', $billing->id)
            ->where('idtipo_comprobante', $billing->idtipo_comprobante)
            ->get();
        $details = DetailBilling::select('detail_billings.*', 'products.descripcion as producto')
            ->join('products', 'detail_billings.idproducto', '=', 'products.id')
            ->where('idfacturacion', $billing->id)
            ->get();

        $formatter = new NumeroALetras();
        $qrImage = $this->ensureBillingQrImage($billing);
        $data = [
            'name' => $name,
            'business' => $business,
            'document_label' => $billing->typeDocument?->descripcion ?? 'COMPROBANTE',
            'document_number' => $billing->serie . ' - ' . $billing->correlativo,
            'customer_name' => $billing->customer?->nombres ?? 'Cliente',
            'customer_document_label' => $billing->customer?->tipoDocumento?->descripcion ?? 'Documento',
            'customer_document_value' => $billing->customer?->nro_documento ?? '-',
            'customer_address' => $billing->customer?->direccion ?? '-',
            'issued_at' => date('d/m/Y', strtotime((string) $billing->fecha_emision)) . ' ' . $billing->hora,
            'seller' => mb_strtoupper((string) ($billing->user->user ?? '')),
            'items' => $details,
            'subtotal' => $billing->gravada,
            'igv' => $billing->igv,
            'total' => $billing->total,
            'amount_in_words' => $formatter->toWords((float) $billing->total, 2),
            'payment_modes' => $payments,
            'count_payment' => $payments->count(),
            'signo' => $this->signo_pais(),
            'moneda' => $this->moneda_pais(),
            'qr_image_path' => $qrImage,
            'show_qr' => true,
        ];

        $path = public_path('files/billings/ticket');
        File::ensureDirectoryExists($path);

        $pdfPath = $path . DIRECTORY_SEPARATOR . $name . '.pdf';
        $pdf = Pdf::loadView('admin.pos.ticket_document', $data)->setPaper([0, 0, 226.77, 900.00], 'portrait');
        $pdf->save($pdfPath);

        return [
            'path' => $pdfPath,
            'url' => asset('files/billings/ticket/' . $name . '.pdf'),
        ];
    }

    protected function buildBillingA4(Billing $billing, string $name): array
    {
        $billing->loadMissing(['customer.tipoDocumento', 'user', 'currency', 'typeDocument', 'warehouse']);

        $business = $this->resolveBusinessForWarehouse(Business::find(1), $billing->warehouse);
        $payments = DetailPayment::select('detail_payments.*', 'pay_modes.descripcion as modo_pago')
            ->join('pay_modes', 'detail_payments.idpago', '=', 'pay_modes.id')
            ->where('idfactura', $billing->id)
            ->where('idtipo_comprobante', $billing->idtipo_comprobante)
            ->get();
        $details = DetailBilling::select('detail_billings.*', 'products.descripcion as producto')
            ->join('products', 'detail_billings.idproducto', '=', 'products.id')
            ->where('idfacturacion', $billing->id)
            ->get();

        $formatter = new NumeroALetras();
        $data = [
            'quote' => (object) [
                'serie' => $billing->serie,
                'correlativo' => $billing->correlativo,
                'total' => $billing->total,
                'subtotal' => $billing->gravada,
                'igv' => $billing->igv,
                'observaciones' => $billing->observaciones,
            ],
            'moneda' => $this->moneda_pais(),
            'signo' => $this->signo_pais(),
            'business' => $business,
            'client' => $billing->customer,
            'name_quote' => $name,
            'logo' => $business?->logo,
            'type_document' => $billing->typeDocument,
            'numero_letras' => $formatter->toWords((float) $billing->total, 2),
            'detail' => $details,
            'payment_modes' => $payments,
        ];

        $path = public_path('files/billings/a4');
        File::ensureDirectoryExists($path);

        $pdfPath = $path . DIRECTORY_SEPARATOR . $name . '.pdf';
        $pdf = Pdf::loadView('admin.quotes.pdf', $data)->setPaper('A4', 'portrait');
        $pdf->save($pdfPath);

        return [
            'path' => $pdfPath,
            'url' => asset('files/billings/a4/' . $name . '.pdf'),
        ];
    }

    protected function ensureBillingQrImage(Billing $billing): ?string
    {
        try {
            $payload = $this->payloadBuilder->build($billing);
        } catch (\Throwable $exception) {
            return null;
        }

        $business = Business::find(1);
        if (! $business) {
            return null;
        }

        $filename = trim((string) ($billing->serie . '-' . $billing->correlativo)) . '.png';
        $relativePath = 'files/billings/qr/' . $filename;
        $absolutePath = public_path($relativePath);

        if (! is_file($absolutePath)) {
            File::ensureDirectoryExists(dirname($absolutePath));

            $qrText = implode('|', [
                (string) ($business->ruc ?? ''),
                (string) ($payload['documento']['tipo'] ?? ''),
                (string) ($payload['documento']['serie'] ?? ''),
                (string) ($payload['documento']['correlativo'] ?? ''),
                number_format((float) ($payload['totales']['igv'] ?? 0), 2, '.', ''),
                number_format((float) ($payload['totales']['importe_total'] ?? 0), 2, '.', ''),
                (string) ($payload['documento']['fecha_emision'] ?? ''),
                (string) ($payload['cliente']['tipo_documento'] ?? ''),
                (string) ($payload['cliente']['numero_documento'] ?? ''),
                '',
            ]);

            File::put(
                $absolutePath,
                QrCode::format('png')->size(140)->margin(0)->generate($qrText)
            );
        }

        if ($billing->qr !== $relativePath) {
            $billing->forceFill(['qr' => $relativePath])->saveQuietly();
        }

        return is_file($absolutePath) ? $absolutePath : null;
    }

    protected function findBillingOrFail(int $id): Billing
    {
        return $this->accessibleBillingsQuery()->findOrFail($id);
    }

    protected function salesBillingsQuery()
    {
        return $this->accessibleBillingsQuery()
            ->whereHas('typeDocument', function ($query) {
                $query->whereIn('codigo', ['01', '03']);
            });
    }

    protected function creditNotesBillingsQuery()
    {
        return $this->accessibleBillingsQuery()
            ->whereHas('typeDocument', function ($query) {
                $query->where('codigo', '07');
            });
    }

    protected function debitNotesBillingsQuery()
    {
        return $this->accessibleBillingsQuery()
            ->whereHas('typeDocument', function ($query) {
                $query->where('codigo', '08');
            });
    }

    protected function accessibleBillingsQuery()
    {
        return $this->billingsByCurrentWarehouse()
            ->whereHas('typeDocument', function ($query) {
                $query->whereIn('codigo', ['01', '03', '07', '08']);
            });
    }

    protected function billingXmlExists($billing): bool
    {
        return is_file($this->billingXmlPath($billing));
    }

    protected function billingCdrExists($billing): bool
    {
        return is_file($this->billingCdrViewPath($billing));
    }

    protected function billingXmlPath($billing): string
    {
        $business = Business::findOrFail(1);
        $typeCode = trim((string) ($billing->tipo_comprobante_codigo ?? $billing->typeDocument?->codigo ?? ''));
        $baseName = $business->ruc . '-' . $typeCode . '-' . $billing->serie . '-' . $billing->correlativo;

        return $this->storagePath->xmlDirectory($business) . DIRECTORY_SEPARATOR . $baseName . '.XML';
    }

    protected function billingCdrZipPath($billing): string
    {
        $business = Business::findOrFail(1);
        $typeCode = trim((string) ($billing->tipo_comprobante_codigo ?? $billing->typeDocument?->codigo ?? ''));
        $baseName = $business->ruc . '-' . $typeCode . '-' . $billing->serie . '-' . $billing->correlativo;

        return $this->storagePath->cdrDirectory($business) . DIRECTORY_SEPARATOR . 'R-' . $baseName . '.ZIP';
    }

    protected function billingCdrXmlPath($billing): string
    {
        $business = Business::findOrFail(1);
        $typeCode = trim((string) ($billing->tipo_comprobante_codigo ?? $billing->typeDocument?->codigo ?? ''));
        $baseName = $business->ruc . '-' . $typeCode . '-' . $billing->serie . '-' . $billing->correlativo;
        $folder = $this->storagePath->cdrDirectory($business) . DIRECTORY_SEPARATOR . 'R-' . $baseName;
        $upper = $folder . DIRECTORY_SEPARATOR . 'R-' . $baseName . '.XML';
        $lower = $folder . DIRECTORY_SEPARATOR . 'R-' . $baseName . '.xml';

        return is_file($upper) ? $upper : $lower;
    }

    protected function billingCdrViewPath($billing): string
    {
        $cdrXmlPath = $this->billingCdrXmlPath($billing);

        return is_file($cdrXmlPath) ? $cdrXmlPath : $this->billingCdrZipPath($billing);
    }

    protected function activeDebitNoteTypes()
    {
        $defaults = [
            ['codigo' => '01', 'descripcion' => 'INTERESES POR MORA', 'estado' => true],
            ['codigo' => '02', 'descripcion' => 'AUMENTO EN EL VALOR', 'estado' => true],
            ['codigo' => '03', 'descripcion' => 'PENALIDADES / OTROS CONCEPTOS', 'estado' => true],
            ['codigo' => '11', 'descripcion' => 'AJUSTES DE OPERACIONES DE EXPORTACION', 'estado' => true],
            ['codigo' => '12', 'descripcion' => 'AJUSTES AFECTOS AL IVAP', 'estado' => true],
        ];

        foreach ($defaults as $row) {
            DebitNoteType::updateOrCreate(['codigo' => $row['codigo']], $row);
        }

        return DebitNoteType::query()
            ->where('estado', true)
            ->orderBy('codigo')
            ->get();
    }

    protected function resolveBusinessForWarehouse(?Business $business, ?Warehouse $warehouse): ?Business
    {
        if (! $business) {
            return null;
        }

        $documentBusiness = clone $business;
        $documentBusiness->direccion_principal = $business->direccion;
        $documentBusiness->direccion_sucursal = null;
        $documentBusiness->direccion_documento = $business->direccion;

        if ($warehouse && filled($warehouse->direccion)) {
            $documentBusiness->direccion_sucursal = $warehouse->direccion;
            $documentBusiness->direccion_documento = $warehouse->direccion;
        }

        return $documentBusiness;
    }

    protected function storeCreditNoteFromBilling(Billing $original, CreditNoteType $creditNoteType, string $reason): Billing
    {
        $creditNoteDocumentType = $this->ensureTypeDocument('07', 'NOTA DE CREDITO ELECTRONICA');

        return DB::transaction(function () use ($original, $creditNoteType, $reason, $creditNoteDocumentType) {
            [$serie, $correlativo, $serieModel] = $this->reserveCreditNoteSerieAndCorrelative(
                auth()->user()->idcaja,
                $creditNoteDocumentType,
                $original->typeDocument
            );

            $creditNote = Billing::create([
                'idtipo_comprobante' => (int) $creditNoteDocumentType->id,
                'serie' => $serie,
                'correlativo' => $correlativo,
                'fecha_emision' => now()->toDateString(),
                'fecha_vencimiento' => null,
                'hora' => now()->format('H:i:s'),
                'idcliente' => (int) $original->idcliente,
                'idmoneda' => (int) $original->idmoneda,
                'idpago' => (int) $original->idpago,
                'modo_pago' => $original->modo_pago,
                'sunat_forma_pago' => $original->sunat_forma_pago,
                'exonerada' => (float) $original->exonerada,
                'inafecta' => (float) $original->inafecta,
                'gravada' => (float) $original->gravada,
                'anticipo' => (float) $original->anticipo,
                'igv' => (float) $original->igv,
                'icbper' => (float) $original->icbper,
                'gratuita' => (float) $original->gratuita,
                'otros_cargos' => (float) $original->otros_cargos,
                'total' => (float) $original->total,
                'monto_credito' => 0,
                'cuotas' => null,
                'payment_breakdown' => $original->payment_breakdown,
                'observaciones' => $original->observaciones,
                'cdr' => null,
                'anulado' => false,
                'id_tipo_nota_credito' => (int) $creditNoteType->id,
                'idfactura_anular' => (int) $original->id,
                'motivo' => $reason,
                'estado_cpe' => null,
                'errores' => null,
                'nticket' => null,
                'idusuario' => auth()->id(),
                'idarqueocaja' => $original->idarqueocaja ? (int) $original->idarqueocaja : null,
                'vuelto' => 0,
                'qr' => null,
                'idalmacen' => $original->idalmacen ? (int) $original->idalmacen : null,
            ]);

            foreach ($original->details as $detail) {
                DetailBilling::create([
                    'idfacturacion' => $creditNote->id,
                    'idproducto' => (int) $detail->idproducto,
                    'cantidad' => (float) $detail->cantidad,
                    'descuento' => (float) $detail->descuento,
                    'igv' => (float) $detail->igv,
                    'icbper' => (float) ($detail->icbper ?? 0),
                    'factor_icbper' => (float) ($detail->factor_icbper ?? 0),
                    'cantidad_bolsas' => (float) ($detail->cantidad_bolsas ?? 0),
                    'id_afectacion_igv' => (int) $detail->id_afectacion_igv,
                    'precio_unitario' => (float) $detail->precio_unitario,
                    'valor_unitario' => (float) $detail->valor_unitario,
                    'valor_total' => (float) $detail->valor_total,
                    'precio_total' => (float) $detail->precio_total,
                ]);
            }

            if ($serieModel) {
                $serieModel->forceFill(['correlativo' => $correlativo])->saveQuietly();
            }

            return $creditNote;
        });
    }

    protected function reserveCreditNoteSerieAndCorrelative(int $cashId, TypeDocument $creditNoteTypeDocument, ?TypeDocument $relatedDocumentType): array
    {
        $serieModel = Serie::query()
            ->where('idtipo_documento', $creditNoteTypeDocument->id)
            ->where('idcaja', $cashId)
            ->when($relatedDocumentType, function ($query) use ($relatedDocumentType) {
                $query->where('idtipo_documento_relacionado', $relatedDocumentType->id);
            })
            ->where('estado', true)
            ->lockForUpdate()
            ->first();

        if (! $serieModel) {
            $serieModel = $this->ensureRelatedNoteSerie($creditNoteTypeDocument, $cashId, $relatedDocumentType, 'FC01', 'BC01', 'NC01');
        }

        $last = Billing::query()
            ->where('idtipo_comprobante', $creditNoteTypeDocument->id)
            ->where('serie', $serieModel->serie)
            ->max('correlativo');

        $next = str_pad((string) (((int) $last) + 1), 8, '0', STR_PAD_LEFT);

        return [$serieModel->serie, $next, $serieModel];
    }

    protected function storeDebitNoteFromBilling(Billing $original, DebitNoteType $debitNoteType, string $reason): Billing
    {
        $debitNoteDocumentType = $this->ensureTypeDocument('08', 'NOTA DE DEBITO ELECTRONICA');

        return DB::transaction(function () use ($original, $debitNoteType, $reason, $debitNoteDocumentType) {
            [$serie, $correlativo, $serieModel] = $this->reserveDebitNoteSerieAndCorrelative(
                auth()->user()->idcaja,
                $debitNoteDocumentType,
                $original->typeDocument
            );

            $debitNote = Billing::create([
                'idtipo_comprobante' => (int) $debitNoteDocumentType->id,
                'serie' => $serie,
                'correlativo' => $correlativo,
                'fecha_emision' => now()->toDateString(),
                'fecha_vencimiento' => null,
                'hora' => now()->format('H:i:s'),
                'idcliente' => (int) $original->idcliente,
                'idmoneda' => (int) $original->idmoneda,
                'idpago' => (int) $original->idpago,
                'modo_pago' => $original->modo_pago,
                'sunat_forma_pago' => $original->sunat_forma_pago,
                'exonerada' => (float) $original->exonerada,
                'inafecta' => (float) $original->inafecta,
                'gravada' => (float) $original->gravada,
                'anticipo' => (float) $original->anticipo,
                'igv' => (float) $original->igv,
                'icbper' => (float) $original->icbper,
                'gratuita' => (float) $original->gratuita,
                'otros_cargos' => (float) $original->otros_cargos,
                'total' => (float) $original->total,
                'monto_credito' => 0,
                'cuotas' => null,
                'payment_breakdown' => $original->payment_breakdown,
                'observaciones' => $original->observaciones,
                'cdr' => null,
                'anulado' => false,
                'id_tipo_nota_credito' => null,
                'id_tipo_nota_debito' => (int) $debitNoteType->id,
                'idfactura_anular' => (int) $original->id,
                'motivo' => $reason,
                'estado_cpe' => null,
                'errores' => null,
                'nticket' => null,
                'idusuario' => auth()->id(),
                'idarqueocaja' => $original->idarqueocaja ? (int) $original->idarqueocaja : null,
                'vuelto' => 0,
                'qr' => null,
                'idalmacen' => $original->idalmacen ? (int) $original->idalmacen : null,
            ]);

            foreach ($original->details as $detail) {
                DetailBilling::create([
                    'idfacturacion' => $debitNote->id,
                    'idproducto' => (int) $detail->idproducto,
                    'cantidad' => (float) $detail->cantidad,
                    'descuento' => (float) $detail->descuento,
                    'igv' => (float) $detail->igv,
                    'icbper' => (float) ($detail->icbper ?? 0),
                    'factor_icbper' => (float) ($detail->factor_icbper ?? 0),
                    'cantidad_bolsas' => (float) ($detail->cantidad_bolsas ?? 0),
                    'id_afectacion_igv' => (int) $detail->id_afectacion_igv,
                    'precio_unitario' => (float) $detail->precio_unitario,
                    'valor_unitario' => (float) $detail->valor_unitario,
                    'valor_total' => (float) $detail->valor_total,
                    'precio_total' => (float) $detail->precio_total,
                ]);
            }

            if ($serieModel) {
                $serieModel->forceFill(['correlativo' => $correlativo])->saveQuietly();
            }

            return $debitNote;
        });
    }

    protected function reserveDebitNoteSerieAndCorrelative(int $cashId, TypeDocument $debitNoteTypeDocument, ?TypeDocument $relatedDocumentType): array
    {
        $serieModel = Serie::query()
            ->where('idtipo_documento', $debitNoteTypeDocument->id)
            ->where('idcaja', $cashId)
            ->when($relatedDocumentType, function ($query) use ($relatedDocumentType) {
                $query->where('idtipo_documento_relacionado', $relatedDocumentType->id);
            })
            ->where('estado', true)
            ->lockForUpdate()
            ->first();

        if (! $serieModel) {
            $serieModel = $this->ensureRelatedNoteSerie($debitNoteTypeDocument, $cashId, $relatedDocumentType, 'FD01', 'BD01', 'ND01');
        }

        $last = Billing::query()
            ->where('idtipo_comprobante', $debitNoteTypeDocument->id)
            ->where('serie', $serieModel->serie)
            ->max('correlativo');

        $next = str_pad((string) (((int) $last) + 1), 8, '0', STR_PAD_LEFT);

        return [$serieModel->serie, $next, $serieModel];
    }

    protected function ensureTypeDocument(string $codigo, string $descripcion): TypeDocument
    {
        return TypeDocument::query()->updateOrCreate(
            ['codigo' => $codigo],
            ['descripcion' => $descripcion, 'estado' => 1]
        );
    }

    protected function ensureRelatedNoteSerie(
        TypeDocument $noteTypeDocument,
        int $cashId,
        ?TypeDocument $relatedDocumentType,
        string $invoiceSerie,
        string $billSerie,
        string $defaultSerie
    ): Serie {
        $relatedCode = trim((string) ($relatedDocumentType?->codigo ?? ''));

        $serieCode = match ($relatedCode) {
            '01' => $invoiceSerie,
            '03' => $billSerie,
            default => $defaultSerie,
        };

        return Serie::query()->updateOrCreate(
            [
                'serie' => $serieCode,
                'idtipo_documento' => $noteTypeDocument->id,
                'idcaja' => $cashId,
            ],
            [
                'correlativo' => '00000001',
                'idtipo_documento_relacionado' => $relatedDocumentType?->id,
                'direccion' => null,
                'estado' => true,
            ]
        );
    }
}
