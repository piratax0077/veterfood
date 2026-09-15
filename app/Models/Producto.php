<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'marca', 'categoria', 'subcategoria', 'peso', 'descripcion', 'precio_compra', 'precio', 'precio_oferta', 'stock', 'stock_minimo', 'foto_url', 'sucursal_destino', 'medio_envio', 'tipo_servicio', 'modalidad_servicio', 'duracion_minutos', 'requiere_agenda', 'activo'])]
class Producto extends Model
{
    use HasFactory;

    /** Nombre visible de cada categoria en la tienda. */
    public const CATEGORIAS = [
        'alimento_mascota' => 'Alimentos',
        'medicamento' => 'Medicamentos',
        'juguete' => 'Accesorios y Juguetes',
        'hotel' => 'Hoteles',
        'paseo_diario' => 'Paseos diarios',
        'cementerio' => 'Cementerio',
        'cuidado' => 'Cuidados',
        'servicio' => 'Servicios',
        'utensilio' => 'Accesorios',
    ];

    public function getCategoriaEtiquetaAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? ucfirst(str_replace('_', ' ', (string) $this->categoria));
    }

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'precio' => 'integer',
            'precio_oferta' => 'integer',
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

    /** Productos del Outlet: tienen un precio de oferta menor al precio normal. */
    public function scopeEnOutlet(Builder $query): Builder
    {
        return $query->whereNotNull('precio_oferta')->whereColumn('precio_oferta', '<', 'precio');
    }

    public function getEnOfertaAttribute(): bool
    {
        return $this->precio_oferta !== null && $this->precio_oferta < $this->precio;
    }

    /** Precio que se cobra: el de oferta si esta en Outlet, si no el normal. */
    public function getPrecioFinalAttribute(): int
    {
        return $this->en_oferta ? $this->precio_oferta : (int) $this->precio;
    }

    public function getDescuentoPorcentajeAttribute(): int
    {
        return $this->en_oferta && $this->precio > 0 ? (int) round(100 - $this->precio_oferta / $this->precio * 100) : 0;
    }
}
