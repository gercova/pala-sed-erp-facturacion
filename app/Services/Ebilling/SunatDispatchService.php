<?php

namespace App\Services\Ebilling;

use App\Models\Billing;
use App\Models\Business;
use App\Services\Ebilling\Cdr\CdrParser;
use App\Services\Ebilling\Payload\BillingPayloadBuilder;
use App\Services\Ebilling\Signing\XmlSigner;
use App\Services\Ebilling\Support\BusinessCertificatePath;
use App\Services\Ebilling\Support\BusinessStoragePath;
use App\Services\Ebilling\Support\SunatEndpointResolver;
use App\Services\Ebilling\Transport\SunatSoapClient;
use App\Services\Ebilling\Validation\PayloadValidator;
use App\Services\Ebilling\Xml\InvoiceXmlBuilder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ZipArchive;

class SunatDispatchService
{
    public function __construct(
        private readonly BillingPayloadBuilder $payloadBuilder,
        private readonly PayloadValidator $validator,
        private readonly InvoiceXmlBuilder $xmlBuilder,
        private readonly XmlSigner $xmlSigner,
        private readonly SunatSoapClient $soapClient,
        private readonly CdrParser $cdrParser,
        private readonly SunatEndpointResolver $endpointResolver,
        private readonly BusinessCertificatePath $certificatePath,
        private readonly BusinessStoragePath $storagePath,
    ) {
    }

    public function dispatch(Billing $billing): array
    {
        $business = Business::query()->findOrFail(1);
        $payload = $this->payloadBuilder->build($billing);
        $validation = $this->validator->validate($payload);

        if (! $validation->isValid()) {
            throw new InvalidArgumentException('El comprobante no paso las validaciones SUNAT previas al envio.');
        }

        $this->ensureDispatchPrerequisites($business);

        $directories = $this->ensureDirectories($business);
        $fileBaseName = $business->ruc . '-' . $payload['documento']['tipo'] . '-' . $payload['documento']['serie'] . '-' . $payload['documento']['correlativo'];
        $xmlPath = $directories['xml'] . DIRECTORY_SEPARATOR . $fileBaseName . '.XML';
        $zipPath = $directories['zip'] . DIRECTORY_SEPARATOR . $fileBaseName . '.ZIP';
        $cdrZipPath = $directories['cdr'] . DIRECTORY_SEPARATOR . 'R-' . $fileBaseName . '.ZIP';
        $cdrExtractPath = $directories['cdr'] . DIRECTORY_SEPARATOR . 'R-' . $fileBaseName;
        $soapRequestPath = $directories['soap'] . DIRECTORY_SEPARATOR . $fileBaseName . '-request.xml';
        $soapResponsePath = $directories['soap'] . DIRECTORY_SEPARATOR . $fileBaseName . '-response.xml';

        file_put_contents($xmlPath, $this->xmlBuilder->build($payload, $business));

        $signatureResult = $this->xmlSigner->sign(
            $xmlPath,
            (string) $this->certificatePath->absolutePathFor($business),
            (string) $business->clave_certificado
        );

        $this->zipXml($xmlPath, $zipPath, $fileBaseName . '.XML');

        $zipContent = base64_encode((string) file_get_contents($zipPath));
        $endpoints = $this->endpointResolver->forBusiness($business);
        $soapEnvelope = $this->soapClient->buildSendBillEnvelope(
            (string) $business->ruc,
            (string) $business->usuario_sunat,
            (string) $business->clave_sunat,
            $fileBaseName . '.ZIP',
            $zipContent
        );

        file_put_contents($soapRequestPath, $soapEnvelope);

        $soapResult = $this->soapClient->sendBill(
            $endpoints['invoice_endpoint'],
            $soapEnvelope,
            public_path('api_sunat/cacert.pem')
        );

        file_put_contents($soapResponsePath, (string) ($soapResult['response_xml'] ?? ''));

        if (! ($soapResult['ok'] ?? false)) {
            $errorMessage = $this->formatSoapErrorMessage($soapResult, $endpoints);
            $billing->update([
                'cdr' => 0,
                'estado_cpe' => null,
                'errores' => $errorMessage,
            ]);

            return [
                'ok' => false,
                'message' => $errorMessage,
                'http_code' => $soapResult['http_code'] ?? null,
                'endpoint' => $endpoints['invoice_endpoint'],
            ];
        }

        file_put_contents($cdrZipPath, base64_decode((string) $soapResult['cdr_zip_base64']));
        $cdr = $this->cdrParser->extract($cdrZipPath, $cdrExtractPath);
        $responseCode = is_numeric($cdr['response_code'] ?? null) ? (int) $cdr['response_code'] : null;
        $description = (string) ($cdr['description'] ?? '');

        $billing->update([
            'cdr' => 1,
            'estado_cpe' => $responseCode,
            'errores' => $description !== '' ? $description : null,
        ]);

        if ($responseCode === 0) {
            $this->handleAcceptedCreditNote($billing);
        }

        return [
            'ok' => $responseCode === 0,
            'message' => $description !== '' ? $description : 'SUNAT devolvio respuesta sin descripcion.',
            'endpoint' => $endpoints['invoice_endpoint'],
            'environment' => $endpoints['environment'],
            'hash_cpe' => $signatureResult['hash_cpe'] ?? null,
            'firma_cpe' => $signatureResult['firma_cpe'] ?? null,
            'cdr' => $cdr,
        ];
    }

