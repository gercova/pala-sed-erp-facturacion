<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayMode extends Model
{
    use HasFactory;
    protected $primaryKey   = 'id';
    protected $table        = 'pay_modes';
    protected $fillable     =
    [
        'descripcion'
    ];
}
