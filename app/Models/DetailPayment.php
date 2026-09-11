<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPayment extends Model
{
    use HasFactory;
    protected $table        = 'detail_payments';
    protected $primaryKey   = 'id';
    protected $fillable     = 
    [
        'idtipo_comprobante',
        'idfactura',
        'idpago',
        'monto',
        'idarqueocaja',
        'estado'
    ];
}
