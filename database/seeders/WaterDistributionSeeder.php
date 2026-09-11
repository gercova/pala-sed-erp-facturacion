<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\LoyaltyPromotion;
use App\Models\Product;
use App\Models\StockProduct;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WaterDistributionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $warehouse = Warehouse::orderBy('id')->first();
        $unitNiu = Unit::where('codigo', 'NIU')->first() ?? Unit::first();
        $warehouseId = $warehouse?->id ?? 1;

        // Categoría de Agua y Bidones
        $category = Category::firstOrCreate(
            ['descripcion' => 'AGUA Y BIDONES']
        );

        // Productos esenciales
        $waterProducts = [
            [
                'codigo_interno' => 'AGUA-001',
                'codigo_barras' => '7759000000001',
                'codigo_sunat' => '50202301',
                'descripcion' => 'RECARGA DE AGUA 20L',
                'idunidad' => $unitNiu->id,
                'idcategoria' => $category->id,
                'igv' => 18.00,
                'idcodigo_igv' => 1,
                'precio_compra' => 4.00,
                'precio_venta' => 15.00,
                'opcion' => 1,
                'stock_actual' => 200,
            ],
            [
                'codigo_interno' => 'AGUA-002',
                'codigo_barras' => '7759000000002',
                'codigo_sunat' => '50202301',
                'descripcion' => 'BIDON NUEVO 20L CON AGUA',
                'idunidad' => $unitNiu->id,
                'idcategoria' => $category->id,
                'igv' => 18.00,
                'idcodigo_igv' => 1,
                'precio_compra' => 16.00,
                'precio_venta' => 35.00,
                'opcion' => 1,
                'stock_actual' => 100,
            ],
            [
                'codigo_interno' => 'AGUA-003',
                'codigo_barras' => '7759000000003',
                'codigo_sunat' => '24111507',
                'descripcion' => 'ENVASE VACIO 20L RETORNABLE',
                'idunidad' => $unitNiu->id,
                'idcategoria' => $category->id,
                'igv' => 18.00,
                'idcodigo_igv' => 1,
                'precio_compra' => 12.00,
                'precio_venta' => 20.00,
                'opcion' => 1,
                'stock_actual' => 150,
            ],
            [
                'codigo_interno' => 'AGUA-004',
                'codigo_barras' => '7759000000004',
                'codigo_sunat' => '40161500',
                'descripcion' => 'DISPENSADOR DE MESA PARA BIDON',
                'idunidad' => $unitNiu->id,
                'idcategoria' => $category->id,
                'igv' => 18.00,
                'idcodigo_igv' => 1,
                'precio_compra' => 14.00,
                'precio_venta' => 25.00,
                'opcion' => 1,
                'stock_actual' => 50,
            ],
            [
                'codigo_interno' => 'AGUA-005',
                'codigo_barras' => '7759000000005',
                'codigo_sunat' => '40161500',
                'descripcion' => 'BOMBA ELECTRICA USB PARA BIDON',
                'idunidad' => $unitNiu->id,
                'idcategoria' => $category->id,
                'igv' => 18.00,
                'idcodigo_igv' => 1,
                'precio_compra' => 18.00,
                'precio_venta' => 30.00,
                'opcion' => 1,
                'stock_actual' => 60,
            ],
        ];

        $recargaProduct = null;

        foreach ($waterProducts as $pData) {
            $product = Product::updateOrCreate(
                ['codigo_interno' => $pData['codigo_interno']],
                $pData
            );

            if ($product->codigo_interno === 'AGUA-001') {
                $recargaProduct = $product;
            }

            // Asignar stock en almacén principal
            StockProduct::updateOrCreate(
                [
                    'idproducto' => $product->id,
                    'idalmacen' => $warehouseId,
                ],
                [
                    'stock_minimo' => 10,
                    'stock_actual' => $pData['stock_actual'],
                    'precio_compra' => $pData['precio_compra'],
                    'precio_venta' => $pData['precio_venta'],
                    'fecha_registro' => $now->toDateString(),
                    'stock_entrada' => $pData['stock_actual'],
                ]
            );
        }

        // Promoción de Fidelización por Defecto (Compra 4, 5to Gratis)
        if ($recargaProduct) {
            LoyaltyPromotion::updateOrCreate(
                ['nombre' => 'Fidelidad 4+1: Por cada 4 recargas, ¡la 5ta es GRATIS!'],
                [
                    'meta_compras' => 4,
                    'bonificacion' => 1,
                    'idproducto_objetivo' => $recargaProduct->id,
                    'idproducto_bonificado' => $recargaProduct->id,
                    'activo' => true,
                    'descripcion' => 'Promoción especial para clientes recurrentes. Acumula 4 recargas de bidón y la quinta unidad no tiene costo.',
                ]
            );
        }

        // Repartidor de prueba
        $repartidorUser = User::updateOrCreate(
            ['user' => 'repartidor1'],
            [
                'nombres' => 'CARLOS MENDEZ (REPARTIDOR)',
                'password' => 'repartidor123.',
                'estado' => 1,
                'idcaja' => 1,
                'idalmacen' => $warehouseId,
            ]
        );
        $repartidorUser->syncRoles(['REPARTIDOR']);
        $repartidorUser->warehouses()->sync([$warehouseId]);
    }
}
