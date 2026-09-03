<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'tipo_contexto', 'contexto_id', 'opinion', 'recibe_voucher', 'porcentaje_descuento', 'intereses', 'comentario'])]
class EncuestaUsuario extends Model
{
    protected $table = 'encuestas_usuarios';

    protected function casts(): array
    {
        return [
            'recibe_voucher' => 'boolean',
            'porcentaje_descuento' => 'integer',
            'intereses' => 'array',
        ];
    }
}
