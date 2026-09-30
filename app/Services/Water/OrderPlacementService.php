<?php

namespace App\Services\Water;

use App\Models\Business;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\IdentityDocumentType;
use App\Models\PayMode;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderPlacementService
{
    public function __construct(
        public LoyaltyService $loyaltyService
    ) {}

    /**
     * Valida y registra un pedido tanto desde el portal de cliente como desde QR.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function placeOrder(array $data, ?Client $client = null, ?int $registeredByUserId = null): array
    {
        $validated = $this->validateOrderData($data, $client !== null);

        $clientIp = $data['client_ip'] ?? request()->ip();
        $this->validateTimestamps($validated['device_timestamp'] ?? null, $clientIp);

        return DB::transaction(function () use ($validated, $client, $registeredByUserId) {
            $enviarOtraDireccion = (bool) ($validated['enviar_otra_direccion'] ?? false);

            $targetClient = $this->resolveClient($validated, $client, $enviarOtraDireccion);

            $direccionEntrega = mb_strtoupper(trim((string) ($validated['direccion_entrega'] ?? $validated['direccion'] ?? '')));
            $referencia = trim((string) ($validated['referencia'] ?? ''));
            $coordenadas = trim((string) ($validated['coordenadas'] ?? '')) ?: $targetClient->coordenadas;
            $telefonoContacto = trim((string) ($validated['telefono_contacto'] ?? $validated['telefono'] ?? '')) ?: $targetClient->telefono;

            $loyaltyStatus = $this->loyaltyService->getClientStatus($targetClient);
            $canClaimFree = $loyaltyStatus['reward_eligible'] ?? false;
            $freeClaimedThisOrder = false;

            $orderCode = $this->generateUniqueOrderCode();

            $subtotal = 0.0;
            $discountTotal = 0.0;
            $bidonesEntrega = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                /** @var Product $product */
                $product = Product::findOrFail($item['idproducto']);
                $qty = (float) $item['cantidad'];
                $price = (float) $product->precio_venta;
                $itemSubtotal = $qty * $price;
                $itemDiscount = 0.0;
                $tipoItem = 'producto';

                if (stripos($product->descripcion, 'recarga') !== false) {
                    $tipoItem = 'recarga';
                    $bidonesEntrega += (int) $qty;

                    if ($canClaimFree && ! $freeClaimedThisOrder) {
                        $itemDiscount = $price;
                        $freeClaimedThisOrder = true;
                    }
                } elseif (stripos($product->descripcion, 'nuevo') !== false) {
                    $tipoItem = 'con_envase';
                    $bidonesEntrega += (int) $qty;
                }

                $subtotal += $itemSubtotal;
                $discountTotal += $itemDiscount;

                $itemsData[] = [
                    'idproducto' => $product->id,
                    'descripcion' => $product->descripcion.($itemDiscount > 0 ? ' [¡Premio Fidelidad GRATIS!]' : ''),
                    'tipo_item' => $itemDiscount > 0 ? 'bonificacion_fidelidad' : $tipoItem,
                    'cantidad' => $qty,
                    'precio_unitario' => $price,
                    'descuento' => $itemDiscount,
                    'subtotal' => max(0.0, $itemSubtotal - $itemDiscount),
                ];
            }

            $finalTotal = max(0.0, $subtotal - $discountTotal);
            $normalizedPaymentMethod = $this->normalizePaymentMethod($validated['metodo_pago'] ?? 'efectivo');

            $origen = $validated['origen'] ?? ($registeredByUserId ? 'portal' : 'qr');
            $notas = trim((string) ($validated['notas'] ?? ''));
            if ($origen === 'portal' && empty($notas)) {
                $notas = 'Pedido desde portal de cliente.';
            } elseif ($origen === 'qr' && empty($notas)) {
                $notas = 'Pedido QR.';
            }

            $order = DeliveryOrder::create([
                'codigo_orden' => $orderCode,
                'idcliente' => $targetClient->id,
                'idusuario_registro' => $registeredByUserId,
                'origen' => $origen,
                'estado' => 'pendiente',
                'direccion_entrega' => $direccionEntrega,
                'referencia' => $referencia,
                'coordenadas' => $coordenadas,
                'telefono_contacto' => $telefonoContacto,
                'fecha_programada' => $validated['fecha_programada'],
                'franja_horaria' => $validated['franja_horaria'] ?? 'flexible',
                'subtotal' => $subtotal,
                'descuento' => $discountTotal,
                'total' => $finalTotal,
                'metodo_pago' => $normalizedPaymentMethod,
                'estado_pago' => 'pendiente',
                'bidones_a_entregar' => $bidonesEntrega,
                'bidones_vacios_recibidos' => (int) ($validated['envases_a_devolver'] ?? 0),
                'notas' => $notas,
            ]);

            foreach ($itemsData as $iData) {
                $iData['iddelivery_order'] = $order->id;
                DeliveryOrderItem::create($iData);
            }

            if ($freeClaimedThisOrder) {
                $this->loyaltyService->redeemReward($targetClient);
            }

            $business = Business::first();
            $waLink = $this->buildWhatsAppLink($business, $order, $targetClient, $finalTotal);

            Log::info("Pedido {$orderCode} registrado con éxito.", [
                'order_code' => $orderCode,
                'order_id' => $order->id,
                'client_id' => $targetClient->id,
                'origen' => $origen,
                'total' => $finalTotal,
                'otra_direccion' => $enviarOtraDireccion,
            ]);

            return [
                'status' => true,
                'msg' => "¡Tu pedido {$orderCode} fue registrado con éxito!",
                'order' => $order,
                'order_code' => $orderCode,
                'tracking_url' => route('public.order.tracking', ['code' => $orderCode]),
                'whatsapp_url' => $waLink,
                'free_reward_applied' => $freeClaimedThisOrder,
                'total' => $finalTotal,
            ];
        });
    }

    /**
     * Valida los campos del pedido.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validateOrderData(array $data, bool $isClientAuthenticated = false): array
    {
        $rules = [
            'direccion_entrega' => 'required_without:direccion|nullable|string|max:255',
            'direccion' => 'required_without:direccion_entrega|nullable|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'coordenadas' => 'nullable|string|max:100',
            'fecha_programada' => 'required|date|after_or_equal:today',
            'franja_horaria' => 'nullable|string|in:manana,tarde,noche,flexible',
            'metodo_pago' => 'required|string|max:50',
            'envases_a_devolver' => 'nullable|integer|min:0',
            'notas' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.idproducto' => 'required|exists:products,id',
            'items.*.cantidad' => 'required|numeric|min:1|max:50',
            'device_timestamp' => 'nullable|string|max:100',
            'enviar_otra_direccion' => 'nullable|boolean',
        ];

        if (! $isClientAuthenticated) {
            $rules['nombres'] = 'required|string|max:255';
            $rules['telefono'] = 'required|string|max:30';
            $rules['nro_documento'] = 'nullable|string|regex:/^\d{8}$/';
        }

        $messages = [
            'items.required' => 'Debes seleccionar al menos un producto.',
            'items.min' => 'Debes seleccionar al menos un producto.',
            'fecha_programada.after_or_equal' => 'La fecha de entrega debe ser hoy o una fecha posterior.',
            'nro_documento.regex' => 'El DNI debe contener exactamente 8 dígitos numéricos.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Compara el timestamp capturado del dispositivo con la hora del servidor (fuente de verdad).
     * Si difieren por más de 15 minutos, registra una alerta en los logs.
     */
    public function validateTimestamps(?string $deviceTimestamp, ?string $clientIp = null): Carbon
    {
        $serverTime = Carbon::now();

        if (! empty($deviceTimestamp)) {
            try {
                $deviceTime = Carbon::parse($deviceTimestamp);
                $diffMinutes = abs($serverTime->diffInMinutes($deviceTime, false));

                if ($diffMinutes > 15) {
                    Log::warning('Diferencia detectada entre timestamp del dispositivo y servidor al registrar pedido.', [
                        'device_timestamp' => $deviceTimestamp,
                        'server_timestamp' => $serverTime->toIso8601String(),
                        'diff_minutes' => round($diffMinutes, 2),
                        'ip' => $clientIp ?? request()->ip(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::notice('No se pudo parsear el timestamp del dispositivo del cliente.', [
                    'device_timestamp' => $deviceTimestamp,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $serverTime;
    }

    /**
     * Resuelve o crea el cliente sin sobrescribir la dirección predeterminada si es un envío alternativo.
     *
     * @param  array<string, mixed>  $data
     */
    protected function resolveClient(array $data, ?Client $existingClient = null, bool $enviarOtraDireccion = false): Client
    {
        if ($existingClient !== null) {
            // Si el cliente ya existe y NO es otra dirección, podemos completar coordenadas/referencia si estaban vacías
            if (! $enviarOtraDireccion) {
                $updates = [];
                $coordenadas = trim((string) ($data['coordenadas'] ?? ''));
                $referencia = trim((string) ($data['referencia'] ?? ''));
                $telefono = trim((string) ($data['telefono_contacto'] ?? $data['telefono'] ?? ''));

                if (! empty($coordenadas) && empty($existingClient->coordenadas)) {
                    $updates['coordenadas'] = $coordenadas;
                }
                if (! empty($referencia) && empty($existingClient->referencia)) {
                    $updates['referencia'] = $referencia;
                }
                if (! empty($telefono) && empty($existingClient->telefono)) {
                    $updates['telefono'] = $telefono;
                }

                if (! empty($updates)) {
                    $existingClient->forceFill($updates)->saveQuietly();
                }
            }

            return $existingClient;
        }

        // Búsqueda para pedidos públicos (QR)
        $dni = trim((string) ($data['nro_documento'] ?? ''));
        $phone = trim((string) ($data['telefono'] ?? ''));
        $foundClient = null;

        if (! empty($dni)) {
            $foundClient = Client::where('nro_documento', $dni)->first();
        }
        if (! $foundClient && ! empty($phone)) {
            $foundClient = Client::where('telefono', $phone)->first();
        }

        if ($foundClient) {
            if (! $enviarOtraDireccion) {
                $updates = [];
                $coordenadas = trim((string) ($data['coordenadas'] ?? ''));
                $referencia = trim((string) ($data['referencia'] ?? ''));

                if (! empty($coordenadas) && empty($foundClient->coordenadas)) {
                    $updates['coordenadas'] = $coordenadas;
                }
                if (! empty($referencia) && empty($foundClient->referencia)) {
                    $updates['referencia'] = $referencia;
                }

                if (! empty($updates)) {
                    $foundClient->forceFill($updates)->saveQuietly();
                }
            }

            return $foundClient;
        }

        $direccion = mb_strtoupper(trim((string) ($data['direccion_entrega'] ?? $data['direccion'] ?? '')));
        $referencia = trim((string) ($data['referencia'] ?? ''));
        $coordenadas = trim((string) ($data['coordenadas'] ?? ''));
        $docType = IdentityDocumentType::where('codigo', '1')->first() ?? IdentityDocumentType::first();

        return Client::create([
            'iddoc' => $docType?->id ?? 1,
            'nro_documento' => ! empty($dni) ? $dni : ('GEN-'.time()),
            'nombres' => mb_strtoupper(trim((string) ($data['nombres'] ?? ''))),
            'direccion' => $direccion,
            'referencia' => $referencia,
            'coordenadas' => $coordenadas,
            'telefono' => $phone,
            'codigo_pais' => 'PE',
            'saldo_envases' => 0,
        ]);
    }

    /**
     * Genera un código de orden único garantizado.
     */
    protected function generateUniqueOrderCode(): string
    {
        $lastId = (int) (DeliveryOrder::max('id') ?? 0);
        $orderCode = 'PED-'.str_pad((string) ($lastId + 1), 6, '0', STR_PAD_LEFT);

        while (DeliveryOrder::where('codigo_orden', $orderCode)->exists()) {
            $lastId++;
            $orderCode = 'PED-'.str_pad((string) ($lastId + 1), 6, '0', STR_PAD_LEFT);
        }

        return $orderCode;
    }

    /**
     * Normaliza el método de pago frente a los métodos disponibles en pay_modes.
     */
    public function normalizePaymentMethod(string $method): string
    {
        $raw = strtolower(trim($method));

        if ($raw === 'contraentrega' || $raw === 'efectivo') {
            return 'efectivo';
        }
        if ($raw === 'yape') {
            return 'yape';
        }
        if ($raw === 'plin') {
            return 'plin';
        }
        if ($raw === 'transferencia') {
            return 'transferencia';
        }

        $match = PayMode::where('descripcion', 'like', "%{$method}%")->first();
        if ($match) {
            return strtolower($match->descripcion);
        }

        return $raw;
    }

    /**
     * Genera enlace de WhatsApp de confirmación si la empresa tiene teléfono configurado.
     */
    protected function buildWhatsAppLink(?Business $business, DeliveryOrder $order, Client $client, float $total): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $business->telefono ?? '');
        if (empty($phone)) {
            return null;
        }

        if (strlen($phone) === 9) {
            $phone = '51'.$phone;
        }

        $msg = "¡Hola! Acabo de registrar mi pedido de agua *{$order->codigo_orden}*.\nNombre: {$client->nombres}\nDirección: {$order->direccion_entrega}\nTotal: S/ ".number_format($total, 2);

        return 'https://wa.me/'.$phone.'?text='.urlencode($msg);
    }
}
