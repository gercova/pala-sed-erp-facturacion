<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class BusineSeeder extends Seeder
{
    public function run(): void
    {
        $payload = [
            'razon_social' => 'MYTEMS E.I.R.L.',
            'nombre_comercial' => 'MYTEMS E.I.R.L.',
            'logo' => 'logo.jpg',
            'idpais' => 1,
            'direccion' => 'JR MANCO CAPAC 452',
            'codigo_pais' => 'PE',
            'ubigeo' => '220501',
            'urbanizacion' => '',
            'local' => '',
            'telefono' => '950772205',
            'url_api' => 'https://facturacion.mytems.cloud/',
            'ruc' => '20610316884',
            'vencimiento_certificado' => '2025-12-06',
            'usuario_sunat' => 'MYTEMS23',
            'clave_sunat' => 'Mytems23',
            'clave_certificado' => 'mytems2022',
            'certificado' => 'api_sunat/20610316884.pfx',
            'servidor_sunat' => '3',
            'gre_client_id' => '',
            'gre_client_secret' => '',
            'instancia_wpp' => 'NTE5NTA3NzIyMDU=',
            'cobrar_igv' => false,
        ];

        Business::updateOrCreate(['id' => 1], $payload);

        Business::query()
            ->where('id', '!=', 1)
            ->where('ruc', '20610316884')
            ->update($payload);
    }
}
