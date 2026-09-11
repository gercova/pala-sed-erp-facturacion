<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailBuy extends Model
{
    use HasFactory;
    protected $table        = 'detail_buys';
    protected $primaryKey   = 'id';
    protected $fillable     =
    [
        'idcompra',
        'idproducto',
        'cantidad',
        'descuento',
        'igv',
        'id_afectacion_igv',
        'precio_unitario',
        'precio_total',
        'idalmacen'
    ];
}
