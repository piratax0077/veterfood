<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['pedido_id', 'producto_id', 'producto_nombre', 'producto_marca', 'precio_unitario', 'cantidad', 'total'])]
class PedidoItem extends Model
{
    protected $table = 'pedido_comercio_items';
}
