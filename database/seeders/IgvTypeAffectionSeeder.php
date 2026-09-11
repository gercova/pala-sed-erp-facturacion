<?php

namespace Database\Seeders;

use App\Models\IgvTypeAffection;
use Illuminate\Database\Seeder;

class IgvTypeAffectionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codigo' => '10', 'descripcion' => 'Gravado - Operacion Onerosa', 'tipo' => 'GRAV', 'estado' => true],
            ['codigo' => '20', 'descripcion' => 'Exonerado - Operacion Onerosa', 'tipo' => 'EXO', 'estado' => true],
            ['codigo' => '30', 'descripcion' => 'Inafecto - Operacion Onerosa', 'tipo' => 'INA', 'estado' => true],
            ['codigo' => '40', 'descripcion' => 'Exportacion', 'tipo' => 'EXP', 'estado' => false],
        ];

        foreach ($rows as $row) {
            IgvTypeAffection::updateOrCreate(['codigo' => $row['codigo']], $row);
        }
    }
}
