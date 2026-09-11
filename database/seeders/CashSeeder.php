<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CashSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cashes =
        [
            [
                'descripcion'         => 'CAJA 1'
            ],
            [
                'descripcion'         => 'CAJA 2'
            ],
        ];

        foreach($cashes as $cash) {
            $new_cash     = new \App\Models\Cash();
            foreach($cash as $k => $value)
            {
                $new_cash->{$k} = $value;
            }

            $new_cash->save();
        }
    }
}
