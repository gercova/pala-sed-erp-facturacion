<?php

namespace App\Services\Ebilling\Validation;

class PayloadValidator
{
    public function validate(array $payload): ValidationResult
    {
        $result = new ValidationResult();

        $this->validateBase($payload, $result);

        $documentType = $payload['documento']['tipo'] ?? null;

        if (in_array($documentType, ['01', '03'], true)) {
            $this->validateInvoiceLike($payload, $result);
        }

        if ($documentType === '07') {
            $this->validateCreditNote($payload, $result);
        }

        if ($documentType === '08') {
            $this->validateDebitNote($payload, $result);
        }

        if ($documentType === '09') {
            $this->validateDespatch($payload, $result);
        }

        return $result;
    }

    private function validateBase(array $payload, ValidationResult $result): void
    {
        $this->requireField($payload, 'tenant_id', $result);

        foreach (['tipo', 'serie', 'correlativo', 'fecha_emision', 'moneda'] as $field) {
            $this->requireField($payload['documento'] ?? [], $field, $result, 'documento.');
        }

        foreach (['tipo_documento', 'numero_documento', 'razon_social'] as $field) {
            $this->requireField($payload['cliente'] ?? [], $field, $result, 'cliente.');
        }

        if (empty($payload['items']) || ! is_array($payload['items'])) {
            $result->addError('items', 'Debe existir al menos un item.');
        }

        $totales = $payload['totales'] ?? [];

        foreach (['importe_total', 'impuestos'] as $field) {
            $this->requireField($totales, $field, $result, 'totales.');
        }

        $this->validateTotals($payload, $result);
        $this->validateItems($payload, $result);
    }

    private function validateInvoiceLike(array $payload, ValidationResult $result): void
    {
        $operacion = $payload['operacion'] ?? [];

        $this->requireField($operacion, 'tipo', $result, 'operacion.');
        $this->requireField($operacion, 'forma_pago', $result, 'operacion.');

        $paymentType = $operacion['forma_pago'] ?? null;
        $creditAmount = (float) ($operacion['monto_credito'] ?? 0);
        $installments = $operacion['cuotas'] ?? [];

        if ($paymentType === 'Contado') {
            if ($creditAmount > 0) {
                $result->addError('operacion.monto_credito', 'En contado el monto_credito debe ser 0.00.');
            }

            if (! empty($installments)) {
                $result->addError('operacion.cuotas', 'En contado no deben enviarse cuotas.');
            }
        }

        if ($paymentType === 'Credito') {
            if (empty($installments) || ! is_array($installments)) {
                $result->addError('operacion.cuotas', 'En credito debe existir al menos una cuota.');
            }

            $installmentTotal = 0.0;

            foreach ($installments as $index => $installment) {
                $this->requireField($installment, 'numero', $result, "operacion.cuotas.$index.");
                $this->requireField($installment, 'importe', $result, "operacion.cuotas.$index.");
                $this->requireField($installment, 'vencimiento', $result, "operacion.cuotas.$index.");
                $installmentTotal += (float) ($installment['importe'] ?? 0);
            }

            if (round($installmentTotal, 2) !== round($creditAmount, 2)) {
                $result->addError('operacion.cuotas', 'La suma de cuotas debe ser igual a monto_credito.');
            }
        }
    }

    private function validateCreditNote(array $payload, ValidationResult $result): void
    {
        $this->validateReferenceDocument($payload, $result);
        $this->validateReason($payload, $result);
    }

    private function validateDebitNote(array $payload, ValidationResult $result): void
    {
        $this->validateReferenceDocument($payload, $result);
        $this->validateReason($payload, $result);
    }

    private function validateDespatch(array $payload, ValidationResult $result): void
    {
        $traslado = $payload['traslado'] ?? [];

        foreach (['motivo_codigo', 'motivo_descripcion', 'fecha_inicio', 'modo_transporte', 'peso_total', 'unidad_peso'] as $field) {
            $this->requireField($traslado, $field, $result, 'traslado.');
        }

        foreach (['ubigeo', 'direccion'] as $field) {
            $this->requireField($payload['partida'] ?? [], $field, $result, 'partida.');
            $this->requireField($payload['llegada'] ?? [], $field, $result, 'llegada.');
        }

        $transportMode = $traslado['modo_transporte'] ?? null;

        if ($transportMode === '01') {
            foreach (['tipo_documento', 'numero_documento', 'razon_social'] as $field) {
                $this->requireField($payload['transportista'] ?? [], $field, $result, 'transportista.');
            }
        }

        if ($transportMode === '02') {
            $this->requireField($payload['vehiculo'] ?? [], 'placa', $result, 'vehiculo.');

            foreach (['tipo_documento', 'numero_documento', 'nombres', 'apellidos', 'licencia'] as $field) {
                $this->requireField($payload['conductor'] ?? [], $field, $result, 'conductor.');
            }
        }
    }

