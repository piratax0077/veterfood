<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoTributario extends Model
{
    protected $table = 'documentos_tributarios';

    protected $fillable = [
        'centro_medico_id', 'tercero_id', 'naturaleza', 'tipo_documento', 'folio',
        'fecha_emision', 'fecha_vencimiento', 'neto', 'exento', 'impuesto', 'total',
        'estado', 'fecha_pago', 'archivo', 'observaciones',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
    ];

    public function tercero(): BelongsTo
    {
        return $this->belongsTo(Tercero::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleDocumentoTributario::class);
    }
}
