<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JugMovement extends Model
{
    use HasFactory;

    protected $table = 'jug_movements';
    protected $primaryKey = 'id';

    protected $fillable = [
        'idcliente',
        'iddelivery_order',
        'idusuario',
        'idalmacen',
        'tipo_movimiento',
        'entregados_llenos',
        'devueltos_intactos',
        'devueltos_danados',
        'costo_dano',
        'saldo_anterior',
        'saldo_nuevo',
        'observaciones',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'entregados_llenos' => 'integer',
        'devueltos_intactos' => 'integer',
        'devueltos_danados' => 'integer',
        'costo_dano' => 'decimal:2',
        'saldo_anterior' => 'integer',
        'saldo_nuevo' => 'integer',
    ];

    public function cliente()
    {
        return $this->belongsTo(Client::class, 'idcliente');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'iddelivery_order');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }

    public function almacen()
    {
        return $this->belongsTo(Warehouse::class, 'idalmacen');
    }
}
