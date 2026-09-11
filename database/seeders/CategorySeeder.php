<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['descripcion'  => 'GENERAL'],
            ['descripcion'  => 'GENERAL'],
            ['descripcion'  => 'ABARROTES'],
            ['descripcion'  => 'LIMPIEZA'],
            ['descripcion'  => 'BEBIDAS'],
            ['descripcion'  => 'LÁCTEOS Y EMBUTIDOS'],
            ['descripcion'  => 'SNACKS'],
            ['descripcion'  => 'SERVICIOS'],
            ['descripcion'  => 'CUIDADO PERSONAL'],
        ];

        foreach ($categories as $category) {
            $new_category     = new \App\Models\Category();
            foreach ($category as $k => $value) {
                $new_category->{$k} = $value;
            }

            $new_category->save();
        }
    }
}
