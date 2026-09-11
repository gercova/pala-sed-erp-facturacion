<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses =
            [
                [
                    'descripcion'           => 'ALMACÉN PRINCIPAL',
                    'direccion'             => 'AVENIDA JORGE CHAVEZ 8686'
                ],

                [
                    'descripcion'           => 'VENTAS 02',
                    'direccion'             => 'JR JOSE OLAYA 3366'
                ],
            ];

        foreach ($warehouses as $store) {
            $new_store     = new \App\Models\Warehouse();
            foreach ($store as $k => $value) {
                $new_store->{$k} = $value;
            }
            $new_store->save();
        }
    }
}
