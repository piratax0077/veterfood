<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['local_venta_id', 'nombre', 'codigo', 'direccion', 'tipo', 'activo'])]
class Bodega extends Model
{
    public function localVenta()
    {
        return $this->belongsTo(LocalVenta::class, 'local_venta_id');
    }

    public function existencias()
    {
        return $this->hasMany(Existencia::class);
    }
}
