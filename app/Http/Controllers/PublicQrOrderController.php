<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Services\Water\LoyaltyService;
use App\Services\Water\OrderPlacementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicQrOrderController extends Controller
{
    public function __construct(
        public LoyaltyService $loyaltyService,
        public OrderPlacementService $orderPlacementService
    ) {}

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

    public function check_client(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('search'));

        if (empty($term)) {
            return response()->json(['status' => false, 'msg' => 'Ingresa tu número de teléfono o documento.'], 422);
        }

        // Rate limiting por IP y por identificador para proteger contra scraping
        $ipKey = 'qr_check_ip:'.$request->ip();
        $termKey = 'qr_check_term:'.Str::lower($term);

        if (RateLimiter::tooManyAttempts($ipKey, 20)) {
            $seconds = RateLimiter::availableIn($ipKey);

            return response()->json([
                'status' => false,
                'msg' => "Demasiados intentos. Intente nuevamente en {$seconds} segundos.",
            ], 429);
        }

        if (RateLimiter::tooManyAttempts($termKey, 5)) {
            $seconds = RateLimiter::availableIn($termKey);

            return response()->json([
                'status' => false,
                'msg' => "Demasiados intentos. Intente nuevamente en {$seconds} segundos.",
            ], 429);
        }

        RateLimiter::hit($ipKey, 60);
        RateLimiter::hit($termKey, 300);

        $client = Client::where('nro_documento', $term)
            ->orWhere('telefono', $term)
            ->first();

        if (! $client) {
            return response()->json([
                'status' => true,
                'found' => false,
                'msg' => 'Cliente no registrado. Complete sus datos para su primer pedido.',
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
                'coordenadas' => $client->coordenadas,
                'saldo_envases' => (int) $client->saldo_envases,
            ],
            'loyalty' => $loyaltyStatus,
        ]);
    }

    public function consultDni(Request $request): JsonResponse
    {
        $dni = trim((string) $request->input('dni', ''));

        if (! preg_match('/^\d{8}$/', $dni)) {
            return response()->json([
                'status' => false,
                'msg' => 'Para DNI debe ingresar exactamente 8 dígitos numéricos.',
            ], 422);
        }

        $ipThrottleKey = 'qr_reniec_ip:'.$request->ip();
        $dniThrottleKey = 'qr_reniec_dni:'.$dni;

        if (RateLimiter::tooManyAttempts($ipThrottleKey, 15)) {
            $seconds = RateLimiter::availableIn($ipThrottleKey);

            return response()->json([
                'status' => false,
                'msg' => "Demasiadas consultas. Intente nuevamente en {$seconds} segundos.",
            ], 429);
        }

        if (RateLimiter::tooManyAttempts($dniThrottleKey, 5)) {
            $seconds = RateLimiter::availableIn($dniThrottleKey);

            return response()->json([
                'status' => false,
                'msg' => "Demasiadas consultas. Intente nuevamente en {$seconds} segundos.",
            ], 429);
        }

        RateLimiter::hit($ipThrottleKey, 60);
        RateLimiter::hit($dniThrottleKey, 300);

        $document = $this->verify__client($dni);

        if (! isset($document->status) || (int) $document->status !== 200) {
            return response()->json([
                'status' => false,
                'msg' => 'No se pudo consultar el documento. Puede ingresar su nombre manualmente.',
                'found' => false,
            ], 200);
        }

        $data = $document->data ?? null;
        if (! $data) {
            return response()->json([
                'status' => false,
                'msg' => 'No se encontró información para el documento consultado.',
                'found' => false,
            ], 200);
        }

        $names = trim(($data->nombres ?? '').' '.($data->apellido_paterno ?? '').' '.($data->apellido_materno ?? ''));

        return response()->json([
            'status' => true,
            'found' => true,
            'nombres' => $names,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $result = $this->orderPlacementService->placeOrder(
                data: array_merge($request->all(), [
                    'origen' => 'qr',
                    'client_ip' => $request->ip(),
                ])
            );

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->validator->errors()->first(),
                'type' => 'warning',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error registrando pedido QR: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => false,
                'msg' => 'Ocurrió un error al procesar el pedido. Por favor intenta nuevamente.',
                'type' => 'error',
            ], 500);
        }
    }

    public function tracking($code): View
    {
        $order = DeliveryOrder::with(['cliente', 'repartidor', 'items.producto'])
            ->where('codigo_orden', $code)
            ->firstOrFail();

        $business = Business::find(1);

        return view('public.order_tracking', compact('order', 'business'));
    }
}
