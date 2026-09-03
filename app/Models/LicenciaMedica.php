<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenciaMedica extends Model
{
    protected $table = 'licencias_medicas';

    protected $fillable = [
        'trabajador_id', 'numero', 'fecha_inicio', 'fecha_termino', 'dias',
        'entidad_pagadora', 'estado', 'monto_subsidio', 'documento', 'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_termino' => 'date',
    ];

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }
}
