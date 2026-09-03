<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['vet_sdi_user_id', 'name', 'email', 'password', 'rol', 'activo', 'telefono', 'direccion', 'plan_preferido', 'recibe_voucher', 'porcentaje_descuento_voucher', 'encuesta_sistema_nacional', 'comentario_sistema_nacional', 'fonavet_valor_mensual', 'georeferencia_url', 'local_venta_id', 'cliente_id', 'foto_url', 'vehiculo_foto_url', 'vehiculo_patente', 'vehiculo_marca', 'vehiculo_modelo', 'two_factor_secret', 'two_factor_confirmed_at', 'two_factor_last_verified_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'recibe_voucher' => 'boolean',
            'porcentaje_descuento_voucher' => 'integer',
            'fonavet_valor_mensual' => 'integer',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_last_verified_at' => 'datetime',
        ];
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'user_id');
    }

    public function repartos()
    {
        return $this->hasMany(Pedido::class, 'repartidor_id');
    }

    public function mascotas()
    {
        return $this->hasMany(Mascota::class);
    }

    public function localVenta()
    {
        return $this->belongsTo(LocalVenta::class);
    }

    public function perfilCliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function planesPedido()
    {
        return $this->hasMany(PlanPedido::class);
    }

    public function direcciones()
    {
        return $this->hasMany(DireccionCliente::class);
    }

    public function centrosMedicos(): BelongsToMany
    {
        return $this->belongsToMany(CentroMedico::class, 'centro_medico_user')
            ->withPivot([
                'rol', 'permisos', 'activo', 'estado_relacion',
                'solicitado_por_id', 'aprobado_admin_por_id',
                'aprobado_admin_at', 'aprobado_contador_at',
            ])
            ->withTimestamps();
    }

    public function tieneRol(string ...$roles): bool
    {
        $aliases = [
            'dueno_mascota' => 'cliente',
            'secretaria' => 'central_ventas',
            'secretaria_veterchile' => 'central_ventas',
            'secretaria_clinica' => 'central_ventas',
        ];

        $rolNormalizado = $aliases[$this->rol] ?? $this->rol;

        return in_array($this->rol, $roles, true) || in_array($rolNormalizado, $roles, true);
    }
}
