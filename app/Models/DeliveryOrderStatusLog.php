<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrderStatusLog extends Model
{
    use HasFactory;

    protected $table = 'delivery_order_status_logs';

    protected $fillable = [
        'iddelivery_order',
        'idusuario',
        'estado_anterior',
        'estado_nuevo',
        'motivo',
        'notas',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'iddelivery_order');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idusuario');
    }
}
