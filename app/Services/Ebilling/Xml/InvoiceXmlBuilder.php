<?php

namespace App\Services\Ebilling\Xml;

use App\Models\Business;
use InvalidArgumentException;

class InvoiceXmlBuilder
{
    public function build(array $payload, Business $business): string
    {
        $documentCode = trim((string) (($payload['documento'] ?? [])['tipo'] ?? ''));

        return match ($documentCode) {
            '07' => $this->buildCreditNote($payload, $business),
            '08' => $this->buildDebitNote($payload, $business),
            '01', '03' => $this->buildInvoiceLike($payload, $business),
            default => throw new InvalidArgumentException('Solo se soporta XML de factura, boleta, nota de credito o nota de debito en esta etapa.'),
        };
    }

    private function buildInvoiceLike(array $payload, Business $business): string
    {
        $document = $payload['documento'] ?? [];
        $customer = $payload['cliente'] ?? [];
        $totals = $payload['totales'] ?? [];
        $operation = $payload['operacion'] ?? [];
        $items = $payload['items'] ?? [];

        $documentCode = trim((string) ($document['tipo'] ?? ''));
        $seriesAndNumber = trim((string) ($document['serie'] ?? '')) . '-' . trim((string) ($document['correlativo'] ?? ''));
        $currency = trim((string) ($document['moneda'] ?? 'PEN'));
        $issueDate = trim((string) ($document['fecha_emision'] ?? ''));
        $dueDate = trim((string) ($document['fecha_vencimiento'] ?? $issueDate));
        $issueTime = trim((string) ($document['hora_emision'] ?? now()->format('H:i:s')));
        $operationCode = trim((string) ($operation['tipo'] ?? '0101'));
        $lineExtensionAmount = round((float) ($totals['gravadas'] ?? 0) + (float) ($totals['exoneradas'] ?? 0) + (float) ($totals['inafectas'] ?? 0), 2);
        $taxInclusiveAmount = round($lineExtensionAmount + (float) ($totals['impuestos'] ?? 0), 2);
        $payableAmount = round((float) ($totals['importe_total'] ?? 0), 2);

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="utf-8"?>';
        $xml[] = '<Invoice xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">';
        $xml[] = '<ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent/></ext:UBLExtension></ext:UBLExtensions>';
        $xml[] = '<cbc:UBLVersionID>2.1</cbc:UBLVersionID>';
        // Para nota de debito SUNAT beta espera el esquema clasico 1.0.
        $xml[] = '<cbc:CustomizationID schemeAgencyName="PE:SUNAT">2.0</cbc:CustomizationID>';
        $xml[] = '<cbc:ProfileID schemeName="Tipo de Operacion" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo17">' . $this->escape($operationCode) . '</cbc:ProfileID>';
        $xml[] = '<cbc:ID>' . $this->escape($seriesAndNumber) . '</cbc:ID>';
        $xml[] = '<cbc:IssueDate>' . $this->escape($issueDate) . '</cbc:IssueDate>';
        $xml[] = '<cbc:IssueTime>' . $this->escape($issueTime) . '</cbc:IssueTime>';
        $xml[] = '<cbc:DueDate>' . $this->escape($dueDate) . '</cbc:DueDate>';
        $xml[] = '<cbc:InvoiceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" listID="' . $this->escape($operationCode) . '" name="Tipo de Operacion">' . $this->escape($documentCode) . '</cbc:InvoiceTypeCode>';
        $xml[] = '<cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listName="Currency" listAgencyName="United Nations Economic Commission for Europe">' . $this->escape($currency) . '</cbc:DocumentCurrencyCode>';
        $xml[] = '<cbc:LineCountNumeric>' . count($items) . '</cbc:LineCountNumeric>';
        $xml[] = $this->signatureBlock($business, $seriesAndNumber);
        $xml[] = $this->supplierBlock($business);
        $xml[] = $this->customerBlock($customer);
        $xml[] = $this->paymentTermsBlock($currency, $operation);
        $xml[] = $this->taxTotalBlock($currency, $totals);
        $xml[] = '<cac:LegalMonetaryTotal>';
        $xml[] = '<cbc:LineExtensionAmount currencyID="' . $this->escape($currency) . '">' . $this->number($lineExtensionAmount) . '</cbc:LineExtensionAmount>';
        $xml[] = '<cbc:TaxInclusiveAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxInclusiveAmount) . '</cbc:TaxInclusiveAmount>';
        $xml[] = '<cbc:PayableAmount currencyID="' . $this->escape($currency) . '">' . $this->number($payableAmount) . '</cbc:PayableAmount>';
        $xml[] = '</cac:LegalMonetaryTotal>';

        foreach ($items as $index => $item) {
            $xml[] = $this->invoiceLineBlock($item, $currency, $index + 1);
        }

        $xml[] = '</Invoice>';

        return implode('', $xml);
    }