    private function ensureDispatchPrerequisites(Business $business): void
    {
        if (blank($business->ruc) || blank($business->usuario_sunat) || blank($business->clave_sunat)) {
            throw new InvalidArgumentException('La empresa no tiene configuradas sus credenciales SOL completas.');
        }

        if (blank($business->certificado) || blank($business->clave_certificado)) {
            throw new InvalidArgumentException('La empresa no tiene configurado su certificado digital.');
        }

        $certificatePath = $this->certificatePath->absolutePathFor($business);

        if (! $certificatePath || ! is_file($certificatePath)) {
            throw new InvalidArgumentException('No se encontro el certificado digital configurado para la empresa.');
        }
    }

    private function ensureDirectories(Business $business): array
    {
        $directories = [
            'xml' => $this->storagePath->xmlDirectory($business),
            'zip' => $this->storagePath->zipDirectory($business),
            'cdr' => $this->storagePath->cdrDirectory($business),
            'soap' => $this->storagePath->soapDirectory($business),
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                throw new InvalidArgumentException('No se pudo crear una carpeta de trabajo para eBilling.');
            }
        }

        return $directories;
    }

    private function zipXml(string $xmlPath, string $zipPath, string $entryName): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new InvalidArgumentException('No se pudo crear el ZIP del comprobante.');
        }

        $zip->addFile($xmlPath, $entryName);
        $zip->close();
    }

    private function formatSoapErrorMessage(array $soapResult, array $endpoints): string
    {
        $parts = [];

        if (filled($soapResult['fault_string'] ?? null)) {
            $parts[] = trim((string) $soapResult['fault_string']);
        } else {
            $parts[] = 'SUNAT devolvio un error sin detalle.';
        }

        if (filled($soapResult['fault_code'] ?? null)) {
            $parts[] = 'codigo: ' . trim((string) $soapResult['fault_code']);
        }

        if (filled($soapResult['http_code'] ?? null)) {
            $parts[] = 'http: ' . (int) $soapResult['http_code'];
        }

        if (filled($endpoints['label'] ?? null)) {
            $parts[] = 'ambiente: ' . trim((string) $endpoints['label']);
        }

        return implode(' | ', $parts);
    }

    private function handleAcceptedCreditNote(Billing $billing): void
    {
        $billing->loadMissing(['typeDocument', 'creditNoteType', 'parentBilling']);

        if (
            (string) ($billing->typeDocument?->codigo ?? '') !== '07'
            || (int) ($billing->idfactura_anular ?? 0) <= 0
            || ! in_array((string) ($billing->creditNoteType?->codigo ?? ''), ['01', '02'], true)
        ) {
            return;
        }

        $parentBilling = $billing->parentBilling;

        if (! $parentBilling || (bool) $parentBilling->anulado) {
            return;
        }

        DB::transaction(function () use ($parentBilling) {
            $parentBilling->update(['anulado' => true]);
        });
    }
}
