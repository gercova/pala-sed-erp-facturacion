<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDocument extends Model
{
    use HasFactory;
    protected $table        = 'type_documents';
    protected $primaryKey   = 'id';

    protected $fillable     =
    [
        'codigo',
        'descripcion',
        'estado'
    ];
}
