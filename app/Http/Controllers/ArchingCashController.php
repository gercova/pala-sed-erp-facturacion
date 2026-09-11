<?php

namespace App\Http\Controllers;

use App\Models\ArchingCash;
use App\Models\Billing;
use App\Models\Business;
use App\Models\Cash;
use App\Models\DetailPayment;
use App\Models\SaleNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ArchingCashController extends Controller
{
    public function index()
    {
        $user = Auth::user()->loadMissing(['roles', 'activeWarehouse']);
        $assignedCash = $this->resolveAssignedCash($user);
        $openArchings = $this->accessibleArchingsQuery()
            ->with(['cash', 'user'])
            ->where('idusuario', $user->id)
            ->where('estado', 1)
            ->orderByDesc('id')
            ->get();

        return view('admin.arching_cashes.list', [
            'signo' => $this->signo_pais(),
            'assignedCash' => $assignedCash,
            'currentWarehouse' => $user->activeWarehouse,
            'openArching' => $openArchings->first(),
            'openArchings' => $openArchings,
            'canOpenArching' => (bool) $assignedCash && $openArchings->isEmpty(),
            'filterCashes' => $this->availableCashesForCurrentWarehouse($assignedCash),
        ]);
    }

    public function get(Request $request)
    {
        $archingCashes = $this->accessibleArchingsQuery()
            ->select('arching_cashes.*', 'users.nombres as usuario', 'cashes.descripcion as caja')
            ->join('users', 'arching_cashes.idusuario', '=', 'users.id')
            ->join('cashes', 'arching_cashes.idcaja', '=', 'cashes.id')
            ->when($request->filled('filter_date'), function ($query) use ($request) {
                $query->whereDate('arching_cashes.fecha_inicio', $request->input('filter_date'));
            })
            ->when($request->filled('filter_responsible'), function ($query) use ($request) {
                $query->where('users.nombres', 'like', '%' . trim((string) $request->input('filter_responsible')) . '%');
            })
            ->when($request->filled('filter_cash'), function ($query) use ($request) {
                $query->where('arching_cashes.idcaja', (int) $request->input('filter_cash'));
            })
            ->when($request->filled('filter_status'), function ($query) use ($request) {
                $query->where('arching_cashes.estado', (int) $request->input('filter_status'));
            })
            ->orderByDesc('arching_cashes.id');

        return datatables()
            ->of($archingCashes)
            ->editColumn('fecha_inicio', function ($archingCash) {
                return optional($archingCash->fecha_inicio)->format('d/m/Y') ?: '-';
            })
            ->addColumn('responsable', function ($archingCash) {
                return '<div class="fw-semibold">' . e(mb_strtoupper((string) $archingCash->usuario)) . '</div>';
            })
            ->addColumn('caja_info', function ($archingCash) {
                return '<div class="ac-cash-cell">'
                    . '<div class="ac-cash-name">' . e((string) $archingCash->caja) . '</div>'
                    . '<small class="ac-cash-warehouse">' . e((string) (Auth::user()->activeWarehouse?->descripcion ?: 'Almacen activo')) . '</small>'
                    . '</div>';
            })
            ->addColumn('monto_apertura', function ($archingCash) {
                return '<span class="fw-semibold">' . e($this->signo_pais() . ' ' . number_format((float) $archingCash->monto_inicial, 2, '.', '')) . '</span>';
            })
            ->addColumn('monto_cierre', function ($archingCash) {
                if ((int) $archingCash->estado === 1) {
                    return '<span class="text-muted">Pendiente</span>';
                }

                return '<span class="fw-semibold">' . e($this->signo_pais() . ' ' . number_format((float) $archingCash->monto_final, 2, '.', '')) . '</span>';
            })
            ->addColumn('estado_badge', function ($archingCash) {
                if ((int) $archingCash->estado === 1) {
                    return '<span class="badge bg-success-subtle text-success">Abierta</span>';
                }

                return '<span class="badge bg-secondary-subtle text-secondary">Cerrada</span>';
            })
            ->addColumn('acciones', function ($archingCash) {
                $closeAction = (int) $archingCash->estado === 1
                    ? '<a class="dropdown-item btn-close-arching" data-id="' . (int) $archingCash->id . '" href="javascript:void(0);">
                            <i class="ri-lock-line me-2 text-danger"></i>
                            <span>Cerrar caja</span>
                       </a>'
                    : '';

                return '<div class="dropdown">
                            <a href="#" role="button" id="dropdownArching' . (int) $archingCash->id . '" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M2 18H9V20H2V18ZM2 11H11V13H2V11ZM2 4H22V6H2V4ZM20.674 13.0251L21.8301 12.634L22.8301 14.366L21.914 15.1711C21.9704 15.4386 22 15.7158 22 16C22 16.2842 21.9704 16.5614 21.914 16.8289L22.8301 17.634L21.8301 19.366L20.674 18.9749C20.2635 19.3441 19.7763 19.6295 19.2391 19.8044L19 21H17L16.7609 19.8044C16.2237 19.6295 15.7365 19.3441 15.326 18.9749L14.1699 19.366L13.1699 17.634L14.086 16.8289C14.0296 16.5614 14 16.2842 14 16C14 15.7158 14.0296 15.4386 14.086 15.1711L13.1699 14.366L14.1699 12.634L15.326 13.0251C15.7365 12.6559 16.2237 12.3705 16.7609 12.1956L17 11H19L19.2391 12.1956C19.7763 12.3705 20.2635 12.6559 20.674 13.0251ZM18 18C19.1046 18 20 17.1046 20 16C20 14.8954 19.1046 14 18 14C16.8954 14 16 14.8954 16 16C16 17.1046 16.8954 18 18 18Z"></path></svg>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownArching' . (int) $archingCash->id . '">
                                <a class="dropdown-item btn-view-summary" data-id="' . (int) $archingCash->id . '" href="javascript:void(0);">
                                    <i class="ri-eye-line me-2"></i>
                                    <span>Resumen</span>
                                </a>
                                <a class="dropdown-item btn-view-movements" data-id="' . (int) $archingCash->id . '" href="javascript:void(0);">
                                    <i class="ri-file-list-3-line me-2"></i>
                                    <span>Movimientos</span>
                                </a>
                                <a class="dropdown-item btn-print-summary" data-id="' . (int) $archingCash->id . '" href="javascript:void(0);">
                                    <i class="ri-printer-line me-2"></i>
                                    <span>Ticket</span>
                                </a>'
                                . $closeAction .
                            '</div>
                        </div>';
            })
            ->rawColumns(['responsable', 'caja_info', 'monto_apertura', 'monto_cierre', 'estado_badge', 'acciones'])
            ->toJson();
    }

    public function save(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning'
            ]);
        }

        $montoInicial = trim((string) $request->input('monto_inicial'));
        if ($montoInicial === '' || ! is_numeric($montoInicial) || (float) $montoInicial < 0) {
            return response()->json([
                'status' => false,
                'msg' => 'Debe ingresar un monto inicial valido.',
                'type' => 'warning'
            ], 422);
        }

        $user = Auth::user();
        $cash = $this->resolveAssignedCash($user);

        if (! $cash) {
            return response()->json([
                'status' => false,
                'msg' => 'El usuario no tiene una caja asignada.',
                'type' => 'warning'
            ], 422);
        }

        $openArching = ArchingCash::query()
            ->where('idcaja', $cash->id)
            ->where('idusuario', $user->id)
            ->where('estado', 1)
            ->latest('id')
            ->first();

        if ($openArching) {
            return response()->json([
                'status' => false,
                'msg' => 'Primero debe cerrar la caja actual.',
                'type' => 'warning'
            ], 422);
        }

        ArchingCash::create([
            'idcaja' => $cash->id,
            'idusuario' => $user->id,
            'idalmacen' => (int) ($user->idalmacen ?: 0) ?: null,
            'fecha_inicio' => date('Y-m-d'),
            'fecha_fin' => null,
            'monto_inicial' => round((float) $montoInicial, 2),
            'monto_final' => null,
            'total_ventas' => 0,
            'estado' => 1,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Caja aperturada correctamente.',
            'type' => 'success'
        ]);
    }

    public function detail_cash(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning'
            ]);
        }

        $archingCash = $this->findAccessibleArchingCash((int) $request->input('id'));
        if (! $archingCash) {
            return response()->json([
                'status' => false,
                'msg' => 'El arqueo no existe.',
                'type' => 'warning'
            ], 404);
        }

        $summary = $this->buildSummary($archingCash);

        return response()->json([
            'status' => true,
            'archingCash' => [
                'id' => $archingCash->id,
                'cash' => $archingCash->cash?->descripcion,
                'warehouse' => Auth::user()->activeWarehouse?->descripcion ?: 'Almacen activo',
                'responsable' => $archingCash->user?->nombres,
                'estado' => (int) $archingCash->estado,
                'fecha_inicio' => optional($archingCash->fecha_inicio)->format('d/m/Y'),
                'fecha_fin' => optional($archingCash->fecha_fin)->format('d/m/Y'),
            ],
            'summary' => $summary,
        ]);
    }

    public function detail_cashes(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning'
            ]);
        }

        $archingCash = $this->findAccessibleArchingCash((int) $request->input('id'));
        if (! $archingCash) {
            return response()->json([
                'status' => false,
                'msg' => 'El arqueo no existe.',
                'type' => 'warning'
            ], 404);
        }

        $movements = $this->movementRowsBaseQuery($archingCash);

        return datatables()
            ->of($movements)
            ->addColumn('cliente_info', function ($row) {
                return '<div>'
                    . '<div>' . e((string) $row->cliente) . '</div>'
                    . (! empty($row->cliente_documento) ? '<small class="text-muted">' . e((string) $row->cliente_documento) . '</small>' : '')
                    . '</div>';
            })
            ->addColumn('documento_info', function ($row) {
                return '<div>'
                    . '<div class="fw-semibold">' . e((string) $row->documento) . '</div>'
                    . '<small class="text-muted">' . e((string) $row->tipo) . '</small>'
                    . '</div>';
            })
            ->editColumn('fecha', function ($row) {
                return e((string) $row->fecha);
            })
            ->editColumn('total', function ($row) {
                return '<span class="fw-semibold">' . e($this->signo_pais() . ' ' . number_format((float) $row->total, 2, '.', '')) . '</span>';
            })
            ->rawColumns(['cliente_info', 'documento_info', 'total'])
            ->toJson();
    }

    public function get_summary(Request $request)
    {
        return $this->detail_cash($request);
    }

    public function print_summary(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning'
            ]);
        }

        $archingCash = $this->findAccessibleArchingCash((int) $request->input('id'));
        if (! $archingCash) {
            return response()->json([
                'status' => false,
                'msg' => 'El arqueo no existe.',
                'type' => 'warning'
            ], 404);
        }

        $summary = $this->buildSummary($archingCash);
        $business = Business::find(1);
        $filename = 'arqueo-caja-' . $archingCash->id . '-' . date('Ymd') . '.pdf';

        File::ensureDirectoryExists(public_path('files/arching-cashes/ticket'));

        $pdf = Pdf::loadView('admin.arching_cashes.ticket', [
            'business' => $business,
            'archingCash' => $archingCash,
            'summary' => $summary,
            'signo' => $this->signo_pais(),
        ])->setPaper([0, 0, 226.77, 900.00], 'portrait');

        $pdf->save(public_path('files/arching-cashes/ticket/' . $filename));

        return response()->json([
            'status' => true,
            'pdf' => $filename
        ]);
    }

    public function close(Request $request)
    {
        if (! $request->ajax()) {
            return response()->json([
                'status' => false,
                'msg' => 'Intente de nuevo',
                'type' => 'warning'
            ]);
        }

        $archingCash = $this->findAccessibleArchingCash((int) $request->input('id'));
        if (! $archingCash) {
            return response()->json([
                'status' => false,
                'msg' => 'El arqueo no existe.',
                'type' => 'warning'
            ], 404);
        }

        if ((int) $archingCash->estado === 2) {
            return response()->json([
                'status' => false,
                'msg' => 'La caja ya se encuentra cerrada.',
                'type' => 'warning'
            ], 422);
        }

        if ((int) $archingCash->idusuario !== (int) Auth::id()) {
            return response()->json([
                'status' => false,
                'msg' => 'Solo la persona que aperturo esta caja puede cerrarla.',
                'type' => 'warning'
            ], 422);
        }

        $summary = $this->buildSummary($archingCash);

        $archingCash->update([
            'estado' => 2,
            'fecha_fin' => date('Y-m-d'),
            'monto_final' => (float) $summary['expected_final'],
            'total_ventas' => (int) $summary['sales_count'],
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Caja cerrada correctamente.',
            'type' => 'success'
        ]);
    }

    private function accessibleArchingsQuery()
    {
        $user = Auth::user()->loadMissing('roles');
        $role = optional($user->roles->first())->name;
        $warehouseId = (int) ($user->idalmacen ?? 0);

        return ArchingCash::query()
            ->when($warehouseId > 0, function ($query) use ($warehouseId) {
                $query->where('arching_cashes.idalmacen', $warehouseId);
            })
            ->when(! in_array($role, ['SUPERADMIN', 'ADMIN'], true), function ($query) use ($user) {
                $query->where('arching_cashes.idusuario', $user->id);
            });
    }

    private function findAccessibleArchingCash(int $id): ?ArchingCash
    {
        return $this->accessibleArchingsQuery()
            ->with(['cash', 'user'])
            ->where('arching_cashes.id', $id)
            ->first();
    }

    private function buildSummary(ArchingCash $archingCash): array
    {
        $saleNotesValid = SaleNote::query()->where('idarqueocaja', $archingCash->id)->where('estado', 1);
        $saleNotesAnnulled = SaleNote::query()->where('idarqueocaja', $archingCash->id)->where('estado', 2);
        $billingsValid = Billing::query()->where('idarqueocaja', $archingCash->id)->where('anulado', false);
        $billingsAnnulled = Billing::query()->where('idarqueocaja', $archingCash->id)->where('anulado', true);

        $salesCount = (clone $saleNotesValid)->count() + (clone $billingsValid)->count();
        $salesTotal = (float) (clone $saleNotesValid)->sum('total') + (float) (clone $billingsValid)->sum('total');
        $annulledCount = (clone $saleNotesAnnulled)->count() + (clone $billingsAnnulled)->count();
        $annulledTotal = (float) (clone $saleNotesAnnulled)->sum('total') + (float) (clone $billingsAnnulled)->sum('total');
        $grossTotal = $salesTotal + $annulledTotal;
        $expectedFinal = (float) $archingCash->monto_inicial + $salesTotal;
        $displayFinal = (int) $archingCash->estado === 2
            ? (float) ($archingCash->monto_final ?? $expectedFinal)
            : $expectedFinal;

        return [
            'opening_amount' => number_format((float) $archingCash->monto_inicial, 2, '.', ''),
            'sales_count' => (int) $salesCount,
            'sales_total' => number_format($salesTotal, 2, '.', ''),
            'gross_total' => number_format($grossTotal, 2, '.', ''),
            'annulled_count' => (int) $annulledCount,
            'annulled_total' => number_format($annulledTotal, 2, '.', ''),
            'expenses_count' => 0,
            'expenses_total' => number_format(0, 2, '.', ''),
            'expected_final' => number_format($expectedFinal, 2, '.', ''),
            'display_final' => number_format($displayFinal, 2, '.', ''),
            'payment_summary' => $this->buildPaymentSummary($archingCash),
        ];
    }

    private function buildPaymentSummary(ArchingCash $archingCash): array
    {
        $rows = collect();

        $groupedPayments = DetailPayment::query()
            ->select('pay_modes.descripcion as tipo_pago', DB::raw('SUM(detail_payments.monto) as monto'))
            ->join('pay_modes', 'detail_payments.idpago', '=', 'pay_modes.id')
            ->where('detail_payments.idarqueocaja', $archingCash->id)
            ->where('detail_payments.estado', 1)
            ->groupBy('pay_modes.descripcion')
            ->get();

        foreach ($groupedPayments as $payment) {
            $rows->push([
                'label' => $this->normalizePaymentMethodLabel((string) $payment->tipo_pago),
                'total' => round((float) $payment->monto, 2),
            ]);
        }

        if ($rows->isEmpty()) {
            $saleNotes = SaleNote::query()
                ->where('idarqueocaja', $archingCash->id)
                ->where('estado', 1)
                ->get(['payment_breakdown', 'total']);

            foreach ($saleNotes as $saleNote) {
                $breakdown = collect($saleNote->payment_breakdown ?? []);

                if ($breakdown->isEmpty()) {
                    $rows->push([
                        'label' => 'Efectivo',
                        'total' => round((float) $saleNote->total, 2),
                    ]);
                    continue;
                }

                foreach ($breakdown as $item) {
                    $rows->push([
                        'label' => $this->normalizePaymentMethodLabel((string) ($item['method'] ?? 'Efectivo')),
                        'total' => round((float) ($item['amount'] ?? 0), 2),
                    ]);
                }
            }

            $billings = Billing::query()
                ->with('payMode:id,descripcion')
                ->where('idarqueocaja', $archingCash->id)
                ->where('anulado', false)
                ->get(['id', 'idpago', 'payment_breakdown', 'sunat_forma_pago', 'total']);

            foreach ($billings as $billing) {
                $breakdown = collect($billing->payment_breakdown ?? []);

                if ($breakdown->isEmpty()) {
                    $rows->push([
                        'label' => $this->normalizePaymentMethodLabel((string) ($billing->payMode?->descripcion ?: $billing->sunat_forma_pago ?: 'Efectivo')),
                        'total' => round((float) $billing->total, 2),
                    ]);
                    continue;
                }

                foreach ($breakdown as $item) {
                    $rows->push([
                        'label' => $this->normalizePaymentMethodLabel((string) ($item['method'] ?? 'Efectivo')),
                        'total' => round((float) ($item['amount'] ?? 0), 2),
                    ]);
                }
            }
        }

        return $rows
            ->groupBy('label')
            ->map(function ($items, $label) {
                return [
                    'label' => $label,
                    'total' => number_format((float) $items->sum('total'), 2, '.', ''),
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function normalizePaymentMethodLabel(string $method): string
    {
        $normalized = mb_strtolower(trim($method), 'UTF-8');

        return match ($normalized) {
            'efectivo', 'cash', 'contado' => 'Efectivo',
            'yape' => 'Yape',
            'plin' => 'Plin',
            'tarjeta', 'visa', 'mastercard', 'debito', 'credito', 'crédito' => 'Tarjeta',
            'transferencia', 'transferencia bancaria', 'deposito', 'depósito' => 'Transferencia',
            default => $method !== '' ? ucfirst($normalized) : 'Efectivo',
        };
    }

    private function movementRowsBaseQuery(ArchingCash $archingCash)
    {
        $billings = DB::table('billings')
            ->leftJoin('clients', 'clients.id', '=', 'billings.idcliente')
            ->leftJoin('type_documents', 'type_documents.id', '=', 'billings.idtipo_comprobante')
            ->leftJoin('pay_modes', 'pay_modes.id', '=', 'billings.idpago')
            ->where('billings.idarqueocaja', $archingCash->id)
            ->where('billings.anulado', false)
            ->selectRaw("
                DATE_FORMAT(billings.fecha_emision, '%d/%m/%Y') as fecha,
                TIME_FORMAT(billings.hora, '%H:%i') as hora,
                billings.fecha_emision as fecha_orden,
                billings.hora as hora_orden,
                CONCAT(billings.serie, '-', billings.correlativo) as documento,
                COALESCE(type_documents.descripcion, 'Comprobante') as tipo,
                COALESCE(clients.nombres, 'Cliente no disponible') as cliente,
                COALESCE(clients.nro_documento, '') as cliente_documento,
                COALESCE(pay_modes.descripcion, 'Sin medio') as pago,
                billings.total as total
            ");

        $saleNotes = DB::table('sale_notes')
            ->leftJoin('clients', 'clients.id', '=', 'sale_notes.idcliente')
            ->where('sale_notes.idarqueocaja', $archingCash->id)
            ->where('sale_notes.estado', 1)
            ->selectRaw("
                DATE_FORMAT(sale_notes.fecha_emision, '%d/%m/%Y') as fecha,
                TIME_FORMAT(sale_notes.hora, '%H:%i') as hora,
                sale_notes.fecha_emision as fecha_orden,
                sale_notes.hora as hora_orden,
                CONCAT(sale_notes.serie, '-', sale_notes.correlativo) as documento,
                'NOTA DE VENTA' as tipo,
                COALESCE(clients.nombres, 'Cliente no disponible') as cliente,
                COALESCE(clients.nro_documento, '') as cliente_documento,
                'Contado' as pago,
                sale_notes.total as total
            ");

        return DB::query()
            ->fromSub($billings->unionAll($saleNotes), 'movements')
            ->orderByDesc('fecha_orden')
            ->orderByDesc('hora_orden')
            ->orderByDesc('documento');
    }

    private function resolveAssignedCash($user): ?Cash
    {
        $assignedCash = null;

        if (! empty($user->idcaja)) {
            $assignedCash = Cash::query()->find((int) $user->idcaja);
        }

        if ($assignedCash) {
            return $assignedCash;
        }

        $defaultCash = Cash::query()->orderBy('id')->first();

        if (! $defaultCash) {
            return null;
        }

        if ((int) $user->idcaja !== (int) $defaultCash->id) {
            $user->idcaja = (int) $defaultCash->id;
            $user->save();
        }

        return $defaultCash;
    }

    private function availableCashesForCurrentWarehouse(?Cash $assignedCash)
    {
        $warehouseId = (int) (Auth::user()->idalmacen ?? 0);

        $cashes = Cash::query()
            ->when($warehouseId > 0, function ($query) use ($warehouseId) {
                $query->whereIn('id', function ($subQuery) use ($warehouseId) {
                    $subQuery->select('idcaja')
                        ->from('arching_cashes')
                        ->where('idalmacen', $warehouseId)
                        ->whereNotNull('idcaja');
                });
            })
            ->orderBy('descripcion')
            ->get();

        if ($assignedCash && ! $cashes->contains('id', $assignedCash->id)) {
            $cashes->prepend($assignedCash);
        }

        return $cashes->unique('id')->values();
    }
}
