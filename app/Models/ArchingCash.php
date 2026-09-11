<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchingCash extends Model
{
    use HasFactory;

    protected $table = 'arching_cashes';

    protected $primaryKey = 'id';

    protected $fillable = [
        'idcaja',
        'idusuario',
        'idalmacen',
        'fecha_inicio',
        'fecha_fin',
        'monto_inicial',
        'monto_final',
        'total_ventas',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto_inicial' => 'decimal:2',
        'monto_final' => 'decimal:2',
    ];

    public function cash()
    {
        return $this->belongsTo(Cash::class, 'idcaja');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }
}
