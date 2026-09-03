<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Finiquito extends Model
{
    protected $table = 'finiquitos';

    protected $fillable = [
        'contrato_id', 'causal', 'fecha_salida', 'base_calculo', 'vacaciones',
        'mes_aviso', 'indemnizacion_anios_servicio', 'indemnizacion_acordada',
        'remuneracion_pendiente', 'descuento_seguro_cesantia', 'otros_descuentos',
        'total', 'estado', 'fecha_pago', 'documento', 'observaciones',
    ];

    protected $casts = [
        'fecha_salida' => 'date',
        'fecha_pago' => 'date',
    ];

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }
}
