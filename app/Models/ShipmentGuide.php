<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentGuide extends Model
{
    use HasFactory;

    protected $table = 'shipment_guides';

    protected $fillable = [
        'idcliente',
        'idalmacen',
        'idtipo_comprobante',
        'serie',
        'correlativo',
        'fecha_emision',
        'fecha_inicio_traslado',
        'motivo_traslado_codigo',
        'motivo_traslado_descripcion',
        'modo_transporte',
        'peso_total',
        'unidad_peso',
        'partida_ubigeo',
        'partida_direccion',
        'llegada_ubigeo',
        'llegada_direccion',
        'transportista_documento_tipo',
        'transportista_documento',
        'transportista_nombre',
        'conductor_documento_tipo',
        'conductor_documento',
        'conductor_nombre',
        'placa_vehiculo',
        'placa_secundaria',
        'observaciones',
        'xml',
        'cdr',
        'estado_cpe',
        'ticket',
        'errores',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_inicio_traslado' => 'date',
        'peso_total' => 'decimal:3',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'idcliente');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'idalmacen');
    }

    public function items()
    {
        return $this->hasMany(ShipmentGuideItem::class, 'shipment_guide_id');
    }
}
