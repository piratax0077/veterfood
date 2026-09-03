<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\CentroMedico;
use App\Models\Convenio;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConvenioController extends Controller
{
    public function index(CentroMedico $centroMedico)
    {
        return Convenio::where('centro_medico_id', $centroMedico->id)->with('trabajador')->latest()->paginate(30);
    }

    public function store(Request $request, CentroMedico $centroMedico)
    {
        $data = $this->validatedData($request);
        if (!empty($data['trabajador_id'])) {
            abort_unless(
                Trabajador::whereKey($data['trabajador_id'])->where('centro_medico_id', $centroMedico->id)->exists(),
                404
            );
        }

        return response()->json($centroMedico->convenios()->create($data), 201);
    }

    public function update(Request $request, CentroMedico $centroMedico, Convenio $convenio)
    {
        abort_unless($convenio->centro_medico_id === $centroMedico->id, 404);
        $convenio->update($this->validatedData($request));

        return response()->json($convenio->fresh());
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'trabajador_id' => ['nullable', 'exists:trabajadores,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'contraparte' => ['nullable', 'string', 'max:150'],
            'tipo_pago' => ['required', Rule::in(['porcentaje', 'monto_fijo', 'por_atencion', 'otro'])],
            'porcentaje' => ['nullable', 'required_if:tipo_pago,porcentaje', 'numeric', 'between:0,100'],
            'monto' => ['nullable', 'required_unless:tipo_pago,porcentaje', 'integer', 'min:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['sometimes', Rule::in(['vigente', 'vencido', 'terminado'])],
            'condiciones' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
