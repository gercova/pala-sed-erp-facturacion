<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\StockProduct;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class ProductsWarehouseImport implements ToModel, WithHeadingRow, WithEvents
{
    public function __construct(
        protected $idalmacen
    ) {
    }

    public function model(array $row)
    {
        $description = trim((string) ($row['descripcion'] ?? ''));
        if ($description === '' ||
            str_contains($description, 'Notas:') ||
            str_contains($description, 'Puede modificar todos los campos, excepto la descripcion.') ||
            str_contains($description, 'Los registros que no tienen stock minimo ni stock actual corresponden a servicios. No es necesario realizar cambios a esta informacion.')) {
            return null;
        }

        $product = Product::query()
            ->where('descripcion', mb_strtoupper($description))
            ->first(['id', 'opcion']);

        if (! $product) {
            throw new \Exception('Producto no encontrado para la descripcion: ' . $description);
        }

        $isService = (int) ($product->opcion ?? 1) === 2;
        $stock = StockProduct::query()->firstOrNew([
            'idproducto' => $product->id,
            'idalmacen' => $this->idalmacen,
        ]);

        $stock->precio_compra = (float) ($row['precio_compra'] ?? 0);
        $stock->precio_venta = (float) ($row['precio_venta'] ?? 0);

        if ($isService) {
            $stock->stock_minimo = null;
            $stock->stock_actual = null;
        } else {
            $stock->stock_minimo = ($row['stock_minimo'] === null || $row['stock_minimo'] === '') ? null : (float) $row['stock_minimo'];
            $stock->stock_actual = ($row['stock_actual'] === null || $row['stock_actual'] === '') ? null : (float) $row['stock_actual'];
            if (! $stock->exists) {
                $stock->fecha_registro = now()->toDateString();
                $stock->stock_entrada = $stock->stock_actual;
            }
        }

        $stock->save();

        return null;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $richText = new RichText();
                $richText->createText('Modifique todos los campos, excepto la descripcion.');

                $event->sheet->getDelegate()->getComment('A1')
                    ->setAuthor('Sistema')
                    ->setText($richText);
            },
        ];
    }
}
