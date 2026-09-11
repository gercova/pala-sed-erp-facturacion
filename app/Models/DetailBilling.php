<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailBilling extends Model
{
    use HasFactory;

    protected $table = 'detail_billings';

    protected $fillable = [
        'idfacturacion',
        'idproducto',
        'cantidad',
        'descuento',
        'igv',
        'icbper',
        'factor_icbper',
        'cantidad_bolsas',
        'id_afectacion_igv',
        'precio_unitario',
        'valor_unitario',
        'valor_total',
        'precio_total',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'descuento' => 'decimal:2',
        'igv' => 'decimal:2',
        'icbper' => 'decimal:2',
        'factor_icbper' => 'decimal:4',
        'cantidad_bolsas' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'valor_unitario' => 'decimal:10',
        'valor_total' => 'decimal:2',
        'precio_total' => 'decimal:2',
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class, 'idfacturacion');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'idproducto');
    }

    public function igvTypeAffection()
    {
        return $this->belongsTo(IgvTypeAffection::class, 'id_afectacion_igv');
    }
}
