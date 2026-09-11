<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransferOrder extends Model
{
    use HasFactory;
    protected $table        = 'detail_transfer_orders';
    protected $primaryKey   = 'id';

    protected $fillable     =
    [
        'idorden_traslado',
        'idproducto',
        'cantidad'
    ];
}
