<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\IdentityDocumentType;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::updateOrCreate(
            ['nro_documento' => '00000000'],
            [
                'iddoc' => IdentityDocumentType::where('codigo', '1')->value('id'),
                'nombres' => 'CLIENTES VARIOS',
                'direccion' => '-',
                'codigo_pais' => 'PE',
                'ubigeo' => '220901',
                'telefono' => '950772205',
                'email' => null,
            ]
        );
    }
}
