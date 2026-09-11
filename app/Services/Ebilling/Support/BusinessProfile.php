<?php

namespace App\Services\Ebilling\Support;

use App\Models\Business;

class BusinessProfile
{
    public function __construct(
        private readonly BusinessCertificatePath $certificatePath,
        private readonly SunatEndpointResolver $endpointResolver,
    ) {
    }

    public function fromModel(Business $business): array
    {
        return [
            'emisor' => [
                'tipo_documento' => '6',
                'ruc' => (string) ($business->ruc ?? ''),
                'razon_social' => (string) ($business->razon_social ?? ''),
                'nombre_comercial' => (string) ($business->nombre_comercial ?? ''),
                'direccion' => (string) ($business->direccion ?? ''),
                'ubigeo' => (string) ($business->ubigeo ?? ''),
                'codigo_pais' => (string) ($business->codigo_pais ?: 'PE'),
            ],
            'sol' => [
                'usuario' => (string) ($business->usuario_sunat ?? ''),
                'clave' => (string) ($business->clave_sunat ?? ''),
            ],
            'certificado' => [
                'ruta' => $this->certificatePath->relativePathFor($business),
                'ruta_absoluta' => $this->certificatePath->absolutePathFor($business),
                'clave' => (string) ($business->clave_certificado ?? ''),
            ],
            'sunat' => [
                'server_code' => (string) ($business->servidor_sunat ?? ''),
                'environment' => blank($business->servidor_sunat) ? null : SunatServer::environment($business->servidor_sunat),
                'label' => blank($business->servidor_sunat) ? null : SunatServer::label($business->servidor_sunat),
                'endpoints' => blank($business->servidor_sunat) ? null : $this->endpointResolver->forBusiness($business),
            ],
        ];
    }
}
