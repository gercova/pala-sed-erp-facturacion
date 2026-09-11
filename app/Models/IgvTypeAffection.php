<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IgvTypeAffection extends Model
{
    use HasFactory;

    protected $table = 'igv_type_affections';

    protected $fillable = [
        'codigo',
        'descripcion',
        'tipo',
        'estado',
    ];
}
