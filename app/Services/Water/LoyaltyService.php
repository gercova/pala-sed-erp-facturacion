<?php

namespace App\Services\Water;

use App\Models\Client;
use App\Models\ClientLoyalty;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\LoyaltyPromotion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Obtener la promoción activa principal de fidelidad.
     */
    public function getActivePromotion(): ?LoyaltyPromotion
    {
        return LoyaltyPromotion::where('activo', true)->orderBy('id')->first();
    }

    /**
     * Obtener el estado de fidelidad actual de un cliente.
     */
    public function getClientStatus(Client $client): array
    {
        $promo = $this->getActivePromotion();

        if (! $promo) {
            return [
                'has_promotion' => false,
                'accumulated' => 0,
                'target' => 0,
                'bonus' => 0,
                'rule_label' => 'Sin promoción',
                'rule_text' => 'Sin promoción activa',
                'summary_text' => 'No hay promociones de fidelidad activas actualmente.',
                'reward_eligible' => false,
                'rewards_available' => 0,
                'surplus' => 0,
                'rewards_claimed' => 0,
                'remaining_to_free' => 0,
                'message' => 'No hay promociones activas actualmente.',
            ];
        }

        $loyalty = ClientLoyalty::firstOrCreate(
            [
                'idcliente' => $client->id,
                'idpromocion' => $promo->id,
            ],
            [
                'compras_acumuladas' => 0,
                'premios_reclamados' => 0,
            ]
        );

        $target = (int) $promo->meta_compras;
        $bonus = (int) $promo->bonificacion;
        $accumulated = (int) $loyalty->compras_acumuladas;

        $rewardEligible = $target > 0 && $accumulated >= $target;
        $rewardsAvailable = $target > 0 ? intdiv($accumulated, $target) : 0;
        $surplus = $target > 0 ? ($accumulated % $target) : 0;
        $remaining = $target > 0 ? max(0, $target - $accumulated) : 0;

        $bonusText = $bonus > 1 ? "{$bonus} bidones GRATIS" : '1 bidón GRATIS';

        $message = $rewardEligible
            ? "¡Felicidades! Tienes acumuladas {$accumulated} compras. ¡Tienes {$bonusText} disponible para canjear!"
            : "Llevas {$accumulated} de {$target} compras acumuladas. Te faltan {$remaining} para {$bonusText}.";

        return [
            'has_promotion' => true,
            'promotion_id' => $promo->id,
            'promotion_name' => $promo->nombre,
            'rule_label' => $promo->rule_label,
            'rule_text' => $promo->rule_text,
            'summary_text' => $promo->summary_text,
            'accumulated' => $accumulated,
            'target' => $target,
            'bonus' => $bonus,
            'reward_eligible' => $rewardEligible,
            'rewards_available' => $rewardsAvailable,
            'surplus' => $surplus,
            'rewards_claimed' => (int) $loyalty->premios_reclamados,
            'remaining_to_free' => $remaining,
            'message' => $message,
        ];
    }

    /**
     * Acumular compras en la cuenta de fidelidad del cliente de forma transaccional.
     *
     * @param  Client  $client  Cliente al que se acreditan las compras
     * @param  int  $quantity  Cantidad de compras válidas a acumular
     * @param  string|null  $reference  Referencia o auditoría del origen (POS, Orden, etc.)
     */
    public function accumulatePurchases(Client $client, int $quantity = 1, ?string $reference = null): array
    {
        $promo = $this->getActivePromotion();

        if (! $promo || $quantity <= 0) {
            return $this->getClientStatus($client);
        }

        return DB::transaction(function () use ($client, $promo, $quantity) {
            $loyalty = ClientLoyalty::firstOrCreate(
                [
                    'idcliente' => $client->id,
                    'idpromocion' => $promo->id,
                ],
                [
                    'compras_acumuladas' => 0,
                    'premios_reclamados' => 0,
                ]
            );

            $loyalty->increment('compras_acumuladas', $quantity);

            return $this->getClientStatus($client);
        });
    }

    /**
     * Redimir la recompensa de fidelidad para el cliente.
     *
     * Regla de negocio:
     * - Descuenta exactamente ($rewardCount * meta_compras) del saldo acumulado.
     * - Preserva los excedentes (surpluses/remanentes) para el siguiente ciclo.
     *   Ejemplo: Si meta = 5 y cliente tiene 7 acumuladas, tras el canje le quedan 2 acumuladas.
     *
     * @param  Client  $client  Cliente que realiza el canje
     * @param  int  $rewardCount  Cantidad de premios a canjear (por defecto 1)
     */
    public function redeemReward(Client $client, int $rewardCount = 1): array
    {
        $promo = $this->getActivePromotion();

        if (! $promo) {
            return ['status' => false, 'msg' => 'No hay promoción activa.'];
        }

        if ($rewardCount < 1) {
            $rewardCount = 1;
        }

        return DB::transaction(function () use ($client, $promo, $rewardCount) {
            $loyalty = ClientLoyalty::where('idcliente', $client->id)
                ->where('idpromocion', $promo->id)
                ->lockForUpdate()
                ->first();

            $costInPurchases = (int) $promo->meta_compras * $rewardCount;

            if (! $loyalty || $loyalty->compras_acumuladas < $costInPurchases) {
                return [
                    'status' => false,
                    'msg' => 'El cliente no tiene suficientes compras acumuladas para canjear '.$rewardCount.' premio(s).',
                ];
            }

            // Preservar los excedentes (surplus) al descontar
            $newAccumulated = max(0, $loyalty->compras_acumuladas - $costInPurchases);
            $claimedBonus = (int) $promo->bonificacion * $rewardCount;

            $loyalty->update([
                'compras_acumuladas' => $newAccumulated,
                'premios_reclamados' => $loyalty->premios_reclamados + $claimedBonus,
                'ultimo_canje' => Carbon::now(),
            ]);

            return [
                'status' => true,
                'msg' => '¡Premio de fidelidad aplicado exitosamente!',
                'new_accumulated' => $newAccumulated,
                'total_claimed' => $loyalty->premios_reclamados,
                'surplus_preserved' => $newAccumulated % max(1, (int) $promo->meta_compras),
            ];
        });
    }

    /**
     * Acumular puntos desde una orden de delivery con prevención de doble acumulación.
     *
     * Invariante de anti-duplicación:
     * - Si la orden ya tiene `puntos_fidelidad_acumulados = true`, se retorna el estado
     *   sin volver a incrementar las compras del cliente.
     * - Las recargas bonificadas/gratuitas (precio 0 o bonificación) NO acumulan compras.
     */
    public function accumulateFromDeliveryOrder(DeliveryOrder $order): array
    {
        $client = $order->cliente ?? Client::find($order->idcliente);
        if (! $client) {
            return ['status' => false, 'msg' => 'Orden sin cliente asignado.'];
        }

        // Si ya fue acumulada previamente por liquidación o POS, no duplicar
        if ($order->puntos_fidelidad_acumulados) {
            return $this->getClientStatus($client);
        }

        $promo = $this->getActivePromotion();
        if (! $promo) {
            return $this->getClientStatus($client);
        }

        return DB::transaction(function () use ($order, $client, $promo) {
            $lockedOrder = DeliveryOrder::where('id', $order->id)->lockForUpdate()->first();
            if ($lockedOrder->puntos_fidelidad_acumulados) {
                return $this->getClientStatus($client);
            }

            $eligibleCount = 0;
            $items = $lockedOrder->items ?? DeliveryOrderItem::where('iddelivery_order', $lockedOrder->id)->get();

            foreach ($items as $item) {
                if ($this->isEligibleRefillItem($item, $promo)) {
                    $eligibleCount += (int) $item->cantidad;
                }
            }

            if ($eligibleCount > 0) {
                $this->accumulatePurchases($client, $eligibleCount, "Orden Delivery {$lockedOrder->codigo_orden}");
            }

            $lockedOrder->update([
                'puntos_fidelidad_acumulados' => true,
                'fecha_acumulacion_fidelidad' => Carbon::now(),
            ]);

            return $this->getClientStatus($client);
        });
    }

    /**
     * Acumular compras desde una venta en POS (directa o proveniente de delivery).
     *
     * @param  Client  $client  Cliente que compra
     * @param  array  $cartProducts  Productos en el carrito POS
     * @param  int|null  $deliveryOrderId  ID de la orden si la venta proviene de un pedido delivery
     */
    public function accumulateFromPosSale(Client $client, array $cartProducts, ?int $deliveryOrderId = null): array
    {
        // Si proviene de un pedido delivery, delegar al procesador de delivery (idempotente)
        if ($deliveryOrderId) {
            $deliveryOrder = DeliveryOrder::find($deliveryOrderId);
            if ($deliveryOrder) {
                return $this->accumulateFromDeliveryOrder($deliveryOrder);
            }
        }

        $promo = $this->getActivePromotion();
        if (! $promo) {
            return $this->getClientStatus($client);
        }

        $eligibleCount = 0;
        foreach ($cartProducts as $product) {
            if ($this->isEligiblePosCartItem($product, $promo)) {
                $eligibleCount += (int) ($product['cantidad'] ?? 1);
            }
        }

        if ($eligibleCount > 0) {
            return $this->accumulatePurchases($client, $eligibleCount, 'Venta Mostrador POS');
        }

        return $this->getClientStatus($client);
    }

    /**
     * Determinar si un ítem de orden de entrega es una recarga pagada elegible.
     * Un bidón gratuito (precio 0 o bonificación) NO califica para acumular puntos.
     */
    public function isEligibleRefillItem($item, ?LoyaltyPromotion $promo): bool
    {
        $unitPrice = (float) ($item->precio_unitario ?? 0);
        $totalPrice = (float) ($item->subtotal ?? 0);
        $discount = (float) ($item->descuento ?? 0);
        $desc = strtolower((string) ($item->descripcion ?? ''));

        // Regla: Si es gratis (bonificado por fidelidad o costo cero), NO cuenta hacia la próxima meta
        if ($unitPrice <= 0 || ($totalPrice - $discount) <= 0 || str_contains($desc, 'gratis') || str_contains($desc, 'bonific')) {
            return false;
        }

        // Si la promoción tiene producto objetivo específico, debe coincidir
        if ($promo && $promo->idproducto_objetivo && $item->idproducto) {
            return (int) $item->idproducto === (int) $promo->idproducto_objetivo;
        }

        // Si no hay producto objetivo específico, aplica a cualquier recarga de agua
        $tipoItem = (string) ($item->tipo_item ?? '');

        return $tipoItem === 'recarga' || str_contains($desc, 'recarga') || str_contains($desc, 'agua');
    }

    /**
     * Determinar si un ítem de venta POS es una recarga pagada elegible.
     * Un bidón bonificado con precio cero NO califica.
     */
    public function isEligiblePosCartItem(array $product, ?LoyaltyPromotion $promo): bool
    {
        $price = (float) ($product['precio_venta'] ?? 0);
        $desc = strtolower((string) ($product['descripcion'] ?? ''));

        if ($price <= 0 || ! empty($product['is_free_reward']) || str_contains($desc, 'gratis') || str_contains($desc, 'bonific')) {
            return false;
        }

        if ($promo && $promo->idproducto_objetivo && ! empty($product['id'])) {
            return (int) $product['id'] === (int) $promo->idproducto_objetivo;
        }

        return str_contains($desc, 'recarga') || str_contains($desc, 'agua');
    }
}
