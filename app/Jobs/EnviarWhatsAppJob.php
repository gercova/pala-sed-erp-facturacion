<?php

namespace App\Jobs;

use App\Models\Billing;
use App\Models\DeliveryOrder;
use App\Models\SaleNote;
use App\Services\Water\WhatsAppSenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EnviarWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de intentos.
     */
    public int $tries = 3;

    /**
     * Tiempos de espera (en segundos) entre reintentos.
     */
    public array $backoff = [15, 60, 180];

    /**
     * Constructor con promoción de propiedades.
     */
    public function __construct(
        public string $documentKind,
        public int $documentId,
        public ?int $orderId = null
    ) {}

    /**
     * Ejecuta el envío de WhatsApp.
     */
    public function handle(WhatsAppSenderService $sender): void
    {
        $document = $this->documentKind === 'billing'
            ? Billing::query()->find($this->documentId)
            : SaleNote::query()->find($this->documentId);

        if (! $document) {
            Log::warning("EnviarWhatsAppJob: No se encontró el documento {$this->documentKind} ID {$this->documentId}.");

            return;
        }

        // Idempotencia: Si ya fue enviado o no tiene teléfono, no duplicar
        if ($document->estado_whatsapp === Billing::WPP_STATUS_ENVIADO || $document->estado_whatsapp === Billing::WPP_STATUS_SIN_TELEFONO) {
            return;
        }

        if ($this->orderId) {
            $order = DeliveryOrder::query()->find($this->orderId);
            if ($order) {
                $result = $sender->sendDeliveryReceipt($order, $document);
                $this->evaluateResult($result);

                return;
            }
        }

        if ($document instanceof Billing) {
            $result = $sender->resendBillingReceipt($document);
            $this->evaluateResult($result);
        } elseif ($document instanceof SaleNote) {
            $result = $sender->sendSaleNoteReceipt($document);
            $this->evaluateResult($result);
        }
    }

    protected function evaluateResult(array $result): void
    {
        if (! ($result['ok'] ?? false) && ($result['status'] ?? '') === Billing::WPP_STATUS_FALLIDO) {
            throw new RuntimeException('Fallo al enviar mensaje de WhatsApp: '.($result['message'] ?? 'Error desconocido'));
        }
    }
}
