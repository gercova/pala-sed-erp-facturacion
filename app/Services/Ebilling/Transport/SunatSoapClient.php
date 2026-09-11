<?php

namespace App\Services\Ebilling\Transport;

use DOMDocument;
use InvalidArgumentException;

class SunatSoapClient
{
    public function sendBill(string $wsdl, string $soapEnvelope, string $caInfoPath): array
    {
        $headers = [
            'Content-type: text/xml; charset="utf-8"',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: ',
            'Content-length: ' . strlen($soapEnvelope),
            'Connection: close',
            'Expect:',
        ];

        $response = false;
        $curlError = '';
        $httpCode = 0;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($curl, CURLOPT_URL, $wsdl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT, 90);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $soapEnvelope);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
            curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);

            if (defined('CURLOPT_SSLVERSION') && defined('CURL_SSLVERSION_TLSv1_2')) {
                curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            }

            if (is_file($caInfoPath)) {
                curl_setopt($curl, CURLOPT_CAINFO, $caInfoPath);
            }

            $response = curl_exec($curl);
            $curlError = curl_error($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($response !== false && $curlError === '') {
                break;
            }

            if (! $this->isTransientTransportError($curlError) || $attempt === 3) {
                break;
            }

            usleep(500000 * $attempt);
        }

        if ($response === false || $curlError !== '') {
            throw new InvalidArgumentException('Error CURL al enviar a SUNAT: ' . $curlError);
        }

        if (trim((string) $response) === '') {
            return [
                'ok' => false,
                'http_code' => $httpCode,
                'response_xml' => '',
                'fault_code' => null,
                'fault_string' => 'SUNAT no devolvio contenido en la respuesta.',
            ];
        }

        $document = new DOMDocument();
        $loaded = @$document->loadXML($response);

        if (! $loaded) {
            return [
                'ok' => false,
                'http_code' => $httpCode,
                'response_xml' => $response,
                'fault_code' => null,
                'fault_string' => 'SUNAT devolvio una respuesta XML invalida.',
            ];
        }

        if ($httpCode !== 200) {
            return [
                'ok' => false,
                'http_code' => $httpCode,
                'response_xml' => $response,
                'fault_code' => optionalDomNodeValue($document, 'faultcode'),
                'fault_string' => optionalDomNodeValue($document, 'faultstring'),
            ];
        }

        $applicationResponse = optionalDomNodeValue($document, 'applicationResponse');

        if ($applicationResponse === null) {
            return [
                'ok' => false,
                'http_code' => $httpCode,
                'response_xml' => $response,
                'fault_code' => optionalDomNodeValue($document, 'faultcode'),
                'fault_string' => optionalDomNodeValue($document, 'faultstring'),
            ];
        }

        return [
            'ok' => true,
            'http_code' => $httpCode,
            'response_xml' => $response,
            'cdr_zip_base64' => $applicationResponse,
        ];
    }

    private function isTransientTransportError(string $curlError): bool
    {
        $normalized = mb_strtolower(trim($curlError));

        if ($normalized === '') {
            return false;
        }

        return str_contains($normalized, 'connection reset by peer')
            || str_contains($normalized, 'ssl_read')
            || str_contains($normalized, 'operation timed out')
            || str_contains($normalized, 'timeout')
            || str_contains($normalized, 'unexpected eof')
            || str_contains($normalized, 'recv failure');
    }

    public function buildSendBillEnvelope(string $ruc, string $solUser, string $solPassword, string $fileName, string $zipContentBase64): string
    {
        return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">'
            . '<soapenv:Header><wsse:Security><wsse:UsernameToken><wsse:Username>' . htmlspecialchars($ruc . $solUser, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</wsse:Username><wsse:Password>' . htmlspecialchars($solPassword, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</wsse:Password></wsse:UsernameToken></wsse:Security></soapenv:Header>'
            . '<soapenv:Body><ser:sendBill><fileName>' . htmlspecialchars($fileName, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</fileName><contentFile>' . $zipContentBase64 . '</contentFile></ser:sendBill></soapenv:Body>'
            . '</soapenv:Envelope>';
    }
}

function optionalDomNodeValue(DOMDocument $document, string $tagName): ?string
{
    $node = $document->getElementsByTagName($tagName)->item(0);

    return $node ? $node->nodeValue : null;
}
