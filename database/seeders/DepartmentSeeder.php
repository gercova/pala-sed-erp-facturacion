<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['codigo' => '01', 'descripcion' => 'Amazonas'],
            ['codigo' => '02', 'descripcion' => 'Áncash'],
            ['codigo' => '03', 'descripcion' => 'Apurímac'],
            ['codigo' => '04', 'descripcion' => 'Arequipa'],
            ['codigo' => '05', 'descripcion' => 'Ayacucho'],
            ['codigo' => '06', 'descripcion' => 'Cajamarca'],
            ['codigo' => '07', 'descripcion' => 'Callao'],
            ['codigo' => '08', 'descripcion' => 'Cusco'],
            ['codigo' => '09', 'descripcion' => 'Huancavelica'],
            ['codigo' => '10', 'descripcion' => 'Huánuco'],
            ['codigo' => '11', 'descripcion' => 'Ica'],
            ['codigo' => '12', 'descripcion' => 'Junín'],
            ['codigo' => '13', 'descripcion' => 'La Libertad'],
            ['codigo' => '14', 'descripcion' => 'Lambayeque'],
            ['codigo' => '15', 'descripcion' => 'Lima'],
            ['codigo' => '16', 'descripcion' => 'Loreto'],
            ['codigo' => '17', 'descripcion' => 'Madre de Dios'],
            ['codigo' => '18', 'descripcion' => 'Moquegua'],
            ['codigo' => '19', 'descripcion' => 'Pasco'],
            ['codigo' => '20', 'descripcion' => 'Piura'],
            ['codigo' => '21', 'descripcion' => 'Puno'],
            ['codigo' => '22', 'descripcion' => 'San Martín'],
            ['codigo' => '23', 'descripcion' => 'Tacna'],
            ['codigo' => '24', 'descripcion' => 'Tumbes'],
            ['codigo' => '25', 'descripcion' => 'Ucayali'],
        ];

        foreach($departments as $department) {
            \App\Models\Department::updateOrCreate(
                ['codigo' => $department['codigo']],
                $department
            );
        } 
    }
}
