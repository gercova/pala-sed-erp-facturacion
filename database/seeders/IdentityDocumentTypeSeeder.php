<?php

namespace Database\Seeders;

use App\Models\IdentityDocumentType;
use Illuminate\Database\Seeder;

class IdentityDocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codigo' => '1', 'descripcion' => 'DOCUMENTO NACIONAL DE IDENTIDAD (DNI)', 'descripcion_documento' => 'DNI', 'estado' => true],
            ['codigo' => '6', 'descripcion' => 'REGISTRO UNICO DE CONTRIBUYENTES (RUC)', 'descripcion_documento' => 'RUC', 'estado' => true],
            ['codigo' => '4', 'descripcion' => 'CARNET DE EXTRANJERIA', 'descripcion_documento' => 'CARNET DE EXTRANJERIA', 'estado' => true],
            ['codigo' => '0', 'descripcion' => 'OTRO TIPO DE DOCUMENTO', 'descripcion_documento' => 'OTROS', 'estado' => true],
            ['codigo' => '7', 'descripcion' => 'PASAPORTE', 'descripcion_documento' => 'PASAPORTE', 'estado' => true],
            ['codigo' => 'A', 'descripcion' => 'CEDULA DIPLOMATICA DE IDENTIDAD', 'descripcion_documento' => 'CEDULA DIPLOMATICA', 'estado' => true],
        ];

        foreach ($rows as $row) {
            IdentityDocumentType::updateOrCreate(['codigo' => $row['codigo']], $row);
        }
    }
}
