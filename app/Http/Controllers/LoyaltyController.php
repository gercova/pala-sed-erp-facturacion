<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientLoyalty;
use App\Models\LoyaltyPromotion;
use App\Models\Product;
use App\Services\Water\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class LoyaltyController extends Controller
{
    protected LoyaltyService $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    public function index()
    {
        $promotion = $this->loyaltyService->getActivePromotion() ?? LoyaltyPromotion::first();
        $target = $promotion?->meta_compras ?? 4;

        $kpis = [
            'total_participantes' => (int) ClientLoyalty::count(),
            'canjes_disponibles' => (int) ClientLoyalty::where('compras_acumuladas', '>=', $target)->count(),
            'total_premios_entregados' => (int) ClientLoyalty::sum('premios_reclamados'),
        ];

        $products = Product::orderBy('descripcion')->get(['id', 'descripcion', 'precio_venta']);

        return view('admin.loyalty.index', compact('promotion', 'kpis', 'products'));
    }

    public function get_clients(Request $request)
    {
        $promotion = $this->loyaltyService->getActivePromotion() ?? LoyaltyPromotion::first();
        $target = $promotion?->meta_compras ?? 4;

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
                            <div class="progress-bar ' . $barClass . '" role="progressbar" style="width: ' . $percent . '%;"></div>
                        </div>
                        <span class="small fw-semibold">' . $accumulated . '/' . $target . '</span>
                    </div>
                ';
            })
            ->addColumn('estado_premio', function ($row) use ($target) {
                $accumulated = (int) ($row->compras_acumuladas ?? 0);
                if ($accumulated >= $target) {
                    return '<span class="badge bg-success-soft text-success fw-bold"><i class="ri-gift-line me-1"></i> ¡Premio Listo!</span>';
                }
                $remaining = $target - $accumulated;
                return '<span class="badge bg-light text-muted">Faltan ' . $remaining . '</span>';
            })
            ->editColumn('premios_reclamados', function ($row) {
                $count = (int) ($row->premios_reclamados ?? 0);
                return $count > 0
                    ? '<span class="badge bg-info-soft text-info fw-semibold">' . $count . ' canjeados</span>'
                    : '<span class="text-muted">0</span>';
            })
            ->addColumn('acciones', function ($row) use ($target) {
                $accumulated = (int) ($row->compras_acumuladas ?? 0);
                $canRedeem = $accumulated >= $target;

                $redeemBtn = $canRedeem
                    ? '<button class="btn btn-sm btn-success btn-redeem-reward" data-id="' . $row->id . '" data-name="' . htmlspecialchars($row->nombres) . '"><i class="ri-gift-line"></i> Canjear</button>'
                    : '';

                return '
                    <div class="d-flex justify-content-center gap-1">
                        ' . $redeemBtn . '
                        <button class="btn btn-sm btn-outline-primary btn-add-loyalty-point" data-id="' . $row->id . '" data-name="' . htmlspecialchars($row->nombres) . '" title="Sumar compra manual">
                            <i class="ri-add-line"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['progreso', 'estado_premio', 'premios_reclamados', 'acciones'])
            ->make(true);
    }

    public function save_settings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'meta_compras' => 'required|integer|min:1|max:100',
            'bonificacion' => 'required|integer|min:1|max:10',
            'idproducto_objetivo' => 'nullable|exists:products,id',
            'idproducto_bonificado' => 'nullable|exists:products,id',
            'descripcion' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'type' => 'warning',
            ], 422);
        }

        $promotion = LoyaltyPromotion::first();

        if ($promotion) {
            $promotion->update([
                'nombre' => $request->input('nombre'),
                'meta_compras' => (int) $request->input('meta_compras'),
                'bonificacion' => (int) $request->input('bonificacion'),
                'idproducto_objetivo' => $request->input('idproducto_objetivo'),
                'idproducto_bonificado' => $request->input('idproducto_bonificado'),
                'activo' => $request->boolean('activo'),
                'descripcion' => $request->input('descripcion'),
            ]);
        } else {
            $promotion = LoyaltyPromotion::create([
                'nombre' => $request->input('nombre'),
                'meta_compras' => (int) $request->input('meta_compras'),
                'bonificacion' => (int) $request->input('bonificacion'),
                'idproducto_objetivo' => $request->input('idproducto_objetivo'),
                'idproducto_bonificado' => $request->input('idproducto_bonificado'),
                'activo' => $request->boolean('activo'),
                'descripcion' => $request->input('descripcion'),
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => 'Configuración de la promoción actualizada exitosamente.',
            'type' => 'success',
            'promotion' => $promotion,
        ]);
    }

    public function check_client($clientId)
    {
        $client = Client::findOrFail($clientId);
        $status = $this->loyaltyService->getClientStatus($client);

        return response()->json([
            'status' => true,
            'cliente' => [
                'id' => $client->id,
                'nombres' => $client->nombres,
                'telefono' => $client->telefono,
                'saldo_envases' => $client->saldo_envases,
            ],
            'loyalty' => $status,
        ]);
    }

    public function add_point(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcliente' => 'required|exists:clients,id',
            'cantidad' => 'required|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        $client = Client::findOrFail($request->input('idcliente'));
        $result = $this->loyaltyService->accumulatePurchases($client, (int) $request->input('cantidad'));

        return response()->json([
            'status' => true,
            'msg' => 'Compra acumulada exitosamente en la cuenta del cliente.',
            'type' => 'success',
            'loyalty' => $result,
        ]);
    }

    public function redeem_reward(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcliente' => 'required|exists:clients,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        $client = Client::findOrFail($request->input('idcliente'));
        $result = $this->loyaltyService->redeemReward($client);

        return response()->json($result);
    }
}
