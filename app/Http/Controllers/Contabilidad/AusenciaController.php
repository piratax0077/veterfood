<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\CentroMedico;
use App\Models\LicenciaMedica;
use App\Models\SolicitudAusencia;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AusenciaController extends Controller
{
    public function licencias(Request $request, CentroMedico $centroMedico)
    {
        return LicenciaMedica::with('trabajador')
            ->whereHas('trabajador', function ($query) use ($centroMedico) {
                $query->where('centro_medico_id', $centroMedico->id);
            })
            ->when($request->input('estado'), function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($request->input('buscar'), function ($query, $buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('numero', 'like', "%{$buscar}%")
                        ->orWhere('entidad_pagadora', 'like', "%{$buscar}%")
                        ->orWhereHas('trabajador', function ($workerQuery) use ($buscar) {
                            $workerQuery->where('nombre', 'like', "%{$buscar}%")
                                ->orWhere('rut', 'like', "%{$buscar}%");
                        });
                });
            })
            ->orderByDesc('fecha_inicio')
            ->paginate(50);
    }

    public function storeSolicitud(Request $request, CentroMedico $centroMedico)
    {
        $data = $request->validate([
            'trabajador_id' => ['required', 'exists:trabajadores,id'],
            'tipo' => ['required', Rule::in(['vacaciones_legales', 'vacaciones_parciales', 'permiso_con_goce', 'permiso_sin_goce', 'otro'])],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'dias_habiles' => ['required', 'numeric', 'min:0.5'],
            'periodo_anio' => ['nullable', 'integer', 'between:2000,2100'],
            'motivo' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->assertTrabajador($centroMedico, $data['trabajador_id']);

        return response()->json(SolicitudAusencia::create($data), 201);
    }

    public function autorizar(Request $request, CentroMedico $centroMedico, SolicitudAusencia $solicitud)
    {
        abort_unless($solicitud->trabajador()->where('centro_medico_id', $centroMedico->id)->exists(), 404);
        $data = $request->validate([
            'estado' => ['required', Rule::in(['autorizada', 'rechazada'])],
            'observacion_autorizacion' => ['nullable', 'string', 'max:2000'],
        ]);

        $usuario = $request->user();
        $solicitud->update($data + [
            'autorizado_por' => $usuario ? $usuario->id : null,
            'autorizado_en' => now(),
        ]);

        return response()->json($solicitud->fresh());
    }

    public function storeLicencia(Request $request, CentroMedico $centroMedico)
    {
        $data = $request->validate([
            'trabajador_id' => ['required', 'exists:trabajadores,id'],
            'numero' => ['required', 'string', 'max:100', 'unique:licencias_medicas,numero'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'dias' => ['required', 'integer', 'min:1'],
            'entidad_pagadora' => ['nullable', 'string', 'max:150'],
            'monto_subsidio' => ['nullable', 'integer', 'min:0'],
            'documento' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->assertTrabajador($centroMedico, $data['trabajador_id']);

        return response()->json(LicenciaMedica::create($data), 201);
    }

    public function actualizarLicencia(Request $request, CentroMedico $centroMedico, LicenciaMedica $licencia)
    {
        abort_unless($licencia->trabajador()->where('centro_medico_id', $centroMedico->id)->exists(), 404);

        $data = $request->validate([
            'estado' => ['required', Rule::in(['recibida', 'en_revision', 'aprobada', 'rechazada', 'procesada'])],
            'monto_subsidio' => ['nullable', 'integer', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $licencia->update($data);

        return response()->json($licencia->fresh('trabajador'));
    }


    private function assertTrabajador(CentroMedico $centroMedico, int $trabajadorId): void
    {
        abort_unless(
            Trabajador::whereKey($trabajadorId)->where('centro_medico_id', $centroMedico->id)->exists(),
            404
        );
    }
}
