<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'pedido_id', 'plan_pedido_id', 'tipo', 'titulo', 'mensaje', 'url', 'leido_at'])]
class ClienteNotificacion extends Model
{
    protected $table = 'cliente_notificaciones';

    protected function casts(): array
    {
        return [
            'leido_at' => 'datetime',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
