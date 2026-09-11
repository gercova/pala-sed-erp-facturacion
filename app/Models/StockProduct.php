<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockProduct extends Model
{
    use HasFactory;
    protected $table        = 'stock_products';
    protected $primaryKey   = 'id';
    protected $fillable     = 
    [
        'idproducto',
        'idalmacen',
        'stock_minimo',
        'stock_actual',
        'precio_compra',
        'precio_venta',
        'fecha_registro',
        'stock_entrada'
    ];
}
