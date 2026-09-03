<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\CentroMedico;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TrabajadorController extends Controller
{
    public function index(CentroMedico $centroMedico)
    {
        return Trabajador::where('centro_medico_id', $centroMedico->id)
            ->with(['contratoVigente', 'cuentasBancarias'])
            ->orderBy('apellido_paterno')
            ->paginate(30);
    }

    public function store(Request $request, CentroMedico $centroMedico)
    {
        $data = $this->validatedData($request, $centroMedico);

        $trabajador = DB::transaction(function () use ($data, $centroMedico) {
            $cuenta = $data['cuenta_bancaria'] ?? null;
            unset($data['cuenta_bancaria']);

            $trabajador = $centroMedico->trabajadores()->create($data);
            if ($cuenta) {
                $trabajador->cuentasBancarias()->create($cuenta);
            }

            return $trabajador;
        });

        if (!$request->expectsJson()) {
            if ($request->filled('_redirect_to')) {
                return redirect()->back()->with('status', 'Trabajador registrado.');
            }

            return redirect()
                ->route('contabilidad.escritorio', ['centroMedico' => $centroMedico->id])
                ->with('status', 'Trabajador registrado.');
        }

        return response()->json($trabajador->load('cuentasBancarias'), 201);
    }

    public function show(CentroMedico $centroMedico, Trabajador $trabajador)
    {
        $this->assertCentro($centroMedico, $trabajador);

        return $trabajador->load(['contratos.remuneraciones', 'cuentasBancarias', 'ausencias', 'licenciasMedicas']);
    }

    public function update(Request $request, CentroMedico $centroMedico, Trabajador $trabajador)
    {
        $this->assertCentro($centroMedico, $trabajador);
        $data = $this->validatedData($request, $centroMedico, $trabajador);
        $cuenta = $data['cuenta_bancaria'] ?? null;
        unset($data['cuenta_bancaria']);

        $trabajador->update($data);
        if ($cuenta && !empty($cuenta['banco']) && !empty($cuenta['numero_cuenta'])) {
            $trabajador->cuentasBancarias()->updateOrCreate(['principal' => true], $cuenta + ['principal' => true]);
        }

        if (!$request->expectsJson()) {
            return redirect()->back()->with('status', 'Trabajador actualizado.');
        }

        return response()->json($trabajador->fresh());
    }

    public function destroy(CentroMedico $centroMedico, Trabajador $trabajador)
    {
        $this->assertCentro($centroMedico, $trabajador);
        $trabajador->update(['activo' => false]);
        $trabajador->delete();

        return response()->noContent();
    }

    private function validatedData(Request $request, CentroMedico $centroMedico, ?Trabajador $trabajador = null): array
    {
        return $request->validate([
            'rut' => [
                'required', 'string', 'max:20',
                Rule::unique('trabajadores')->where('centro_medico_id', $centroMedico->id)->ignore($trabajador),
            ],
            'nombres' => ['required', 'string', 'max:150'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'tipo' => ['required', Rule::in(['profesional', 'administrativo', 'mantencion', 'otro'])],
            'profesion' => ['nullable', 'string', 'max:150'],
            'especialidad' => ['nullable', 'string', 'max:150'],
            'funcion' => ['nullable', 'string', 'max:150'],
            'sexo' => ['nullable', Rule::in(['femenino', 'masculino', 'otro', 'no_informa'])],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'telefono_alternativo' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'numero_direccion' => ['nullable', 'string', 'max:30'],
            'comuna' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'afp' => ['nullable', 'string', 'max:150'],
            'fecha_afiliacion_afp' => ['nullable', 'date'],
            'salud_previsional' => ['nullable', 'string', 'max:150'],
            'tipo_salud' => ['nullable', Rule::in(['fonasa', 'isapre', 'ffaa', 'otro'])],
            'caja_compensacion' => ['nullable', 'string', 'max:150'],
            'mutualidad' => ['nullable', 'string', 'max:150'],
            'regimen_previsional' => ['nullable', Rule::in(['afp', 'ips', 'capredena', 'dipreca', 'sin_regimen', 'otro'])],
            'seguro_cesantia' => ['sometimes', 'boolean'],
            'tramo_asignacion_familiar' => ['nullable', Rule::in(['A', 'B', 'C', 'D', 'sin_tramo'])],
            'cargas_familiares' => ['nullable', 'integer', 'min:0', 'max:30'],
            'activo' => ['sometimes', 'boolean'],
            'cuenta_bancaria' => ['nullable', 'array'],
            'cuenta_bancaria.banco' => ['required_with:cuenta_bancaria', 'string', 'max:100'],
            'cuenta_bancaria.tipo_cuenta' => ['nullable', Rule::in(['corriente', 'vista', 'ahorro', 'rut', 'otra'])],
            'cuenta_bancaria.numero_cuenta' => ['required_with:cuenta_bancaria', 'string', 'max:80'],
            'cuenta_bancaria.sucursal' => ['nullable', 'string', 'max:100'],
            'cuenta_bancaria.email_pago' => ['nullable', 'email', 'max:150'],
        ]);
    }

    private function assertCentro(CentroMedico $centroMedico, Trabajador $trabajador): void
    {
        abort_unless($trabajador->centro_medico_id === $centroMedico->id, 404);
    }
}
