<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table        = 'products';
    protected $primaryKey   = 'id';

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
        'stock_actual'
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'idunidad');
    }

    public function igvTypeAffection()
    {
        return $this->belongsTo(IgvTypeAffection::class, 'idcodigo_igv');
    }
}
