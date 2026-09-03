<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['pedido_id', 'metodo', 'estado', 'monto', 'referencia', 'detalle', 'pagado_at'])]
class Pago extends Model
{
    protected $table = 'pagos_comercio';

    protected function casts(): array
    {
        return [
            'detalle' => 'array',
            'pagado_at' => 'datetime',
        ];
    }
}
