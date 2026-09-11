<?php

namespace Database\Seeders;

use App\Models\TypeDocument;
use Illuminate\Database\Seeder;

class TypeDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codigo' => '01', 'descripcion' => 'FACTURA ELECTRONICA', 'estado' => 1],
            ['codigo' => '03', 'descripcion' => 'BOLETA DE VENTA ELECTRONICA', 'estado' => 1],
            ['codigo' => '04', 'descripcion' => 'LIQUIDACION DE COMPRA', 'estado' => 0],
            ['codigo' => '06', 'descripcion' => 'CARTA DE PORTE AEREO', 'estado' => 0],
            ['codigo' => '07', 'descripcion' => 'NOTA DE CREDITO ELECTRONICA', 'estado' => 1],
            ['codigo' => '02', 'descripcion' => 'NOTA DE VENTA', 'estado' => 1],
            ['codigo' => '08', 'descripcion' => 'NOTA DE DEBITO ELECTRONICA', 'estado' => 0],
            ['codigo' => '09', 'descripcion' => 'GUIA DE REMISION REMITENTE', 'estado' => 1],
        ];

        foreach ($rows as $row) {
            TypeDocument::updateOrCreate(['codigo' => $row['codigo']], $row);
        }
    }
}
