<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObligacionLaboral extends Model
{
    protected $table = 'obligaciones_laborales';

    protected $fillable = [
        'remuneracion_id', 'tipo', 'institucion', 'monto', 'fecha_vencimiento',
        'fecha_pago', 'estado', 'comprobante',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
    ];

    public function remuneracion(): BelongsTo
    {
        return $this->belongsTo(Remuneracion::class);
    }
}
