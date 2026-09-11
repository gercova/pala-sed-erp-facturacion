<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codigo' => 'PEN', 'descripcion' => 'SOL', 'pais' => 'PERU', 'simbolo' => 'S/', 'estado' => true],
            ['codigo' => 'USD', 'descripcion' => 'US DOLLAR', 'pais' => 'ESTADOS UNIDOS', 'simbolo' => '$', 'estado' => true],
            ['codigo' => 'EUR', 'descripcion' => 'EURO', 'pais' => 'ESPANA', 'simbolo' => 'EUR', 'estado' => true],
        ];

        foreach ($rows as $row) {
            Currency::updateOrCreate(['codigo' => $row['codigo']], $row);
        }
    }
}
