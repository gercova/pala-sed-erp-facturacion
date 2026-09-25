<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\Product;
use App\Models\StockProduct;
use App\Models\User;
use App\Services\Water\JugMovementService;
use App\Services\Water\LoyaltyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    protected JugMovementService $jugService;
    protected LoyaltyService $loyaltyService;

    public function __construct(JugMovementService $jugService, LoyaltyService $loyaltyService)
    {
        $this->jugService = $jugService;
        $this->loyaltyService = $loyaltyService;
    }

    public function index()
    {
        $today = Carbon::today();

        $kpis = [
            'pendientes' => DeliveryOrder::where('estado', 'pendiente')->count(),
            'en_ruta' => DeliveryOrder::where('estado', 'en_ruta')->count(),
            'entregados_hoy' => DeliveryOrder::where('estado', 'entregado')->whereDate('fecha_entrega', $today)->count(),
            'recaudado_hoy' => (float) DeliveryOrder::where('estado', 'entregado')
                ->whereDate('fecha_entrega', $today)
                ->where('estado_pago', 'pagado')
                ->sum('total'),
        ];

        $repartidores = User::whereHas('roles', function ($q) {
            $q->where('name', 'REPARTIDOR');
        })->where('estado', 1)->orderBy('nombres')->get(['id', 'nombres']);

        $clients = Client::orderBy('nombres')->get(['id', 'nombres', 'nro_documento', 'telefono', 'direccion', 'saldo_envases']);
        $products = Product::where('opcion', 1)->orderBy('descripcion')->get();

        $user = auth()->user();
        $canBill = $user->hasAnyRole(['ADMIN', 'SUPERADMIN', 'CAJERO', 'CONTABILIDAD', 'VENDEDOR']) || $user->can('admin.pos');

        return view('admin.deliveries.list', compact('kpis', 'repartidores', 'clients', 'products', 'canBill'));
    }

    public function get(Request $request)
    {
        $query = DeliveryOrder::with(['cliente', 'repartidor'])
            ->select('delivery_orders.*')
            ->latest('id');

        // Filtro por rol: Si es cliente (portal), solo ve sus propios pedidos
        if (auth()->check() && auth()->user()->hasRole('Cliente')) {
            $clienteId = auth()->user()->idcliente;
            if ($clienteId) {
                $query->where('idcliente', $clienteId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filtros opcionales
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha_programada', $request->input('fecha'));
        }

        if ($request->filled('idrepartidor')) {
            $query->where('idrepartidor', $request->input('idrepartidor'));
        }

        $today = Carbon::today();
        $kpis = [
            'pendientes' => DeliveryOrder::where('estado', 'pendiente')->count(),
            'en_ruta' => DeliveryOrder::where('estado', 'en_ruta')->count(),
            'entregados_hoy' => DeliveryOrder::where('estado', 'entregado')->whereDate('fecha_entrega', $today)->count(),
            'recaudado_hoy' => (float) DeliveryOrder::where('estado', 'entregado')
                ->whereDate('fecha_entrega', $today)
                ->where('estado_pago', 'pagado')
                ->sum('total'),
        ];

        return DataTables::of($query)
            ->with(['kpis' => $kpis])
            ->editColumn('fecha_programada', function ($row) {
                $time = $row->franja_horaria ? ' (' . ucfirst($row->franja_horaria) . ')' : '';
                return Carbon::parse($row->fecha_programada)->format('d/m/Y') . $time;
            })
            ->editColumn('cliente', function ($row) {
                $name = $row->cliente ? $row->cliente->nombres : 'Cliente no asignado';
                $phone = $row->telefono_contacto ? '<br><small class="text-muted"><i class="ri-phone-line"></i> ' . $row->telefono_contacto . '</small>' : '';
                return '<div class="fw-semibold text-dark">' . htmlspecialchars($name) . '</div>' . $phone;
            })
            ->editColumn('direccion_entrega', function ($row) {
                $ref = $row->referencia ? '<br><small class="text-muted">Ref: ' . htmlspecialchars($row->referencia) . '</small>' : '';
                return '<span class="text-truncate d-inline-block" style="max-width: 200px;" title="' . htmlspecialchars($row->direccion_entrega) . '">' . htmlspecialchars($row->direccion_entrega) . '</span>' . $ref;
            })
            ->addColumn('envases_badge', function ($row) {
                $html = '<span class="badge bg-primary-soft text-primary fw-semibold">' . $row->bidones_a_entregar . ' por entregar</span>';
                if ($row->estado === 'entregado') {
                    $html .= '<br><small class="text-success fw-semibold">-' . $row->bidones_vacios_recibidos . ' devueltos</small>';
                    if ($row->bidones_danados_recibidos > 0) {
                        $html .= '<br><small class="text-danger fw-semibold">-' . $row->bidones_danados_recibidos . ' dañados</small>';
                    }
                }
                return $html;
            })
            ->editColumn('repartidor', function ($row) {
                return $row->repartidor
                    ? '<span class="badge bg-light text-dark"><i class="ri-user-follow-line me-1"></i> ' . htmlspecialchars($row->repartidor->nombres) . '</span>'
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
                return $badges[$row->estado] ?? '<span class="badge bg-light text-dark">' . $row->estado . '</span>';
            })
            ->editColumn('total', function ($row) {
                $pagoBadge = $row->estado_pago === 'pagado'
                    ? '<span class="badge bg-success-soft text-success" style="font-size: 10px;">Pagado</span>'
                    : '<span class="badge bg-warning-soft text-warning" style="font-size: 10px;">Por cobrar</span>';
                return '<strong>S/ ' . number_format($row->total, 2) . '</strong><br>' . $pagoBadge;
            })
            ->editColumn('origen', function ($row) {
                return $row->origen === 'qr'
                    ? '<span class="badge bg-primary-soft text-primary"><i class="ri-qr-code-line"></i> QR</span>'
                    : '<span class="badge bg-light text-muted">' . ucfirst($row->origen) . '</span>';
            })
            ->addColumn('acciones', function ($row) {
                $user = auth()->user();
                $canBill = $user && ($user->hasAnyRole(['ADMIN', 'SUPERADMIN', 'CAJERO', 'CONTABILIDAD', 'VENDEDOR']) || $user->can('admin.pos'));

                $items = '';

                // Despachar a ruta (solo si está pendiente)
                if ($row->estado === 'pendiente') {
                    $items .= '<li>
                        <a class="dropdown-item btn-assign-driver py-2" href="javascript:void(0);" data-id="' . $row->id . '" data-code="' . htmlspecialchars($row->codigo_orden) . '">
                            <i class="ri-truck-line me-2 text-info align-middle"></i> Asignar y Despachar
                        </a>
                    </li>';
                }

                // Completar entrega (si está en ruta o pendiente)
                if ($row->estado === 'en_ruta' || $row->estado === 'pendiente') {
                    $items .= '<li>
                        <a class="dropdown-item btn-complete-delivery py-2" href="javascript:void(0);" data-id="' . $row->id . '" data-code="' . htmlspecialchars($row->codigo_orden) . '" data-client="' . htmlspecialchars($row->cliente?->nombres ?? '') . '" data-total="' . $row->total . '" data-delivered="' . $row->bidones_a_entregar . '">
                            <i class="ri-check-double-line me-2 text-success align-middle"></i> Completar Entrega
                        </a>
                    </li>';
                }

                // Emitir Comprobante en POS
                if ($canBill) {
                    $urlToPos = route('deliveries.to_pos', $row->id);
                    $items .= '<li>
                        <a class="dropdown-item py-2" href="' . $urlToPos . '">
                            <i class="ri-receipt-line me-2 text-primary align-middle"></i> Emitir Comprobante (POS)
                        </a>
                    </li>';
                }

                // Contactar por WhatsApp
                if ($row->telefono_contacto) {
                    $cleanPhone = preg_replace('/[^0-9]/', '', $row->telefono_contacto);
                    if (strlen($cleanPhone) === 9) {
                        $cleanPhone = '51' . $cleanPhone;
                    }
                    $waText = urlencode("¡Hola! Tu pedido de agua *{$row->codigo_orden}* está en camino a {$row->direccion_entrega}. Total: S/ " . number_format($row->total, 2));
                    $items .= '<li>
                        <a class="dropdown-item py-2" href="https://wa.me/' . $cleanPhone . '?text=' . $waText . '" target="_blank">
                            <i class="ri-whatsapp-line me-2 text-success align-middle"></i> Contactar por WhatsApp
                        </a>
                    </li>';
                }

                // Ver detalles (siempre disponible)
                $items .= '<li>
                    <a class="dropdown-item btn-order-details py-2" href="javascript:void(0);" data-id="' . $row->id . '">
                        <i class="ri-file-list-line me-2 text-secondary align-middle"></i> Ver Detalles
                    </a>
                </li>';

                // Cancelar pedido (si no está cancelado ni entregado)
                if ($row->estado !== 'cancelado' && $row->estado !== 'entregado') {
                    $items .= '<li><hr class="dropdown-divider my-1"></li>';
                    $items .= '<li>
                        <a class="dropdown-item btn-cancel-order text-danger py-2" href="javascript:void(0);" data-id="' . $row->id . '">
                            <i class="ri-close-circle-line me-2 text-danger align-middle"></i> Cancelar Pedido
                        </a>
                    </li>';
                }

                return '<div class="dropdown text-center">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle waves-effect shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-2-fill me-1 align-middle"></i> Acciones
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1" style="font-size: 0.85rem; min-width: 195px;">
                        ' . $items . '
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
            $orderCode = 'PED-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);

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
        $order->update([
            'idrepartidor' => $request->input('idrepartidor'),
            'estado' => 'en_ruta',
        ]);

        return response()->json([
            'status' => true,
            'msg' => "Pedido {$order->codigo_orden} despachado a ruta exitosamente.",
            'type' => 'success',
        ]);
    }

    public function complete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:delivery_orders,id',
            'bidones_vacios_recibidos' => 'required|integer|min:0',
            'bidones_danados_recibidos' => 'nullable|integer|min:0',
            'cobro_envases_danados' => 'nullable|numeric|min:0',
            'metodo_pago' => 'nullable|string|max:50',
            'estado_pago' => 'nullable|string|in:pagado,pendiente',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        return DB::transaction(function () use ($request) {
            $order = DeliveryOrder::with('items')->findOrFail($request->input('id'));
            $client = Client::findOrFail($order->idcliente);

            $intact = (int) $request->input('bidones_vacios_recibidos', 0);
            $damaged = (int) $request->input('bidones_danados_recibidos', 0);
            $damageCost = (float) $request->input('cobro_envases_danados', 0);

            $finalTotal = $order->total + $damageCost;

            $order->update([
                'estado' => 'entregado',
                'fecha_entrega' => Carbon::now(),
                'bidones_vacios_recibidos' => $intact,
                'bidones_danados_recibidos' => $damaged,
                'cobro_envases_danados' => $damageCost,
                'total' => $finalTotal,
                'metodo_pago' => $request->input('metodo_pago') ?: $order->metodo_pago,
                'estado_pago' => $request->input('estado_pago', 'pagado'),
            ]);

            // Registrar movimiento de envases
            $this->jugService->recordMovement(
                client: $client,
                deliveredFull: $order->bidones_a_entregar,
                returnedIntact: $intact,
                returnedDamaged: $damaged,
                damageCost: $damageCost,
                movementType: 'entrega_recarga',
                orderId: $order->id,
                warehouseId: $order->idalmacen,
                userId: auth()->id(),
                notes: "Entrega completada orden {$order->codigo_orden}"
            );

            // Acumular puntos de fidelidad por bidones entregados
            $eligibleCount = 0;
            foreach ($order->items as $item) {
                if ($item->tipo_item === 'recarga' || stripos($item->descripcion, 'recarga') !== false) {
                    $eligibleCount += (int) $item->cantidad;
                }
            }

            if ($eligibleCount > 0) {
                $this->loyaltyService->accumulatePurchases($client, $eligibleCount);
            }

            return response()->json([
                'status' => true,
                'msg' => "¡Entrega de orden {$order->codigo_orden} completada con éxito!",
                'type' => 'success',
            ]);
        });
    }

    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:delivery_orders,id',
            'motivo' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        $order = DeliveryOrder::findOrFail($request->input('id'));
        $order->update([
            'estado' => 'cancelado',
            'notas' => ($order->notas ? $order->notas . ' | ' : '') . 'Cancelado: ' . $request->input('motivo', 'Sin motivo'),
        ]);

        return response()->json([
            'status' => true,
            'msg' => "Pedido {$order->codigo_orden} cancelado.",
            'type' => 'success',
        ]);
    }

    public function show($id)
    {
        $order = DeliveryOrder::with(['cliente', 'repartidor', 'items.producto'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'order' => $order,
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
                'id'             => $product->id,
                'descripcion'    => $product->descripcion,
                'idunidad'       => $product->idunidad,
                'unidad'         => optional($product->unidad)->codigo ?? 'NIU',
                'igv'            => $igvVal,
                'idcodigo_igv'   => $product->idcodigo_igv ?? 1,
                'precio_compra'  => $product->precio_compra,
                'precio_venta'   => $precioVenta,
                'stock'          => $stockRow ? (int) $stockRow->stock_actual : null,
                'opcion'         => (int) $product->opcion,
                'cantidad'       => $cantidad,
                'idalmacen'      => $idalmacen,
            ];
        }

        $total = $subtotal + $igv;

        session(['pos' => [
            'products' => $cartProducts,
            'igv'      => $igv,
            'subtotal' => $subtotal,
            'total'    => $total,
        ]]);

        // Guardar ID del pedido de entrega en sesión para vincular con el comprobante al guardar venta
        session(['from_delivery_order_id' => $order->id]);

        // Pasar tipo de documento y datos del cliente como query-string
        $tipo = request()->query('tipo', 'boleta');   // boleta | factura_ruc | nota_venta
        $clientId = optional($order->cliente)->id;

        return redirect()->route('admin.pos.create', [
            'from_delivery' => $order->id,
            'tipo'          => $tipo,
            'client_id'     => $clientId,
        ]);
    }

    public function qr_generator()
    {
        $business = Business::find(1);
        $publicUrl = url('/pedido');

        return view('admin.deliveries.qr', compact('business', 'publicUrl'));
    }
}
