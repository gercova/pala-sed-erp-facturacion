<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $table = 'inventory_adjustments';

    protected $fillable = [
        'idalmacen',
        'idproducto',
        'idusuario',
        'tipo_ajuste',
        'stock_anterior',
        'stock_nuevo',
        'cantidad_diferencia',
        'costo_unitario',
        'motivo',
        'documento_referencia',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'idalmacen' => 'integer',
        'idproducto' => 'integer',
        'idusuario' => 'integer',
        'stock_anterior' => 'float',
        'stock_nuevo' => 'float',
        'cantidad_diferencia' => 'float',
        'costo_unitario' => 'float',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'idalmacen');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'idproducto');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idusuario');
    }
}
