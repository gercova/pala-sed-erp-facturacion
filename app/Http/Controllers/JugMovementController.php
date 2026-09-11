<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\JugMovement;
use App\Services\Water\JugMovementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class JugMovementController extends Controller
{
    protected JugMovementService $jugService;

    public function __construct(JugMovementService $jugService)
    {
        $this->jugService = $jugService;
    }

    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        $kpis = [
            'total_envases_clientes' => (int) Client::sum('saldo_envases'),
            'devueltos_intactos_mes' => (int) JugMovement::where('fecha', '>=', $startOfMonth)->sum('devueltos_intactos'),
            'devueltos_danados_mes' => (int) JugMovement::where('fecha', '>=', $startOfMonth)->sum('devueltos_danados'),
            'clientes_con_saldo' => (int) Client::where('saldo_envases', '>', 0)->count(),
        ];

        $clients = Client::orderBy('nombres')->get(['id', 'nombres', 'nro_documento', 'saldo_envases', 'telefono']);

        return view('admin.jug_movements.list', compact('kpis', 'clients'));
    }

    public function get(Request $request)
    {
        $clients = Client::query()
            ->select(['id', 'nro_documento', 'nombres', 'telefono', 'direccion', 'saldo_envases'])
            ->orderBy('saldo_envases', 'desc')
            ->orderBy('nombres', 'asc');

        return DataTables::of($clients)
            ->addColumn('status_badge', function ($client) {
                if ($client->saldo_envases > 5) {
                    return '<span class="badge bg-danger-soft text-danger fw-semibold">' . $client->saldo_envases . ' pendientes</span>';
                } elseif ($client->saldo_envases > 0) {
                    return '<span class="badge bg-warning-soft text-warning fw-semibold">' . $client->saldo_envases . ' pendientes</span>';
                } else {
                    return '<span class="badge bg-success-soft text-success fw-semibold">Al día (0)</span>';
                }
            })
            ->addColumn('acciones', function ($client) {
                return '
                    <div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-sm btn-outline-primary btn-record-return" 
                            data-id="' . $client->id . '" 
                            data-name="' . htmlspecialchars($client->nombres) . '" 
                            data-balance="' . $client->saldo_envases . '" 
                            title="Registrar devolución">
                            <i class="ri-arrow-go-back-line"></i> Devolución
                        </button>
                        <button class="btn btn-sm btn-outline-secondary btn-client-history" 
                            data-id="' . $client->id . '" 
                            data-name="' . htmlspecialchars($client->nombres) . '" 
                            title="Ver historial">
                            <i class="ri-history-line"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning btn-adjust-balance" 
                            data-id="' . $client->id . '" 
                            data-name="' . htmlspecialchars($client->nombres) . '" 
                            data-balance="' . $client->saldo_envases . '" 
                            title="Ajuste manual">
                            <i class="ri-equalizer-line"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status_badge', 'acciones'])
            ->make(true);
    }

    public function get_movements(Request $request)
    {
        $query = JugMovement::with(['cliente', 'usuario', 'deliveryOrder'])
            ->select('jug_movements.*')
            ->orderBy('fecha', 'desc');

        if ($request->filled('idcliente')) {
            $query->where('idcliente', $request->input('idcliente'));
        }

        return DataTables::of($query)
            ->editColumn('fecha', function ($row) {
                return Carbon::parse($row->fecha)->format('d/m/Y H:i');
            })
            ->editColumn('cliente', function ($row) {
                return $row->cliente ? $row->cliente->nombres : 'Cliente no encontrado';
            })
            ->editColumn('tipo_movimiento', function ($row) {
                $badges = [
                    'entrega_recarga' => '<span class="badge bg-primary-soft text-primary">Recarga</span>',
                    'nuevo_comodato' => '<span class="badge bg-info-soft text-info">Nuevo Envase</span>',
                    'devolucion_intactos' => '<span class="badge bg-success-soft text-success">Dev. Intactos</span>',
                    'devolucion_danados' => '<span class="badge bg-danger-soft text-danger">Dev. Dañados</span>',
                    'ajuste' => '<span class="badge bg-secondary-soft text-secondary">Ajuste</span>',
                ];
                return $badges[$row->tipo_movimiento] ?? '<span class="badge bg-light text-dark">' . $row->tipo_movimiento . '</span>';
            })
            ->addColumn('detalle_cantidades', function ($row) {
                $html = [];
                if ($row->entregados_llenos > 0) {
                    $html[] = '<span class="text-primary fw-semibold">+' . $row->entregados_llenos . ' llenos</span>';
                }
                if ($row->devueltos_intactos > 0) {
                    $html[] = '<span class="text-success fw-semibold">-' . $row->devueltos_intactos . ' intactos</span>';
                }
                if ($row->devueltos_danados > 0) {
                    $html[] = '<span class="text-danger fw-semibold">-' . $row->devueltos_danados . ' rotos (S/ ' . number_format($row->costo_dano, 2) . ')</span>';
                }
                return empty($html) ? '-' : implode('<br>', $html);
            })
            ->rawColumns(['tipo_movimiento', 'detalle_cantidades'])
            ->make(true);
    }

    public function store_return(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcliente' => 'required|exists:clients,id',
            'devueltos_intactos' => 'required|integer|min:0',
            'devueltos_danados' => 'required|integer|min:0',
            'costo_dano' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'type' => 'warning',
            ], 422);
        }

        $intact = (int) $request->input('devueltos_intactos', 0);
        $damaged = (int) $request->input('devueltos_danados', 0);

        if ($intact === 0 && $damaged === 0) {
            return response()->json([
                'status' => false,
                'msg' => 'Debes registrar al menos un bidón devuelto (intacto o dañado).',
                'type' => 'warning',
            ], 422);
        }

        $client = Client::findOrFail($request->input('idcliente'));
        $damageCost = (float) $request->input('costo_dano', 0);

        $movementType = $damaged > 0 && $intact === 0 ? 'devolucion_danados' : 'devolucion_intactos';

        $movement = $this->jugService->recordMovement(
            client: $client,
            deliveredFull: 0,
            returnedIntact: $intact,
            returnedDamaged: $damaged,
            damageCost: $damageCost,
            movementType: $movementType,
            orderId: null,
            warehouseId: null,
            userId: auth()->id(),
            notes: $request->input('observaciones')
        );

        return response()->json([
            'status' => true,
            'msg' => 'Devolución de envases registrada correctamente.',
            'type' => 'success',
            'saldo_actual' => $movement->saldo_nuevo,
        ]);
    }

    public function adjust_balance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcliente' => 'required|exists:clients,id',
            'nuevo_saldo' => 'required|integer|min:0',
            'motivo' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'type' => 'warning',
            ], 422);
        }

        $client = Client::findOrFail($request->input('idcliente'));

        $movement = $this->jugService->adjustBalance(
            client: $client,
            newBalance: (int) $request->input('nuevo_saldo'),
            reason: $request->input('motivo'),
            userId: auth()->id()
        );

        return response()->json([
            'status' => true,
            'msg' => 'Saldo de envases actualizado correctamente.',
            'type' => 'success',
            'saldo_actual' => $movement->saldo_nuevo,
        ]);
    }

    public function client_history($id)
    {
        $client = Client::findOrFail($id);
        $movements = JugMovement::where('idcliente', $id)
            ->with(['usuario', 'deliveryOrder'])
            ->orderBy('fecha', 'desc')
            ->limit(30)
            ->get();

        return response()->json([
            'status' => true,
            'cliente' => $client,
            'movements' => $movements,
        ]);
    }
}