    private function validateReferenceDocument(array $payload, ValidationResult $result): void
    {
        foreach (['tipo', 'serie', 'correlativo'] as $field) {
            $this->requireField($payload['documento_referencia'] ?? [], $field, $result, 'documento_referencia.');
        }
    }

    private function validateReason(array $payload, ValidationResult $result): void
    {
        foreach (['codigo', 'descripcion'] as $field) {
            $this->requireField($payload['motivo'] ?? [], $field, $result, 'motivo.');
        }
    }

    private function validateTotals(array $payload, ValidationResult $result): void
    {
        $totales = $payload['totales'] ?? [];
        $items = $payload['items'] ?? [];

        if (! is_array($items)) {
            return;
        }

        $sumTax = 0.0;
        $sumIcbper = 0.0;
        $sumGravadas = 0.0;
        $sumExoneradas = 0.0;
        $sumInafectas = 0.0;

        foreach ($items as $item) {
            $sumTax += (float) ($item['igv'] ?? 0) + (float) ($item['icbper'] ?? 0);
            $sumIcbper += (float) ($item['icbper'] ?? 0);

            $codigo = $item['afectacion_igv']['codigo'] ?? null;
            $base = (float) ($item['valor_total'] ?? 0);

            if ($codigo === '10') {
                $sumGravadas += $base;
            }

            if ($codigo === '20') {
                $sumExoneradas += $base;
            }

            if ($codigo === '30') {
                $sumInafectas += $base;
            }
        }

        if (isset($totals['impuestos']) && round((float) $totals['impuestos'], 2) !== round($sumTax, 2)) {
            $result->addError('totales.impuestos', 'Los impuestos no cuadran con la suma de IGV e ICBPER de los items.');
        }

        if (isset($totals['icbper']) && round((float) $totals['icbper'], 2) !== round($sumIcbper, 2)) {
            $result->addError('totales.icbper', 'El total de ICBPER no coincide con los items.');
        }

        if (isset($totals['gravadas']) && round((float) $totals['gravadas'], 2) !== round($sumGravadas, 2)) {
            $result->addError('totales.gravadas', 'El total de operaciones gravadas no coincide con los items.');
        }

        if (isset($totals['exoneradas']) && round((float) $totals['exoneradas'], 2) !== round($sumExoneradas, 2)) {
            $result->addError('totales.exoneradas', 'El total de operaciones exoneradas no coincide con los items.');
        }

        if (isset($totals['inafectas']) && round((float) $totals['inafectas'], 2) !== round($sumInafectas, 2)) {
            $result->addError('totales.inafectas', 'El total de operaciones inafectas no coincide con los items.');
        }
    }

    private function validateItems(array $payload, ValidationResult $result): void
    {
        foreach (($payload['items'] ?? []) as $index => $item) {
            foreach (['descripcion', 'cantidad', 'unidad'] as $field) {
                $this->requireField($item, $field, $result, "items.$index.");
            }

            if (isset($item['cantidad']) && (float) $item['cantidad'] <= 0) {
                $result->addError("items.$index.cantidad", 'La cantidad debe ser mayor a cero.');
            }

            $this->requireField($item['afectacion_igv'] ?? [], 'codigo', $result, "items.$index.afectacion_igv.");

            $affectationCode = $item['afectacion_igv']['codigo'] ?? null;
            $igv = (float) ($item['igv'] ?? 0);
            $icbper = (float) ($item['icbper'] ?? 0);

            if ($affectationCode === '10' && $igv <= 0) {
                $result->addError("items.$index.igv", 'Un item gravado debe tener IGV mayor a cero.');
            }

            if (in_array($affectationCode, ['20', '30'], true) && $igv > 0) {
                $result->addError("items.$index.igv", 'Un item exonerado o inafecto no debe tener IGV.');
            }

            if ($icbper > 0 && ! isset($item['factor_icbper'])) {
                $result->addError("items.$index.factor_icbper", 'Si el item tiene ICBPER debe enviarse factor_icbper.');
            }
        }
    }

    private function requireField(array $source, string $field, ValidationResult $result, string $prefix = ''): void
    {
        if (! array_key_exists($field, $source) || $source[$field] === null || $source[$field] === '') {
            $result->addError($prefix . $field, 'Este campo es obligatorio.');
        }
    }
}
