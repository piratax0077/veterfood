<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'cliente_id',
    'origen_sistema',
    'origen_id',
    'nombre',
    'especie',
    'raza',
    'sexo',
    'color',
    'peso_kg',
    'fecha_nacimiento',
    'numero_chip',
    'foto_url',
    'esterilizado',
    'alergias',
    'observaciones',
])]
class Mascota extends Model
{
    protected $table = 'mascotas';

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'esterilizado' => 'boolean',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
