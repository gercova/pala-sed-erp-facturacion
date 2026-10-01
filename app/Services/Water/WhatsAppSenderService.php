<?php

namespace App\Services\Water;

use App\Models\Billing;
use App\Models\Business;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\SaleNote;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppSenderService
{
    /**
     * Envía la notificación de comprobante por WhatsApp a partir de una orden de delivery o un comprobante.
     */
    public function sendDeliveryReceipt(DeliveryOrder $order, Billing|SaleNote $document, ?Business $business = null): array
    {
        $business = $business ?: $this->resolveBusiness();
        if ($order->exists) {
            $order->loadMissing(['cliente', 'items']);
        }
        $client = $order->cliente;

        $rawPhone = $order->telefono_contacto ?: ($client?->telefono);
        $cleanPhone = $this->normalizePhone($rawPhone);

        if (! $cleanPhone) {
            $this->markAsNoPhone($document, 'El cliente o pedido no cuenta con un número de teléfono válido.');

            return [
                'ok' => false,
                'status' => Billing::WPP_STATUS_SIN_TELEFONO,
                'message' => 'El cliente no tiene teléfono registrado o el número es inválido.',
            ];
        }

        $message = $this->buildReceiptMessage($business, $order, $client, $document);
        $ticketUrl = $this->resolveTicketUrl($document);

        return $this->dispatchToGateway($business, $cleanPhone, $message, $ticketUrl, $document);
    }

    /**
     * Reenvía un comprobante ya existente (desde panel /billings).
     */
    public function resendBillingReceipt(Billing $billing, ?Business $business = null): array
    {
        $business = $business ?: $this->resolveBusiness();
        if ($billing->exists) {
            $billing->loadMissing(['customer', 'details']);
        }
        $client = $billing->customer;

        $cleanPhone = $this->normalizePhone($client?->telefono);

        if (! $cleanPhone) {
            $this->markAsNoPhone($billing, 'El cliente no cuenta con número de teléfono registrado.');

            return [
                'ok' => false,
                'status' => Billing::WPP_STATUS_SIN_TELEFONO,
                'message' => 'El cliente no tiene teléfono registrado.',
            ];
        }

        $message = $this->buildBillingSummaryMessage($business, $billing, $client);
        $ticketUrl = $this->resolveTicketUrl($billing);

        return $this->dispatchToGateway($business, $cleanPhone, $message, $ticketUrl, $billing);
    }

    /**
     * Alias de conveniencia para envío de comprobante individual.
     */
    public function sendBilling(Billing $billing, ?Business $business = null): array
    {
        return $this->resendBillingReceipt($billing, $business);
    }

    /**
     * Envía notificación de Nota de Venta individual.
     */
    public function sendSaleNoteReceipt(SaleNote $saleNote, ?Business $business = null): array
    {
        $business = $business ?: $this->resolveBusiness();
        if ($saleNote->exists) {
            $saleNote->loadMissing(['customer', 'details']);
        }
        $client = $saleNote->customer;

        $cleanPhone = $this->normalizePhone($client?->telefono);

        if (! $cleanPhone) {
            $this->markAsNoPhone($saleNote, 'El cliente no cuenta con número de teléfono registrado.');

            return [
                'ok' => false,
                'status' => Billing::WPP_STATUS_SIN_TELEFONO,
                'message' => 'El cliente no tiene teléfono registrado.',
            ];
        }

        $businessName = $business?->razon_social ?: ($business?->nombre_comercial ?: 'Pala-Sed');
        $docNumber = $saleNote->serie.'-'.$saleNote->correlativo;
        $ticketUrl = $this->resolveTicketUrl($saleNote);

        $msg = "💧 *NOTA DE VENTA - {$businessName}*\n"
            ."━━━━━━━━━━━━━━━━━━━━━\n"
            ."📄 *Documento:* NOTA DE VENTA {$docNumber}\n"
            .'👤 *Cliente:* '.($client?->nombres ?? 'Cliente')."\n"
            .'📅 *Fecha:* '.Carbon::parse((string) $saleNote->fecha_emision)->format('d/m/Y')."\n"
            .'💰 *Total:* S/ '.number_format((float) $saleNote->total, 2)."\n";

        if ($ticketUrl) {
            $msg .= "🔗 *Descargar Comprobante:* {$ticketUrl}\n";
        }

        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n"
            .'¡Gracias por su compra!';

        return $this->dispatchToGateway($business, $cleanPhone, $msg, $ticketUrl, $saleNote);
    }

    /**
     * Normaliza el teléfono celular al estándar con código de país (51 para Perú).
     */
    public function normalizePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $phone);

        if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
            return '51'.$digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '519')) {
            return $digits;
        }

        if (strlen($digits) >= 8) {
            return $digits;
        }

        return null;
    }

    /**
     * Construye el texto completo del comprobante de entrega.
     */
    protected function buildReceiptMessage(?Business $business, DeliveryOrder $order, ?Client $client, Billing|SaleNote $document): string
    {
        $businessName = $business?->razon_social ?: ($business?->nombre_comercial ?: 'Pala-Sed');
        $deliveryDate = $order->fecha_entrega ? Carbon::parse($order->fecha_entrega)->format('d/m/Y H:i') : date('d/m/Y H:i');

        $docTitle = $document instanceof Billing
            ? (($document->typeDocument?->codigo ?? '') === '01' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA')
            : 'NOTA DE VENTA';

        $docNumber = $document->serie.'-'.$document->correlativo;
        $ticketUrl = $this->resolveTicketUrl($document);

        $sunatStatusText = '';
        if ($document instanceof Billing) {
            if ((int) $document->cdr === 1 && (int) $document->estado_cpe === 0) {
                $sunatStatusText = "\n✅ *Estado SUNAT:* Aceptado";
            } elseif ($document->sunat_status === Billing::SUNAT_STATUS_ERROR_COMUNICACION || is_null($document->cdr)) {
                $sunatStatusText = "\n⏳ *Estado SUNAT:* En proceso de envío";
            } elseif ((int) $document->cdr === 1 && (int) $document->estado_cpe > 0) {
                $sunatStatusText = "\n⚠️ *Estado SUNAT:* Observado / Rechazado";
            }
        }

        $msg = "💧 *COMPROBANTE DE ENTREGA - {$businessName}*\n"
            ."━━━━━━━━━━━━━━━━━━━━━\n"
            ."📄 *Comprobante:* {$docTitle} {$docNumber}\n"
            ."📦 *Pedido:* {$order->codigo_orden}\n"
            .'👤 *Cliente:* '.($client?->nombres ?? 'Cliente')."\n"
            ."📅 *Fecha de Entrega:* {$deliveryDate}\n"
            ."📍 *Dirección:* {$order->direccion_entrega}\n"
            ."━━━━━━━━━━━━━━━━━━━━━\n"
            ."📦 *Bidones Entregados:* {$order->bidones_a_entregar}\n"
            ."🔄 *Envases Recibidos (Intactos):* {$order->bidones_vacios_recibidos}\n";

        if ($order->bidones_danados_recibidos > 0) {
            $msg .= "⚠️ *Envases Dañados / Cobro:* {$order->bidones_danados_recibidos} (S/ ".number_format((float) $order->cobro_envases_danados, 2).")\n";
        }

        $msg .= '💳 *Método de Pago:* '.ucfirst($order->metodo_pago ?? 'Efectivo')."\n"
            .'💰 *TOTAL PAGADO:* S/ '.number_format((float) $order->total, 2)."\n";

        if ($client && isset($client->saldo_envases)) {
            $msg .= "🪣 *Saldo Actual de Envases:* {$client->saldo_envases} en tu poder\n";
        }

        $msg .= $sunatStatusText;

        if ($ticketUrl) {
            $msg .= "\n━━━━━━━━━━━━━━━━━━━━━\n"
                ."🔗 *Ver comprobante digital:* {$ticketUrl}\n";
        }

        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n"
            .'¡Muchas gracias por su preferencia!';

        return $msg;
    }

    /**
     * Construye un mensaje resumido para reenvío de Billing.
     */
    protected function buildBillingSummaryMessage(?Business $business, Billing $billing, ?Client $client): string
    {
        $businessName = $business?->razon_social ?: ($business?->nombre_comercial ?: 'Pala-Sed');
        $docTitle = ($billing->typeDocument?->codigo ?? '') === '01' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA';
        $docNumber = $billing->serie.'-'.$billing->correlativo;
        $ticketUrl = $this->resolveTicketUrl($billing);

        $sunatStatusText = ((int) $billing->cdr === 1 && (int) $billing->estado_cpe === 0)
            ? 'Aceptado por SUNAT'
            : 'En proceso';

        $msg = "💧 *COMPROBANTE ELECTRÓNICO - {$businessName}*\n"
            ."━━━━━━━━━━━━━━━━━━━━━\n"
            ."📄 *Documento:* {$docTitle} {$docNumber}\n"
            .'👤 *Cliente:* '.($client?->nombres ?? 'Cliente')."\n"
            .'📅 *Fecha:* '.Carbon::parse((string) $billing->fecha_emision)->format('d/m/Y')."\n"
            .'💰 *Total:* S/ '.number_format((float) $billing->total, 2)."\n"
            ."🏛️ *Estado SUNAT:* {$sunatStatusText}\n";

        if ($ticketUrl) {
            $msg .= "🔗 *Descargar Comprobante:* {$ticketUrl}\n";
        }

        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n"
            .'¡Gracias por su compra!';

        return $msg;
    }

    /**
     * Envía la petición HTTP a la pasarela configurada con timeout seguro.
     */
    protected function dispatchToGateway(?Business $business, string $phone, string $message, ?string $ticketUrl, Billing|SaleNote $document): array
    {
        $urlApi = trim((string) ($business?->url_api ?? ''));
        $instance = trim((string) ($business?->instancia_wpp ?? ''));

        // Si no está configurada la URL o la instancia, se registra como pendiente/simulado
        if (empty($urlApi) || empty($instance)) {
            $this->saveDocumentStatus($document, [
                'estado_whatsapp' => Billing::WPP_STATUS_PENDIENTE,
                'whatsapp_error' => 'No se configuró instancia_wpp o url_api en la empresa.',
                'whatsapp_intentos' => (int) $document->whatsapp_intentos + 1,
            ]);

            return [
                'ok' => false,
                'status' => Billing::WPP_STATUS_PENDIENTE,
                'message' => 'Instancia o URL de WhatsApp no configurada en la empresa.',
            ];
        }

        $endpoint = rtrim($urlApi, '/');
        if (! str_contains($endpoint, '/api/')) {
            $endpoint .= '/api/whatsapp/send';
        }

        try {
            $response = Http::timeout(10)->post($endpoint, [
                'instancia' => $instance,
                'numero' => $phone,
                'mensaje' => $message,
                'ticket_url' => $ticketUrl,
            ]);

            if ($response->successful()) {
                $this->saveDocumentStatus($document, [
                    'estado_whatsapp' => Billing::WPP_STATUS_ENVIADO,
                    'whatsapp_error' => null,
                    'whatsapp_enviado_at' => now(),
                    'whatsapp_intentos' => (int) $document->whatsapp_intentos + 1,
                ]);

                return [
                    'ok' => true,
                    'status' => Billing::WPP_STATUS_ENVIADO,
                    'message' => 'Mensaje de WhatsApp enviado correctamente.',
                ];
            }

            $errorMessage = 'Error en pasarela WhatsApp: '.$response->status().' - '.$response->body();
            $this->saveDocumentStatus($document, [
                'estado_whatsapp' => Billing::WPP_STATUS_FALLIDO,
                'whatsapp_error' => mb_substr($errorMessage, 0, 500),
                'whatsapp_intentos' => (int) $document->whatsapp_intentos + 1,
            ]);

            return [
                'ok' => false,
                'status' => Billing::WPP_STATUS_FALLIDO,
                'message' => $errorMessage,
            ];
        } catch (Throwable $e) {
            Log::warning("Fallo al conectar con pasarela WhatsApp: {$e->getMessage()}");

            $this->saveDocumentStatus($document, [
                'estado_whatsapp' => Billing::WPP_STATUS_FALLIDO,
                'whatsapp_error' => mb_substr($e->getMessage(), 0, 500),
                'whatsapp_intentos' => (int) $document->whatsapp_intentos + 1,
            ]);

            return [
                'ok' => false,
                'status' => Billing::WPP_STATUS_FALLIDO,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function resolveTicketUrl(Billing|SaleNote $document): ?string
    {
        try {
            if ($document instanceof Billing) {
                return url("/billings/{$document->id}/ticket");
            }

            return url("/salenotes/{$document->id}/ticket");
        } catch (Throwable) {
            return null;
        }
    }

    protected function markAsNoPhone(Billing|SaleNote $document, string $reason): void
    {
        $this->saveDocumentStatus($document, [
            'estado_whatsapp' => Billing::WPP_STATUS_SIN_TELEFONO,
            'whatsapp_error' => $reason,
        ]);
    }

    protected function saveDocumentStatus(Billing|SaleNote $document, array $attributes): void
    {
        $document->forceFill($attributes);

        if ($document->exists) {
            try {
                $document->save();
            } catch (Throwable $e) {
                Log::warning("No se pudo persistir estado de WhatsApp: {$e->getMessage()}");
            }
        }
    }

    protected function resolveBusiness(): ?Business
    {
        try {
            return Business::query()->first();
        } catch (Throwable) {
            return null;
        }
    }
}