    private function buildCreditNote(array $payload, Business $business): string
    {
        $document = $payload['documento'] ?? [];
        $customer = $payload['cliente'] ?? [];
        $totals = $payload['totales'] ?? [];
        $referenceDocument = $payload['documento_referencia'] ?? [];
        $reason = $payload['motivo'] ?? [];
        $items = $payload['items'] ?? [];

        $seriesAndNumber = trim((string) ($document['serie'] ?? '')) . '-' . trim((string) ($document['correlativo'] ?? ''));
        $currency = trim((string) ($document['moneda'] ?? 'PEN'));
        $issueDate = trim((string) ($document['fecha_emision'] ?? ''));
        $issueTime = trim((string) ($document['hora_emision'] ?? now()->format('H:i:s')));
        $lineExtensionAmount = round((float) ($totals['gravadas'] ?? 0) + (float) ($totals['exoneradas'] ?? 0) + (float) ($totals['inafectas'] ?? 0), 2);
        $taxInclusiveAmount = round($lineExtensionAmount + (float) ($totals['impuestos'] ?? 0), 2);
        $payableAmount = round((float) ($totals['importe_total'] ?? 0), 2);
        $referenceId = trim((string) ($referenceDocument['serie'] ?? '')) . '-' . trim((string) ($referenceDocument['correlativo'] ?? ''));

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="utf-8"?>';
        $xml[] = '<CreditNote xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2">';
        $xml[] = '<ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent/></ext:UBLExtension></ext:UBLExtensions>';
        $xml[] = '<cbc:UBLVersionID>2.1</cbc:UBLVersionID>';
        $xml[] = '<cbc:CustomizationID schemeAgencyName="PE:SUNAT">2.0</cbc:CustomizationID>';
        $xml[] = '<cbc:ID>' . $this->escape($seriesAndNumber) . '</cbc:ID>';
        $xml[] = '<cbc:IssueDate>' . $this->escape($issueDate) . '</cbc:IssueDate>';
        $xml[] = '<cbc:IssueTime>' . $this->escape($issueTime) . '</cbc:IssueTime>';
        $xml[] = '<cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listName="Currency" listAgencyName="United Nations Economic Commission for Europe">' . $this->escape($currency) . '</cbc:DocumentCurrencyCode>';
        $xml[] = '<cbc:LineCountNumeric>' . count($items) . '</cbc:LineCountNumeric>';
        $xml[] = '<cac:DiscrepancyResponse><cbc:ReferenceID>' . $this->escape($referenceId) . '</cbc:ReferenceID><cbc:ResponseCode listAgencyName="PE:SUNAT" listName="Tipo de nota de credito" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo09">' . $this->escape((string) ($reason['codigo'] ?? '01')) . '</cbc:ResponseCode><cbc:Description><![CDATA[' . trim((string) ($reason['descripcion'] ?? '')) . ']]></cbc:Description></cac:DiscrepancyResponse>';
        $xml[] = '<cac:BillingReference><cac:InvoiceDocumentReference><cbc:ID>' . $this->escape($referenceId) . '</cbc:ID><cbc:DocumentTypeCode>' . $this->escape((string) ($referenceDocument['tipo'] ?? '')) . '</cbc:DocumentTypeCode></cac:InvoiceDocumentReference></cac:BillingReference>';
        $xml[] = $this->signatureBlock($business, $seriesAndNumber);
        $xml[] = $this->supplierBlock($business);
        $xml[] = $this->customerBlock($customer);
        $xml[] = $this->taxTotalBlock($currency, $totals);
        $xml[] = '<cac:LegalMonetaryTotal>';
        $xml[] = '<cbc:LineExtensionAmount currencyID="' . $this->escape($currency) . '">' . $this->number($lineExtensionAmount) . '</cbc:LineExtensionAmount>';
        $xml[] = '<cbc:TaxInclusiveAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxInclusiveAmount) . '</cbc:TaxInclusiveAmount>';
        $xml[] = '<cbc:PayableAmount currencyID="' . $this->escape($currency) . '">' . $this->number($payableAmount) . '</cbc:PayableAmount>';
        $xml[] = '</cac:LegalMonetaryTotal>';

        foreach ($items as $index => $item) {
            $xml[] = $this->creditNoteLineBlock($item, $currency, $index + 1);
        }

        $xml[] = '</CreditNote>';

        return implode('', $xml);
    }

