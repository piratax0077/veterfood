<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'voucher_descuento_id',
    'tipo',
    'estado',
    'monto',
    'responsable_id',
    'profesional_id',
    'pedido_id',
    'detalle',
    'firma_seguridad',
])]
class VoucherMovimiento extends Model
{
    protected $table = 'voucher_movimientos';

    public function voucher()
    {
        return $this->belongsTo(VoucherDescuento::class, 'voucher_descuento_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function profesional()
    {
        return $this->belongsTo(Profesional::class);
    }
}
