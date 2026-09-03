<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'rut', 'telefono', 'email', 'fecha_inscripcion'])]
class Cliente extends Model
{
    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'datetime',
        ];
    }
}
