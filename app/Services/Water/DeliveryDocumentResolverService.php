<?php

namespace App\Services\Water;

use App\Models\Business;
use App\Models\Client;
use App\Models\TypeDocument;

class DeliveryDocumentResolverService
{
    public const CODE_FACTURA = '01';

    public const CODE_BOLETA = '03';

    public const CODE_NOTA_VENTA = '02';

    public const DEFAULT_BOLETA_THRESHOLD = 700.00;

    /**
     * Resuelve el tipo de comprobante que corresponde emitir para un pedido de delivery.
     */
    public function resolve(Client $client, float $total, ?Business $business = null): TypeDocument
    {
        $business = $business ?: Business::query()->first();
        $code = $this->resolveCode($client, $total, $business);

        return $this->findTypeDocument($code);
    }

    /**
     * Resuelve únicamente el código de tipo de documento ('01', '03', '02').
     */
    public function resolveCode(Client $client, float $total, ?Business $business = null): string
    {
        $preference = (string) ($business?->tipo_documento_delivery_defecto ?? 'auto');
        $threshold = (float) ($business?->boleta_umbral_identidad ?? self::DEFAULT_BOLETA_THRESHOLD);

        $clientDocCode = $client->relationLoaded('tipoDocumento')
            ? trim((string) optional($client->tipoDocumento)->codigo)
            : '';
        $documentNumber = preg_replace('/\D/', '', (string) $client->nro_documento);
        $clientName = mb_strtoupper(trim((string) $client->nombres));

        $hasRuc = $clientDocCode === '6' || (strlen($documentNumber) === 11 && (str_starts_with($documentNumber, '10') || str_starts_with($documentNumber, '20')));
        $hasDni = $clientDocCode === '1' || (strlen($documentNumber) === 8);
        $isAnonymous = empty($documentNumber)
            || $documentNumber === '00000000'
            || str_contains($clientName, 'VARIOS')
            || str_contains($clientName, 'GENERAL')
            || str_contains($clientName, 'PUBLICO');

        if ($preference === self::CODE_FACTURA && $hasRuc) {
            return self::CODE_FACTURA;
        }

        if ($preference === self::CODE_BOLETA) {
            if ($total >= $threshold && (! $hasDni || $isAnonymous)) {
                return self::CODE_NOTA_VENTA;
            }

            return self::CODE_BOLETA;
        }

        if ($preference === self::CODE_NOTA_VENTA) {
            return self::CODE_NOTA_VENTA;
        }

        // Modo 'auto' (Por defecto)
        if ($hasRuc && ! $isAnonymous) {
            return self::CODE_FACTURA;
        }

        if ($hasDni && ! $isAnonymous) {
            // Validación de umbral SUNAT para Boleta (>= S/ 700 exige DNI y nombres completos)
            if ($total >= $threshold && (strlen($documentNumber) !== 8 || empty($clientName))) {
                return self::CODE_NOTA_VENTA;
            }

            return self::CODE_BOLETA;
        }

        return self::CODE_NOTA_VENTA;
    }

    private function findTypeDocument(string $code): TypeDocument
    {
        return TypeDocument::query()
            ->where('codigo', $code)
            ->firstOrFail();
    }
}
