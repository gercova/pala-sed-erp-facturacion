<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentGuideItem extends Model
{
    use HasFactory;

    protected $table = 'shipment_guide_items';

    protected $fillable = [
        'shipment_guide_id',
        'product_id',
        'codigo',
        'descripcion',
        'unidad',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
    ];

    public function guide()
    {
        return $this->belongsTo(ShipmentGuide::class, 'shipment_guide_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
