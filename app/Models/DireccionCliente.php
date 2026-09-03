<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'alias', 'direccion', 'region_id', 'region', 'comuna_id', 'comuna', 'referencia', 'dia_preferencia', 'horario_preferencia', 'forma_pago_preferida', 'principal'])]
class DireccionCliente extends Model
{
    protected $table = 'direcciones_cliente';

    protected function casts(): array
    {
        return [
            'principal' => 'boolean',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
