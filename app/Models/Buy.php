<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buy extends Model
{
    use HasFactory;
    protected $table = 'buys';
    protected $primaryKey = 'id';
    protected $fillable = 
    [
        'idtipo_comprobante',
        'serie',
        'correlativo',
        'fecha_emision',
        'fecha_vencimiento',
        'hora',
        'idproveedor',
        'idmoneda',
        'idpago',
        'modo_pago',
        'exonerada',
        'inafecta',
        'gravada',
        'anticipo',
        'igv',
        'gratuita',
        'otros_cargos',
        'total',
        'estado',
        'idusuario',
    ];

    protected $casts = [
        'idtipo_comprobante'    => 'integer',
        'idproveedor'           => 'integer',
        'idmoneda'              => 'integer',
        'idpago'                => 'integer',
        'modo_pago'             => 'integer',
        'total'                 => 'float',
        'igv'                   => 'float',
        'estado'                => 'integer',
        'idusuario'             => 'integer',
    ];

    public function proveedor(): BelongsTo {
        return $this->belongsTo(Client::class, 'idproveedor');
    }

    public function tipoComprobante(): BelongsTo {
        return $this->belongsTo(TypeDocument::class, 'idtipo_comprobante');
    }

    public function details(): HasMany {
        return $this->hasMany(DetailBuy::class, 'idcompra');
    }

    public function usuario(): BelongsTo {
        return $this->belongsTo(User::class, 'idusuario');
    }
}
