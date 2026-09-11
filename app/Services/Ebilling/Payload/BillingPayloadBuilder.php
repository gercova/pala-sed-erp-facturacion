<?php

namespace App\Services\Ebilling\Payload;

use App\Models\Billing;
use App\Models\DetailBilling;
use InvalidArgumentException;

class BillingPayloadBuilder
{
    public function build(Billing $billing): array
    {
        $billing->loadMissing([
            'customer.tipoDocumento',
            'currency',
            'typeDocument',
            'parentBilling.typeDocument',
            'noteType',
            'creditNoteType',
            'debitNoteType',
            'payMode',
            'details.product.unit',
            'details.product.igvTypeAffection',
            'details.igvTypeAffection',
        ]);

        if (! $billing->customer || ! $billing->currency || ! $billing->typeDocument) {
            throw new InvalidArgumentException('El comprobante no tiene configuradas sus relaciones principales.');
        }

        if ($billing->details->isEmpty()) {
            throw new InvalidArgumentException('El comprobante no tiene items para convertir a payload.');
        }

        $documentCode = trim((string) $billing->typeDocument->codigo);

        if (! in_array($documentCode, ['01', '03', '07', '08'], true)) {
            throw new InvalidArgumentException('Solo se soporta payload para factura, boleta, nota de credito y nota de debito.');
        }

        $payload = [
            'tenant_id' => 'business-1',
            'documento' => [
                'tipo' => $documentCode,
                'serie' => trim((string) $billing->serie),
                'correlativo' => trim((string) $billing->correlativo),
                'fecha_emision' => optional($billing->fecha_emision)->format('Y-m-d'),
                'fecha_vencimiento' => optional($billing->fecha_vencimiento)->format('Y-m-d'),
                'hora_emision' => trim((string) $billing->hora),
                'moneda' => trim((string) $billing->currency->codigo),
            ],
            'cliente' => [
                'tipo_documento' => $this->resolveCustomerDocumentCode($billing),
                'numero_documento' => trim((string) $billing->customer->nro_documento),
                'razon_social' => trim((string) $billing->customer->nombres),
                'direccion' => trim((string) ($billing->customer->direccion ?? '')),
            ],
            'totales' => [
                'gravadas' => round((float) $billing->gravada, 2),
                'exoneradas' => round((float) $billing->exonerada, 2),
                'inafectas' => round((float) $billing->inafecta, 2),
                'igv' => round((float) $billing->igv, 2),
                'icbper' => round((float) $billing->icbper, 2),
                'impuestos' => round((float) $billing->igv + (float) $billing->icbper, 2),
                'importe_total' => round((float) $billing->total, 2),
            ],
            'operacion' => [
                'tipo' => '0101',
                'forma_pago' => trim((string) ($billing->sunat_forma_pago ?: 'Contado')),
                'monto_credito' => round((float) ($billing->monto_credito ?? 0), 2),
                'cuotas' => $this->normalizeInstallments($billing->cuotas ?? []),
            ],
            'items' => $billing->details
                ->map(fn (DetailBilling $detail) => $this->mapItem($detail))
                ->values()
                ->all(),
        ];

        if ($documentCode === '07') {
            $noteType = $billing->creditNoteType ?: $billing->noteType;

            if (! $billing->parentBilling || ! $billing->parentBilling->typeDocument || ! $noteType) {
                throw new InvalidArgumentException('La nota de credito requiere comprobante y motivo de referencia.');
            }

            $payload['documento_referencia'] = [
                'tipo' => trim((string) $billing->parentBilling->typeDocument->codigo),
                'serie' => trim((string) $billing->parentBilling->serie),
                'correlativo' => trim((string) $billing->parentBilling->correlativo),
            ];
            $payload['motivo'] = [
                'codigo' => trim((string) $noteType->codigo),
                'descripcion' => trim((string) ($billing->motivo ?: $noteType->descripcion)),
            ];
        }

        if ($documentCode === '08') {
            $noteType = $billing->debitNoteType;

            if (! $billing->parentBilling || ! $billing->parentBilling->typeDocument || ! $noteType) {
                throw new InvalidArgumentException('La nota de debito requiere comprobante y motivo de referencia.');
            }

            $payload['documento_referencia'] = [
                'tipo' => trim((string) $billing->parentBilling->typeDocument->codigo),
                'serie' => trim((string) $billing->parentBilling->serie),
                'correlativo' => trim((string) $billing->parentBilling->correlativo),
            ];
            $payload['motivo'] = [
                'codigo' => trim((string) $noteType->codigo),
                'descripcion' => trim((string) ($billing->motivo ?: $noteType->descripcion)),
            ];
        }

        return $payload;
    }

    private function mapItem(DetailBilling $detail): array
    {
        if (! $detail->product) {
            throw new InvalidArgumentException('Existe un detalle sin producto asociado.');
        }

        $igvType = $detail->igvTypeAffection ?: $detail->product->igvTypeAffection;

        if (! $igvType) {
            throw new InvalidArgumentException('Existe un detalle sin tipo de afectacion IGV asociado.');
        }

        $item = [
            'descripcion' => trim((string) $detail->product->descripcion),
            'cantidad' => round((float) $detail->cantidad, 2),
            'unidad' => trim((string) optional($detail->product->unit)->codigo ?: 'NIU'),
            'valor_unitario' => round((float) $detail->valor_unitario, 10),
            'precio_unitario' => round((float) $detail->precio_unitario, 10),
            'valor_total' => round((float) $detail->valor_total, 2),
            'igv' => round((float) $detail->igv, 2),
            'icbper' => round((float) $detail->icbper, 2),
            'descuento' => round((float) $detail->descuento, 2),
            'afectacion_igv' => [
                'codigo' => trim((string) $igvType->codigo),
                'tipo' => trim((string) ($igvType->tipo ?? '')),
            ],
            'codigo_interno' => trim((string) ($detail->product->codigo_interno ?? '')),
            'codigo_sunat' => trim((string) ($detail->product->codigo_sunat ?? '00000000')),
        ];

        if ((float) $detail->icbper > 0) {
            $item['factor_icbper'] = round((float) ($detail->factor_icbper ?? 0), 4);
        }

        return $item;
    }

    private function resolveCustomerDocumentCode(Billing $billing): string
    {
        $customer = $billing->customer;
        $documentCode = trim((string) optional($customer->tipoDocumento)->codigo);
        $documentNumber = preg_replace('/\D+/', '', (string) ($customer->nro_documento ?? ''));

        if ($documentCode !== '') {
            return $documentCode;
        }

        return match (strlen($documentNumber)) {
            11 => '6',
            8 => '1',
            default => '0',
        };
    }

    private function normalizeInstallments(array $installments): array
    {
        return collect($installments)
            ->map(function ($installment, $index) {
                $number = $installment['numero'] ?? $installment['nro'] ?? ($index + 1);
                $amount = $installment['importe'] ?? $installment['monto'] ?? 0;
                $dueDate = $installment['vencimiento'] ?? $installment['fecha_vencimiento'] ?? null;

                return [
                    'numero' => (string) $number,
                    'importe' => round((float) $amount, 2),
                    'vencimiento' => $dueDate ? trim((string) $dueDate) : null,
                ];
            })
            ->filter(function (array $installment) {
                return $installment['vencimiento'] !== null && $installment['vencimiento'] !== '';
            })
            ->values()
            ->all();
    }
}
