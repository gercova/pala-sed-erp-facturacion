<?php

namespace Tests\Feature;

use App\Jobs\EmitirComprobanteJob;
use App\Jobs\EnviarWhatsAppJob;
use App\Models\Billing;
use App\Models\Business;
use App\Models\Client;
use App\Models\IdentityDocumentType;
use App\Models\SaleNote;
use App\Services\Water\DeliveryDocumentResolverService;
use App\Services\Water\WhatsAppSenderService;
use Tests\TestCase;

class AutomaticBillingAndWhatsAppNotificationTest extends TestCase
{
    private DeliveryDocumentResolverService $resolver;

    private WhatsAppSenderService $whatsAppService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new DeliveryDocumentResolverService;
        $this->whatsAppService = new WhatsAppSenderService;
    }

    public function test_emitir_comprobante_job_configuration_and_backoff(): void
    {
        $job = new EmitirComprobanteJob(billingId: 101);

        $this->assertSame(101, $job->billingId);
        $this->assertSame(5, $job->tries);
        $this->assertSame([15, 60, 180, 600], $job->backoff);
    }

    public function test_enviar_whatsapp_job_configuration_and_backoff(): void
    {
        $job = new EnviarWhatsAppJob(documentKind: 'billing', documentId: 202, orderId: 55);

        $this->assertSame('billing', $job->documentKind);
        $this->assertSame(202, $job->documentId);
        $this->assertSame(55, $job->orderId);
        $this->assertSame(3, $job->tries);
        $this->assertSame([15, 60, 180], $job->backoff);
    }

    public function test_billing_and_sale_note_model_constants_are_defined(): void
    {
        $this->assertSame('pendiente', Billing::SUNAT_STATUS_PENDIENTE);
        $this->assertSame('enviado', Billing::SUNAT_STATUS_ENVIADO);
        $this->assertSame('aceptado', Billing::SUNAT_STATUS_ACEPTADO);
        $this->assertSame('rechazado', Billing::SUNAT_STATUS_RECHAZADO);
        $this->assertSame('error_comunicacion', Billing::SUNAT_STATUS_ERROR_COMUNICACION);

        $this->assertSame('pendiente', Billing::WPP_STATUS_PENDIENTE);
        $this->assertSame('enviado', Billing::WPP_STATUS_ENVIADO);
        $this->assertSame('fallido', Billing::WPP_STATUS_FALLIDO);
        $this->assertSame('sin_telefono', Billing::WPP_STATUS_SIN_TELEFONO);

        $this->assertSame('pendiente', SaleNote::WPP_STATUS_PENDIENTE);
        $this->assertSame('enviado', SaleNote::WPP_STATUS_ENVIADO);
        $this->assertSame('fallido', SaleNote::WPP_STATUS_FALLIDO);
        $this->assertSame('sin_telefono', SaleNote::WPP_STATUS_SIN_TELEFONO);
    }

    public function test_resolver_with_identity_document_type_relation(): void
    {
        $rucType = new IdentityDocumentType(['codigo' => '6', 'descripcion' => 'RUC']);
        $client = new Client([
            'nro_documento' => '20600000001',
            'nombres' => 'COMERCIALIZADORA DEL ORIENTE SAC',
        ]);
        $client->setRelation('tipoDocumento', $rucType);

        $business = new Business([
            'tipo_documento_delivery_defecto' => 'auto',
            'boleta_umbral_identidad' => 700.00,
        ]);

        $code = $this->resolver->resolveCode($client, 120.00, $business);
        $this->assertSame(DeliveryDocumentResolverService::CODE_FACTURA, $code);

        $dniType = new IdentityDocumentType(['codigo' => '1', 'descripcion' => 'DNI']);
        $dniClient = new Client([
            'nro_documento' => '71234567',
            'nombres' => 'MARIA LOPEZ TORRES',
        ]);
        $dniClient->setRelation('tipoDocumento', $dniType);

        $codeBoleta = $this->resolver->resolveCode($dniClient, 50.00, $business);
        $this->assertSame(DeliveryDocumentResolverService::CODE_BOLETA, $codeBoleta);
    }

    public function test_resolver_enforces_sunat_threshold_for_boletas_without_valid_dni(): void
    {
        $business = new Business([
            'tipo_documento_delivery_defecto' => 'auto',
            'boleta_umbral_identidad' => 700.00,
        ]);

        // Sin DNI y venta >= 700 -> DEBE ser Nota de Venta para evitar rechazo 2014 de SUNAT
        $clientSinDni = new Client([
            'nro_documento' => '',
            'nombres' => 'CLIENTE MOSTRADOR',
        ]);

        $codeOverThreshold = $this->resolver->resolveCode($clientSinDni, 700.00, $business);
        $this->assertSame(DeliveryDocumentResolverService::CODE_NOTA_VENTA, $codeOverThreshold);

        // Con DNI de 8 dígitos y venta >= 700 -> Puede ser Boleta
        $clientConDni = new Client([
            'nro_documento' => '12345678',
            'nombres' => 'CARLOS RAMIREZ SILVA',
        ]);
        $codeConDni = $this->resolver->resolveCode($clientConDni, 850.00, $business);
        $this->assertSame(DeliveryDocumentResolverService::CODE_BOLETA, $codeConDni);
    }

    public function test_whatsapp_service_handles_missing_gateway_configuration_gracefully(): void
    {
        // Documento en memoria
        $billing = new Billing([
            'id' => 999,
            'serie' => 'B001',
            'correlativo' => '00000001',
            'fecha_emision' => '2026-10-01',
            'total' => 45.00,
            'whatsapp_intentos' => 0,
        ]);

        $client = new Client([
            'nombres' => 'TEST CLIENT',
            'telefono' => '987654321',
        ]);
        $billing->setRelation('customer', $client);

        // Sin URL ni Instancia configurada en Business
        $result = $this->whatsAppService->resendBillingReceipt($billing);

        $this->assertFalse($result['ok']);
        $this->assertSame(Billing::WPP_STATUS_PENDIENTE, $result['status']);
        $this->assertStringContainsString('no configurada', $result['message']);
    }

    public function test_whatsapp_service_detects_client_without_phone_number(): void
    {
        $billing = new Billing([
            'id' => 998,
            'serie' => 'B001',
            'correlativo' => '00000002',
            'fecha_emision' => '2026-10-01',
            'total' => 20.00,
            'whatsapp_intentos' => 0,
        ]);

        $client = new Client([
            'nombres' => 'TEST CLIENT SIN TELEFONO',
            'telefono' => null,
        ]);
        $billing->setRelation('customer', $client);

        $result = $this->whatsAppService->resendBillingReceipt($billing);

        $this->assertFalse($result['ok']);
        $this->assertSame(Billing::WPP_STATUS_SIN_TELEFONO, $result['status']);
        $this->assertStringContainsString('no tiene teléfono', $result['message']);
    }
}
