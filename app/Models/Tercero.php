<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tercero extends Model
{
    protected $table = 'terceros';

    protected $fillable = [
        'centro_medico_id', 'tipo', 'rut', 'razon_social', 'giro', 'email',
        'telefono', 'direccion', 'comuna', 'region', 'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function centroMedico(): BelongsTo
    {
        return $this->belongsTo(CentroMedico::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoTributario::class);
    }
}
