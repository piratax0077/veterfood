<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoContable extends Model
{
    protected $table = 'movimientos_contables';

    protected $fillable = [
        'centro_medico_id', 'documento_tributario_id', 'remuneracion_id', 'tipo',
        'fecha', 'categoria', 'glosa', 'monto', 'medio_pago', 'referencia',
        'estado', 'fecha_pago', 'comprobante', 'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_pago' => 'date',
    ];

    public function centroMedico(): BelongsTo
    {
        return $this->belongsTo(CentroMedico::class);
    }
}
