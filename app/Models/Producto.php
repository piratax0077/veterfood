<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'marca', 'categoria', 'subcategoria', 'peso', 'descripcion', 'precio_compra', 'precio', 'stock', 'stock_minimo', 'foto_url', 'sucursal_destino', 'medio_envio', 'tipo_servicio', 'modalidad_servicio', 'duracion_minutos', 'requiere_agenda', 'activo'])]
class Producto extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'precio' => 'integer',
            'stock' => 'integer',
            'duracion_minutos' => 'integer',
            'requiere_agenda' => 'boolean',
        ];
    }

    public function existencias()
    {
        return $this->hasMany(Existencia::class);
    }

    public function vouchers()
    {
        return $this->hasMany(VoucherDescuento::class);
    }
}
