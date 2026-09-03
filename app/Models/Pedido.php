<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'codigo_tracking',
    'user_id',
    'repartidor_id',
    'local_venta_id',
    'bodega_id',
    'plan_pedido_id',
    'voucher_descuento_id',
    'cliente_nombre',
    'cliente_email',
    'cliente_telefono',
    'direccion_entrega',
    'region_id',
    'ciudad_id',
    'region_nombre',
    'ciudad_nombre',
    'notas_entrega',
    'fecha_entrega',
    'frecuencia',
    'prioridad',
    'estado',
    'estado_pago',
    'subtotal',
    'costo_envio',
    'descuento_total',
    'total',
    'pagado_at',
    'despachado_at',
    'entregado_at',
    'cliente_conformidad',
    'cliente_reclamo',
    'reclamo_estado',
    'feedback_cliente_at',
])]
class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos_comercio';

    protected function casts(): array
    {
        return [
            'fecha_entrega' => 'date',
            'pagado_at' => 'datetime',
            'despachado_at' => 'datetime',
            'entregado_at' => 'datetime',
            'feedback_cliente_at' => 'datetime',
        ];
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function pago()
    {
        return $this->hasOne(Pago::class);
    }

    public function tracking()
    {
        return $this->hasMany(TrackingEvento::class)->oldest();
    }

    public function repartidor()
    {
        return $this->belongsTo(User::class, 'repartidor_id');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function localVenta()
    {
        return $this->belongsTo(LocalVenta::class);
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }

    public function planPedido()
    {
        return $this->belongsTo(PlanPedido::class);
    }

    public function voucher()
    {
        return $this->belongsTo(VoucherDescuento::class, 'voucher_descuento_id');
    }
}
