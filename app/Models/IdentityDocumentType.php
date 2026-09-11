<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentityDocumentType extends Model
{
    use HasFactory;

    protected $table = 'identity_document_types';

    protected $fillable = [
        'codigo',
        'descripcion',
        'descripcion_documento',
        'estado',
    ];
}
