<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\CentroMedico;
use App\Models\Contrato;
use App\Models\MovimientoContable;
use App\Models\ObligacionLaboral;
use App\Models\Remuneracion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RemuneracionController extends Controller
{
    public function index(Request $request, CentroMedico $centroMedico)
    {
        return Remuneracion::with('contrato.trabajador')
            ->whereHas('contrato.trabajador', fn ($query) => $query->where('centro_medico_id', $centroMedico->id))
            ->when($request->integer('anio'), fn ($query, $anio) => $query->where('anio', $anio))
            ->when($request->integer('mes'), fn ($query, $mes) => $query->where('mes', $mes))
            ->orderByDesc('anio')->orderByDesc('mes')->paginate(30);
    }

    public function store(Request $request, CentroMedico $centroMedico)
    {
        $data = $this->validatedData($request);
        $contrato = Contrato::with('trabajador')->findOrFail($data['contrato_id']);
        abort_unless($contrato->trabajador->centro_medico_id === $centroMedico->id, 404);

        $data = $this->calculate($data, $contrato);
        $remuneracion = Remuneracion::updateOrCreate(
            ['contrato_id' => $contrato->id, 'anio' => $data['anio'], 'mes' => $data['mes']],
            $data
        );
        $this->syncObligaciones($remuneracion->fresh('contrato.trabajador'));

        if (!$request->expectsJson()) {
            return redirect()->back()->with('status', 'Remuneracion registrada.');
        }

        return response()->json($remuneracion->load('contrato.trabajador'), 201);
    }

    public function update(Request $request, CentroMedico $centroMedico, Remuneracion $remuneracion)
    {
        $this->assertCentro($centroMedico, $remuneracion);
        $remuneracion->load('contrato.trabajador');
        $data = $this->calculate($this->validatedData($request, $remuneracion), $remuneracion->contrato);
        $remuneracion->update($data);
        $this->syncObligaciones($remuneracion->fresh('contrato.trabajador'));

        return response()->json($remuneracion->fresh());
    }

    public function pagar(Request $request, CentroMedico $centroMedico, Remuneracion $remuneracion)
    {
        $this->assertCentro($centroMedico, $remuneracion);
        $data = $request->validate([
            'fecha_pago' => ['required', 'date'],
            'comprobante' => ['nullable', 'string', 'max:255'],
        ]);
        $remuneracion->update($data + ['estado' => 'pagada']);
        $remuneracion->load('contrato.trabajador');
        $remuneracion->obligaciones()->update([
            'estado' => 'pagada',
            'fecha_pago' => $data['fecha_pago'],
            'comprobante' => $data['comprobante'] ?? null,
        ]);
        $this->registrarEgresoPago($centroMedico, $remuneracion, $data);

        if (!$request->expectsJson()) {
            return redirect()->back()->with('status', 'Pago registrado.');
        }

        return response()->json($remuneracion->fresh());
    }

    private function validatedData(Request $request, ?Remuneracion $remuneracion = null): array
    {
        return $request->validate([
            'contrato_id' => [$remuneracion ? 'sometimes' : 'required', 'exists:contratos,id'],
            'anio' => ['required', 'integer', 'between:2000,2100'],
            'mes' => ['required', 'integer', 'between:1,12'],
            'fecha_pago' => ['nullable', 'date'],
            'sueldo_base' => ['nullable', 'integer', 'min:0'],
            'bonos' => ['nullable', 'integer', 'min:0'],
            'horas_extra' => ['nullable', 'integer', 'min:0'],
            'otros_imponibles' => ['nullable', 'integer', 'min:0'],
            'colacion' => ['nullable', 'integer', 'min:0'],
            'movilizacion' => ['nullable', 'integer', 'min:0'],
            'asignacion_familiar' => ['nullable', 'integer', 'min:0'],
            'otros_no_imponibles' => ['nullable', 'integer', 'min:0'],
            'afp' => ['nullable', 'integer', 'min:0'],
            'salud' => ['nullable', 'integer', 'min:0'],
            'seguro_cesantia' => ['nullable', 'integer', 'min:0'],
            'cotizacion_voluntaria' => ['nullable', 'integer', 'min:0'],
            'anticipos' => ['nullable', 'integer', 'min:0'],
            'prestamos' => ['nullable', 'integer', 'min:0'],
            'otros_descuentos' => ['nullable', 'integer', 'min:0'],
            'estado' => ['sometimes', Rule::in(['borrador', 'calculada', 'pagada', 'anulada'])],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function calculate(array $data, Contrato $contrato): array
    {
        $value = function (string $key) use (&$data): int {
            return (int) ($data[$key] ?? 0);
        };
        $trabajador = $contrato->trabajador;
        $isProfesional = $trabajador?->tipo === 'profesional';
        $sueldoBase = $value('sueldo_base') ?: (int) $contrato->sueldo_base;

        $data['sueldo_base'] = $sueldoBase;
        $data['total_imponible'] = $sueldoBase + $value('bonos')
            + $value('horas_extra') + $value('otros_imponibles');
        if (!$isProfesional) {
            $data['colacion'] = $value('colacion') ?: $this->percentageAmount($sueldoBase, (float) $contrato->porcentaje_colacion);
            $data['movilizacion'] = $value('movilizacion') ?: $this->percentageAmount($sueldoBase, (float) $contrato->porcentaje_movilizacion);
            $data['afp'] = $value('afp') ?: $this->percentageAmount($data['total_imponible'], 10.77);
            $data['salud'] = $value('salud') ?: $this->percentageAmount($data['total_imponible'], 7);
            $data['seguro_cesantia'] = $value('seguro_cesantia') ?: ($trabajador?->seguro_cesantia ? $this->percentageAmount($data['total_imponible'], 0.6) : 0);
        }

        $data['total_haberes'] = $data['total_imponible'] + $value('colacion')
            + $value('movilizacion') + $value('asignacion_familiar') + $value('otros_no_imponibles');
        $data['total_descuentos'] = $value('afp') + $value('salud') + $value('seguro_cesantia')
            + $value('cotizacion_voluntaria') + $value('anticipos') + $value('prestamos')
            + $value('otros_descuentos');
        $data['liquido_pagar'] = max(0, $data['total_haberes'] - $data['total_descuentos']);
        $data['estado'] = $data['estado'] ?? 'calculada';

        return $data;
    }

    private function percentageAmount(int $base, float $percentage): int
    {
        return max(0, (int) round($base * ($percentage / 100)));
    }

    private function syncObligaciones(Remuneracion $remuneracion): void
    {
        $trabajador = $remuneracion->contrato?->trabajador;
        if (!$trabajador || $trabajador->tipo === 'profesional') {
            return;
        }

        $vencimiento = Carbon::create($remuneracion->anio, $remuneracion->mes, 1)->addMonth()->day(13);
        $items = [
            ['tipo' => 'afp', 'institucion' => $trabajador->afp ?: 'AFP no informada', 'monto' => $remuneracion->afp],
            ['tipo' => 'salud', 'institucion' => $trabajador->salud_previsional ?: 'Salud no informada', 'monto' => $remuneracion->salud],
            ['tipo' => 'cesantia', 'institucion' => 'Seguro de cesantia', 'monto' => $remuneracion->seguro_cesantia],
        ];

        if ($trabajador->caja_compensacion) {
            $items[] = ['tipo' => 'caja_compensacion', 'institucion' => $trabajador->caja_compensacion, 'monto' => 0];
        }
        if ($trabajador->mutualidad) {
            $items[] = ['tipo' => 'mutual', 'institucion' => $trabajador->mutualidad, 'monto' => 0];
        }

        foreach ($items as $item) {
            if ((int) $item['monto'] <= 0 && !in_array($item['tipo'], ['caja_compensacion', 'mutual'], true)) {
                continue;
            }
            ObligacionLaboral::updateOrCreate(
                ['remuneracion_id' => $remuneracion->id, 'tipo' => $item['tipo']],
                [
                    'institucion' => $item['institucion'],
                    'monto' => (int) $item['monto'],
                    'fecha_vencimiento' => $vencimiento,
                    'estado' => $remuneracion->estado === 'pagada' ? 'pagada' : 'pendiente',
                ]
            );
        }
    }

    private function registrarEgresoPago(CentroMedico $centroMedico, Remuneracion $remuneracion, array $data): void
    {
        MovimientoContable::updateOrCreate(
            ['centro_medico_id' => $centroMedico->id, 'remuneracion_id' => $remuneracion->id],
            [
                'tipo' => 'egreso',
                'fecha' => $data['fecha_pago'],
                'categoria' => $remuneracion->contrato?->trabajador?->tipo === 'profesional' ? 'liquidacion profesional' : 'remuneraciones',
                'glosa' => 'Pago ' . ($remuneracion->contrato?->trabajador?->tipo === 'profesional' ? 'liquidacion profesional ' : 'remuneracion ') . $remuneracion->mes . '/' . $remuneracion->anio . ' - ' . $remuneracion->contrato?->trabajador?->nombre_completo,
                'monto' => $remuneracion->liquido_pagar,
                'medio_pago' => 'transferencia',
                'referencia' => $data['comprobante'] ?? null,
                'estado' => 'pagado',
                'fecha_pago' => $data['fecha_pago'],
                'comprobante' => $data['comprobante'] ?? null,
                'observaciones' => 'Generado automaticamente desde modulo de remuneraciones/liquidaciones.',
            ]
        );
    }

    private function assertCentro(CentroMedico $centroMedico, Remuneracion $remuneracion): void
    {
        abort_unless(
            $remuneracion->contrato()->whereHas('trabajador', fn ($query) => $query->where('centro_medico_id', $centroMedico->id))->exists(),
            404
        );
    }
}
