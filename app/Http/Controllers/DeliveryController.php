<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\PayMode;
use App\Models\Product;
use App\Models\StockProduct;
use App\Models\User;
use App\Services\Water\DeliverySettlementService;
use App\Services\Water\JugMovementService;
use App\Services\Water\LoyaltyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function __construct(
        protected JugMovementService $jugService,
        protected LoyaltyService $loyaltyService,
        protected DeliverySettlementService $settlementService
    ) {}

    public function index()
    {
        $today = Carbon::today();
        $user = auth()->user();
        $isRepartidor = $user && $user->hasRole('REPARTIDOR') && ! $user->hasAnyRole(['ADMIN', 'SUPERADMIN', 'CAJERO']);

        $baseKpiQuery = DeliveryOrder::query();
        if ($isRepartidor) {
            $baseKpiQuery->where('idrepartidor', $user->id);
        }

        $kpis = [
            'pendientes' => (clone $baseKpiQuery)->where('estado', 'pendiente')->count(),
            'en_ruta' => (clone $baseKpiQuery)->where('estado', 'en_ruta')->count(),
            'entregados_hoy' => (clone $baseKpiQuery)->where('estado', 'entregado')->whereDate('fecha_entrega', $today)->count(),
            'recaudado_hoy' => (float) (clone $baseKpiQuery)->where('estado', 'entregado')
                ->whereDate('fecha_entrega', $today)
                ->where('estado_pago', 'pagado')
                ->sum('total'),
        ];

        $containerSummary = [
            'total_prestados' => (int) Client::sum('saldo_envases'),
            'total_danados' => (int) DeliveryOrder::sum('bidones_danados_recibidos'),
            'total_entregados' => (int) DeliveryOrder::where('estado', 'entregado')->sum('bidones_a_entregar'),
            'total_devueltos' => (int) DeliveryOrder::where('estado', 'entregado')->sum('bidones_vacios_recibidos'),
        ];

        $repartidores = User::whereHas('roles', function ($q) {
            $q->where('name', 'REPARTIDOR');
        })->where('estado', 1)->orderBy('nombres')->get(['id', 'nombres']);

        $clients = Client::orderBy('nombres')->get(['id', 'nombres', 'nro_documento', 'telefono', 'direccion', 'saldo_envases']);
        $products = Product::where('opcion', 1)->orderBy('descripcion')->get();
        $payModes = PayMode::where('estado', 1)->orderBy('descripcion')->get();

        $canBill = $user && ($user->hasAnyRole(['ADMIN', 'SUPERADMIN', 'CAJERO', 'CONTABILIDAD', 'VENDEDOR']) || $user->can('admin.pos'));

        return view('admin.deliveries.list', compact(
            'kpis',
            'containerSummary',
            'repartidores',
            'clients',
            'products',
            'payModes',
            'canBill',
            'isRepartidor'
        ));
    }

    public function get(Request $request)
    {
        $user = auth()->user();
        $isRepartidor = $user && $user->hasRole('REPARTIDOR') && ! $user->hasAnyRole(['ADMIN', 'SUPERADMIN', 'CAJERO']);

        $query = DeliveryOrder::with(['cliente', 'repartidor'])
            ->select('delivery_orders.*')
            ->latest('id');

        // Filtro por rol: Si es cliente (portal), solo ve sus propios pedidos
        if ($user && $user->hasRole('Cliente')) {
            $clienteId = $user->idcliente;
            if ($clienteId) {
                $query->where('idcliente', $clienteId);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($isRepartidor) {
            // El repartidor SOLO ve los pedidos asignados a él
            $query->where('delivery_orders.idrepartidor', $user->id);
        }

        // Filtros opcionales
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha_programada', $request->input('fecha'));
        }

        if ($request->filled('idrepartidor') && ! $isRepartidor) {
            $query->where('idrepartidor', $request->input('idrepartidor'));
        }

        // KPIs dinámicos contextualizados al rol del usuario
        $kpiQuery = DeliveryOrder::query();
        if ($isRepartidor) {
            $kpiQuery->where('idrepartidor', $user->id);
        }
        $today = Carbon::today();
        $kpis = [
            'pendientes' => (clone $kpiQuery)->where('estado', 'pendiente')->count(),
            'en_ruta' => (clone $kpiQuery)->where('estado', 'en_ruta')->count(),
            'entregados_hoy' => (clone $kpiQuery)->where('estado', 'entregado')->whereDate('fecha_entrega', $today)->count(),
            'recaudado_hoy' => (float) (clone $kpiQuery)->where('estado', 'entregado')
                ->whereDate('fecha_entrega', $today)
                ->where('estado_pago', 'pagado')
                ->sum('total'),
        ];

        return DataTables::of($query)
            ->with(['kpis' => $kpis])
            ->editColumn('fecha_programada', function ($row) {
                $time = $row->franja_horaria ? ' ('.ucfirst($row->franja_horaria).')' : '';

                return Carbon::parse($row->fecha_programada)->format('d/m/Y').$time;
            })
            ->editColumn('cliente', function ($row) {
                $name = $row->cliente ? $row->cliente->nombres : 'Cliente no asignado';
                $phone = $row->telefono_contacto ? '<br><small class="text-muted"><i class="ri-phone-line"></i> '.$row->telefono_contacto.'</small>' : '';

                return '<div class="fw-semibold text-dark">'.htmlspecialchars($name).'</div>'.$phone;
            })
            ->editColumn('direccion_entrega', function ($row) {
                $ref = $row->referencia ? '<br><small class="text-muted">Ref: '.htmlspecialchars($row->referencia).'</small>' : '';
                $maps = $this->settlementService->buildMapsLinks($row);

                if ($maps['has_location']) {
                    $mapBtn = '<a href="'.htmlspecialchars($maps['view_url']).'" target="_blank" rel="noopener" class="btn btn-xs btn-outline-danger ms-1 p-0 px-1" title="Ver en Google Maps" style="font-size: 11px;"><i class="ri-map-pin-line"></i> Mapa</a>';
                    $routeBtn = '<a href="'.htmlspecialchars($maps['route_url']).'" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary ms-1 p-0 px-1" title="Cómo llegar (Ruta GPS)" style="font-size: 11px;"><i class="ri-navigation-line"></i> Ruta</a>';
                } else {
                    $mapBtn = '<span class="badge bg-secondary-soft text-muted ms-1 p-1" title="Sin dirección ni coordenadas" style="font-size: 10px;"><i class="ri-map-pin-line"></i> Sin Mapa</span>';
                    $routeBtn = '';
                }

                return '<span class="text-truncate d-inline-block align-middle" style="max-width: 170px;" title="'.htmlspecialchars($row->direccion_entrega).'">'.htmlspecialchars($row->direccion_entrega).'</span>'.$mapBtn.$routeBtn.$ref;
            })
            ->addColumn('envases_badge', function ($row) {
                $html = '<span class="badge bg-primary-soft text-primary fw-semibold">'.$row->bidones_a_entregar.' por entregar</span>';
                if ($row->estado === 'entregado') {
                    $html .= '<br><small class="text-success fw-semibold">-'.$row->bidones_vacios_recibidos.' devueltos</small>';
                    if ($row->bidones_danados_recibidos > 0) {
                        $html .= '<br><small class="text-danger fw-semibold">-'.$row->bidones_danados_recibidos.' dañados</small>';
                    }
                }

                return $html;
            })
            ->editColumn('repartidor', function ($row) {
                return $row->repartidor
                    ? '<span class="badge bg-light text-dark"><i class="ri-user-follow-line me-1"></i> '.htmlspecialchars($row->repartidor->nombres).'</span>'
                    : '<span class="badge bg-secondary-soft text-secondary">Sin asignar</span>';
            })
            ->editColumn('estado', function ($row) {
                $badges = [
                    'pendiente' => '<span class="badge bg-warning-soft text-warning fw-semibold"><i class="ri-time-line me-1"></i> Pendiente</span>',
                    'en_ruta' => '<span class="badge bg-info-soft text-info fw-semibold"><i class="ri-truck-line me-1"></i> En Ruta</span>',
                    'entregado' => '<span class="badge bg-success-soft text-success fw-semibold"><i class="ri-checkbox-circle-line me-1"></i> Entregado</span>',
                    'cancelado' => '<span class="badge bg-danger-soft text-danger fw-semibold"><i class="ri-close-circle-line me-1"></i> Cancelado</span>',
                    'reprogramado' => '<span class="badge bg-secondary-soft text-secondary fw-semibold"><i class="ri-calendar-line me-1"></i> Reprogramado</span>',
                ];

                return $badges[$row->estado] ?? '<span class="badge bg-light text-dark">'.$row->estado.'</span>';
            })
            ->editColumn('total', function ($row) {
                $pagoBadge = $row->estado_pago === 'pagado'
                    ? '<span class="badge bg-success-soft text-success" style="font-size: 10px;">Pagado</span>'
                    : '<span class="badge bg-warning-soft text-warning" style="font-size: 10px;">Por cobrar</span>';

                return '<strong>S/ '.number_format($row->total, 2).'</strong><br>'.$pagoBadge;
            })
            ->editColumn('origen', function ($row) {
                return $row->origen === 'qr'
                    ? '<span class="badge bg-primary-soft text-primary"><i class="ri-qr-code-line"></i> QR</span>'
                    : '<span class="badge bg-light text-muted">'.ucfirst($row->origen).'</span>';
            })
            ->addColumn('acciones', function ($row) use ($isRepartidor) {
                $user = auth()->user();
                $canBill = $user && ($user->hasAnyRole(['ADMIN', 'SUPERADMIN', 'CAJERO', 'CONTABILIDAD', 'VENDEDOR']) || $user->can('admin.pos'));

                $items = '';

                // Despachar a ruta (solo si está pendiente y no es repartidor restringido)
                if ($row->estado === 'pendiente' && ! $isRepartidor) {
                    $items .= '<li>
                        <a class="dropdown-item btn-assign-driver py-2" href="javascript:void(0);" data-id="'.$row->id.'" data-code="'.htmlspecialchars($row->codigo_orden).'">
                            <i class="ri-truck-line me-2 text-info align-middle"></i> Asignar y Despachar
                        </a>
                    </li>';
                }

                // Completar entrega y liquidación (si está en ruta o pendiente)
                if ($row->estado === 'en_ruta' || $row->estado === 'pendiente') {
                    $items .= '<li>
                        <a class="dropdown-item btn-complete-delivery py-2" href="javascript:void(0);" data-id="'.$row->id.'" data-code="'.htmlspecialchars($row->codigo_orden).'" data-client="'.htmlspecialchars($row->cliente?->nombres ?? '').'" data-total="'.$row->total.'" data-subtotal="'.($row->subtotal - $row->descuento).'" data-delivered="'.$row->bidones_a_entregar.'" data-method="'.htmlspecialchars($row->metodo_pago ?? 'contraentrega').'">
                            <i class="ri-check-double-line me-2 text-success align-middle"></i> Liquidar Entrega
                        </a>
                    </li>';
                }

                // Emitir Comprobante en POS (solo personal habilitado para facturar)
                if ($canBill && ! $isRepartidor) {
                    $urlToPos = route('deliveries.to_pos', $row->id);
                    $items .= '<li>
                        <a class="dropdown-item py-2" href="'.$urlToPos.'">
                            <i class="ri-receipt-line me-2 text-primary align-middle"></i> Emitir Comprobante (POS)
                        </a>
                    </li>';
                }

                // Contactar por WhatsApp
                if ($row->telefono_contacto) {
                    $cleanPhone = preg_replace('/[^0-9]/', '', $row->telefono_contacto);
                    if (strlen($cleanPhone) === 9) {
                        $cleanPhone = '51'.$cleanPhone;
                    }
                    $waText = urlencode("¡Hola! Tu pedido de agua *{$row->codigo_orden}* está en camino a {$row->direccion_entrega}. Total: S/ ".number_format($row->total, 2));
                    $items .= '<li>
                        <a class="dropdown-item py-2" href="https://wa.me/'.$cleanPhone.'?text='.$waText.'" target="_blank" rel="noopener">
                            <i class="ri-whatsapp-line me-2 text-success align-middle"></i> Contactar por WhatsApp
                        </a>
                    </li>';
                }

                // Google Maps - Ver ubicación y Ruta
                $maps = $this->settlementService->buildMapsLinks($row);
                if ($maps['has_location']) {
                    $items .= '<li>
                        <a class="dropdown-item py-2" href="'.htmlspecialchars($maps['view_url']).'" target="_blank" rel="noopener">
                            <i class="ri-map-pin-line me-2 text-danger align-middle"></i> Ver Ubicación (Maps)
                        </a>
                    </li>';
                    $items .= '<li>
                        <a class="dropdown-item py-2" href="'.htmlspecialchars($maps['route_url']).'" target="_blank" rel="noopener">
                            <i class="ri-navigation-line me-2 text-primary align-middle"></i> Cómo llegar (Ruta GPS)
                        </a>
                    </li>';
                } else {
                    $items .= '<li>
                        <a class="dropdown-item py-2 text-muted disabled" href="javascript:void(0);" onclick="alert(\'Este pedido no tiene dirección ni coordenadas registradas.\')">
                            <i class="ri-map-pin-line me-2 text-muted align-middle"></i> Sin ubicación registrada
                        </a>
                    </li>';
                }

                // Ver detalles (siempre disponible)
                $items .= '<li>
                    <a class="dropdown-item btn-order-details py-2" href="javascript:void(0);" data-id="'.$row->id.'">
                        <i class="ri-file-list-line me-2 text-secondary align-middle"></i> Ver Detalles y Auditoría
                    </a>
                </li>';

                // Cancelar pedido (si no está cancelado ni entregado)
                if ($row->estado !== 'cancelado' && $row->estado !== 'entregado') {
                    $items .= '<li><hr class="dropdown-divider my-1"></li>';
                    $items .= '<li>
                        <a class="dropdown-item btn-cancel-order text-danger py-2" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="ri-close-circle-line me-2 text-danger align-middle"></i> Cancelar Pedido
                        </a>
                    </li>';
                }

                return '<div class="dropdown text-center">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle waves-effect shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-2-fill me-1 align-middle"></i> Acciones
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1" style="font-size: 0.85rem; min-width: 215px;">
                        '.$items.'
                    </ul>
                </div>';
            })
            ->rawColumns(['cliente', 'direccion_entrega', 'envases_badge', 'repartidor', 'estado', 'total', 'origen', 'acciones'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcliente' => 'required|exists:clients,id',
            'fecha_programada' => 'required|date',
            'franja_horaria' => 'nullable|string|max:50',
            'direccion_entrega' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:30',
            'metodo_pago' => 'nullable|string|max:50',
            'notas' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.idproducto' => 'required|exists:products,id',
            'items.*.cantidad' => 'required|numeric|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'type' => 'warning',
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $client = Client::findOrFail($request->input('idcliente'));

            // Generar código de orden PED-XXXXXX
            $lastId = DeliveryOrder::max('id') ?? 0;
            $orderCode = 'PED-'.str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $totalDeliveredJugs = 0;

            $itemsData = [];
            foreach ($request->input('items') as $item) {
                $product = Product::find($item['idproducto']);
                $qty = (float) $item['cantidad'];
                $price = (float) $item['precio_unitario'];
                $itemSubtotal = $qty * $price;
                $subtotal += $itemSubtotal;

                $tipoItem = 'producto';
                if ($product && stripos($product->descripcion, 'recarga') !== false) {
                    $tipoItem = 'recarga';
                    $totalDeliveredJugs += (int) $qty;
                } elseif ($product && stripos($product->descripcion, 'nuevo') !== false) {
                    $tipoItem = 'con_envase';
                    $totalDeliveredJugs += (int) $qty;
                }

                $itemsData[] = [
                    'idproducto' => $product->id,
                    'descripcion' => $product->descripcion,
                    'tipo_item' => $tipoItem,
                    'cantidad' => $qty,
                    'precio_unitario' => $price,
                    'descuento' => 0,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $order = DeliveryOrder::create([
                'codigo_orden' => $orderCode,
                'idcliente' => $client->id,
                'idusuario_registro' => auth()->id(),
                'origen' => 'manual',
                'estado' => 'pendiente',
                'direccion_entrega' => $request->input('direccion_entrega'),
                'referencia' => $request->input('referencia'),
                'telefono_contacto' => $request->input('telefono_contacto') ?: $client->telefono,
                'fecha_programada' => $request->input('fecha_programada'),
                'franja_horaria' => $request->input('franja_horaria', 'flexible'),
                'subtotal' => $subtotal,
                'descuento' => 0,
                'total' => $subtotal,
                'metodo_pago' => $request->input('metodo_pago', 'contraentrega'),
                'estado_pago' => 'pendiente',
                'bidones_a_entregar' => $totalDeliveredJugs,
                'notas' => $request->input('notas'),
            ]);

            foreach ($itemsData as $iData) {
                $iData['iddelivery_order'] = $order->id;
                DeliveryOrderItem::create($iData);
            }

            return response()->json([
                'status' => true,
                'msg' => "Pedido {$orderCode} registrado correctamente.",
                'type' => 'success',
                'order' => $order,
            ]);
        });
    }

    public function assign_driver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:delivery_orders,id',
            'idrepartidor' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        $order = DeliveryOrder::findOrFail($request->input('id'));
        $driver = User::findOrFail($request->input('idrepartidor'));

        try {
            $order->idrepartidor = $driver->id;
            $order->save();

            $this->settlementService->transition(
                order: $order,
                newStatus: DeliverySettlementService::STATUS_EN_RUTA,
                reason: 'Despacho a repartidor',
                notes: "Asignado a {$driver->nombres}",
                metadata: ['idrepartidor' => $driver->id, 'repartidor_nombre' => $driver->nombres],
                userId: auth()->id()
            );

            return response()->json([
                'status' => true,
                'msg' => "Pedido {$order->codigo_orden} despachado a ruta exitosamente con {$driver->nombres}.",
                'type' => 'success',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage(),
                'type' => 'warning',
            ], 422);
        }
    }

    public function complete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:delivery_orders,id',
            'bidones_vacios_recibidos' => 'required|integer|min:0',
            'bidones_danados_recibidos' => 'nullable|integer|min:0',
            'cobro_envases_danados' => 'nullable|numeric|min:0',
            'motivo_liquidacion' => 'nullable|string|in:despacho_estandar,envase_danado,pedido_cancelado,envio_duplicado',
            'metodo_pago' => 'nullable|string|max:50',
            'estado_pago' => 'nullable|string|in:pagado,pendiente',
            'notas' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        $order = DeliveryOrder::findOrFail($request->input('id'));

        $user = auth()->user();
        $isRepartidor = $user && $user->hasRole('REPARTIDOR') && ! $user->hasAnyRole(['ADMIN', 'SUPERADMIN', 'CAJERO']);
        if ($isRepartidor && (int) $order->idrepartidor !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'msg' => 'No tienes permisos para liquidar un pedido asignado a otro repartidor.',
                'type' => 'danger',
            ], 403);
        }

        try {
            $result = $this->settlementService->settle($order, $request->all(), $user?->id);

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage(),
                'type' => 'warning',
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Error al liquidar el pedido: '.$e->getMessage(),
                'type' => 'error',
            ], 500);
        }
    }

    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:delivery_orders,id',
            'motivo' => 'nullable|string|max:255',
            'motivo_liquidacion' => 'nullable|string|in:despacho_estandar,envase_danado,pedido_cancelado,envio_duplicado',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        $order = DeliveryOrder::findOrFail($request->input('id'));

        $user = auth()->user();
        $isRepartidor = $user && $user->hasRole('REPARTIDOR') && ! $user->hasAnyRole(['ADMIN', 'SUPERADMIN', 'CAJERO']);
        if ($isRepartidor && (int) $order->idrepartidor !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'msg' => 'No tienes permisos para cancelar un pedido asignado a otro repartidor.',
                'type' => 'danger',
            ], 403);
        }

        $reason = $request->input('motivo') ?: ($request->input('motivo_liquidacion') ?: DeliverySettlementService::REASON_CANCELLED_ORDER);

        try {
            $this->settlementService->cancel($order, $reason, $user?->id);

            return response()->json([
                'status' => true,
                'msg' => "Pedido {$order->codigo_orden} cancelado.",
                'type' => 'success',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage(),
                'type' => 'warning',
            ], 422);
        }
    }

    public function show($id)
    {
        $order = DeliveryOrder::with([
            'cliente',
            'repartidor',
            'items.producto',
            'statusLogs.usuario',
            'arqueoCaja',
        ])->findOrFail($id);

        $user = auth()->user();
        $isRepartidor = $user && $user->hasRole('REPARTIDOR') && ! $user->hasAnyRole(['ADMIN', 'SUPERADMIN', 'CAJERO']);
        if ($isRepartidor && (int) $order->idrepartidor !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'msg' => 'No tienes permisos para visualizar este pedido.',
            ], 403);
        }

        $maps = $this->settlementService->buildMapsLinks($order);

        return response()->json([
            'status' => true,
            'order' => $order,
            'maps' => $maps,
            'status_logs' => $order->statusLogs,
        ]);
    }

    public function containers_summary(Request $request)
    {
        $summary = [
            'total_prestados' => (int) Client::sum('saldo_envases'),
            'total_danados' => (int) DeliveryOrder::sum('bidones_danados_recibidos'),
            'total_entregados' => (int) DeliveryOrder::where('estado', 'entregado')->sum('bidones_a_entregar'),
            'total_devueltos' => (int) DeliveryOrder::where('estado', 'entregado')->sum('bidones_vacios_recibidos'),
            'clientes_con_saldo' => Client::where('saldo_envases', '!=', 0)->count(),
        ];

        $clientsQuery = Client::select('id', 'nombres', 'nro_documento', 'telefono', 'direccion', 'saldo_envases')
            ->where('saldo_envases', '!=', 0)
            ->orWhereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('delivery_orders')
                    ->whereColumn('delivery_orders.idcliente', 'clients.id')
                    ->where('delivery_orders.bidones_danados_recibidos', '>', 0);
            })
            ->orderByDesc('saldo_envases');

        if ($request->wantsJson() || $request->ajax()) {
            return DataTables::of($clientsQuery)
                ->with([
                    'status' => true,
                    'summary' => $summary,
                ])
                ->addColumn('danados_historicos', function ($row) {
                    return (int) DeliveryOrder::where('idcliente', $row->id)->sum('bidones_danados_recibidos');
                })
                ->addColumn('entregados_historicos', function ($row) {
                    return (int) DeliveryOrder::where('idcliente', $row->id)->where('estado', 'entregado')->sum('bidones_a_entregar');
                })
                ->addColumn('devueltos_historicos', function ($row) {
                    return (int) DeliveryOrder::where('idcliente', $row->id)->where('estado', 'entregado')->sum('bidones_vacios_recibidos');
                })
                ->make(true);
        }

        return response()->json([
            'status' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Pre-carga el carrito del POS con los items del pedido de delivery
     * y redirige al POS para emitir boleta, factura o nota de venta.
     */
    public function toPOS($id)
    {
        $order = DeliveryOrder::with(['items.producto.unidad', 'cliente'])->findOrFail($id);

        // Determinar el almacén del usuario autenticado (o el del pedido o 1)
        $idalmacen = (int) auth()->user()->idalmacen ?: ((int) $order->idalmacen ?: 1);

        // Limpiar carrito previo
        session()->forget('pos');

        $cartProducts = [];
        $subtotal = 0;
        $igv = 0;

        // Añadir cada ítem del pedido al carrito de sesión
        foreach ($order->items as $item) {
            /** @var \App\Models\Product $product */
            $product = $item->producto;
            if (! $product) {
                continue;
            }

            // Obtener stock actual del producto en el almacén
            $stockRow = StockProduct::where('idproducto', $product->id)
                ->where('idalmacen', $idalmacen)
                ->first();

            $cantidad = (int) $item->cantidad;
            $precioVenta = (float) $item->precio_unitario;
            $igvVal = (int) ($product->igv ?? 18);
            $igvFactor = (100 + $igvVal) / 100;
            $precioBase = $igvFactor > 0 ? ($precioVenta / $igvFactor) : $precioVenta;
            $igvItem = ($precioVenta - $precioBase) * $cantidad;

            $igv += round($igvItem, 2);
            $subtotal += round($precioBase * $cantidad, 2);

            $cartProducts[] = [
                'id' => $product->id,
                'descripcion' => $product->descripcion,
                'idunidad' => $product->idunidad,
                'unidad' => optional($product->unidad)->codigo ?? 'NIU',
                'igv' => $igvVal,
                'idcodigo_igv' => $product->idcodigo_igv ?? 1,
                'precio_compra' => $product->precio_compra,
                'precio_venta' => $precioVenta,
                'stock' => $stockRow ? (int) $stockRow->stock_actual : null,
                'opcion' => (int) $product->opcion,
                'cantidad' => $cantidad,
                'idalmacen' => $idalmacen,
            ];
        }

        $total = $subtotal + $igv;

        session(['pos' => [
            'products' => $cartProducts,
            'igv' => $igv,
            'subtotal' => $subtotal,
            'total' => $total,
        ]]);

        // Guardar ID del pedido de entrega en sesión para vincular con el comprobante al guardar venta
        session(['from_delivery_order_id' => $order->id]);

        // Pasar tipo de documento y datos del cliente como query-string
        $tipo = request()->query('tipo', 'boleta');   // boleta | factura_ruc | nota_venta
        $clientId = optional($order->cliente)->id;

        return redirect()->route('admin.pos.create', [
            'from_delivery' => $order->id,
            'tipo' => $tipo,
            'client_id' => $clientId,
        ]);
    }

    public function qr_generator()
    {
        $business = Business::find(1);
        $publicUrl = url('/pedido');

        return view('admin.deliveries.qr', compact('business', 'publicUrl'));
    }
}
