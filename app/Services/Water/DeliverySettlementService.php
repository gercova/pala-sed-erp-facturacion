<?php

namespace App\Services\Water;

use App\Jobs\EmitirComprobanteJob;
use App\Jobs\EnviarWhatsAppJob;
use App\Models\ArchingCash;
use App\Models\Billing;
use App\Models\Business;
use App\Models\Client;
use App\Models\Currency;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderStatusLog;
use App\Models\DetailBilling;
use App\Models\DetailPayment;
use App\Models\DetailSaleNote;
use App\Models\IgvTypeAffection;
use App\Models\PayMode;
use App\Models\Product;
use App\Models\SaleNote;
use App\Models\Serie;
use App\Models\User;
use App\Services\Inventory\StockService;
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
        protected LoyaltyService $loyaltyService,
        protected DeliveryDocumentResolverService $documentResolver,
        protected WhatsAppSenderService $whatsappSender,
        protected StockService $stockService
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

            // Acumular puntos de fidelidad por recargas entregadas (centralizado y anti-duplicación)
            $this->loyaltyService->accumulateFromDeliveryOrder($lockedOrder);

            // Invariante B1: Descuento de stock en almacén para productos físicos inventariables
            foreach ($lockedOrder->items as $item) {
                if ($item->idproducto) {
                    $this->stockService->decreaseStock(
                        (int) $item->idproducto,
                        $idalmacen,
                        (float) $item->cantidad,
                        'entrega_delivery',
                        'Pedido Delivery',
                        $lockedOrder->codigo_orden
                    );
                }
            }

            // Emisión Automática de Comprobante (Factura 01, Boleta 03 o Nota de Venta 02)
            $business = Business::query()->first();
            $payMode = $this->resolvePayMode($metodoPago);
            $issuedDocument = null;
            $issuedKind = null;

            $hasExistingDoc = (bool) ($lockedOrder->idfactura || $lockedOrder->idnotaventa);

            if (! $hasExistingDoc && ($business?->facturacion_automatica_delivery ?? true)) {
                $docType = $this->documentResolver->resolve($client, $finalTotal, $business);

                $serieModel = Serie::where('idtipo_documento', (int) $docType->id)
                    ->when($activeArqueo?->idcaja, fn ($q, $cajaId) => $q->where('idcaja', $cajaId))
                    ->where('estado', 1)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if (! $serieModel) {
                    $serieModel = Serie::where('idtipo_documento', (int) $docType->id)
                        ->where('estado', 1)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();
                }

                if ($serieModel) {
                    $correlativoActual = (int) $serieModel->correlativo;
                    $formattedCorrelative = str_pad((string) $correlativoActual, 8, '0', STR_PAD_LEFT);
                    $serieModel->update([
                        'correlativo' => str_pad((string) ($correlativoActual + 1), 8, '0', STR_PAD_LEFT),
                    ]);

                    $todayDate = Carbon::now()->format('Y-m-d');
                    $nowTime = Carbon::now()->format('H:i:s');
                    $baseDocName = $serieModel->serie.'-'.$formattedCorrelative;

                    if ((string) $docType->codigo === '02') {
                        // Nota de Venta (Comprobante interno, sin reporte a SUNAT)
                        $issuedDocument = SaleNote::create([
                            'idtipo_comprobante' => (int) $docType->id,
                            'serie' => $serieModel->serie,
                            'correlativo' => $formattedCorrelative,
                            'fecha_emision' => $todayDate,
                            'fecha_vencimiento' => $todayDate,
                            'hora' => $nowTime,
                            'idcliente' => (int) $client->id,
                            'modo_pago' => 1,
                            'subtotal' => $baseTotal,
                            'igv' => 0.00,
                            'total' => $finalTotal,
                            'monto_credito' => 0,
                            'payment_breakdown' => [[
                                'id' => $payMode->id,
                                'descripcion' => $payMode->descripcion,
                                'monto' => $finalTotal,
                            ]],
                            'observaciones' => "Entrega Delivery {$lockedOrder->codigo_orden}",
                            'estado' => 1,
                            'estado_whatsapp' => Billing::WPP_STATUS_PENDIENTE,
                            'idusuario' => $effectiveUserId,
                            'idarqueocaja' => $activeArqueo?->id,
                            'vuelto' => 0.00,
                        ]);

                        foreach ($lockedOrder->items as $item) {
                            DetailSaleNote::create([
                                'idnotaventa' => $issuedDocument->id,
                                'idproducto' => $item->idproducto ?: 1,
                                'cantidad' => $item->cantidad,
                                'igv' => 0.00,
                                'precio_unitario' => $item->precio_unitario,
                                'precio_total' => $item->subtotal,
                                'descuento' => 0,
                                'opcion' => 1,
                                'idalmacen' => $idalmacen,
                            ]);
                        }

                        if ($damageCost > 0) {
                            DetailSaleNote::create([
                                'idnotaventa' => $issuedDocument->id,
                                'idproducto' => 1,
                                'cantidad' => max(1, $damaged),
                                'igv' => 0.00,
                                'precio_unitario' => round($damageCost / max(1, $damaged), 2),
                                'precio_total' => $damageCost,
                                'descuento' => 0,
                                'opcion' => 2,
                                'idalmacen' => $idalmacen,
                            ]);
                        }

                        $lockedOrder->update(['idnotaventa' => $issuedDocument->id]);
                        $issuedKind = 'sale_note';
                    } else {
                        // Factura Electrónica (01) o Boleta Electrónica (03)
                        $isTaxed = (bool) ($business?->cobrar_igv ?? false);
                        $gravada = $isTaxed ? round($finalTotal / 1.18, 2) : $finalTotal;
                        $igv = $isTaxed ? round($finalTotal - $gravada, 2) : 0.00;
                        $afectacionId = (int) (IgvTypeAffection::where('codigo', $isTaxed ? '10' : '20')->value('id') ?? IgvTypeAffection::first()?->id ?? 1);
                        $currencyId = (int) (Currency::where('codigo', 'PEN')->value('id') ?? Currency::first()?->id ?? 1);
                        $defaultProductId = (int) ($lockedOrder->items->first()?->idproducto ?? Product::first()?->id ?? 1);

                        $issuedDocument = Billing::create([
                            'idtipo_comprobante' => (int) $docType->id,
                            'serie' => $serieModel->serie,
                            'correlativo' => $formattedCorrelative,
                            'fecha_emision' => $todayDate,
                            'fecha_vencimiento' => $todayDate,
                            'hora' => $nowTime,
                            'idcliente' => (int) $client->id,
                            'idmoneda' => $currencyId,
                            'idpago' => $payMode->id,
                            'modo_pago' => 1,
                            'sunat_forma_pago' => 'Contado',
                            'exonerada' => 0,
                            'inafecta' => 0,
                            'gravada' => $gravada,
                            'anticipo' => 0,
                            'igv' => $igv,
                            'icbper' => 0,
                            'gratuita' => 0,
                            'otros_cargos' => 0,
                            'total' => $finalTotal,
                            'monto_credito' => 0,
                            'cuotas' => null,
                            'payment_breakdown' => [[
                                'id' => $payMode->id,
                                'descripcion' => $payMode->descripcion,
                                'monto' => $finalTotal,
                            ]],
                            'observaciones' => "Entrega Delivery {$lockedOrder->codigo_orden}",
                            'cdr' => null,
                            'anulado' => false,
                            'estado_cpe' => null,
                            'sunat_status' => Billing::SUNAT_STATUS_PENDIENTE,
                            'estado_whatsapp' => Billing::WPP_STATUS_PENDIENTE,
                            'errores' => null,
                            'nticket' => $docType->codigo.'-'.$baseDocName,
                            'idusuario' => $effectiveUserId,
                            'idarqueocaja' => $activeArqueo?->id,
                            'vuelto' => 0.00,
                            'idalmacen' => $idalmacen,
                        ]);

                        foreach ($lockedOrder->items as $item) {
                            $itemTotal = (float) $item->subtotal;
                            $itemValorTotal = $isTaxed ? round($itemTotal / 1.18, 2) : $itemTotal;
                            $itemIgv = $isTaxed ? round($itemTotal - $itemValorTotal, 2) : 0.00;
                            $itemPrecioUnitario = (float) $item->precio_unitario;
                            $itemValorUnitario = $isTaxed ? round($itemPrecioUnitario / 1.18, 2) : $itemPrecioUnitario;

                            DetailBilling::create([
                                'idfacturacion' => $issuedDocument->id,
                                'idproducto' => $item->idproducto ?: $defaultProductId,
                                'cantidad' => $item->cantidad,
                                'descuento' => 0,
                                'igv' => $itemIgv,
                                'icbper' => 0,
                                'factor_icbper' => null,
                                'cantidad_bolsas' => 0,
                                'id_afectacion_igv' => $afectacionId,
                                'precio_unitario' => $itemPrecioUnitario,
                                'valor_unitario' => $itemValorUnitario,
                                'valor_total' => $itemValorTotal,
                                'precio_total' => $itemTotal,
                            ]);
                        }

                        if ($damageCost > 0) {
                            $dmgValor = $isTaxed ? round($damageCost / 1.18, 2) : $damageCost;
                            $dmgIgv = $isTaxed ? round($damageCost - $dmgValor, 2) : 0.00;

                            DetailBilling::create([
                                'idfacturacion' => $issuedDocument->id,
                                'idproducto' => $defaultProductId,
                                'cantidad' => max(1, $damaged),
                                'descuento' => 0,
                                'igv' => $dmgIgv,
                                'icbper' => 0,
                                'factor_icbper' => null,
                                'cantidad_bolsas' => 0,
                                'id_afectacion_igv' => $afectacionId,
                                'precio_unitario' => round($damageCost / max(1, $damaged), 2),
                                'valor_unitario' => round($dmgValor / max(1, $damaged), 2),
                                'valor_total' => $dmgValor,
                                'precio_total' => $damageCost,
                            ]);
                        }

                        $lockedOrder->update(['idfactura' => $issuedDocument->id]);
                        $issuedKind = 'billing';
                    }
                }
            }

            // Invariante B1: Registrar pago en DetailPayment vinculado a idarqueocaja
            if ($estadoPago === 'pagado' && $finalTotal > 0 && $activeArqueo) {
                $docTypeId = $issuedDocument ? (int) $docType->id : 2;
                $docId = $issuedDocument ? $issuedDocument->id : ($lockedOrder->idnotaventa ?: ($lockedOrder->idfactura ?: $lockedOrder->id));

                DetailPayment::create([
                    'idtipo_comprobante' => $docTypeId,
                    'idfactura' => $docId,
                    'idpago' => $payMode->id,
                    'monto' => $finalTotal,
                    'idarqueocaja' => $activeArqueo->id,
                    'estado' => 1,
                ]);
            }

            // Despacho de Jobs en cola tras commit (afterCommit)
            if ($issuedDocument) {
                DB::afterCommit(function () use ($issuedDocument, $issuedKind, $lockedOrder) {
                    try {
                        if ($issuedDocument instanceof Billing) {
                            EmitirComprobanteJob::dispatch($issuedDocument->id);
                        }

                        EnviarWhatsAppJob::dispatch($issuedKind, $issuedDocument->id, $lockedOrder->id);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Error en despacho de jobs tras liquidación de pedido: '.$e->getMessage());
                    }
                });
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
            ?? PayMode::create(['descripcion' => 'Efectivo']);
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
