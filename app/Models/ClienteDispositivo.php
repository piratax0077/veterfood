<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'device_uuid',
    'device_token',
    'nombre',
    'plataforma',
    'modelo',
    'version_sistema',
    'estado',
    'enrolado_at',
    'ultimo_uso_at',
    'ultimo_ip',
    'user_agent',
])]
class ClienteDispositivo extends Model
{
    protected $table = 'cliente_dispositivos';

    protected function casts(): array
    {
        return [
            'enrolado_at' => 'datetime',
            'ultimo_uso_at' => 'datetime',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
