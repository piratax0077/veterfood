<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'vet_sdi_profesional_id',
    'nombre',
    'rut',
    'telefono',
    'email',
    'direccion_consulta',
    'especialidad',
    'recibe_voucher',
    'porcentaje_descuento_voucher',
    'encuesta_sistema_nacional',
    'comentario_sistema_nacional',
    'visita_domiciliaria',
    'password_acceso',
    'foto_url',
    'geolocalizacion',
    'codigo_geolocalizacion',
    'banco',
    'tipo_cuenta',
    'numero_cuenta',
    'titular_cuenta',
    'rut_cuenta',
    'activo',
])]
#[Hidden(['password_acceso'])]
class Profesional extends Model
{
    protected $table = 'profesionales';

    protected function casts(): array
    {
        return [
            'recibe_voucher' => 'boolean',
            'porcentaje_descuento_voucher' => 'integer',
            'visita_domiciliaria' => 'boolean',
            'activo' => 'boolean',
        ];
    }
}
