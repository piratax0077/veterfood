<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\CentroMedico;
use App\Models\Contrato;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContratoController extends Controller
{
    public function store(Request $request, CentroMedico $centroMedico, Trabajador $trabajador)
    {
        abort_unless($trabajador->centro_medico_id === $centroMedico->id, 404);
        $data = $this->validateData($request);

        $contrato = DB::transaction(function () use ($trabajador, $data) {
            if ($data['estado'] === 'vigente') {
                $trabajador->contratos()->where('estado', 'vigente')->update(['estado' => 'finalizado']);
            }

            return $trabajador->contratos()->create($data);
        });

        if (!$request->expectsJson()) {
            return redirect()->back()->with('status', 'Contrato registrado.');
        }

        return response()->json($contrato, 201);
    }

    public function update(Request $request, CentroMedico $centroMedico, Contrato $contrato)
    {
        $this->assertCentro($centroMedico, $contrato);
        $contrato->update($this->validateData($request));

        if (!$request->expectsJson()) {
            return redirect()->back()->with('status', 'Contrato actualizado.');
        }

        return response()->json($contrato->fresh());
    }

    public function finalizar(Request $request, CentroMedico $centroMedico, Contrato $contrato)
    {
        $this->assertCentro($centroMedico, $contrato);
        $data = $request->validate([
            'fecha_termino' => ['required', 'date', 'after_or_equal:' . $contrato->fecha_inicio->toDateString()],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $contrato->update([
            'fecha_termino' => $data['fecha_termino'],
            'estado' => 'finalizado',
            'observaciones' => $data['observaciones'] ?? $contrato->observaciones,
        ]);

        if (!$request->expectsJson()) {
            return redirect()->back()->with('status', 'Contrato finalizado.');
        }

        return response()->json($contrato->fresh());
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'tipo' => ['required', Rule::in(['indefinido', 'plazo_fijo', 'honorarios', 'prestacion_servicios'])],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'cargo' => ['required', 'string', 'max:150'],
            'horas_semanales' => ['nullable', 'integer', 'between:1,60'],
            'sueldo_base' => ['required', 'integer', 'min:0'],
            'monto_imponible' => ['nullable', 'integer', 'min:0'],
            'porcentaje_colacion' => ['nullable', 'numeric', 'between:0,100'],
            'porcentaje_movilizacion' => ['nullable', 'numeric', 'between:0,100'],
            'cargas_familiares' => ['nullable', 'integer', 'min:0'],
            'porcentaje_caja_compensacion' => ['nullable', 'numeric', 'between:0,100'],
            'dias_laborales' => ['nullable', 'array'],
            'dias_laborales.*' => [Rule::in(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'])],
            'hora_entrada' => ['nullable', 'date_format:H:i'],
            'hora_salida' => ['nullable', 'date_format:H:i'],
            'inicio_colacion' => ['nullable', 'date_format:H:i'],
            'termino_colacion' => ['nullable', 'date_format:H:i'],
            'estado' => ['sometimes', Rule::in(['vigente', 'finalizado', 'suspendido'])],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]) + ['estado' => $request->input('estado', 'vigente')];
    }

    private function assertCentro(CentroMedico $centroMedico, Contrato $contrato): void
    {
        abort_unless($contrato->trabajador()->where('centro_medico_id', $centroMedico->id)->exists(), 404);
    }
}
