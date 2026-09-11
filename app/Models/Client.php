<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';
    protected $primaryKey = 'id';

    protected $fillable = [
        'iddoc',
        'nro_documento',
        'nombres',
        'direccion',
        'referencia',
        'coordenadas',
        'codigo_pais',
        'ubigeo',
        'telefono',
        'saldo_envases',
        'email',
    ];

    protected $casts = [
        'saldo_envases' => 'integer',
    ];

    public function tipoDocumento()
    {
        return $this->belongsTo(IdentityDocumentType::class, 'iddoc');
    }

    public function jugMovements()
    {
        return $this->hasMany(JugMovement::class, 'idcliente')->orderBy('id', 'desc');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'idcliente')->orderBy('id', 'desc');
    }

    public function loyaltyBalances()
    {
        return $this->hasMany(ClientLoyalty::class, 'idcliente');
    }
}
