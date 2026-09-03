<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['pedido_id', 'repartidor_id', 'estado', 'mensaje', 'latitud', 'longitud', 'foto_entrega'])]
class TrackingEvento extends Model
{
    protected $table = 'tracking_pedido_eventos';
}
