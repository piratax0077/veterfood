<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaBancaria extends Model
{
    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'trabajador_id', 'banco', 'tipo_cuenta', 'numero_cuenta',
        'sucursal', 'email_pago', 'principal',
    ];

    protected $casts = ['principal' => 'boolean'];

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }
}
