<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'fecha', 'repartidor_id', 'estado'])]
class RutaReparto extends Model
{
    protected $table = 'rutas_reparto';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function repartidor()
    {
        return $this->belongsTo(User::class, 'repartidor_id');
    }

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'ruta_pedido')->withPivot('orden')->withTimestamps()->orderBy('ruta_pedido.orden');
    }
}
