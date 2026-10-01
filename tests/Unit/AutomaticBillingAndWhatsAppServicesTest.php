<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Client;
use App\Services\Water\DeliveryDocumentResolverService;
use App\Services\Water\WhatsAppSenderService;
use PHPUnit\Framework\TestCase;

class AutomaticBillingAndWhatsAppServicesTest extends TestCase
{
    private DeliveryDocumentResolverService $resolver;

    private WhatsAppSenderService $whatsAppService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new DeliveryDocumentResolverService;
        $this->whatsAppService = new WhatsAppSenderService;
    }

    public function test_resolves_factura_for_ruc_client(): void
    {
        $client = new Client([
            'nro_documento' => '20123456789',
            'nombres' => 'EMPRESA TEST SAC',
        ]);

        $business = new Business([
            'tipo_documento_delivery_defecto' => 'auto',
            'boleta_umbral_identidad' => 700.00,
        ]);

        $code = $this->resolver->resolveCode($client, 150.00, $business);

        $this->assertSame(DeliveryDocumentResolverService::CODE_FACTURA, $code);
    }

    public function test_resolves_boleta_for_dni_client_under_threshold(): void
    {
        $client = new Client([
            'nro_documento' => '45678901',
            'nombres' => 'JUAN PEREZ GARCIA',
        ]);

        $business = new Business([
            'tipo_documento_delivery_defecto' => 'auto',
            'boleta_umbral_identidad' => 700.00,
        ]);

        $code = $this->resolver->resolveCode($client, 699.90, $business);

        $this->assertSame(DeliveryDocumentResolverService::CODE_BOLETA, $code);
    }

    public function test_resolves_sale_note_for_anonymous_or_various_clients(): void
    {
        $client = new Client([
            'nro_documento' => '00000000',
            'nombres' => 'CLIENTES VARIOS',
        ]);

        $business = new Business([
            'tipo_documento_delivery_defecto' => 'auto',
        ]);

        $code = $this->resolver->resolveCode($client, 45.00, $business);

        $this->assertSame(DeliveryDocumentResolverService::CODE_NOTA_VENTA, $code);
    }

    public function test_resolves_sale_note_when_over_threshold_without_valid_dni(): void
    {
        $client = new Client([
            'nro_documento' => '123', // Documento incompleto/inválido
            'nombres' => 'CLIENTE ANONIMO',
        ]);

        $business = new Business([
            'tipo_documento_delivery_defecto' => 'auto',
            'boleta_umbral_identidad' => 700.00,
        ]);

        $code = $this->resolver->resolveCode($client, 750.00, $business);

        $this->assertSame(DeliveryDocumentResolverService::CODE_NOTA_VENTA, $code);
    }

    public function test_respects_business_preference_override(): void
    {
        $client = new Client([
            'nro_documento' => '45678901',
            'nombres' => 'JUAN PEREZ GARCIA',
        ]);

        $business = new Business([
            'tipo_documento_delivery_defecto' => DeliveryDocumentResolverService::CODE_NOTA_VENTA,
        ]);

        $code = $this->resolver->resolveCode($client, 30.00, $business);

        $this->assertSame(DeliveryDocumentResolverService::CODE_NOTA_VENTA, $code);
    }

    public function test_normalizes_peruvian_phone_numbers(): void
    {
        $this->assertSame('51987654321', $this->whatsAppService->normalizePhone('987654321'));
        $this->assertSame('51987654321', $this->whatsAppService->normalizePhone('51987654321'));
        $this->assertSame('51987654321', $this->whatsAppService->normalizePhone('+51 987-654-321'));
        $this->assertSame('51987654321', $this->whatsAppService->normalizePhone(' (51) 987 654 321 '));
        $this->assertNull($this->whatsAppService->normalizePhone('123'));
        $this->assertNull($this->whatsAppService->normalizePhone(''));
        $this->assertNull($this->whatsAppService->normalizePhone(null));
    }
}
