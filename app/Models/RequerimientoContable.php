<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequerimientoContable extends Model
{
    protected $table = 'requerimientos_contables';

    protected $fillable = [
        'centro_medico_id', 'solicitante_id', 'contador_id', 'trabajador_id',
        'documento_tributario_id', 'codigo', 'tipo', 'titulo', 'prioridad',
        'estado', 'datos', 'detalle', 'archivo_solicitud', 'archivo_respuesta',
        'requiere_firma_institucion', 'requiere_firma_trabajador',
        'firmado_institucion_at', 'firmado_trabajador_at', 'firmado_contador_at',
        'firma_hash', 'fecha_requerida', 'respondido_at',
    ];

    protected $casts = [
        'datos' => 'array',
        'fecha_requerida' => 'date',
        'respondido_at' => 'datetime',
        'requiere_firma_institucion' => 'boolean',
        'requiere_firma_trabajador' => 'boolean',
        'firmado_institucion_at' => 'datetime',
        'firmado_trabajador_at' => 'datetime',
        'firmado_contador_at' => 'datetime',
    ];

    public function centroMedico(): BelongsTo
    {
        return $this->belongsTo(CentroMedico::class);
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function contador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contador_id');
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(DocumentoTributario::class, 'documento_tributario_id');
    }
}
