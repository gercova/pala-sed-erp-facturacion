<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $primaryKey = 'id';

    protected $fillable =
        [
            'codigo_interno',
            'codigo_barras',
            'codigo_sunat',
            'descripcion',
            'idunidad',
            'idcategoria',
            'igv',
            'idcodigo_igv',
            'precio_compra',
            'precio_venta',
            'opcion',
            'stock_actual',
        ];

    protected $casts = [
        'idunidad' => 'integer',
        'idcategoria' => 'integer',
        'igv' => 'float',
        'idcodigo_igv' => 'integer',
        'precio_compra' => 'float',
        'precio_venta' => 'float',
        'opcion' => 'integer',
        'stock_actual' => 'float',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'idunidad');
    }

    public function unidad()
    {
        return $this->unit();
    }

    public function igvTypeAffection()
    {
        return $this->belongsTo(IgvTypeAffection::class, 'idcodigo_igv');
    }

    public function stocks()
    {
        return $this->hasMany(StockProduct::class, 'idproducto');
    }

    public function adjustments()
    {
        return $this->hasMany(InventoryAdjustment::class, 'idproducto');
    }

    public function isPhysical(): bool
    {
        return (int) $this->opcion === 1;
    }

    public function scopePhysical($query)
    {
        return $query->where('opcion', 1);
    }

    public function syncStockActual(): ?float
    {
        if (! $this->isPhysical()) {
            $this->update(['stock_actual' => null]);

            return null;
        }

        $totalStock = (float) StockProduct::where('idproducto', $this->id)->sum('stock_actual');
        $this->update(['stock_actual' => $totalStock]);

        return $totalStock;
    }
}
