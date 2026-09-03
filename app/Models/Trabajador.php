<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trabajador extends Model
{
    protected $table = 'trabajadores';

    use SoftDeletes;

    protected $fillable = [
        'centro_medico_id', 'rut', 'nombres', 'apellido_paterno', 'apellido_materno',
        'tipo', 'profesion', 'especialidad', 'funcion', 'sexo', 'fecha_nacimiento',
        'email', 'telefono', 'telefono_alternativo', 'direccion', 'numero_direccion',
        'comuna', 'region', 'afp', 'fecha_afiliacion_afp', 'salud_previsional',
        'tipo_salud', 'caja_compensacion', 'mutualidad', 'regimen_previsional',
        'seguro_cesantia', 'tramo_asignacion_familiar', 'cargas_familiares', 'activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_afiliacion_afp' => 'date',
        'seguro_cesantia' => 'boolean',
        'cargas_familiares' => 'integer',
        'activo' => 'boolean',
    ];

    protected $appends = ['nombre_completo'];

    public function centroMedico(): BelongsTo
    {
        return $this->belongsTo(CentroMedico::class);
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    public function contratoVigente(): HasOne
    {
        return $this->hasOne(Contrato::class)->where('estado', 'vigente')->orderByDesc('id');
    }

    public function cuentasBancarias(): HasMany
    {
        return $this->hasMany(CuentaBancaria::class);
    }

    public function ausencias(): HasMany
    {
        return $this->hasMany(SolicitudAusencia::class);
    }

    public function licenciasMedicas(): HasMany
    {
        return $this->hasMany(LicenciaMedica::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }
}
