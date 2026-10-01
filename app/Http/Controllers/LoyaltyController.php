<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientLoyalty;
use App\Models\LoyaltyPromotion;
use App\Models\LoyaltyPromotionLog;
use App\Models\Product;
use App\Services\Water\JugMovementService;
use App\Services\Water\LoyaltyService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class LoyaltyController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService,
        protected JugMovementService $jugMovementService
    ) {}

    public function index(): View
    {
        $promotion = $this->loyaltyService->getActivePromotion() ?? LoyaltyPromotion::first();
        $target = $promotion?->meta_compras ?? 5;
        $kpis = [
            'total_participantes' => (int) ClientLoyalty::count(),
            'canjes_disponibles' => (int) ClientLoyalty::where('compras_acumuladas', '>=', $target)->count(),
            'total_premios_entregados' => (int) ClientLoyalty::sum('premios_reclamados'),
        ];

        $products = Product::orderBy('descripcion')->get(['id', 'descripcion', 'precio_venta']);

        $recentLogs = $promotion
            ? $promotion->logs()->with(['usuario', 'productoObjetivoNuevo', 'productoBonificadoNuevo'])->limit(20)->get()
            : collect();

        return view('admin.loyalty.index', compact('promotion', 'kpis', 'products', 'recentLogs'));
    }

    public function get_clients(Request $request): JsonResponse
    {
        $promotion = $this->loyaltyService->getActivePromotion() ?? LoyaltyPromotion::first();
        $target = $promotion?->meta_compras ?? 5;

        $clients = Client::query()
            ->leftJoin('client_loyalty', function ($join) use ($promotion) {
                $join->on('clients.id', '=', 'client_loyalty.idcliente');
                if ($promotion) {
                    $join->where('client_loyalty.idpromocion', '=', $promotion->id);
                }
            })
            ->select([
                'clients.id',
                'clients.nro_documento',
                'clients.nombres',
                'clients.telefono',
                'client_loyalty.compras_acumuladas',
                'client_loyalty.premios_reclamados',
                'client_loyalty.ultimo_canje',
            ])
            ->orderByRaw('COALESCE(client_loyalty.compras_acumuladas, 0) DESC');

        return DataTables::of($clients)
            ->editColumn('compras_acumuladas', function ($row) {
                return (int) ($row->compras_acumuladas ?? 0);
            })
            ->addColumn('progreso', function ($row) use ($target) {
                $accumulated = (int) ($row->compras_acumuladas ?? 0);
                $percent = min(100, round(($accumulated / max(1, $target)) * 100));
                $barClass = $accumulated >= $target ? 'bg-success' : 'bg-primary';

                return '
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar '.$barClass.'" role="progressbar" style="width: '.$percent.'%;"></div>
                        </div>
                        <span class="small fw-semibold">'.$accumulated.'/'.$target.'</span>
                    </div>
                ';
            })
            ->addColumn('estado_premio', function ($row) use ($target) {
                $accumulated = (int) ($row->compras_acumuladas ?? 0);
                if ($accumulated >= $target) {
                    $available = intdiv($accumulated, $target);

                    return '<span class="badge bg-success-soft text-success fw-bold"><i class="ri-gift-line me-1"></i> ¡'.$available.' Premio(s) Listo(s)!</span>';
                }
                $remaining = $target - $accumulated;

                return '<span class="badge bg-light text-muted">Faltan '.$remaining.'</span>';
            })
            ->editColumn('premios_reclamados', function ($row) {
                $count = (int) ($row->premios_reclamados ?? 0);

                return $count > 0
                    ? '<span class="badge bg-info-soft text-info fw-semibold">'.$count.' canjeados</span>'
                    : '<span class="text-muted">0</span>';
            })
            ->addColumn('acciones', function ($row) use ($target) {
                $accumulated = (int) ($row->compras_acumuladas ?? 0);
                $canRedeem = $accumulated >= $target;

                $redeemBtn = $canRedeem
                    ? '<button class="btn btn-sm btn-success btn-redeem-reward" data-id="'.$row->id.'" data-name="'.htmlspecialchars($row->nombres).'"><i class="ri-gift-line"></i> Canjear</button>'
                    : '';

                return '
                    <div class="d-flex justify-content-center gap-1">
                        '.$redeemBtn.'
                        <button class="btn btn-sm btn-outline-primary btn-add-loyalty-point" data-id="'.$row->id.'" data-name="'.htmlspecialchars($row->nombres).'" title="Sumar compra manual">
                            <i class="ri-add-line"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['progreso', 'estado_premio', 'premios_reclamados', 'acciones'])
            ->make(true);
    }

    public function get_logs(Request $request): JsonResponse
    {
        $logs = LoyaltyPromotionLog::with(['usuario', 'productoObjetivoNuevo', 'productoBonificadoNuevo'])
            ->orderBy('id', 'desc');

        return DataTables::of($logs)
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y H:i:s');
            })
            ->addColumn('usuario_nombre', function ($row) {
                return $row->usuario?->nombres ?? 'Sistema';
            })
            ->addColumn('cambio_meta', function ($row) {
                $ant = $row->meta_compras_anterior ?? '-';
                $nue = $row->meta_compras_nueva;

                return "<span class='text-muted'>{$ant}</span> <i class='ri-arrow-right-line'></i> <strong>{$nue}</strong>";
            })
            ->addColumn('cambio_bonif', function ($row) {
                $ant = $row->bonificacion_anterior ?? '-';
                $nue = $row->bonificacion_nueva;

                return "<span class='text-muted'>{$ant}</span> <i class='ri-arrow-right-line'></i> <strong>{$nue}</strong>";
            })
            ->addColumn('estado_activo', function ($row) {
                return $row->activo_nuevo
                    ? '<span class="badge bg-success-soft text-success">Activo</span>'
                    : '<span class="badge bg-danger-soft text-danger">Inactivo</span>';
            })
            ->rawColumns(['cambio_meta', 'cambio_bonif', 'estado_activo'])
            ->make(true);
    }

    public function save_settings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'meta_compras' => 'required|integer|min:1|max:100',
            'bonificacion' => 'required|integer|min:1|max:10',
            'idproducto_objetivo' => 'nullable|exists:products,id',
            'idproducto_bonificado' => 'nullable|exists:products,id',
            'descripcion' => 'nullable|string|max:500',
            'motivo_cambio' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'type' => 'warning',
            ], 422);
        }

        $promotion = LoyaltyPromotion::first();

        $prevMeta = $promotion?->meta_compras;
        $prevBonus = $promotion?->bonificacion;
        $prevObj = $promotion?->idproducto_objetivo;
        $prevBonif = $promotion?->idproducto_bonificado;
        $prevActivo = $promotion?->activo;

        $newMeta = (int) $request->input('meta_compras');
        $newBonus = (int) $request->input('bonificacion');
        $newObj = $request->input('idproducto_objetivo') ?: null;
        $newBonif = $request->input('idproducto_bonificado') ?: null;
        $newActivo = $request->boolean('activo');
        $reason = $request->input('motivo_cambio') ?: 'Ajuste de parámetros de fidelización';

        if ($promotion) {
            $promotion->update([
                'nombre' => $request->input('nombre'),
                'meta_compras' => $newMeta,
                'bonificacion' => $newBonus,
                'idproducto_objetivo' => $newObj,
                'idproducto_bonificado' => $newBonif,
                'activo' => $newActivo,
                'descripcion' => $request->input('descripcion'),
            ]);
        } else {
            $promotion = LoyaltyPromotion::create([
                'nombre' => $request->input('nombre'),
                'meta_compras' => $newMeta,
                'bonificacion' => $newBonus,
                'idproducto_objetivo' => $newObj,
                'idproducto_bonificado' => $newBonif,
                'activo' => $newActivo,
                'descripcion' => $request->input('descripcion'),
            ]);
        }

        // Registrar auditoría histórica de cambio de reglas
        LoyaltyPromotionLog::create([
            'idpromocion' => $promotion->id,
            'idusuario' => auth()->id(),
            'meta_compras_anterior' => $prevMeta,
            'meta_compras_nueva' => $newMeta,
            'bonificacion_anterior' => $prevBonus,
            'bonificacion_nueva' => $newBonus,
            'idproducto_objetivo_anterior' => $prevObj,
            'idproducto_objetivo_nuevo' => $newObj,
            'idproducto_bonificado_anterior' => $prevBonif,
            'idproducto_bonificado_nuevo' => $newBonif,
            'activo_anterior' => $prevActivo,
            'activo_nuevo' => $newActivo,
            'motivo' => $reason,
        ]);

        return response()->json([
            'status' => true,
            'msg' => 'Configuración de fidelización actualizada exitosamente.',
            'type' => 'success',
            'promotion' => $promotion,
            'rule_label' => $promotion->rule_label,
            'rule_text' => $promotion->rule_text,
        ]);
    }

    public function check_client($clientId): JsonResponse
    {
        $client = Client::findOrFail($clientId);
        $status = $this->loyaltyService->getClientStatus($client);
        $jugSummary = $this->jugMovementService->getClientJugSummary($client);

        return response()->json([
            'status' => true,
            'cliente' => [
                'id' => $client->id,
                'nombres' => $client->nombres,
                'telefono' => $client->telefono,
                'saldo_envases' => $client->saldo_envases,
                'jug_summary' => $jugSummary,
            ],
            'loyalty' => $status,
        ]);
    }

    public function add_point(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'idcliente' => 'required|exists:clients,id',
            'cantidad' => 'required|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        $client = Client::findOrFail($request->input('idcliente'));
        $result = $this->loyaltyService->accumulatePurchases($client, (int) $request->input('cantidad'), 'Ajuste manual desde módulo Fidelización');

        return response()->json([
            'status' => true,
            'msg' => 'Compra acumulada exitosamente en la cuenta del cliente.',
            'type' => 'success',
            'loyalty' => $result,
        ]);
    }

    public function redeem_reward(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'idcliente' => 'required|exists:clients,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
            ], 422);
        }

        $client = Client::findOrFail($request->input('idcliente'));
        $result = $this->loyaltyService->redeemReward($client);

        return response()->json($result);
    }
}
