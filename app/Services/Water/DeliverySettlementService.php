<?php

namespace App\Services\Water;

use App\Models\ArchingCash;
use App\Models\Business;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderStatusLog;
use App\Models\DetailPayment;
use App\Models\PayMode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeliverySettlementService
{
    public const STATUS_PENDIENTE = 'pendiente';

    public const STATUS_EN_RUTA = 'en_ruta';

    public const STATUS_ENTREGADO = 'entregado';

    public const STATUS_CANCELADO = 'cancelado';

    public const STATUS_REPROGRAMADO = 'reprogramado';

    /**
     * Matriz de transiciones válidas de la máquina de estados.
     */
    public const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDIENTE => [self::STATUS_EN_RUTA, self::STATUS_ENTREGADO, self::STATUS_CANCELADO, self::STATUS_REPROGRAMADO],
        self::STATUS_EN_RUTA => [self::STATUS_ENTREGADO, self::STATUS_CANCELADO, self::STATUS_REPROGRAMADO],
        self::STATUS_REPROGRAMADO => [self::STATUS_EN_RUTA, self::STATUS_ENTREGADO, self::STATUS_CANCELADO],
        self::STATUS_ENTREGADO => [], // Estado final
        self::STATUS_CANCELADO => [], // Estado final
    ];

    public const REASON_STANDARD_CLEARANCE = 'despacho_estandar';

    public const REASON_DAMAGED_PACKAGING = 'envase_danado';

    public const REASON_CANCELLED_ORDER = 'pedido_cancelado';

    public const REASON_DUPLICATE_SHIPMENT = 'envio_duplicado';

    public const VALID_REASONS = [
        self::REASON_STANDARD_CLEARANCE => 'Despacho estándar / Liquidación conforme',
        self::REASON_DAMAGED_PACKAGING => 'Envase dañado / cobro por merma',
        self::REASON_CANCELLED_ORDER => 'Pedido cancelado',
        self::REASON_DUPLICATE_SHIPMENT => 'Envío duplicado / anulación',
    ];

    public function __construct(
        protected JugMovementService $jugService,
        protected LoyaltyService $loyaltyService
    ) {}

    /**
     * Valida si una transición de estado es permitida.
     */
    public function canTransition(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        return in_array($newStatus, $allowed, true);
    }

    /**
     * Ejecuta una transición de estado con registro de auditoría.
     */
    public function transition(
        DeliveryOrder $order,
        string $newStatus,
        ?string $reason = null,
        ?string $notes = null,
        ?array $metadata = null,
        ?int $userId = null
    ): DeliveryOrder {
        $currentStatus = $order->estado;

        if (! $this->canTransition($currentStatus, $newStatus)) {
            throw new InvalidArgumentException(
                "Transición de estado inválida: no es posible pasar de '{$currentStatus}' a '{$newStatus}'."
            );
        }

        if ($currentStatus === $newStatus) {
            return $order;
        }

        return DB::transaction(function () use ($order, $currentStatus, $newStatus, $reason, $notes, $metadata, $userId) {
            $effectiveUserId = $userId ?: auth()->id();

            $order->update([
                'estado' => $newStatus,
            ]);

            DeliveryOrderStatusLog::create([
                'iddelivery_order' => $order->id,
                'idusuario' => $effectiveUserId,
                'estado_anterior' => $currentStatus,
                'estado_nuevo' => $newStatus,
                'motivo' => $reason,
                'notas' => $notes,
                'metadata' => $metadata,
            ]);

            return $order;
        });
    }

    /**
     * Realiza la liquidación completa e idempotente de la entrega.
     *
     * Invariante B1: Todo dentro de una única transacción DB.
     * La llamada externa (WhatsApp) se genera fuera de la transacción.
     */
    public function settle(DeliveryOrder $order, array $data, ?int $userId = null): array
    {
        $effectiveUserId = $userId ?: auth()->id();

        // 1. COMPROBACIÓN DE IDEMPOTENCIA:
        // Si el pedido ya fue liquidado o está entregado, NO duplicar movimientos ni cobros.
        if ($order->liquidado_at !== null || $order->estado === self::STATUS_ENTREGADO) {
            $whatsappUrl = $this->buildWhatsAppReceiptUrl($order);

            return [
                'status' => true,
                'already_settled' => true,
                'msg' => "El pedido {$order->codigo_orden} ya fue liquidado previamente (operación idempotente).",
                'type' => 'info',
                'order' => $order,
                'whatsapp_url' => $whatsappUrl,
                'has_whatsapp' => ! empty($whatsappUrl),
            ];
        }

        // 2. Transacción atómica
        $order = DB::transaction(function () use ($order, $data, $effectiveUserId) {
            // Bloqueo pesimista para evitar carreras en doble-click
            $lockedOrder = DeliveryOrder::with(['items', 'cliente'])->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->liquidado_at !== null || $lockedOrder->estado === self::STATUS_ENTREGADO) {
                return $lockedOrder;
            }

            // Validar transición en la máquina de estados
            if (! $this->canTransition($lockedOrder->estado, self::STATUS_ENTREGADO)) {
                throw new InvalidArgumentException(
                    "No se puede liquidar el pedido {$lockedOrder->codigo_orden} porque su estado actual es '{$lockedOrder->estado}'."
                );
            }

            $client = Client::findOrFail($lockedOrder->idcliente);

            $intact = (int) ($data['bidones_vacios_recibidos'] ?? 0);
            $damaged = (int) ($data['bidones_danados_recibidos'] ?? 0);
            $damageCost = (float) ($data['cobro_envases_danados'] ?? 0);
            $motivo = $data['motivo_liquidacion'] ?? self::REASON_STANDARD_CLEARANCE;
            $metodoPago = $data['metodo_pago'] ?? $lockedOrder->metodo_pago ?? 'efectivo';
            $estadoPago = $data['estado_pago'] ?? 'pagado';
            $notas = $data['notas'] ?? null;

            // Recalcular total con cobro por envases dañados
            $baseTotal = (float) $lockedOrder->subtotal - (float) $lockedOrder->descuento;
            if ($baseTotal <= 0 && $lockedOrder->total > 0) {
                $baseTotal = (float) $lockedOrder->total;
            }
            $finalTotal = round($baseTotal + $damageCost, 2);

            // Invariante B1: Resolver idalmacen (usuario o pedido o 1)
            $user = User::find($effectiveUserId);
            $idalmacen = (int) ($user?->idalmacen ?: ($lockedOrder->idalmacen ?: 1));

            // Invariante B1: Resolver idarqueocaja activo
            $activeArqueo = $this->resolveActiveArqueo($effectiveUserId, $lockedOrder->idrepartidor, $idalmacen);

            $prevStatus = $lockedOrder->estado;

            // Actualizar orden
            $lockedOrder->update([
                'estado' => self::STATUS_ENTREGADO,
                'fecha_entrega' => Carbon::now(),
                'liquidado_at' => Carbon::now(),
                'bidones_vacios_recibidos' => $intact,
                'bidones_danados_recibidos' => $damaged,
                'cobro_envases_danados' => $damageCost,
                'total' => $finalTotal,
                'metodo_pago' => $metodoPago,
                'estado_pago' => $estadoPago,
                'idalmacen' => $idalmacen,
                'idarqueocaja' => $activeArqueo?->id,
                'motivo_liquidacion' => $motivo,
                'notas' => $notas ? ($lockedOrder->notas ? $lockedOrder->notas.' | '.$notas : $notas) : $lockedOrder->notas,
            ]);

            // Auditoría de estado
            DeliveryOrderStatusLog::create([
                'iddelivery_order' => $lockedOrder->id,
                'idusuario' => $effectiveUserId,
                'estado_anterior' => $prevStatus,
                'estado_nuevo' => self::STATUS_ENTREGADO,
                'motivo' => $motivo,
                'notas' => $notas,
                'metadata' => [
                    'intact' => $intact,
                    'damaged' => $damaged,
                    'damage_cost' => $damageCost,
                    'total' => $finalTotal,
                    'metodo_pago' => $metodoPago,
                    'estado_pago' => $estadoPago,
                    'idarqueocaja' => $activeArqueo?->id,
                ],
            ]);

            // Actualizar saldo de envases del cliente
            $this->jugService->recordMovement(
                client: $client,
                deliveredFull: $lockedOrder->bidones_a_entregar,
                returnedIntact: $intact,
                returnedDamaged: $damaged,
                damageCost: $damageCost,
                movementType: 'entrega_recarga',
                orderId: $lockedOrder->id,
                warehouseId: $idalmacen,
                userId: $effectiveUserId,
                notes: "Liquidación orden {$lockedOrder->codigo_orden} - Motivo: {$motivo}"
            );

            // Acumular puntos de fidelidad por recargas entregadas
            $eligibleCount = 0;
            foreach ($lockedOrder->items as $item) {
                if ($item->tipo_item === 'recarga' || stripos($item->descripcion, 'recarga') !== false) {
                    $eligibleCount += (int) $item->cantidad;
                }
            }
            if ($eligibleCount > 0) {
                $this->loyaltyService->accumulatePurchases($client, $eligibleCount);
            }

            // Invariante B1: Registrar pago en DetailPayment vinculado a idarqueocaja
            if ($estadoPago === 'pagado' && $finalTotal > 0 && $activeArqueo) {
                $payMode = $this->resolvePayMode($metodoPago);

                DetailPayment::create([
                    'idtipo_comprobante' => 2, // Nota de Venta / Recibo de Entrega
                    'idfactura' => $lockedOrder->idnotaventa ?: ($lockedOrder->idfactura ?: $lockedOrder->id),
                    'idpago' => $payMode->id,
                    'monto' => $finalTotal,
                    'idarqueocaja' => $activeArqueo->id,
                    'estado' => 1,
                ]);
            }

            return $lockedOrder;
        });

        // 3. Fuera de la transacción: generar enlace de WhatsApp
        $whatsappUrl = $this->buildWhatsAppReceiptUrl($order);

        return [
            'status' => true,
            'already_settled' => false,
            'msg' => "¡Entrega y liquidación de orden {$order->codigo_orden} completada con éxito!",
            'type' => 'success',
            'order' => $order,
            'whatsapp_url' => $whatsappUrl,
            'has_whatsapp' => ! empty($whatsappUrl),
        ];
    }

    /**
     * Cancela un pedido con validación de estado y auditoría.
     */
    public function cancel(DeliveryOrder $order, ?string $reason = null, ?int $userId = null): DeliveryOrder
    {
        return $this->transition(
            order: $order,
            newStatus: self::STATUS_CANCELADO,
            reason: $reason ?: self::REASON_CANCELLED_ORDER,
            notes: $reason,
            metadata: ['motivo' => $reason],
            userId: $userId
        );
    }

    /**
     * Construye enlaces inteligentes a Google Maps sin costo ni consumo de API.
     */
    public function buildMapsLinks(DeliveryOrder $order): array
    {
        $coordinates = trim((string) $order->coordenadas);
        $address = trim((string) $order->direccion_entrega);

        $hasCoordinates = ! empty($coordinates) && preg_match('/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/', $coordinates);
        $hasAddress = ! empty($address);
        $hasLocation = $hasCoordinates || $hasAddress;

        if ($hasCoordinates) {
            $coordQuery = str_replace(' ', '', $coordinates);
            $viewUrl = "https://www.google.com/maps/search/?api=1&query={$coordQuery}";
            $routeUrl = "https://www.google.com/maps/dir/?api=1&destination={$coordQuery}";
        } elseif ($hasAddress) {
            $addrQuery = urlencode($address.', Peru');
            $viewUrl = "https://www.google.com/maps/search/?api=1&query={$addrQuery}";
            $routeUrl = "https://www.google.com/maps/dir/?api=1&destination={$addrQuery}";
        } else {
            $viewUrl = null;
            $routeUrl = null;
        }

        return [
            'has_location' => $hasLocation,
            'has_coordinates' => $hasCoordinates,
            'coordinates' => $coordinates,
            'address' => $address,
            'view_url' => $viewUrl,
            'route_url' => $routeUrl,
            'message' => $hasLocation ? null : 'Sin dirección ni coordenadas registradas para este pedido.',
        ];
    }

    /**
     * Resuelve el arqueo de caja abierto correspondiente.
     */
    protected function resolveActiveArqueo(?int $userId, ?int $driverId, int $warehouseId): ?ArchingCash
    {
        if ($userId) {
            $userArqueo = ArchingCash::where('idusuario', $userId)
                ->where('estado', 1)
                ->latest('id')
                ->first();
            if ($userArqueo) {
                return $userArqueo;
            }
        }

        if ($driverId) {
            $driverArqueo = ArchingCash::where('idusuario', $driverId)
                ->where('estado', 1)
                ->latest('id')
                ->first();
            if ($driverArqueo) {
                return $driverArqueo;
            }
        }

        if ($warehouseId) {
            $warehouseArqueo = ArchingCash::where('idalmacen', $warehouseId)
                ->where('estado', 1)
                ->latest('id')
                ->first();
            if ($warehouseArqueo) {
                return $warehouseArqueo;
            }
        }

        return ArchingCash::where('estado', 1)->latest('id')->first();
    }

    /**
     * Resuelve el modo de pago en pay_modes.
     */
    protected function resolvePayMode(?string $methodName): PayMode
    {
        $normalized = strtolower(trim((string) $methodName));

        if (! empty($normalized)) {
            $found = PayMode::whereRaw('LOWER(descripcion) LIKE ?', ["%{$normalized}%"])->first();
            if ($found) {
                return $found;
            }
        }

        return PayMode::whereRaw('LOWER(descripcion) LIKE ?', ['%efectivo%'])->first()
            ?? PayMode::first()
            ?? PayMode::create(['descripcion' => 'Efectivo', 'estado' => 1]);
    }

    /**
     * Construye la URL de comprobante por WhatsApp fuera de la transacción.
     */
    protected function buildWhatsAppReceiptUrl(DeliveryOrder $order): ?string
    {
        $order->loadMissing('cliente');
        $client = $order->cliente;
        $phone = $order->telefono_contacto ?: ($client?->telefono);

        if (! $phone) {
            return null;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 9) {
            $cleanPhone = '51'.$cleanPhone;
        }

        $business = Business::first();
        $businessName = $business?->razon_social ?: ($business?->nombre_comercial ?: 'Pala-Sed');

        $deliveryDate = $order->fecha_entrega ? Carbon::parse($order->fecha_entrega)->format('d/m/Y H:i') : date('d/m/Y H:i');

        $receiptMessage = "💧 *COMPROBANTE DE ENTREGA Y LIQUIDACIÓN - {$businessName}*\n"
            ."━━━━━━━━━━━━━━━━━━━━━\n"
            ."📄 *Pedido:* {$order->codigo_orden}\n"
            .'👤 *Cliente:* '.($client?->nombres ?? 'Cliente')."\n"
            ."📅 *Fecha de Entrega:* {$deliveryDate}\n"
            ."📍 *Dirección:* {$order->direccion_entrega}\n"
            ."━━━━━━━━━━━━━━━━━━━━━\n"
            ."📦 *Bidones Entregados:* {$order->bidones_a_entregar}\n"
            ."🔄 *Envases Devueltos (Intactos):* {$order->bidones_vacios_recibidos}\n";

        if ($order->bidones_danados_recibidos > 0) {
            $receiptMessage .= "⚠️ *Envases Dañados / Reposición:* {$order->bidones_danados_recibidos} (S/ ".number_format($order->cobro_envases_danados, 2).")\n";
        }

        $receiptMessage .= '💳 *Método de Pago:* '.ucfirst($order->metodo_pago ?? 'Efectivo')."\n"
            .'💰 *TOTAL PAGADO:* S/ '.number_format($order->total, 2)."\n";

        if ($client) {
            $receiptMessage .= "🪣 *Saldo Actual de Envases:* {$client->saldo_envases} en tu poder\n";
        }

        $receiptMessage .= "━━━━━━━━━━━━━━━━━━━━━\n"
            .'¡Muchas gracias por su preferencia!';

        return 'https://wa.me/'.$cleanPhone.'?text='.urlencode($receiptMessage);
    }
}
