<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferOrder extends Model
{
    use HasFactory;
    protected $table        = 'transfer_orders';
    protected $primaryKey   = 'id';
    protected $fillable     =
    [
        'serie',
        'correlativo',
        'fecha_emision',
        'fecha_vencimiento',
        'hora',
        'idalmacen_despacho',
        'idalmacen_receptor',
        'observaciones',
        'idusuario',
        'estado',
        'idalmacen'
    ];
}
