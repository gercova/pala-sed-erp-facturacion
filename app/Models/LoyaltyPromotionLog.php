<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPromotionLog extends Model
{
    use HasFactory;

    protected $table = 'loyalty_promotion_logs';

    protected $primaryKey = 'id';

    protected $fillable = [
        'idpromocion',
        'idusuario',
        'meta_compras_anterior',
        'meta_compras_nueva',
        'bonificacion_anterior',
        'bonificacion_nueva',
        'idproducto_objetivo_anterior',
        'idproducto_objetivo_nuevo',
        'idproducto_bonificado_anterior',
        'idproducto_bonificado_nuevo',
        'activo_anterior',
        'activo_nuevo',
        'motivo',
    ];

    protected $casts = [
        'meta_compras_anterior' => 'integer',
        'meta_compras_nueva' => 'integer',
        'bonificacion_anterior' => 'integer',
        'bonificacion_nueva' => 'integer',
        'activo_anterior' => 'boolean',
        'activo_nuevo' => 'boolean',
    ];

    public function promocion()
    {
        return $this->belongsTo(LoyaltyPromotion::class, 'idpromocion');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }

    public function productoObjetivoNuevo()
    {
        return $this->belongsTo(Product::class, 'idproducto_objetivo_nuevo');
    }

    public function productoBonificadoNuevo()
    {
        return $this->belongsTo(Product::class, 'idproducto_bonificado_nuevo');
    }
}
