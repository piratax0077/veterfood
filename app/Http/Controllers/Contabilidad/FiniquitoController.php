<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\CentroMedico;
use App\Models\Contrato;
use App\Models\Finiquito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FiniquitoController extends Controller
{
    public function store(Request $request, CentroMedico $centroMedico)
    {
        $data = $this->validatedData($request);
        $contrato = Contrato::with('trabajador')->findOrFail($data['contrato_id']);
        abort_unless($contrato->trabajador->centro_medico_id === $centroMedico->id, 404);

        $data['total'] = $this->calculateTotal($data);

        $finiquito = DB::transaction(function () use ($data, $contrato) {
            $finiquito = Finiquito::updateOrCreate(['contrato_id' => $contrato->id], $data);
            $contrato->update([
                'estado' => 'finalizado',
                'fecha_termino' => $data['fecha_salida'],
            ]);

            return $finiquito;
        });

        if (!$request->expectsJson()) {
            return redirect()->back()->with('status', 'Finiquito registrado.');
        }

        return response()->json($finiquito->load('contrato.trabajador'), 201);
    }

    public function update(Request $request, CentroMedico $centroMedico, Finiquito $finiquito)
    {
        $this->assertCentro($centroMedico, $finiquito);
        $data = $this->validatedData($request, $finiquito);
        $data['total'] = $this->calculateTotal($data);
        $finiquito->update($data);

        if (!$request->expectsJson()) {
            return redirect()->back()->with('status', 'Finiquito actualizado.');
        }

        return response()->json($finiquito->fresh());
    }

    public function pagar(Request $request, CentroMedico $centroMedico, Finiquito $finiquito)
    {
        $this->assertCentro($centroMedico, $finiquito);
        $data = $request->validate([
            'fecha_pago' => ['required', 'date'],
            'documento' => ['nullable', 'string', 'max:255'],
        ]);
        $finiquito->update($data + ['estado' => 'pagado']);

        if (!$request->expectsJson()) {
            return redirect()->back()->with('status', 'Finiquito pagado.');
        }

        return response()->json($finiquito->fresh());
    }

    private function validatedData(Request $request, ?Finiquito $finiquito = null): array
    {
        return $request->validate([
            'contrato_id' => [$finiquito ? 'sometimes' : 'required', 'exists:contratos,id'],
            'causal' => ['required', 'string', 'max:255'],
            'fecha_salida' => ['required', 'date'],
            'base_calculo' => ['nullable', 'integer', 'min:0'],
            'vacaciones' => ['nullable', 'integer', 'min:0'],
            'mes_aviso' => ['nullable', 'integer', 'min:0'],
            'indemnizacion_anios_servicio' => ['nullable', 'integer', 'min:0'],
            'indemnizacion_acordada' => ['nullable', 'integer', 'min:0'],
            'remuneracion_pendiente' => ['nullable', 'integer', 'min:0'],
            'descuento_seguro_cesantia' => ['nullable', 'integer', 'min:0'],
            'otros_descuentos' => ['nullable', 'integer', 'min:0'],
            'estado' => ['sometimes', Rule::in(['borrador', 'emitido', 'firmado', 'pagado', 'anulado'])],
            'documento' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function calculateTotal(array $data): int
    {
        $value = fn (string $key): int => (int) ($data[$key] ?? 0);
        $haberes = $value('vacaciones') + $value('mes_aviso')
            + $value('indemnizacion_anios_servicio') + $value('indemnizacion_acordada')
            + $value('remuneracion_pendiente');

        return max(0, $haberes - $value('descuento_seguro_cesantia') - $value('otros_descuentos'));
    }

    private function assertCentro(CentroMedico $centroMedico, Finiquito $finiquito): void
    {
        abort_unless(
            $finiquito->contrato()->whereHas('trabajador', fn ($query) => $query->where('centro_medico_id', $centroMedico->id))->exists(),
            404
        );
    }
}
