<?php

namespace App\Services\Water;

use App\Models\Client;
use App\Models\JugMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JugMovementService
{
    /**
     * Registrar un movimiento de envases para un cliente y actualizar su saldo.
     *
     * @param  int  $deliveredFull  Bidones llenos entregados al cliente
     * @param  int  $returnedIntact  Bidones vacíos recibidos en buen estado
     * @param  int  $returnedDamaged  Bidones vacíos devueltos rotos/dañados
     * @param  float  $damageCost  Cobro aplicado por reposición de envases dañados
     * @param  string  $movementType  Tipo de movimiento
     * @param  int|null  $orderId  ID de la orden de delivery (opcional)
     * @param  int|null  $warehouseId  ID del almacén (opcional)
     * @param  int|null  $userId  ID del usuario que registra
     * @param  string|null  $notes  Observaciones
     */
    public function recordMovement(
        Client $client,
        int $deliveredFull = 0,
        int $returnedIntact = 0,
        int $returnedDamaged = 0,
        float $damageCost = 0.0,
        string $movementType = 'entrega_recarga',
        ?int $orderId = null,
        ?int $warehouseId = null,
        ?int $userId = null,
        ?string $notes = null
    ): JugMovement {
        return DB::transaction(function () use (
            $client,
            $deliveredFull,
            $returnedIntact,
            $returnedDamaged,
            $damageCost,
            $movementType,
            $orderId,
            $warehouseId,
            $userId,
            $notes
        ) {
            $prevBalance = (int) ($client->saldo_envases ?? 0);

            // Fórmula de saldo en poder del cliente:
            // Saldo anterior + bidones llenos entregados - (devueltos intactos + devueltos dañados retirados)
            $newBalance = $prevBalance + $deliveredFull - ($returnedIntact + $returnedDamaged);

            $movement = JugMovement::create([
                'idcliente' => $client->id,
                'iddelivery_order' => $orderId,
                'idusuario' => $userId ?: auth()->id(),
                'idalmacen' => $warehouseId,
                'tipo_movimiento' => $movementType,
                'entregados_llenos' => $deliveredFull,
                'devueltos_intactos' => $returnedIntact,
                'devueltos_danados' => $returnedDamaged,
                'costo_dano' => $damageCost,
                'saldo_anterior' => $prevBalance,
                'saldo_nuevo' => $newBalance,
                'observaciones' => $notes,
                'fecha' => Carbon::now(),
            ]);

            $client->update([
                'saldo_envases' => $newBalance,
            ]);

            return $movement;
        });
    }

    /**
     * Ajuste manual directo del saldo de envases de un cliente.
     */
    public function adjustBalance(Client $client, int $newBalance, string $reason, ?int $userId = null): JugMovement
    {
        return DB::transaction(function () use ($client, $newBalance, $reason, $userId) {
            $prevBalance = (int) ($client->saldo_envases ?? 0);
            $diff = $newBalance - $prevBalance;

            $delivered = $diff > 0 ? $diff : 0;
            $returned = $diff < 0 ? abs($diff) : 0;

            $movement = JugMovement::create([
                'idcliente' => $client->id,
                'idusuario' => $userId ?: auth()->id(),
                'tipo_movimiento' => 'ajuste',
                'entregados_llenos' => $delivered,
                'devueltos_intactos' => $returned,
                'devueltos_danados' => 0,
                'costo_dano' => 0,
                'saldo_anterior' => $prevBalance,
                'saldo_nuevo' => $newBalance,
                'observaciones' => 'Ajuste manual de saldo: '.$reason,
                'fecha' => Carbon::now(),
            ]);

            $client->update([
                'saldo_envases' => $newBalance,
            ]);

            return $movement;
        });
    }

    /**
     * Resumen completo de envases por cliente: en posesión, dañados, en préstamo/comodato y alertas.
     */
    public function getClientJugSummary(Client $client): array
    {
        $inPossession = (int) ($client->saldo_envases ?? 0);

        $movements = JugMovement::where('idcliente', $client->id)->get();
        $damaged = (int) $movements->sum('devueltos_danados');
        $onLoan = (int) $movements->where('tipo_movimiento', 'nuevo_comodato')->sum('entregados_llenos');
        $totalDelivered = (int) $movements->sum('entregados_llenos');
        $totalReturnedIntact = (int) $movements->sum('devueltos_intactos');

        $totalContainers = $inPossession + $damaged + $onLoan;

        $isAbnormal = false;
        $alertType = 'normal'; // normal | danger | warning
        $alertMessage = null;

        if ($inPossession < 0) {
            $isAbnormal = true;
            $alertType = 'danger';
            $alertMessage = "¡Alerta! Saldo negativo de envases ({$inPossession}). Revisar devoluciones no registradas.";
        } elseif ($inPossession >= 10) {
            $isAbnormal = true;
            $alertType = 'warning';
            $alertMessage = "Advertencia: Cliente tiene saldo elevado ({$inPossession} envases en posesión).";
        }

        return [
            'en_posesion' => $inPossession,
            'danados' => $damaged,
            'en_prestamo' => $onLoan,
            'total_envases' => $totalContainers,
            'total_historico_entregados' => $totalDelivered,
            'total_historico_devueltos' => $totalReturnedIntact,
            'es_anormal' => $isAbnormal,
            'tipo_alerta' => $alertType,
            'mensaje_alerta' => $alertMessage,
        ];
    }
}
