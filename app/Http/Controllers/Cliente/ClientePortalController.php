<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\PayMode;
use App\Models\Product;
use App\Services\Water\LoyaltyService;
use App\Services\Water\OrderPlacementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ClientePortalController extends Controller
{
    public function __construct(
        public LoyaltyService $loyaltyService,
        public OrderPlacementService $orderPlacementService
    ) {}

    // ── Dashboard ──────────────────────────────────────────────────────────────

    public function dashboard(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $client = $this->resolveClient($user);
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
        $user = Auth::user();
        $client = $this->resolveClient($user);
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

        $loyalty = $this->loyaltyService->getClientStatus($client);
        $mapsApiKey = (string) config('services.maps.google_api_key', '');
        $mapProvider = (string) config('services.maps.provider', 'osm');
        $osmTileUrl = (string) config('services.maps.osm_tile_url', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
        $osmAttribution = (string) config('services.maps.osm_attribution', '&copy; OpenStreetMap contributors');
        $payModes = PayMode::all();

        return view('cliente.order', compact(
            'user', 'client', 'business', 'products', 'loyalty', 'mapsApiKey', 'mapProvider', 'osmTileUrl', 'osmAttribution', 'payModes'
        ));
    }

    // ── Guardar pedido ─────────────────────────────────────────────────────────

    public function storeOrder(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $client = $this->resolveClient($user);

        try {
            $result = $this->orderPlacementService->placeOrder(
                data: array_merge($request->all(), [
                    'origen' => 'portal',
                    'client_ip' => $request->ip(),
                ]),
                client: $client,
                registeredByUserId: $user->id
            );

            // Permitir ruta directa al seguimiento del portal si está disponible
            $result['portal_tracking_url'] = route('cliente.tracking', ['code' => $result['order_code']]);

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error registrando pedido desde portal cliente: '.$e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);

            return response()->json([
                'status' => false,
                'msg' => 'Ocurrió un error al procesar tu pedido. Por favor intenta nuevamente.',
            ], 500);
        }
    }

    // ── Seguimiento ────────────────────────────────────────────────────────────

    public function tracking(string $code): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
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
                'iddoc' => 1,
                'nro_documento' => 'USR-'.$user->id,
                'nombres' => mb_strtoupper($user->nombres ?? $user->user),
                'telefono' => '',
                'direccion' => '',
                'codigo_pais' => 'PE',
                'saldo_envases' => 0,
            ]);
            $user->forceFill(['idcliente' => $client->id])->saveQuietly();
        }

        return $client;
    }
}
