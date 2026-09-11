<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleNote extends Model
{
    use HasFactory;
    protected $table        = 'sale_notes';
    protected $primaryKey   = 'id';
    protected $fillable     = [
        'idtipo_comprobante',
        'serie',
        'correlativo',
        'fecha_emision',
        'fecha_vencimiento',
        'hora',
        'idcliente',
        'modo_pago',
        'subtotal',
        'igv',
        'total',
        'monto_credito',
        'cuotas',
        'payment_breakdown',
        'observaciones',
        'estado',
        'idusuario',
        'idarqueocaja',
        'idfactura_anular',
        'vuelto',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_credito' => 'decimal:2',
        'vuelto' => 'decimal:2',
        'cuotas' => 'array',
        'payment_breakdown' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }

    public function pago()
    {
        return $this->belongsTo(PayMode::class, 'idpago');
    }

    public function cliente()
    {
        return $this->belongsTo(Client::class, 'idcliente');
    }
}
