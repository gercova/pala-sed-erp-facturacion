<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\IdentityDocumentType;
use App\Models\Product;
use App\Services\Water\LoyaltyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PublicQrOrderController extends Controller
{
    protected LoyaltyService $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    public function index(Request $request)
    {
        $business = Business::find(1);

        $waterProducts = Product::where('opcion', 1)
            ->where(function ($q) {
                $q->where('descripcion', 'LIKE', '%AGUA%')
                    ->orWhere('descripcion', 'LIKE', '%BIDON%')
                    ->orWhere('descripcion', 'LIKE', '%DISPENSADOR%')
                    ->orWhere('descripcion', 'LIKE', '%BOMBA%')
                    ->orWhere('descripcion', 'LIKE', '%ENVASE%');
            })
            ->orderBy('id')
            ->get();

        if ($waterProducts->isEmpty()) {
            $waterProducts = Product::where('opcion', 1)->limit(6)->get();
        }

        $promotion = $this->loyaltyService->getActivePromotion();

        return view('public.order_qr', compact('business', 'waterProducts', 'promotion'));
    }

    public function check_client(Request $request)
    {
        $term = trim((string) $request->input('search'));

        if (empty($term)) {
            return response()->json(['status' => false, 'msg' => 'Ingresa tu número de teléfono o documento.'], 422);
        }

        $client = Client::where('telefono', $term)
            ->orWhere('nro_documento', $term)
            ->first();

        if (!$client) {
            return response()->json([
                'status' => false,
                'found' => false,
                'msg' => 'Cliente nuevo. Por favor completa tus datos para la entrega.',
            ]);
        }

        $loyaltyStatus = $this->loyaltyService->getClientStatus($client);

        return response()->json([
            'status' => true,
            'found' => true,
            'cliente' => [
                'id' => $client->id,
                'nombres' => $client->nombres,
                'nro_documento' => $client->nro_documento,
                'telefono' => $client->telefono,
                'direccion' => $client->direccion,
                'referencia' => $client->referencia,
                'saldo_envases' => $client->saldo_envases,
            ],
            'loyalty' => $loyaltyStatus,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombres' => 'required|string|max:255',
            'telefono' => 'required|string|max:30',
            'nro_documento' => 'nullable|string|max:20',
            'direccion' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'fecha_programada' => 'required|date|after_or_equal:today',
            'franja_horaria' => 'nullable|string|max:50',
            'metodo_pago' => 'required|string|max:50',
            'envases_a_devolver' => 'nullable|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.idproducto' => 'required|exists:products,id',
            'items.*.cantidad' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
                'type' => 'warning',
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            $phone = trim((string) $request->input('telefono'));
            $dni = trim((string) $request->input('nro_documento'));

            // Buscar cliente existente por teléfono o documento, o crear nuevo
            $client = null;
            if (!empty($phone)) {
                $client = Client::where('telefono', $phone)->first();
            }
            if (!$client && !empty($dni)) {
                $client = Client::where('nro_documento', $dni)->first();
            }

            $docType = IdentityDocumentType::where('codigo', '1')->first() ?? IdentityDocumentType::first();

            if (!$client) {
                $client = Client::create([
                    'iddoc' => $docType?->id ?? 1,
                    'nro_documento' => !empty($dni) ? $dni : ('GEN-' . time()),
                    'nombres' => mb_strtoupper(trim((string) $request->input('nombres'))),
                    'direccion' => mb_strtoupper(trim((string) $request->input('direccion'))),
                    'referencia' => trim((string) $request->input('referencia')),
                    'telefono' => $phone,
                    'codigo_pais' => 'PE',
                    'saldo_envases' => 0,
                ]);
            } else {
                // Actualizar dirección y referencia si se proporcionaron
                $client->update([
                    'nombres' => mb_strtoupper(trim((string) $request->input('nombres'))),
                    'direccion' => mb_strtoupper(trim((string) $request->input('direccion'))),
                    'referencia' => trim((string) $request->input('referencia')),
                ]);
            }

            // Revisar si cliente califica a promoción de fidelidad
            $loyaltyStatus = $this->loyaltyService->getClientStatus($client);
            $canClaimFree = $loyaltyStatus['reward_eligible'] ?? false;
            $freeClaimedThisOrder = false;

            $lastId = DeliveryOrder::max('id') ?? 0;
            $orderCode = 'PED-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $discountTotal = 0;
            $totalDeliveredJugs = 0;
            $itemsData = [];

            foreach ($request->input('items') as $item) {
                $product = Product::find($item['idproducto']);
                $qty = (float) $item['cantidad'];
                $price = (float) $product->precio_venta;
                $itemSubtotal = $qty * $price;
                $itemDiscount = 0;

                $tipoItem = 'producto';
                if (stripos($product->descripcion, 'recarga') !== false) {
                    $tipoItem = 'recarga';
                    $totalDeliveredJugs += (int) $qty;

                    // Si califica a premio y no ha canjeado aún en este pedido, bonificar 1 unidad
                    if ($canClaimFree && !$freeClaimedThisOrder) {
                        $itemDiscount = $price; // 100% descuento en 1 unidad
                        $freeClaimedThisOrder = true;
                    }
                } elseif (stripos($product->descripcion, 'nuevo') !== false) {
                    $tipoItem = 'con_envase';
                    $totalDeliveredJugs += (int) $qty;
                }

                $subtotal += $itemSubtotal;
                $discountTotal += $itemDiscount;

                $itemsData[] = [
                    'idproducto' => $product->id,
                    'descripcion' => $product->descripcion . ($itemDiscount > 0 ? ' [¡Premio Fidelidad 100% GRATIS!]' : ''),
                    'tipo_item' => $itemDiscount > 0 ? 'bonificacion_fidelidad' : $tipoItem,
                    'cantidad' => $qty,
                    'precio_unitario' => $price,
                    'descuento' => $itemDiscount,
                    'subtotal' => max(0, $itemSubtotal - $itemDiscount),
                ];
            }

            $finalTotal = max(0, $subtotal - $discountTotal);

            $order = DeliveryOrder::create([
                'codigo_orden' => $orderCode,
                'idcliente' => $client->id,
                'origen' => 'qr',
                'estado' => 'pendiente',
                'direccion_entrega' => $request->input('direccion') ?: $request->input('direccion_entrega'),
                'referencia' => $request->input('referencia'),
                'telefono_contacto' => $phone,
                'fecha_programada' => $request->input('fecha_programada'),
                'franja_horaria' => $request->input('franja_horaria', 'flexible'),
                'subtotal' => $subtotal,
                'descuento' => $discountTotal,
                'total' => $finalTotal,
                'metodo_pago' => $request->input('metodo_pago', 'contraentrega'),
                'estado_pago' => 'pendiente',
                'bidones_a_entregar' => $totalDeliveredJugs,
                'bidones_vacios_recibidos' => (int) $request->input('envases_a_devolver', 0),
                'notas' => 'Pedido QR. ' . ($request->filled('notas') ? 'Nota: ' . $request->input('notas') : ''),
            ]);

            foreach ($itemsData as $iData) {
                $iData['iddelivery_order'] = $order->id;
                DeliveryOrderItem::create($iData);
            }

            // Si se canjeó el premio de fidelidad, actualizar en el servicio
            if ($freeClaimedThisOrder) {
                $this->loyaltyService->redeemReward($client);
            }

            // Preparar enlace de WhatsApp para el cliente
            $business = Business::find(1);
            $companyPhone = preg_replace('/[^0-9]/', '', $business->telefono ?? '');
            if (!empty($companyPhone) && strlen($companyPhone) === 9) {
                $companyPhone = '51' . $companyPhone;
            }

            $waMsg = urlencode("¡Hola! Acabo de registrar mi pedido de agua *{$orderCode}* desde el código QR.\nNombre: {$client->nombres}\nDirección: {$order->direccion_entrega}\nTotal: S/ " . number_format($finalTotal, 2));
            $waLink = !empty($companyPhone) ? "https://wa.me/{$companyPhone}?text={$waMsg}" : null;

            return response()->json([
                'status' => true,
                'msg' => "¡Tu pedido {$orderCode} ha sido registrado con éxito!",
                'order_code' => $orderCode,
                'tracking_url' => route('public.order.tracking', ['code' => $orderCode]),
                'whatsapp_url' => $waLink,
                'free_reward_applied' => $freeClaimedThisOrder,
            ]);
        });
    }

    public function tracking($code)
    {
        $order = DeliveryOrder::with(['cliente', 'repartidor', 'items.producto'])
            ->where('codigo_orden', $code)
            ->firstOrFail();

        $business = Business::find(1);

        return view('public.order_tracking', compact('order', 'business'));
    }
}
