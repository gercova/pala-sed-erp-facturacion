<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    use HasFactory;
    protected $table        = 'series';
    protected $primaryKey   = 'id';
    protected $fillable     = 
    [
        'serie',
        'correlativo',
        'idtipo_documento',
        'idtipo_documento_relacionado',
        'idcaja',
        'direccion',
        'estado'
    ];
}
