<?php

namespace Database\Seeders;

use App\Models\CreditNoteType;
use Illuminate\Database\Seeder;

class CreditNoteTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codigo' => '01', 'descripcion' => 'ANULACION DE LA OPERACION', 'estado' => true],
            ['codigo' => '02', 'descripcion' => 'ANULACION POR ERROR EN EL RUC', 'estado' => true],
            ['codigo' => '03', 'descripcion' => 'CORRECCION POR ERROR EN LA DESCRIPCION', 'estado' => true],
            ['codigo' => '06', 'descripcion' => 'DEVOLUCION TOTAL', 'estado' => true],
            ['codigo' => '07', 'descripcion' => 'DEVOLUCION POR ITEM', 'estado' => true],
            ['codigo' => '10', 'descripcion' => 'OTROS CONCEPTOS', 'estado' => true],
        ];

        foreach ($rows as $row) {
            CreditNoteType::updateOrCreate(['codigo' => $row['codigo']], $row);
        }
    }
}
