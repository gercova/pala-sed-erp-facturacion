<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $table = 'delivery_orders';
    protected $primaryKey = 'id';

    protected $fillable = [
        'codigo_orden',
        'idcliente',
        'idrepartidor',
        'idusuario_registro',
        'idalmacen',
        'idnotaventa',
        'idfactura',
        'origen',
        'estado',
        'direccion_entrega',
        'referencia',
        'telefono_contacto',
        'coordenadas',
        'fecha_programada',
        'franja_horaria',
        'fecha_entrega',
        'subtotal',
        'descuento',
        'total',
        'metodo_pago',
        'estado_pago',
        'bidones_a_entregar',
        'bidones_vacios_recibidos',
        'bidones_danados_recibidos',
        'cobro_envases_danados',
        'notas',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_entrega' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'cobro_envases_danados' => 'decimal:2',
        'bidones_a_entregar' => 'integer',
        'bidones_vacios_recibidos' => 'integer',
        'bidones_danados_recibidos' => 'integer',
    ];

    public function cliente()
    {
        return $this->belongsTo(Client::class, 'idcliente');
    }

    public function repartidor()
    {
        return $this->belongsTo(User::class, 'idrepartidor');
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'idusuario_registro');
    }

    public function almacen()
    {
        return $this->belongsTo(Warehouse::class, 'idalmacen');
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class, 'iddelivery_order');
    }

    public function notaVenta()
    {
        return $this->belongsTo(SaleNote::class, 'idnotaventa');
    }

    public function comprobante()
    {
        return $this->belongsTo(Billing::class, 'idfactura');
    }

    public function movimientosEnvases()
    {
        return $this->hasMany(JugMovement::class, 'iddelivery_order');
    }
}
