<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPromotion extends Model
{
    use HasFactory;

    protected $table = 'loyalty_promotions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'meta_compras',
        'bonificacion',
        'idproducto_objetivo',
        'idproducto_bonificado',
        'activo',
        'descripcion',
    ];

    protected $casts = [
        'meta_compras' => 'integer',
        'bonificacion' => 'integer',
        'activo' => 'boolean',
    ];

    public function productoObjetivo()
    {
        return $this->belongsTo(Product::class, 'idproducto_objetivo');
    }

    public function productoBonificado()
    {
        return $this->belongsTo(Product::class, 'idproducto_bonificado');
    }

    public function balancesClientes()
    {
        return $this->hasMany(ClientLoyalty::class, 'idpromocion');
    }
}