    private function buildDebitNote(array $payload, Business $business): string
    {
        $document = $payload['documento'] ?? [];
        $customer = $payload['cliente'] ?? [];
        $totals = $payload['totales'] ?? [];
        $referenceDocument = $payload['documento_referencia'] ?? [];
        $reason = $payload['motivo'] ?? [];
        $items = $payload['items'] ?? [];

        $seriesAndNumber = trim((string) ($document['serie'] ?? '')) . '-' . trim((string) ($document['correlativo'] ?? ''));
        $currency = trim((string) ($document['moneda'] ?? 'PEN'));
        $issueDate = trim((string) ($document['fecha_emision'] ?? ''));
        $issueTime = trim((string) ($document['hora_emision'] ?? now()->format('H:i:s')));
        $lineExtensionAmount = round((float) ($totals['gravadas'] ?? 0) + (float) ($totals['exoneradas'] ?? 0) + (float) ($totals['inafectas'] ?? 0), 2);
        $taxInclusiveAmount = round($lineExtensionAmount + (float) ($totals['impuestos'] ?? 0), 2);
        $payableAmount = round((float) ($totals['importe_total'] ?? 0), 2);
        $referenceId = trim((string) ($referenceDocument['serie'] ?? '')) . '-' . trim((string) ($referenceDocument['correlativo'] ?? ''));

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="utf-8"?>';
        $xml[] = '<DebitNote xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2">';
        $xml[] = '<ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent/></ext:UBLExtension></ext:UBLExtensions>';
        // SUNAT beta valida la nota de debito con este esquema clasico.
        $xml[] = '<cbc:UBLVersionID>2.0</cbc:UBLVersionID>';
        $xml[] = '<cbc:CustomizationID schemeAgencyName="PE:SUNAT">1.0</cbc:CustomizationID>';
        $xml[] = '<cbc:ID>' . $this->escape($seriesAndNumber) . '</cbc:ID>';
        $xml[] = '<cbc:IssueDate>' . $this->escape($issueDate) . '</cbc:IssueDate>';
        $xml[] = '<cbc:IssueTime>' . $this->escape($issueTime) . '</cbc:IssueTime>';
        $xml[] = '<cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listName="Currency" listAgencyName="United Nations Economic Commission for Europe">' . $this->escape($currency) . '</cbc:DocumentCurrencyCode>';
        $xml[] = '<cbc:LineCountNumeric>' . count($items) . '</cbc:LineCountNumeric>';
        $xml[] = '<cac:DiscrepancyResponse><cbc:ReferenceID>' . $this->escape($referenceId) . '</cbc:ReferenceID><cbc:ResponseCode listAgencyName="PE:SUNAT" listName="Tipo de nota de debito" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo10">' . $this->escape((string) ($reason['codigo'] ?? '01')) . '</cbc:ResponseCode><cbc:Description><![CDATA[' . trim((string) ($reason['descripcion'] ?? '')) . ']]></cbc:Description></cac:DiscrepancyResponse>';
        $xml[] = '<cac:BillingReference><cac:InvoiceDocumentReference><cbc:ID>' . $this->escape($referenceId) . '</cbc:ID><cbc:DocumentTypeCode>' . $this->escape((string) ($referenceDocument['tipo'] ?? '')) . '</cbc:DocumentTypeCode></cac:InvoiceDocumentReference></cac:BillingReference>';
        $xml[] = $this->signatureBlock($business, $seriesAndNumber);
        $xml[] = $this->legacySupplierBlock($business);
        $xml[] = $this->legacyCustomerBlock($customer);
        $xml[] = $this->taxTotalBlock($currency, $totals);
        $xml[] = '<cac:RequestedMonetaryTotal>';
        $xml[] = '<cbc:LineExtensionAmount currencyID="' . $this->escape($currency) . '">' . $this->number($lineExtensionAmount) . '</cbc:LineExtensionAmount>';
        $xml[] = '<cbc:TaxInclusiveAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxInclusiveAmount) . '</cbc:TaxInclusiveAmount>';
        $xml[] = '<cbc:PayableAmount currencyID="' . $this->escape($currency) . '">' . $this->number($payableAmount) . '</cbc:PayableAmount>';
        $xml[] = '</cac:RequestedMonetaryTotal>';

        foreach ($items as $index => $item) {
            $xml[] = $this->debitNoteLineBlock($item, $currency, $index + 1);
        }

        $xml[] = '</DebitNote>';

        return implode('', $xml);
    }

