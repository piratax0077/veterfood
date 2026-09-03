<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\CentroMedico;
use App\Models\Tercero;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TerceroController extends Controller
{
    public function index(Request $request, CentroMedico $centroMedico)
    {
        return Tercero::where('centro_medico_id', $centroMedico->id)
            ->when($request->input('tipo'), function ($query, $tipo) {
                $query->whereIn('tipo', [$tipo, 'ambos']);
            })
            ->when($request->input('buscar'), function ($query, $buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('rut', 'like', "%{$buscar}%")
                        ->orWhere('razon_social', 'like', "%{$buscar}%");
                });
            })
            ->where('activo', true)
            ->orderBy('razon_social')
            ->paginate(50);
    }

    public function store(Request $request, CentroMedico $centroMedico)
    {
        $tercero = $centroMedico->terceros()->create($this->validatedData($request, $centroMedico));

        if (!$request->expectsJson()) {
            if ($request->filled('_redirect_to')) {
                return redirect()->back()->with('status', 'Cliente o proveedor registrado.');
            }

            return redirect()
                ->route('contabilidad.escritorio', ['centroMedico' => $centroMedico->id])
                ->with('status', 'Cliente o proveedor registrado.');
        }

        return response()->json($tercero, 201);
    }

    public function update(Request $request, CentroMedico $centroMedico, Tercero $tercero)
    {
        abort_unless($tercero->centro_medico_id === $centroMedico->id, 404);
        $tercero->update($this->validatedData($request, $centroMedico, $tercero));

        return response()->json($tercero->fresh());
    }

    private function validatedData(Request $request, CentroMedico $centroMedico, ?Tercero $tercero = null): array
    {
        return $request->validate([
            'tipo' => ['required', Rule::in(['cliente', 'proveedor', 'ambos'])],
            'rut' => [
                'required',
                'string',
                'max:20',
                Rule::unique('terceros')->where(function ($query) use ($centroMedico) {
                    return $query->where('centro_medico_id', $centroMedico->id);
                })->ignore($tercero ? $tercero->id : null),
            ],
            'razon_social' => ['required', 'string', 'max:255'],
            'giro' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'comuna' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'activo' => ['sometimes', 'boolean'],
        ]);
    }
}
