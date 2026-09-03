<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentroMedico extends Model
{
    protected $table = 'centros_medicos';

    protected $fillable = [
        'rut', 'razon_social', 'nombre_fantasia', 'giro', 'email', 'telefono',
        'direccion', 'comuna', 'region', 'rut_representante_legal',
        'representante_legal', 'clave_serv_impuestos', 'contacto_comercial',
        'valor_pactado_servicio', 'sucursales', 'observaciones', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'valor_pactado_servicio' => 'integer',
        'sucursales' => 'array',
    ];

    public function trabajadores(): HasMany
    {
        return $this->hasMany(Trabajador::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoContable::class);
    }

    public function convenios(): HasMany
    {
        return $this->hasMany(Convenio::class);
    }

    public function documentosTributarios(): HasMany
    {
        return $this->hasMany(DocumentoTributario::class);
    }

    public function requerimientosContables(): HasMany
    {
        return $this->hasMany(RequerimientoContable::class);
    }

    public function terceros(): HasMany
    {
        return $this->hasMany(Tercero::class);
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'centro_medico_user')
            ->withPivot([
                'rol', 'permisos', 'activo', 'estado_relacion',
                'solicitado_por_id', 'aprobado_admin_por_id',
                'aprobado_admin_at', 'aprobado_contador_at',
            ])
            ->withTimestamps();
    }
}
