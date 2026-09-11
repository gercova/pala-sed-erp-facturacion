<?php

namespace Database\Seeders;

use App\Models\Serie;
use App\Models\TypeDocument;
use Illuminate\Database\Seeder;

class SerieSeeder extends Seeder
{
    public function run(): void
    {
        $documentIds = TypeDocument::query()->pluck('id', 'codigo');

        $rows = [
            ['serie' => 'F001', 'correlativo' => '00000001', 'idtipo_documento' => $documentIds['01'] ?? null, 'idtipo_documento_relacionado' => null, 'idcaja' => 1, 'direccion' => null, 'estado' => true],
            ['serie' => 'B001', 'correlativo' => '00000001', 'idtipo_documento' => $documentIds['03'] ?? null, 'idtipo_documento_relacionado' => null, 'idcaja' => 1, 'direccion' => null, 'estado' => true],
            ['serie' => 'NV01', 'correlativo' => '00000001', 'idtipo_documento' => $documentIds['02'] ?? null, 'idtipo_documento_relacionado' => null, 'idcaja' => 1, 'direccion' => null, 'estado' => true],
            ['serie' => 'FC01', 'correlativo' => '00000001', 'idtipo_documento' => $documentIds['07'] ?? null, 'idtipo_documento_relacionado' => $documentIds['01'] ?? null, 'idcaja' => 1, 'direccion' => null, 'estado' => true],
            ['serie' => 'BC01', 'correlativo' => '00000001', 'idtipo_documento' => $documentIds['07'] ?? null, 'idtipo_documento_relacionado' => $documentIds['03'] ?? null, 'idcaja' => 1, 'direccion' => null, 'estado' => true],
            ['serie' => 'T001', 'correlativo' => '00000001', 'idtipo_documento' => $documentIds['09'] ?? null, 'idtipo_documento_relacionado' => null, 'idcaja' => 1, 'direccion' => null, 'estado' => true],
            ['serie' => 'FD01', 'correlativo' => '00000001', 'idtipo_documento' => $documentIds['08'] ?? null, 'idtipo_documento_relacionado' => $documentIds['01'] ?? null, 'idcaja' => 1, 'direccion' => null, 'estado' => true],
            ['serie' => 'BD01', 'correlativo' => '00000001', 'idtipo_documento' => $documentIds['08'] ?? null, 'idtipo_documento_relacionado' => $documentIds['03'] ?? null, 'idcaja' => 1, 'direccion' => null, 'estado' => true],
        ];

        foreach ($rows as $row) {
            if (! $row['idtipo_documento']) {
                continue;
            }

            Serie::updateOrCreate(
                [
                    'serie' => $row['serie'],
                    'idtipo_documento' => $row['idtipo_documento'],
                    'idcaja' => $row['idcaja'],
                ],
                $row
            );
        }
    }
}
