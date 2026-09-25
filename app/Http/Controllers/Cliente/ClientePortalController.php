<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\IdentityDocumentType;
use App\Models\Product;
use App\Services\Water\LoyaltyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClientePortalController extends Controller
{
    protected LoyaltyService $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    // ── Dashboard ──────────────────────────────────────────────────────────────

    public function dashboard(): View
    {
        /** @var \App\Models\User $user */
        $user     = Auth::user();
        $client   = $this->resolveClient($user);
        $business = Business::first();

        $orders = DeliveryOrder::where('idcliente', $client->id)
            ->orderByDesc('id')
            ->take(20)
            ->get();

        $loyalty = $this->loyaltyService->getClientStatus($client);

        return view('cliente.dashboard', compact('user', 'client', 'orders', 'loyalty', 'business'));
    }

    // ── Nuevo pedido ───────────────────────────────────────────────────────────

    public function order(): View
    {
        /** @var \App\Models\User $user */
        $user     = Auth::user();
        $client   = $this->resolveClient($user);
        $business = Business::first();

        $products = Product::with('unit')
            ->where('opcion', 1)
            ->where(function ($q) {
                $q->where('descripcion', 'LIKE', '%AGUA%')
                  ->orWhere('descripcion', 'LIKE', '%BIDON%')
                  ->orWhere('descripcion', 'LIKE', '%DISPENSADOR%')
                  ->orWhere('descripcion', 'LIKE', '%BOMBA%')
                  ->orWhere('descripcion', 'LIKE', '%ENVASE%');
            })
            ->orderBy('precio_venta')
            ->get();

        if ($products->isEmpty()) {
            $products = Product::with('unit')->where('opcion', 1)->limit(6)->get();
        }

        $loyalty    = $this->loyaltyService->getClientStatus($client);
        $mapsApiKey = config('services.google.maps_api_key', env('GOOGLE_MAPS_API_KEY', ''));

        return view('cliente.order', compact(
            'user', 'client', 'business', 'products', 'loyalty', 'mapsApiKey'
        ));
    }

    // ── Guardar pedido ─────────────────────────────────────────────────────────

    public function storeOrder(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user   = Auth::user();
        $client = $this->resolveClient($user);

        $validator = Validator::make($request->all(), [
            'direccion_entrega' => 'required|string|max:255',
            'referencia'        => 'nullable|string|max:255',
            'coordenadas'       => 'nullable|string|max:100',
            'fecha_programada'  => 'required|date|after_or_equal:today',
            'franja_horaria'    => 'nullable|string|max:50',
            'metodo_pago'       => 'required|string|in:efectivo,yape,plin,transferencia',
            'notas'             => 'nullable|string|max:500',
            'items'             => 'required|array|min:1',
            'items.*.idproducto' => 'required|exists:products,id',
            'items.*.cantidad'  => 'required|numeric|min:1|max:50',
        ], [
            'items.required'    => 'Debes seleccionar al menos un producto.',
            'metodo_pago.in'    => 'Método de pago no válido.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 422);
        }

        return DB::transaction(function () use ($request, $client) {
            $loyaltyStatus      = $this->loyaltyService->getClientStatus($client);
            $canClaimFree       = $loyaltyStatus['reward_eligible'] ?? false;
            $freeClaimedThisOrder = false;

            $lastId    = DeliveryOrder::max('id') ?? 0;
            $orderCode = 'PED-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);

            $subtotal       = 0.0;
            $discountTotal  = 0.0;
            $bidonesEntrega = 0;
            $itemsData      = [];

            foreach ($request->input('items') as $item) {
                $product     = Product::findOrFail($item['idproducto']);
                $qty         = (float) $item['cantidad'];
                $price       = (float) $product->precio_venta;
                $itemSubtotal = $qty * $price;
                $itemDiscount = 0.0;
                $tipoItem    = 'producto';

                if (stripos($product->descripcion, 'recarga') !== false) {
                    $tipoItem        = 'recarga';
                    $bidonesEntrega += (int) $qty;

                    if ($canClaimFree && ! $freeClaimedThisOrder) {
                        $itemDiscount        = $price;
                        $freeClaimedThisOrder = true;
                    }
                } elseif (stripos($product->descripcion, 'nuevo') !== false) {
                    $tipoItem        = 'con_envase';
                    $bidonesEntrega += (int) $qty;
                }

                $subtotal      += $itemSubtotal;
                $discountTotal += $itemDiscount;

                $itemsData[] = [
                    'idproducto'      => $product->id,
                    'descripcion'     => $product->descripcion . ($itemDiscount > 0 ? ' [¡Premio Fidelidad GRATIS!]' : ''),
                    'tipo_item'       => $itemDiscount > 0 ? 'bonificacion_fidelidad' : $tipoItem,
                    'cantidad'        => $qty,
                    'precio_unitario' => $price,
                    'descuento'       => $itemDiscount,
                    'subtotal'        => max(0.0, $itemSubtotal - $itemDiscount),
                ];
            }

            $finalTotal = max(0.0, $subtotal - $discountTotal);

            $order = DeliveryOrder::create([
                'codigo_orden'          => $orderCode,
                'idcliente'             => $client->id,
                'idusuario_registro'    => auth()->id(),   // usuario cliente que hizo el pedido
                'origen'                => 'portal',
                'estado'                => 'pendiente',
                'direccion_entrega'     => mb_strtoupper(trim($request->input('direccion_entrega'))),
                'referencia'            => trim((string) $request->input('referencia', '')),
                'coordenadas'           => $request->input('coordenadas'),
                'telefono_contacto'     => $request->input('telefono_contacto') ?: $client->telefono,
                'fecha_programada'      => $request->input('fecha_programada'),
                'franja_horaria'        => $request->input('franja_horaria', 'flexible'),
                'subtotal'              => $subtotal,
                'descuento'             => $discountTotal,
                'total'                 => $finalTotal,
                'metodo_pago'           => $request->input('metodo_pago'),
                'estado_pago'           => 'pendiente',
                'bidones_a_entregar'    => $bidonesEntrega,
                'notas'                 => $request->filled('notas') ? $request->input('notas') : 'Pedido desde portal de cliente.',
            ]);

            if ($request->filled('telefono_contacto') && $request->input('telefono_contacto') !== $client->telefono) {
                $client->forceFill(['telefono' => $request->input('telefono_contacto')])->saveQuietly();
            }
            if ($request->filled('coordenadas') && empty($client->coordenadas)) {
                $client->forceFill(['coordenadas' => $request->input('coordenadas')])->saveQuietly();
            }


            foreach ($itemsData as $iData) {
                $iData['iddelivery_order'] = $order->id;
                DeliveryOrderItem::create($iData);
            }

            if ($freeClaimedThisOrder) {
                $this->loyaltyService->redeemReward($client);
            }

            Log::info('Pedido creado desde portal cliente.', [
                'order_code' => $orderCode,
                'client_id'  => $client->id,
                'total'      => $finalTotal,
            ]);

            return response()->json([
                'status'             => true,
                'msg'                => "¡Tu pedido {$orderCode} fue registrado con éxito!",
                'order_code'         => $orderCode,
                'tracking_url'       => route('cliente.tracking', ['code' => $orderCode]),
                'free_reward_applied' => $freeClaimedThisOrder,
            ]);
        });
    }

    // ── Seguimiento ────────────────────────────────────────────────────────────

    public function tracking(string $code): View
    {
        /** @var \App\Models\User $user */
        $user   = Auth::user();
        $client = $this->resolveClient($user);

        $order = DeliveryOrder::with(['repartidor', 'items'])
            ->where('codigo_orden', $code)
            ->where('idcliente', $client->id)
            ->firstOrFail();

        $business = Business::first();

        return view('cliente.tracking', compact('order', 'client', 'business'));
    }

    // ── Logout ─────────────────────────────────────────────────────────────────

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function resolveClient(\App\Models\User $user): Client
    {
        if ($user->idcliente && $client = Client::find($user->idcliente)) {
            return $client;
        }

        // Fallback: buscar por teléfono/documento si la FK no está aún
        $client = Client::where('telefono', $user->user)
            ->orWhere('nro_documento', $user->user)
            ->first();

        if (! $client) {
            // Crear perfil mínimo
            $client = Client::create([
                'iddoc'        => 1,
                'nro_documento' => 'USR-' . $user->id,
                'nombres'      => mb_strtoupper($user->nombres ?? $user->user),
                'telefono'     => '',
                'direccion'    => '',
                'codigo_pais'  => 'PE',
                'saldo_envases' => 0,
            ]);
            $user->forceFill(['idcliente' => $client->id])->saveQuietly();
        }

        return $client;
    }
}
