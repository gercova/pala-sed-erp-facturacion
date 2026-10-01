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

    public function logs()
    {
        return $this->hasMany(LoyaltyPromotionLog::class, 'idpromocion')->latest('id');
    }

    public function getRuleLabelAttribute(): string
    {
        return "{$this->meta_compras}+{$this->bonificacion}";
    }

    public function getRuleTextAttribute(): string
    {
        return "{$this->meta_compras} más {$this->bonificacion}";
    }

    public function getSummaryTextAttribute(): string
    {
        $bonus = $this->bonificacion > 1 ? "{$this->bonificacion} GRATIS" : '1 GRATIS';

        return "Por cada {$this->meta_compras} compras, ¡{$bonus}!";
    }
}
