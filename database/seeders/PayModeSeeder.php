<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PayModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pay_modes     =
        [
            [
                'descripcion'             => 'Efectivo'
            ],

            [
                'descripcion'             => 'Yape'
            ],

            [
                'descripcion'             => 'Plin'
            ],
            [
                'descripcion'             => 'Tunki'
            ],
            [
                'descripcion'             => 'Tarjeta de crédito'
            ],
            [
                'descripcion'             => 'Tarjeta de débito'
            ],
            [
                'descripcion'             => 'Transferencia'
            ],
            [
                'descripcion'             => 'Cheque'
            ],
        ];

        foreach($pay_modes as $pay_mode)
        {
            $new_pay_mode  = new \App\Models\PayMode();
            foreach($pay_mode as $k => $value)
            {
                $new_pay_mode->{$k}    = $value;
            }

            $new_pay_mode->save();
        }
    }
}
