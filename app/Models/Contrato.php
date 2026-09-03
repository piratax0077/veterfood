<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Contrato extends Model
{
    protected $table = 'contratos';

    protected $fillable = [
        'trabajador_id', 'tipo', 'fecha_inicio', 'fecha_termino', 'cargo',
        'horas_semanales', 'sueldo_base', 'monto_imponible', 'porcentaje_colacion',
        'porcentaje_movilizacion', 'cargas_familiares', 'porcentaje_caja_compensacion',
        'dias_laborales', 'hora_entrada', 'hora_salida', 'inicio_colacion',
        'termino_colacion', 'estado', 'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_termino' => 'date',
        'dias_laborales' => 'array',
        'porcentaje_colacion' => 'decimal:4',
        'porcentaje_movilizacion' => 'decimal:4',
        'porcentaje_caja_compensacion' => 'decimal:4',
    ];

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function remuneraciones(): HasMany
    {
        return $this->hasMany(Remuneracion::class);
    }

    public function finiquito(): HasOne
    {
        return $this->hasOne(Finiquito::class);
    }
}
