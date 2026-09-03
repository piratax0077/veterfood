<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleDocumentoTributario extends Model
{
    protected $table = 'detalle_documentos_tributarios';

    protected $fillable = [
        'documento_tributario_id', 'codigo', 'descripcion', 'cantidad',
        'precio_unitario', 'descuento_porcentaje', 'total',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'precio_unitario' => 'decimal:3',
        'descuento_porcentaje' => 'decimal:4',
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(DocumentoTributario::class, 'documento_tributario_id');
    }
}
