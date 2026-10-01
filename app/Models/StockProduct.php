<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockProduct extends Model
{
    use HasFactory;

    protected $table = 'stock_products';

    protected $primaryKey = 'id';

    protected $fillable =
        [
            'idproducto',
            'idalmacen',
            'stock_minimo',
            'stock_actual',
            'precio_compra',
            'precio_venta',
            'fecha_registro',
            'stock_entrada',
        ];

    protected $casts = [
        'idproducto' => 'integer',
        'idalmacen' => 'integer',
        'stock_minimo' => 'float',
        'stock_actual' => 'float',
        'precio_compra' => 'float',
        'precio_venta' => 'float',
        'stock_entrada' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'idproducto');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'idalmacen');
    }
}
