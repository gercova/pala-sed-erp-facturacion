<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $table        = 'warehouses';
    protected $primaryKey   = 'id';
    protected $fillable     = 
    [
        'descripcion',
        'direccion'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_warehouse', 'warehouse_id', 'user_id')->withTimestamps();
    }
}
