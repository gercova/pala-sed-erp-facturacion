<?php

namespace App\Jobs;

use App\Models\Billing;
use App\Services\Ebilling\SunatDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class EmitirComprobanteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de intentos antes de marcar error definitivo.
     */
    public int $tries = 5;

    /**
     * Tiempos de espera (en segundos) entre reintentos: 15s, 1m, 3m, 10m.
     */
    public array $backoff = [15, 60, 180, 600];

    /**
     * Constructor con promoción de propiedades PHP 8.
     */
    public function __construct(public int $billingId) {}

    /**
     * Ejecuta el envío SOAP a SUNAT de manera idempotente.
     */
    public function handle(SunatDispatchService $service): void
    {
        $billing = Billing::query()->find($this->billingId);

        if (! $billing) {
            Log::warning("EmitirComprobanteJob: No se encontró el comprobante ID {$this->billingId}.");

            return;
        }

        // Idempotencia: Si ya fue aceptado con CDR 0, no volver a enviar
        if ((int) $billing->cdr === 1 && (int) $billing->estado_cpe === 0) {
            Log::info("EmitirComprobanteJob: Comprobante {$billing->serie}-{$billing->correlativo} ya aceptado por SUNAT.");

            return;
        }

        $billing->increment('sunat_intentos');
        $billing->update([
            'sunat_ultimo_intento_at' => now(),
            'sunat_status' => Billing::SUNAT_STATUS_ENVIADO,
        ]);

        try {
            $result = $service->dispatch($billing);
        } catch (Throwable $exception) {
            $errorMessage = $exception->getMessage();
            Log::error("EmitirComprobanteJob: Excepción enviando comprobante {$billing->serie}-{$billing->correlativo}: {$errorMessage}");

            $billing->update([
                'sunat_status' => Billing::SUNAT_STATUS_ERROR_COMUNICACION,
                'errores' => $errorMessage,
            ]);

            throw new RuntimeException("Error comunicando con SUNAT: {$errorMessage}", 0, $exception);
        }

        $billing->refresh();

        if ($result['ok'] ?? false) {
            $billing->update([
                'sunat_status' => Billing::SUNAT_STATUS_ACEPTADO,
            ]);

            Log::info("EmitirComprobanteJob: Comprobante {$billing->serie}-{$billing->correlativo} ACEPTADO por SUNAT.");

            return;
        }

        // Si SUNAT devolvió CDR con código de rechazo (> 0), no se reintenta automáticamente
        if ((int) $billing->cdr === 1 && (int) $billing->estado_cpe > 0) {
            $billing->update([
                'sunat_status' => Billing::SUNAT_STATUS_RECHAZADO,
            ]);

            Log::warning("EmitirComprobanteJob: Comprobante {$billing->serie}-{$billing->correlativo} RECHAZADO por SUNAT: {$billing->errores}");

            return;
        }

        // Si fue falla de comunicación (HTTP code != 200, timeout, SOAP fault temporal sin CDR)
        $billing->update([
            'sunat_status' => Billing::SUNAT_STATUS_ERROR_COMUNICACION,
        ]);

        throw new RuntimeException('SUNAT no procesó el comprobante o hubo error de comunicación: '.($result['message'] ?? 'Sin detalle'));
    }
}
