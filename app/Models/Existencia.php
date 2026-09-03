<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['producto_id', 'bodega_id', 'local_venta_id', 'cantidad', 'stock_critico', 'stock_objetivo'])]
class Existencia extends Model
{
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'stock_critico' => 'integer',
            'stock_objetivo' => 'integer',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }

    public function local()
    {
        return $this->belongsTo(LocalVenta::class, 'local_venta_id');
    }

    public function estaCritico(): bool
    {
        return $this->cantidad <= $this->stock_critico;
    }
}
