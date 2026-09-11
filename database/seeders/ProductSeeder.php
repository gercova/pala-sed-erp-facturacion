<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $warehouseId = (int) DB::table('warehouses')->orderBy('id')->value('id');
        $unitProductId = $this->getUnitIdByCode('NIU');
        $unitServiceId = $this->getUnitIdByCode('ZZ');
        $gravadoId = $this->getIgvTypeAffectionIdByCode('10');
        $exoneradoId = $this->getIgvTypeAffectionIdByCode('20');

        if ($warehouseId <= 0 || $unitProductId <= 0 || $unitServiceId <= 0 || $gravadoId <= 0 || $exoneradoId <= 0) {
            throw new \RuntimeException('No se pudo preparar ProductSeeder porque faltan almacenes, unidades o afectaciones IGV base.');
        }

        $products = [
            [
                'codigo_interno' => '000001',
                'codigo_barras' => '7750215397435',
                'codigo_sunat' => '50131701',
                'descripcion' => 'LECHE GLORIA 400G',
                'unit_code' => 'NIU',
                'category' => 'ABARROTES',
                'igv_code' => '10',
                'precio_compra' => 2.00,
                'precio_venta' => 3.00,
                'opcion' => 1,
                'stock_actual' => 40,
            ],
            [
                'codigo_interno' => '000002',
                'codigo_barras' => '7750035012345',
                'codigo_sunat' => '50171707',
                'descripcion' => 'ACEITE PRIMOR 1L',
                'unit_code' => 'NIU',
                'category' => 'ABARROTES',
                'igv_code' => '10',
                'precio_compra' => 7.50,
                'precio_venta' => 9.00,
                'opcion' => 1,
                'stock_actual' => 40,
            ],
            [
                'codigo_interno' => '000003',
                'codigo_barras' => '7750265416789',
                'codigo_sunat' => '50192902',
                'descripcion' => 'FIDEOS DON VITTORIO 500G',
                'unit_code' => 'NIU',
                'category' => 'ABARROTES',
                'igv_code' => '10',
                'precio_compra' => 3.50,
                'precio_venta' => 4.50,
                'opcion' => 1,
                'stock_actual' => 40,
            ],
            [
                'codigo_interno' => '000012',
                'codigo_barras' => '7750789635214',
                'codigo_sunat' => '50202306',
                'descripcion' => 'COCA-COLA BOTELLA 1.5L',
                'unit_code' => 'NIU',
                'category' => 'BEBIDAS',
                'igv_code' => '10',
                'precio_compra' => 4.00,
                'precio_venta' => 5.50,
                'opcion' => 1,
                'stock_actual' => 40,
            ],
            [
                'codigo_interno' => '000027',
                'codigo_barras' => '7752145632147',
                'codigo_sunat' => '53131608',
                'descripcion' => 'SHAMPOO SEDAL RESTAURACION 340ML',
                'unit_code' => 'NIU',
                'category' => 'CUIDADO PERSONAL',
                'igv_code' => '10',
                'precio_compra' => 11.50,
                'precio_venta' => 14.50,
                'opcion' => 1,
                'stock_actual' => 40,
            ],
            [
                'codigo_interno' => '000029',
                'codigo_barras' => '7752365412896',
                'codigo_sunat' => '50131702',
                'descripcion' => 'YOGURT GLORIA FRESA 1L',
                'unit_code' => 'NIU',
                'category' => 'LACTEOS Y EMBUTIDOS',
                'igv_code' => '10',
                'precio_compra' => 5.50,
                'precio_venta' => 7.50,
                'opcion' => 1,
                'stock_actual' => 40,
            ],
            [
                'codigo_interno' => '000030',
                'codigo_barras' => '7752412589634',
                'codigo_sunat' => '50161814',
                'descripcion' => 'CHOCOLATE SUBLIME GALLETA 40G',
                'unit_code' => 'NIU',
                'category' => 'SNACKS',
                'igv_code' => '10',
                'precio_compra' => 1.20,
                'precio_venta' => 2.00,
                'opcion' => 1,
                'stock_actual' => 100,
            ],
            [
                'codigo_interno' => '000031',
                'codigo_barras' => 'SERV-001',
                'codigo_sunat' => '78102203',
                'descripcion' => 'SERVICIO DE DELIVERY LIMA METROPOLITANA',
                'unit_code' => 'ZZ',
                'category' => 'SERVICIOS',
                'igv_code' => '20',
                'precio_compra' => 0.00,
                'precio_venta' => 10.00,
                'opcion' => 2,
                'stock_actual' => 0,
            ],
            [
                'codigo_interno' => '000032',
                'codigo_barras' => 'SERV-002',
                'codigo_sunat' => '72101511',
                'descripcion' => 'MANTENIMIENTO DE VITRINA REFRIGERADA',
                'unit_code' => 'ZZ',
                'category' => 'SERVICIOS',
                'igv_code' => '20',
                'precio_compra' => 50.00,
                'precio_venta' => 85.00,
                'opcion' => 2,
                'stock_actual' => 0,
            ],
            [
                'codigo_interno' => '000033',
                'codigo_barras' => '7752514789632',
                'codigo_sunat' => '50171709',
                'descripcion' => 'MAYONESA ALACENA 190G',
                'unit_code' => 'NIU',
                'category' => 'ABARROTES',
                'igv_code' => '10',
                'precio_compra' => 4.20,
                'precio_venta' => 6.00,
                'opcion' => 1,
                'stock_actual' => 40,
            ],
            [
                'codigo_interno' => '000034',
                'codigo_barras' => '7752636985471',
                'codigo_sunat' => '50192100',
                'descripcion' => 'SNACK LAYS CLASICAS 160G',
                'unit_code' => 'NIU',
                'category' => 'SNACKS',
                'igv_code' => '10',
                'precio_compra' => 6.50,
                'precio_venta' => 8.50,
                'opcion' => 1,
                'stock_actual' => 40,
            ],
            [
                'codigo_interno' => '000035',
                'codigo_barras' => 'SERV-003',
                'codigo_sunat' => '81111811',
                'descripcion' => 'CONSULTORIA TECNICA / INSTALACION',
                'unit_code' => 'ZZ',
                'category' => 'SERVICIOS',
                'igv_code' => '20',
                'precio_compra' => 0.00,
                'precio_venta' => 150.00,
                'opcion' => 2,
                'stock_actual' => 0,
            ],
            [
                'codigo_interno' => '000036',
                'codigo_barras' => '7752741258963',
                'codigo_sunat' => '47131811',
                'descripcion' => 'DETERGENTE ARIEL POWER 800G',
                'unit_code' => 'NIU',
                'category' => 'LIMPIEZA',
                'igv_code' => '10',
                'precio_compra' => 8.50,
                'precio_venta' => 11.20,
                'opcion' => 1,
                'stock_actual' => 30,
            ],
        ];

        foreach ($products as $product) {
            $productId = $this->upsertProduct($product, $now, $unitProductId, $unitServiceId, $gravadoId, $exoneradoId);
            $this->upsertWarehouseStock($product, $productId, $warehouseId, $now);
        }
    }

    private function upsertProduct(
        array $product,
        Carbon $now,
        int $unitProductId,
        int $unitServiceId,
        int $gravadoId,
        int $exoneradoId
    ): int {
        $categoryId = $this->getCategoryIdByName($product['category']);
        $isService = (int) $product['opcion'] === 2;
        $igvTypeId = $product['igv_code'] === '10' ? $gravadoId : $exoneradoId;
        $igvPercent = $product['igv_code'] === '10' ? 18 : 0;

        DB::table('products')->updateOrInsert(
            ['codigo_interno' => $product['codigo_interno']],
            [
                'codigo_barras' => $product['codigo_barras'],
                'codigo_sunat' => $product['codigo_sunat'],
                'descripcion' => $product['descripcion'],
                'idunidad' => $isService ? $unitServiceId : $unitProductId,
                'idcategoria' => $categoryId,
                'igv' => $igvPercent,
                'idcodigo_igv' => $igvTypeId,
                'precio_compra' => $product['precio_compra'],
                'precio_venta' => $product['precio_venta'],
                'opcion' => $product['opcion'],
                'stock_actual' => $isService ? null : $product['stock_actual'],
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        return (int) DB::table('products')
            ->where('codigo_interno', $product['codigo_interno'])
            ->value('id');
    }

    private function upsertWarehouseStock(array $product, int $productId, int $warehouseId, Carbon $now): void
    {
        $isService = (int) $product['opcion'] === 2;
        $stockActual = $isService ? null : (int) $product['stock_actual'];

        DB::table('stock_products')->updateOrInsert(
            [
                'idproducto' => $productId,
                'idalmacen' => $warehouseId,
            ],
            [
                'stock_minimo' => $isService ? null : 10,
                'stock_actual' => $stockActual,
                'precio_compra' => $product['precio_compra'],
                'precio_venta' => $product['precio_venta'],
                'fecha_registro' => $now->format('Y-m-d'),
                'stock_entrada' => $stockActual,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    private function getUnitIdByCode(string $code): int
    {
        return (int) DB::table('units')->where('codigo', $code)->value('id');
    }

    private function getIgvTypeAffectionIdByCode(string $code): int
    {
        return (int) DB::table('igv_type_affections')->where('codigo', $code)->value('id');
    }

    private function getCategoryIdByName(string $name): int
    {
        $normalized = strtoupper($this->normalizeText($name));

        $categories = DB::table('categories')
            ->select('id', 'descripcion')
            ->orderBy('id')
            ->get();

        foreach ($categories as $category) {
            if (strtoupper($this->normalizeText((string) $category->descripcion)) === $normalized) {
                return (int) $category->id;
            }
        }

        throw new \RuntimeException('No se encontrÃ³ la categorÃ­a requerida para ProductSeeder: ' . $name);
    }

    private function normalizeText(string $value): string
    {
        $value = trim($value);

        return strtr($value, [
            'Ã' => 'A',
            'Ã‰' => 'E',
            'Ã' => 'I',
            'Ã“' => 'O',
            'Ãš' => 'U',
            'Ã‘' => 'N',
            'Ã¡' => 'a',
            'Ã©' => 'e',
            'Ã­' => 'i',
            'Ã³' => 'o',
            'Ãº' => 'u',
            'Ã±' => 'n',
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ñ' => 'N',
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ñ' => 'n',
        ]);
    }
}
