<?php

namespace Database\Seeders;

use App\Models\DebitNoteType;
use Illuminate\Database\Seeder;

class DebitNoteTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codigo' => '01', 'descripcion' => 'INTERESES POR MORA', 'estado' => 1],
            ['codigo' => '02', 'descripcion' => 'AUMENTO EN EL VALOR', 'estado' => 1],
            ['codigo' => '03', 'descripcion' => 'PENALIDADES / OTROS CONCEPTOS', 'estado' => 1],
            ['codigo' => '11', 'descripcion' => 'AJUSTES DE OPERACIONES DE EXPORTACION', 'estado' => 1],
            ['codigo' => '12', 'descripcion' => 'AJUSTES AFECTOS AL IVAP', 'estado' => 1],
        ];

        foreach ($rows as $row) {
            DebitNoteType::updateOrCreate(['codigo' => $row['codigo']], $row);
        }
    }
}
