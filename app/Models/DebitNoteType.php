<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitNoteType extends Model
{
    use HasFactory;

    protected $table = 'debit_note_types';

    protected $fillable = [
        'codigo',
        'descripcion',
        'estado',
    ];
}
