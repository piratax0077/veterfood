<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Remuneracion extends Model
{
    protected $table = 'remuneraciones';

    protected $fillable = [
        'contrato_id', 'anio', 'mes', 'fecha_pago', 'sueldo_base', 'bonos',
        'horas_extra', 'otros_imponibles', 'total_imponible', 'colacion',
        'movilizacion', 'asignacion_familiar', 'otros_no_imponibles', 'total_haberes',
        'afp', 'salud', 'seguro_cesantia', 'cotizacion_voluntaria', 'anticipos',
        'prestamos', 'otros_descuentos', 'total_descuentos', 'liquido_pagar',
        'estado', 'comprobante', 'observaciones',
    ];

    protected $casts = ['fecha_pago' => 'date'];

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    public function obligaciones(): HasMany
    {
        return $this->hasMany(ObligacionLaboral::class);
    }
}
