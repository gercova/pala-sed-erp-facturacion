<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientLoyalty extends Model
{
    use HasFactory;

    protected $table = 'client_loyalty';
    protected $primaryKey = 'id';

    protected $fillable = [
        'idcliente',
        'idpromocion',
        'compras_acumuladas',
        'premios_reclamados',
        'ultimo_canje',
    ];

    protected $casts = [
        'compras_acumuladas' => 'integer',
        'premios_reclamados' => 'integer',
        'ultimo_canje' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Client::class, 'idcliente');
    }

    public function promocion()
    {
        return $this->belongsTo(LoyaltyPromotion::class, 'idpromocion');
    }
}
