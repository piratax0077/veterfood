<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'codigo',
    'titulo',
    'descripcion',
    'tipo_descuento',
    'valor',
    'monto_minimo',
    'usos_maximos',
    'usos_realizados',
    'valido_desde',
    'valido_hasta',
    'destinatario_nombre',
    'destinatario_email',
    'vendedor_id',
    'local_venta_id',
    'producto_id',
    'categoria_aplicable',
    'alcance_territorial',
    'region_id',
    'comuna_id',
    'tipo_destinatario',
    'segmento_destinatario',
    'mascota_id',
    'usuario_destinatario_id',
    'tipo_beneficio',
    'firma_seguridad',
    'activo',
])]
class VoucherDescuento extends Model
{
    protected $table = 'vouchers_descuento';

    protected function casts(): array
    {
        return [
            'valido_desde' => 'date',
            'valido_hasta' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function localVenta()
    {
        return $this->belongsTo(LocalVenta::class);
    }

    public function mascota() { return $this->belongsTo(Mascota::class); }
    public function usuarioDestinatario() { return $this->belongsTo(User::class, 'usuario_destinatario_id'); }

    public function movimientos()
    {
        return $this->hasMany(VoucherMovimiento::class, 'voucher_descuento_id');
    }
}