    private function signatureBlock(Business $business, string $seriesAndNumber): string
    {
        return '<cac:Signature><cbc:ID>' . $this->escape($seriesAndNumber) . '</cbc:ID><cac:SignatoryParty><cac:PartyIdentification><cbc:ID>' . $this->escape((string) $business->ruc) . '</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[' . ($business->razon_social ?? '') . ']]></cbc:Name></cac:PartyName></cac:SignatoryParty><cac:DigitalSignatureAttachment><cac:ExternalReference><cbc:URI>#SignatureSP</cbc:URI></cac:ExternalReference></cac:DigitalSignatureAttachment></cac:Signature>';
    }

    private function supplierBlock(Business $business): string
    {
        return '<cac:AccountingSupplierParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID="6" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $this->escape((string) $business->ruc) . '</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[' . ($business->nombre_comercial ?: $business->razon_social) . ']]></cbc:Name></cac:PartyName><cac:PartyTaxScheme><cbc:RegistrationName><![CDATA[' . ($business->razon_social ?? '') . ']]></cbc:RegistrationName><cbc:CompanyID schemeID="6" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $this->escape((string) $business->ruc) . '</cbc:CompanyID><cac:TaxScheme><cbc:ID schemeID="6" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $this->escape((string) $business->ruc) . '</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[' . ($business->razon_social ?? '') . ']]></cbc:RegistrationName><cac:RegistrationAddress><cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">' . $this->escape((string) ($business->ubigeo ?? '')) . '</cbc:ID><cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">0000</cbc:AddressTypeCode><cac:AddressLine><cbc:Line><![CDATA[' . ($business->direccion ?? '') . ']]></cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country">' . $this->escape((string) ($business->codigo_pais ?: 'PE')) . '</cbc:IdentificationCode></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingSupplierParty>';
    }

    private function legacySupplierBlock(Business $business): string
    {
        $tradeName = $business->nombre_comercial ?: $business->razon_social;

        return '<cac:AccountingSupplierParty>'
            . '<cbc:CustomerAssignedAccountID>' . $this->escape((string) $business->ruc) . '</cbc:CustomerAssignedAccountID>'
            . '<cbc:AdditionalAccountID>6</cbc:AdditionalAccountID>'
            . '<cac:Party>'
            . '<cac:PartyName><cbc:Name><![CDATA[' . $tradeName . ']]></cbc:Name></cac:PartyName>'
            . '<cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[' . ($business->razon_social ?? '') . ']]></cbc:RegistrationName></cac:PartyLegalEntity>'
            . '</cac:Party>'
            . '</cac:AccountingSupplierParty>';
    }

    private function legacySellerSupplierPartyBlock(Business $business): string
    {
        return '<cac:SellerSupplierParty><cac:Party><cac:PostalAddress>'
            . '<cbc:AddressTypeCode>0000</cbc:AddressTypeCode>'
            . '</cac:PostalAddress></cac:Party></cac:SellerSupplierParty>';
    }

    private function customerBlock(array $customer): string
    {
        $documentType = trim((string) ($customer['tipo_documento'] ?? '0'));
        $documentNumber = trim((string) ($customer['numero_documento'] ?? ''));
        $name = trim((string) ($customer['razon_social'] ?? ''));
        $address = trim((string) ($customer['direccion'] ?? ''));

        return '<cac:AccountingCustomerParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID="' . $this->escape($documentType) . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $this->escape($documentNumber) . '</cbc:ID></cac:PartyIdentification><cac:PartyTaxScheme><cbc:RegistrationName><![CDATA[' . $name . ']]></cbc:RegistrationName><cbc:CompanyID schemeID="' . $this->escape($documentType) . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $this->escape($documentNumber) . '</cbc:CompanyID><cac:TaxScheme><cbc:ID schemeID="' . $this->escape($documentType) . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $this->escape($documentNumber) . '</cbc:ID></cac:TaxScheme></cac:PartyTaxScheme><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[' . $name . ']]></cbc:RegistrationName><cac:RegistrationAddress><cac:AddressLine><cbc:Line><![CDATA[' . $address . ']]></cbc:Line></cac:AddressLine></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingCustomerParty>';
    }

    private function legacyCustomerBlock(array $customer): string
    {
        $documentType = trim((string) ($customer['tipo_documento'] ?? '0'));
        $documentNumber = trim((string) ($customer['numero_documento'] ?? ''));
        $name = trim((string) ($customer['razon_social'] ?? ''));

        return '<cac:AccountingCustomerParty>'
            . '<cbc:CustomerAssignedAccountID>' . $this->escape($documentNumber) . '</cbc:CustomerAssignedAccountID>'
            . '<cbc:AdditionalAccountID>' . $this->escape($documentType) . '</cbc:AdditionalAccountID>'
            . '<cac:Party>'
            . '<cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[' . $name . ']]></cbc:RegistrationName></cac:PartyLegalEntity>'
            . '</cac:Party>'
            . '</cac:AccountingCustomerParty>';
    }

    private function paymentTermsBlock(string $currency, array $operation): string
    {
        $paymentType = trim((string) ($operation['forma_pago'] ?? 'Contado'));
        $amount = (float) ($operation['monto_credito'] ?? 0);
        $xml = '<cac:PaymentTerms><cbc:ID>FormaPago</cbc:ID><cbc:PaymentMeansID>' . $this->escape($paymentType) . '</cbc:PaymentMeansID><cbc:Amount currencyID="' . $this->escape($currency) . '">' . $this->number($amount) . '</cbc:Amount></cac:PaymentTerms>';

        foreach (($operation['cuotas'] ?? []) as $installment) {
            $number = trim((string) ($installment['numero'] ?? ''));
            $installmentAmount = (float) ($installment['importe'] ?? 0);
            $dueDate = trim((string) ($installment['vencimiento'] ?? ''));
            $paymentMeansId = 'Cuota' . str_pad(preg_replace('/\D+/', '', $number) ?: '1', 3, '0', STR_PAD_LEFT);
            $xml .= '<cac:PaymentTerms><cbc:ID>FormaPago</cbc:ID><cbc:PaymentMeansID>' . $this->escape($paymentMeansId) . '</cbc:PaymentMeansID><cbc:Amount currencyID="' . $this->escape($currency) . '">' . $this->number($installmentAmount) . '</cbc:Amount><cbc:PaymentDueDate>' . $this->escape($dueDate) . '</cbc:PaymentDueDate></cac:PaymentTerms>';
        }

        return $xml;
    }

    private function taxTotalBlock(string $currency, array $totals): string
    {
        $xml = '<cac:TaxTotal><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) ($totals['impuestos'] ?? 0)) . '</cbc:TaxAmount>';

        if ((float) ($totals['gravadas'] ?? 0) > 0) {
            $xml .= $this->taxSubtotalBlock($currency, (float) $totals['gravadas'], (float) ($totals['igv'] ?? 0), 'S', '1000', 'IGV', 'VAT');
        }

        if ((float) ($totals['exoneradas'] ?? 0) > 0) {
            $xml .= $this->taxSubtotalBlock($currency, (float) $totals['exoneradas'], 0.0, 'E', '9997', 'EXO', 'VAT');
        }

        if ((float) ($totals['inafectas'] ?? 0) > 0) {
            $xml .= $this->taxSubtotalBlock($currency, (float) $totals['inafectas'], 0.0, 'O', '9998', 'INA', 'FRE');
        }

        if ((float) ($totals['icbper'] ?? 0) > 0) {
            $xml .= '<cac:TaxSubtotal><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) $totals['icbper']) . '</cbc:TaxAmount><cac:TaxCategory><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">7152</cbc:ID><cbc:Name>ICBPER</cbc:Name><cbc:TaxTypeCode>OTH</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
        }

        return $xml . '</cac:TaxTotal>';
    }

    private function taxSubtotalBlock(string $currency, float $taxableAmount, float $taxAmount, string $categoryId, string $taxSchemeId, string $taxName, string $taxTypeCode): string
    {
        return '<cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxableAmount) . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxAmount) . '</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">' . $this->escape($categoryId) . '</cbc:ID><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">' . $this->escape($taxSchemeId) . '</cbc:ID><cbc:Name>' . $this->escape($taxName) . '</cbc:Name><cbc:TaxTypeCode>' . $this->escape($taxTypeCode) . '</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
    }

    private function invoiceLineBlock(array $item, string $currency, int $lineNumber): string
    {
        $affectationCode = trim((string) ($item['afectacion_igv']['codigo'] ?? '10'));
        [$categoryId, $taxSchemeId, $taxName, $taxTypeCode] = $this->codesForAffectation($affectationCode);
        $taxableAmount = (float) ($item['valor_total'] ?? 0);
        $taxAmount = (float) ($item['igv'] ?? 0);
        $totalTaxAmount = $taxAmount + (float) ($item['icbper'] ?? 0);
        $unitCode = trim((string) ($item['unidad'] ?? 'NIU'));
        $quantity = (float) ($item['cantidad'] ?? 0);

        $xml = '<cac:InvoiceLine><cbc:ID>' . $lineNumber . '</cbc:ID><cbc:InvoicedQuantity unitCode="' . $this->escape($unitCode) . '" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">' . $this->trimNumber($quantity) . '</cbc:InvoicedQuantity><cbc:LineExtensionAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxableAmount) . '</cbc:LineExtensionAmount><cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) ($item['precio_unitario'] ?? 0)) . '</cbc:PriceAmount><cbc:PriceTypeCode listName="Tipo de Precio" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference><cac:TaxTotal><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number($totalTaxAmount) . '</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxableAmount) . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxAmount) . '</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">' . $this->escape($categoryId) . '</cbc:ID><cbc:Percent>18</cbc:Percent><cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . $this->escape($affectationCode) . '</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT">' . $this->escape($taxSchemeId) . '</cbc:ID><cbc:Name>' . $this->escape($taxName) . '</cbc:Name><cbc:TaxTypeCode>' . $this->escape($taxTypeCode) . '</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';

        if ((float) ($item['icbper'] ?? 0) > 0) {
            $xml .= '<cac:TaxSubtotal><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) $item['icbper']) . '</cbc:TaxAmount><cbc:BaseUnitMeasure unitCode="' . $this->escape($unitCode) . '">' . $this->trimNumber($quantity) . '</cbc:BaseUnitMeasure><cac:TaxCategory><cbc:PerUnitAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) ($item['factor_icbper'] ?? 0)) . '</cbc:PerUnitAmount><cac:TaxScheme><cbc:ID>7152</cbc:ID><cbc:Name>ICBPER</cbc:Name><cbc:TaxTypeCode>OTH</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
        }

        $xml .= '</cac:TaxTotal><cac:Item><cbc:Description><![CDATA[' . trim((string) ($item['descripcion'] ?? '')) . ']]></cbc:Description><cac:SellersItemIdentification><cbc:ID><![CDATA[' . trim((string) ($item['codigo_interno'] ?? '')) . ']]></cbc:ID></cac:SellersItemIdentification></cac:Item><cac:Price><cbc:PriceAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) ($item['valor_unitario'] ?? 0)) . '</cbc:PriceAmount></cac:Price></cac:InvoiceLine>';

        return $xml;
    }

    private function creditNoteLineBlock(array $item, string $currency, int $lineNumber): string
    {
        $affectationCode = trim((string) ($item['afectacion_igv']['codigo'] ?? '10'));
        [$categoryId, $taxSchemeId, $taxName, $taxTypeCode] = $this->codesForAffectation($affectationCode);
        $taxableAmount = (float) ($item['valor_total'] ?? 0);
        $taxAmount = (float) ($item['igv'] ?? 0);
        $totalTaxAmount = $taxAmount + (float) ($item['icbper'] ?? 0);
        $unitCode = trim((string) ($item['unidad'] ?? 'NIU'));
        $quantity = (float) ($item['cantidad'] ?? 0);

        $xml = '<cac:CreditNoteLine><cbc:ID>' . $lineNumber . '</cbc:ID><cbc:CreditedQuantity unitCode="' . $this->escape($unitCode) . '">' . $this->trimNumber($quantity) . '</cbc:CreditedQuantity><cbc:LineExtensionAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxableAmount) . '</cbc:LineExtensionAmount><cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) ($item['precio_unitario'] ?? 0)) . '</cbc:PriceAmount><cbc:PriceTypeCode listName="Tipo de Precio" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference><cac:TaxTotal><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number($totalTaxAmount) . '</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxableAmount) . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxAmount) . '</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">' . $this->escape($categoryId) . '</cbc:ID><cbc:Percent>18</cbc:Percent><cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . $this->escape($affectationCode) . '</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT">' . $this->escape($taxSchemeId) . '</cbc:ID><cbc:Name>' . $this->escape($taxName) . '</cbc:Name><cbc:TaxTypeCode>' . $this->escape($taxTypeCode) . '</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';

        if ((float) ($item['icbper'] ?? 0) > 0) {
            $xml .= '<cac:TaxSubtotal><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) $item['icbper']) . '</cbc:TaxAmount><cbc:BaseUnitMeasure unitCode="' . $this->escape($unitCode) . '">' . $this->trimNumber($quantity) . '</cbc:BaseUnitMeasure><cac:TaxCategory><cbc:PerUnitAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) ($item['factor_icbper'] ?? 0)) . '</cbc:PerUnitAmount><cac:TaxScheme><cbc:ID>7152</cbc:ID><cbc:Name>ICBPER</cbc:Name><cbc:TaxTypeCode>OTH</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
        }

        $xml .= '</cac:TaxTotal><cac:Item><cbc:Description><![CDATA[' . trim((string) ($item['descripcion'] ?? '')) . ']]></cbc:Description></cac:Item><cac:Price><cbc:PriceAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) ($item['valor_unitario'] ?? 0)) . '</cbc:PriceAmount></cac:Price></cac:CreditNoteLine>';

        return $xml;
    }

    private function debitNoteLineBlock(array $item, string $currency, int $lineNumber): string
    {
        $affectationCode = trim((string) ($item['afectacion_igv']['codigo'] ?? '10'));
        [$categoryId, $taxSchemeId, $taxName, $taxTypeCode] = $this->codesForAffectation($affectationCode);
        $taxableAmount = (float) ($item['valor_total'] ?? 0);
        $taxAmount = (float) ($item['igv'] ?? 0);
        $totalTaxAmount = $taxAmount + (float) ($item['icbper'] ?? 0);
        $unitCode = trim((string) ($item['unidad'] ?? 'NIU'));
        $quantity = (float) ($item['cantidad'] ?? 0);

        $xml = '<cac:DebitNoteLine><cbc:ID>' . $lineNumber . '</cbc:ID><cbc:DebitedQuantity unitCode="' . $this->escape($unitCode) . '">' . $this->trimNumber($quantity) . '</cbc:DebitedQuantity><cbc:LineExtensionAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxableAmount) . '</cbc:LineExtensionAmount><cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) ($item['precio_unitario'] ?? 0)) . '</cbc:PriceAmount><cbc:PriceTypeCode listName="Tipo de Precio" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference><cac:TaxTotal><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number($totalTaxAmount) . '</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxableAmount) . '</cbc:TaxableAmount><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number($taxAmount) . '</cbc:TaxAmount><cac:TaxCategory><cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">' . $this->escape($categoryId) . '</cbc:ID><cbc:Percent>18</cbc:Percent><cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . $this->escape($affectationCode) . '</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID schemeID="UN/ECE 5153" schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT">' . $this->escape($taxSchemeId) . '</cbc:ID><cbc:Name>' . $this->escape($taxName) . '</cbc:Name><cbc:TaxTypeCode>' . $this->escape($taxTypeCode) . '</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';

        if ((float) ($item['icbper'] ?? 0) > 0) {
            $xml .= '<cac:TaxSubtotal><cbc:TaxAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) $item['icbper']) . '</cbc:TaxAmount><cbc:BaseUnitMeasure unitCode="' . $this->escape($unitCode) . '">' . $this->trimNumber($quantity) . '</cbc:BaseUnitMeasure><cac:TaxCategory><cbc:PerUnitAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) ($item['factor_icbper'] ?? 0)) . '</cbc:PerUnitAmount><cac:TaxScheme><cbc:ID>7152</cbc:ID><cbc:Name>ICBPER</cbc:Name><cbc:TaxTypeCode>OTH</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal>';
        }

        $xml .= '</cac:TaxTotal><cac:Item><cbc:Description><![CDATA[' . trim((string) ($item['descripcion'] ?? '')) . ']]></cbc:Description></cac:Item><cac:Price><cbc:PriceAmount currencyID="' . $this->escape($currency) . '">' . $this->number((float) ($item['valor_unitario'] ?? 0)) . '</cbc:PriceAmount></cac:Price></cac:DebitNoteLine>';

        return $xml;
    }

    private function codesForAffectation(string $code): array
    {
        return match ($code) {
            '20' => ['E', '9997', 'EXO', 'VAT'],
            '30' => ['O', '9998', 'INA', 'FRE'],
            default => ['S', '1000', 'IGV', 'VAT'],
        };
    }

    private function number(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function trimNumber(float $value): string
    {
        $formatted = number_format($value, 10, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
