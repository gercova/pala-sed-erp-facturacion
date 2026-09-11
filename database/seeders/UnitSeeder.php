<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units  =
        [
            [
                'codigo'        => '4A',
                'descripcion'   => 'BOBINAS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'BE',
                'descripcion'   => 'FARDO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'BG',
                'descripcion'   => 'BOLSA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'BJ',
                'descripcion'   => 'BALDE',
                'estado'        => 1
            ],

            [
                'codigo'        => 'BLL',
                'descripcion'   => 'BARRILES',
                'estado'        => 1
            ],

            [
                'codigo'        => 'BO',
                'descripcion'   => 'BOTELLAS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'BX',
                'descripcion'   => 'CAJA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'C62',
                'descripcion'   => 'PIEZAS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'CA',
                'descripcion'   => 'LATAS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'CEN',
                'descripcion'   => 'CIENTOS DE UNIDADES',
                'estado'        => 1
            ],

            [
                'codigo'        => 'CJ',
                'descripcion'   => 'CONOS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'CMK',
                'descripcion'   => 'CENTIMETRO CUADRADO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'CMQ',
                'descripcion'   => 'CENTIMETRO CUBICO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'CMT',
                'descripcion'   => 'CENTIMETRO LINEAL',
                'estado'        => 1
            ],

            [
                'codigo'        => 'CT',
                'descripcion'   => 'CARTONES',
                'estado'        => 1
            ],

            [
                'codigo'        => 'CY',
                'descripcion'   => 'CILINDRO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'DR',
                'descripcion'   => 'TAMBOR',
                'estado'        => 1
            ],

            [
                'codigo'        => 'DZN',
                'descripcion'   => 'DOCENA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'DZP',
                'descripcion'   => 'DOCENA POR 10**6',
                'estado'        => 1
            ],

            [
                'codigo'        => 'FOT',
                'descripcion'   => 'PIES',
                'estado'        => 1
            ],

            [
                'codigo'        => 'FTK',
                'descripcion'   => 'PIES CUADRADOS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'GLI',
                'descripcion'   => 'GALON INGLES (4,545956L)',
                'estado'        => 1
            ],

            [
                'codigo'        => 'GLL',
                'descripcion'   => 'US GALON (3,7843 L)',
                'estado'        => 1
            ],

            [
                'codigo'        => 'GRM',
                'descripcion'   => 'GRAMO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'GRO',
                'descripcion'   => 'GRUESA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'HLT',
                'descripcion'   => 'HECTOLITRO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'INH',
                'descripcion'   => 'PULGADAS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'KGM',
                'descripcion'   => 'KILOGRAMO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'KT',
                'descripcion'   => 'KIT',
                'estado'        => 1
            ],

            [
                'codigo'        => 'KTM',
                'descripcion'   => 'KILOMETRO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'KWH',
                'descripcion'   => 'KILOVATIO HORA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'LBR',
                'descripcion'   => 'LIBRAS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'LEF',
                'descripcion'   => 'HOJA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'LTN',
                'descripcion'   => 'TONELADA LARGA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'LTR',
                'descripcion'   => 'LITRO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'MGM',
                'descripcion'   => 'MILIGRAMOS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'MLL',
                'descripcion'   => 'MILLARES',
                'estado'        => 1
            ],

            [
                'codigo'        => 'MLT',
                'descripcion'   => 'MILILITRO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'MMK',
                'descripcion'   => 'MILIMETRO CUADRADO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'MMQ',
                'descripcion'   => 'MILIMETRO CUBICO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'MMT',
                'descripcion'   => 'MILIMETRO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'MTK',
                'descripcion'   => 'METRO CUADRADO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'MTQ',
                'descripcion'   => 'METRO CUBICO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'MTR',
                'descripcion'   => 'METRO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'MWH',
                'descripcion'   => 'MEGAWATT HORA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'NIU',
                'descripcion'   => 'UNIDAD (BIENES)',
                'estado'        => 1
            ],

            [
                'codigo'        => 'ONZ',
                'descripcion'   => 'ONZAS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'PF',
                'descripcion'   => 'PALETAS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'PG',
                'descripcion'   => 'PLACAS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'PK',
                'descripcion'   => 'PAQUETE',
                'estado'        => 1
            ],

            [
                'codigo'        => 'PR',
                'descripcion'   => 'PAR',
                'estado'        => 1
            ],

            [
                'codigo'        => 'RM',
                'descripcion'   => 'RESMA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'SET',
                'descripcion'   => 'JUEGO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'ST',
                'descripcion'   => 'PLIEGO',
                'estado'        => 1
            ],

            [
                'codigo'        => 'STN',
                'descripcion'   => 'TONELADA CORTA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'TNE',
                'descripcion'   => 'TONELADAS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'TU',
                'descripcion'   => 'TUBOS',
                'estado'        => 1
            ],

            [
                'codigo'        => 'UM',
                'descripcion'   => 'MILLON DE UNIDADES',
                'estado'        => 1
            ],

            [
                'codigo'        => 'YDK',
                'descripcion'   => 'YARDA CUADRADA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'YRD',
                'descripcion'   => 'YARDA',
                'estado'        => 1
            ],

            [
                'codigo'        => 'ZZ',
                'descripcion'   => 'UNIDAD (SERVICIOS)',
                'estado'        => 1
            ],
        ];

        foreach($units as $unit)
        {
            $new_unit  = new \App\Models\Unit();
            foreach($unit as $k => $value)
            {
                $new_unit->{$k} = $value;
            }

            $new_unit->save();
        }
    }
}
