<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $table = 'providers';

    protected $primaryKey = 'id';

    protected $fillable = [
        'iddoc',
        'nro_documento',
        'nombres',
        'direccion',
        'codigo_pais',
        'ubigeo',
        'telefono',
        'email',
    ];

    public function tipoDocumento()
    {
        return $this->belongsTo(IdentityDocumentType::class, 'iddoc');
    }
}
