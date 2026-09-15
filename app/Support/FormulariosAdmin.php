<?php

namespace App\Support;

use App\Models\LocalVenta;
use App\Models\Mascota;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Catalogos y datos de los formularios del administrador.
 * Los usan los modales de "crear" (via View Composers en AppServiceProvider) y las paginas de edicion.
 */
class FormulariosAdmin
{
    public static function tiposLocal(): array
    {
        return [
            'sucursal' => 'Sucursal propia',
            'comercio_adherido' => 'Comercio adherido',
            'punto_retiro' => 'Punto de retiro',
            'farmacia' => 'Farmacia asociada',
            'clinica_veterinaria' => 'Clínica o veterinaria',
            'marketplace' => 'Convenio / marketplace',
            'otro' => 'Otro',
        ];
    }

    public static function rolOperativo(string $rol): array
    {
        return match ($rol) {
            'repartidor' => [
                'ruta' => 'repartidores',
                'titulo' => 'Repartidores',
                'singular' => 'Repartidor',
                'descripcion' => 'Registro de repartidores.',
                'icono' => 'R',
                'boton' => 'Ver repartidores',
            ],
            default => [
                'ruta' => 'vendedores',
                'titulo' => 'Vendedores',
                'singular' => 'Vendedor',
                'descripcion' => 'Vendedores autorizados para emitir vouchers.',
                'icono' => 'V',
                'boton' => 'Ver Vendedores',
            ],
        };
    }

    /** Clientes que pueden ser tutores de una mascota (con datos para la ficha del tutor). */
    public static function clientesMascota(): Collection
    {
        return User::with('perfilCliente')->whereIn('rol', ['cliente', 'dueno_mascota'])->orderBy('name')->get();
    }

    public static function localesAsignables(): Collection
    {
        return LocalVenta::orderBy('nombre')->get();
    }

    public static function datosVoucher(): array
    {
        return [
            'productos' => Producto::where('activo', true)->orderBy('categoria')->orderBy('nombre')->get(),
            'categorias' => Producto::where('activo', true)->whereNotNull('categoria')->distinct()->orderBy('categoria')->pluck('categoria'),
            'locales' => LocalVenta::where('activo', true)->orderBy('nombre')->get(),
            'regiones' => self::desdeVetSdi(fn () => DB::connection('vet_sdi')->table('regiones')->orderBy('id')->get(['id', 'nombre'])),
            'comunas' => self::desdeVetSdi(fn () => DB::connection('vet_sdi')->table('ciudades')->orderBy('nombre')->get(['id', 'nombre', 'id_region'])),
            'usuariosDestino' => User::where('activo', true)->whereNotNull('vet_sdi_user_id')->orderBy('name')->get(['id', 'name', 'email', 'vet_sdi_user_id']),
            'mascotasDestino' => Mascota::with('cliente:id,name,email,vet_sdi_user_id')->orderBy('nombre')->get(),
        ];
    }

    /** Regiones y comunas vienen de VET-SDI: si esa conexion falla, el formulario igual se muestra. */
    private static function desdeVetSdi(callable $consulta): Collection
    {
        try {
            return collect($consulta());
        } catch (\Throwable $error) {
            report($error);

            return collect();
        }
    }
}
