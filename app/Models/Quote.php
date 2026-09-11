<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;
    protected $table        = 'quotes';
    protected $primaryKey   = 'id';
    protected $fillable     = [
        'serie',
        'correlativo',
        'fecha_emision',
        'fecha_vencimiento',
        'hora',
        'idcliente',
        'idpago',
        'subtotal',
        'igv',
        'total',
        'observaciones',
        'estado',
        'idusuario',
        'idcaja',
    ];
}
