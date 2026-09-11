<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
    protected $table = 'businesses';
    protected $primaryKey = 'id';
    protected $fillable = [
        'ruc',
        'razon_social',
        'nombre_comercial',
        'logo',
        'idpais',
        'direccion',
        'codigo_pais',
        'ubigeo',
        'urbanizacion',
        'local',
        'telefono',
        'url_api',
        'vencimiento_certificado',
        'usuario_sunat',
        'clave_sunat',
        'clave_certificado',
        'certificado',
        'servidor_sunat',
        'gre_client_id',
        'gre_client_secret',
        'instancia_wpp',
        'cobrar_igv'
    ];

    protected $casts = [
        'vencimiento_certificado' => 'date',
        'cobrar_igv' => 'boolean',
    ];
}
