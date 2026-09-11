<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNoteType extends Model
{
    use HasFactory;

    protected $table = 'credit_note_types';

    protected $fillable = [
        'codigo',
        'descripcion',
        'estado',
    ];
}
