<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailQuote extends Model
{
    use HasFactory;
    protected $table        = 'detail_quotes';
    protected $primaryKey   = 'id';
    protected $fillable     =
    [
        'idcotizacion',
        'idproducto',
        'cantidad',
        'precio_unitario',
        'precio_total',
        'idalmacen'
    ];
}
