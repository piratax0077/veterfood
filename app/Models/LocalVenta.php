<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'vet_sdi_centro_id',
    'nombre',
    'codigo',
    'tipo',
    'rut',
    'razon_social',
    'direccion',
    'comuna',
    'telefono',
    'email',
    'responsable',
    'contacto_comercial',
    'modalidad_convenio',
    'servicios_ofrecidos',
    'condiciones_convenio',
    'publica_ofertas',
    'url_ofertas',
    'recibe_voucher',
    'porcentaje_descuento_voucher',
    'encuesta_sistema_nacional',
    'comentario_sistema_nacional',
    'georeferencia_url',
    'observaciones',
    'activo',
])]
class LocalVenta extends Model
{
    protected $table = 'locales_venta';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'recibe_voucher' => 'boolean',
            'porcentaje_descuento_voucher' => 'integer',
            'servicios_ofrecidos' => 'array',
            'publica_ofertas' => 'boolean',
        ];
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'local_venta_id');
    }

    public function bodegas()
    {
        return $this->hasMany(Bodega::class, 'local_venta_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'local_venta_id');
    }
}
