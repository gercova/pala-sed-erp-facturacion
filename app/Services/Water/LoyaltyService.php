<?php

namespace App\Services\Water;

use App\Models\Client;
use App\Models\ClientLoyalty;
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

        if (!$promo) {
            return [
                'has_promotion' => false,
                'accumulated' => 0,
                'target' => 0,
                'reward_eligible' => false,
                'rewards_claimed' => 0,
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

        $target = $promo->meta_compras;
        $accumulated = $loyalty->compras_acumuladas;
        $rewardEligible = $accumulated >= $target;
        $remaining = max(0, $target - $accumulated);

        $message = $rewardEligible
            ? "¡Felicidades! Tienes acumuladas {$accumulated} compras. ¡Tu próximo bidón es GRATIS!"
            : "Llevas {$accumulated} de {$target} compras acumuladas. Te faltan {$remaining} para tu bidón gratis.";

        return [
            'has_promotion' => true,
            'promotion_id' => $promo->id,
            'promotion_name' => $promo->nombre,
            'accumulated' => $accumulated,
            'target' => $target,
            'bonus' => $promo->bonificacion,
            'reward_eligible' => $rewardEligible,
            'rewards_claimed' => $loyalty->premios_reclamados,
            'remaining_to_free' => $remaining,
            'message' => $message,
        ];
    }

    /**
     * Acumular compras en la cuenta de fidelidad del cliente.
     */
    public function accumulatePurchases(Client $client, int $quantity = 1): array
    {
        $promo = $this->getActivePromotion();

        if (!$promo || $quantity <= 0) {
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
     * Redimir la recompensa de fidelidad (descontar meta y registrar canje).
     */
    public function redeemReward(Client $client): array
    {
        $promo = $this->getActivePromotion();

        if (!$promo) {
            return ['status' => false, 'msg' => 'No hay promoción activa.'];
        }

        return DB::transaction(function () use ($client, $promo) {
            $loyalty = ClientLoyalty::where('idcliente', $client->id)
                ->where('idpromocion', $promo->id)
                ->first();

            if (!$loyalty || $loyalty->compras_acumuladas < $promo->meta_compras) {
                return [
                    'status' => false,
                    'msg' => 'El cliente aún no alcanza la meta para canjear el premio.',
                ];
            }

            // Descontar la meta del contador acumulado (manteniendo remanentes si compró más)
            $newAccumulated = max(0, $loyalty->compras_acumuladas - $promo->meta_compras);

            $loyalty->update([
                'compras_acumuladas' => $newAccumulated,
                'premios_reclamados' => $loyalty->premios_reclamados + $promo->bonificacion,
                'ultimo_canje' => Carbon::now(),
            ]);

            return [
                'status' => true,
                'msg' => '¡Premio de fidelidad aplicado exitosamente!',
                'new_accumulated' => $newAccumulated,
                'total_claimed' => $loyalty->premios_reclamados,
            ];
        });
    }
}
