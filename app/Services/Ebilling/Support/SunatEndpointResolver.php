<?php

namespace App\Services\Ebilling\Support;

use App\Models\Business;

class SunatEndpointResolver
{
    public function forBusiness(Business $business): array
    {
        $serverCode = SunatServer::normalize($business->servidor_sunat);

        return [
            'server_code' => $serverCode,
            'environment' => SunatServer::environment($serverCode),
            'label' => SunatServer::label($serverCode),
            'invoice_wsdl' => $this->invoiceWsdl($serverCode),
            'invoice_endpoint' => $this->invoiceEndpoint($serverCode),
        ];
    }

    public function invoiceWsdl(string|int|null $serverCode): string
    {
        return match (SunatServer::normalize($serverCode)) {
            SunatServer::PRODUCTION => 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService?wsdl',
            SunatServer::BETA => 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl',
        };
    }

    public function invoiceEndpoint(string|int|null $serverCode): string
    {
        return match (SunatServer::normalize($serverCode)) {
            SunatServer::PRODUCTION => 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService',
            SunatServer::BETA => 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService',
        };
    }
}
