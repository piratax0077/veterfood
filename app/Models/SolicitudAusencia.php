<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudAusencia extends Model
{
    protected $table = 'solicitudes_ausencia';

    protected $fillable = [
        'trabajador_id', 'tipo', 'fecha_inicio', 'fecha_termino', 'dias_habiles',
        'periodo_anio', 'motivo', 'estado', 'autorizado_por', 'autorizado_en',
        'observacion_autorizacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_termino' => 'date',
        'dias_habiles' => 'decimal:2',
        'autorizado_en' => 'datetime',
    ];

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }
}
