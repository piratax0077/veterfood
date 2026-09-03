<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\CentroMedico;
use App\Models\MovimientoContable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MovimientoContableController extends Controller
{
    public function index(Request $request, CentroMedico $centroMedico)
    {
        return MovimientoContable::where('centro_medico_id', $centroMedico->id)
            ->when($request->input('tipo'), fn ($query, $tipo) => $query->where('tipo', $tipo))
            ->when($request->input('desde'), fn ($query, $desde) => $query->whereDate('fecha', '>=', $desde))
            ->when($request->input('hasta'), fn ($query, $hasta) => $query->whereDate('fecha', '<=', $hasta))
            ->orderByDesc('fecha')->paginate(50);
    }

    public function store(Request $request, CentroMedico $centroMedico)
    {
        $movimiento = $centroMedico->movimientos()->create($this->validatedData($request));

        if (!$request->expectsJson()) {
            if ($request->filled('_redirect_to')) {
                return redirect()->back()->with('status', 'Movimiento contable registrado.');
            }

            return redirect()
                ->route('contabilidad.escritorio', ['centroMedico' => $centroMedico->id])
                ->with('status', 'Movimiento contable registrado.');
        }

        return response()->json($movimiento, 201);
    }

    public function update(Request $request, CentroMedico $centroMedico, MovimientoContable $movimiento)
    {
        $this->assertCentro($centroMedico, $movimiento);
        $movimiento->update($this->validatedData($request));

        return response()->json($movimiento->fresh());
    }

    public function destroy(CentroMedico $centroMedico, MovimientoContable $movimiento)
    {
        $this->assertCentro($centroMedico, $movimiento);
        $movimiento->update(['estado' => 'anulado']);

        return response()->noContent();
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'documento_tributario_id' => ['nullable', 'exists:documentos_tributarios,id'],
            'remuneracion_id' => ['nullable', 'exists:remuneraciones,id'],
            'tipo' => ['required', Rule::in(['ingreso', 'egreso'])],
            'fecha' => ['required', 'date'],
            'categoria' => ['required', 'string', 'max:150'],
            'glosa' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'integer', 'min:1'],
            'medio_pago' => ['nullable', Rule::in(['efectivo', 'transferencia', 'tarjeta', 'cheque', 'otro'])],
            'referencia' => ['nullable', 'string', 'max:150'],
            'estado' => ['sometimes', Rule::in(['pendiente', 'pagado', 'conciliado', 'anulado'])],
            'fecha_pago' => ['nullable', 'date'],
            'comprobante' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function assertCentro(CentroMedico $centroMedico, MovimientoContable $movimiento): void
    {
        abort_unless($movimiento->centro_medico_id === $centroMedico->id, 404);
    }
}
