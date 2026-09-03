<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Convenio extends Model
{
    protected $table = 'convenios';

    protected $fillable = [
        'centro_medico_id', 'trabajador_id', 'nombre', 'contraparte', 'tipo_pago',
        'porcentaje', 'monto', 'fecha_inicio', 'fecha_termino', 'estado', 'condiciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_termino' => 'date',
        'porcentaje' => 'decimal:4',
    ];

    public function centroMedico(): BelongsTo
    {
        return $this->belongsTo(CentroMedico::class);
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }
}
