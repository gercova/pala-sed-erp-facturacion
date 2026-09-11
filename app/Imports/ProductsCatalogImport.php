<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\IgvTypeAffection;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsCatalogImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $productId = (int) ($row['product_id'] ?? 0);
        if ($productId <= 0) {
            return null;
        }

        $description = trim((string) ($row['descripcion'] ?? ''));
        if ($description === '' || Str::contains($description, ['Notas:', 'No modifique product_id'])) {
            return null;
        }

        $product = Product::query()->find($productId);
        if (! $product) {
            throw new \Exception('Producto no encontrado para product_id: ' . $productId);
        }

        $unitId = $this->resolveUnitId((string) ($row['unidad_codigo'] ?? ''), (int) $product->idunidad);
        $categoryId = $this->resolveCategoryId((string) ($row['categoria'] ?? ''), (int) $product->idcategoria);
        $igvAffectionId = $this->resolveIgvAffectionId((string) ($row['afectacion_igv_codigo'] ?? ''), (int) $product->idcodigo_igv);
        $itemType = $this->resolveItemType((string) ($row['tipo_item'] ?? ''));

        $product->update([
            'codigo_interno' => $this->nullableText($row['codigo_interno'] ?? null),
            'codigo_barras' => $this->nullableText($row['codigo_barras'] ?? null),
            'codigo_sunat' => '00000000',
            'descripcion' => mb_strtoupper($description),
            'idunidad' => $unitId,
            'idcategoria' => $categoryId,
            'idcodigo_igv' => $igvAffectionId,
            'igv' => $this->resolveIgvPercent($igvAffectionId),
            'opcion' => $itemType,
        ]);

        return null;
    }

    private function resolveUnitId(string $code, int $fallbackId): int
    {
        $code = trim($code);
        if ($code === '') {
            return $fallbackId;
        }

        $unitId = (int) Unit::query()->whereRaw('UPPER(codigo) = ?', [mb_strtoupper($code)])->value('id');
        if ($unitId <= 0) {
            throw new \Exception('Unidad no encontrada para codigo: ' . $code);
        }

        return $unitId;
    }

    private function resolveCategoryId(string $name, int $fallbackId): int
    {
        $name = trim($name);
        if ($name === '') {
            return $fallbackId;
        }

        $categoryId = (int) Category::query()->whereRaw('UPPER(descripcion) = ?', [mb_strtoupper($name)])->value('id');
        if ($categoryId <= 0) {
            throw new \Exception('Categoria no encontrada: ' . $name);
        }

        return $categoryId;
    }

    private function resolveIgvAffectionId(string $code, int $fallbackId): int
    {
        $code = trim($code);
        if ($code === '') {
            return $fallbackId;
        }

        $affectionId = (int) IgvTypeAffection::query()->whereRaw('UPPER(codigo) = ?', [mb_strtoupper($code)])->value('id');
        if ($affectionId <= 0) {
            throw new \Exception('Afectacion IGV no encontrada para codigo: ' . $code);
        }

        return $affectionId;
    }

    private function resolveIgvPercent(int $affectionId): int
    {
        $code = (string) IgvTypeAffection::query()->whereKey($affectionId)->value('codigo');

        return $code === '10' ? 18 : 0;
    }

    private function resolveItemType(string $value): int
    {
        $normalized = mb_strtoupper(trim($value));

        return in_array($normalized, ['2', 'SERVICIO'], true) ? 2 : 1;
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

}
