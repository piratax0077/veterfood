<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'mascota_id', 'producto_id', 'voucher_descuento_id', 'frecuencia', 'cantidad', 'proxima_entrega', 'direccion_entrega', 'forma_pago', 'activo'])]
class PlanPedido extends Model
{
    protected $table = 'planes_pedido';

    protected function casts(): array
    {
        return [
            'proxima_entrega' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function voucher()
    {
        return $this->belongsTo(VoucherDescuento::class, 'voucher_descuento_id');
    }
}
