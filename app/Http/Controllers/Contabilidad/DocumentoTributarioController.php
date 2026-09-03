<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\CentroMedico;
use App\Models\DocumentoTributario;
use App\Models\Tercero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentoTributarioController extends Controller
{
    public function index(Request $request, CentroMedico $centroMedico)
    {
        return DocumentoTributario::with('tercero')
            ->where('centro_medico_id', $centroMedico->id)
            ->when($request->input('naturaleza'), function ($query, $naturaleza) {
                $query->where('naturaleza', $naturaleza);
            })
            ->when($request->input('tipo_documento'), function ($query, $tipo) {
                $query->where('tipo_documento', $tipo);
            })
            ->when($request->input('estado'), function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($request->boolean('pendientes'), function ($query) {
                $query->whereIn('estado', ['pendiente', 'vencido']);
            })
            ->when($request->input('desde'), function ($query, $desde) {
                $query->whereDate('fecha_emision', '>=', $desde);
            })
            ->when($request->input('hasta'), function ($query, $hasta) {
                $query->whereDate('fecha_emision', '<=', $hasta);
            })
            ->orderByDesc('fecha_emision')
            ->paginate(50);
    }

    public function show(CentroMedico $centroMedico, DocumentoTributario $documento)
    {
        $this->assertCentro($centroMedico, $documento);

        return response()->json($documento->load(['tercero', 'detalles']));
    }

    public function emitir(CentroMedico $centroMedico, DocumentoTributario $documento)
    {
        $this->assertCentro($centroMedico, $documento);

        return view('contabilidad.factura_emitir', [
            'centroMedico' => $centroMedico,
            'documento' => $documento->load(['tercero', 'detalles']),
        ]);
    }

    public function archivo(CentroMedico $centroMedico, DocumentoTributario $documento)
    {
        $this->assertCentro($centroMedico, $documento);
        abort_unless($documento->archivo && Storage::disk('local')->exists($documento->archivo), 404);

        $path = Storage::disk('local')->path($documento->archivo);
        $filename = basename($documento->archivo);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function store(Request $request, CentroMedico $centroMedico)
    {
        $data = $request->validate([
            'tercero_id' => ['nullable', 'exists:terceros,id'],
            'naturaleza' => ['required', Rule::in(['venta', 'compra'])],
            'tipo_documento' => ['required', Rule::in(['factura', 'boleta', 'nota_credito', 'nota_debito', 'guia_despacho', 'otro'])],
            'folio' => ['nullable', 'string', 'max:100'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'exento' => ['nullable', 'integer', 'min:0'],
            'estado' => ['sometimes', Rule::in(['borrador', 'emitido', 'pendiente', 'pagado', 'vencido', 'anulado'])],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.codigo' => ['nullable', 'string', 'max:100'],
            'detalles.*.descripcion' => ['required', 'string', 'max:255'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'detalles.*.descuento_porcentaje' => ['nullable', 'numeric', 'between:0,100'],
        ]);

        if (!empty($data['tercero_id'])) {
            abort_unless(
                Tercero::whereKey($data['tercero_id'])->where('centro_medico_id', $centroMedico->id)->exists(),
                404
            );
        }

        $documento = DB::transaction(function () use ($data, $centroMedico) {
            $detalles = $data['detalles'];
            unset($data['detalles']);

            $neto = 0;
            foreach ($detalles as &$detalle) {
                $detalle['cantidad'] = round((float) $detalle['cantidad'], 3);
                $detalle['precio_unitario'] = round((float) $detalle['precio_unitario'], 3);
                $bruto = $detalle['cantidad'] * $detalle['precio_unitario'];
                $detalle['total'] = (int) round($bruto * (1 - (($detalle['descuento_porcentaje'] ?? 0) / 100)));
                $neto += $detalle['total'];
            }

            $data['neto'] = $neto;
            $data['exento'] = (int) ($data['exento'] ?? 0);
            $data['impuesto'] = in_array($data['tipo_documento'], ['factura', 'boleta', 'nota_debito', 'nota_credito'], true)
                ? (int) round(max(0, $neto - $data['exento']) * 0.19)
                : 0;
            $data['total'] = $neto + $data['impuesto'];

            $documento = $centroMedico->documentosTributarios()->create($data);
            $documento->detalles()->createMany($detalles);

            return $documento;
        });

        if (!$request->expectsJson()) {
            if ($request->filled('_redirect_to')) {
                return redirect()->back()->with('status', 'Documento tributario registrado.');
            }

            return redirect()
                ->route('contabilidad.escritorio', ['centroMedico' => $centroMedico->id])
                ->with('status', 'Documento tributario registrado.');
        }

        return response()->json($documento->load(['tercero', 'detalles']), 201);
    }

    public function subirFacturaEmitida(Request $request, CentroMedico $centroMedico)
    {
        $data = $request->validate([
            'tercero_id' => ['nullable', 'exists:terceros,id'],
            'receptor' => ['nullable', 'array'],
            'receptor.rut' => ['nullable', 'string', 'max:20'],
            'receptor.razon_social' => ['nullable', 'string', 'max:255'],
            'receptor.giro' => ['nullable', 'string', 'max:255'],
            'receptor.email' => ['nullable', 'email', 'max:255'],
            'receptor.telefono' => ['nullable', 'string', 'max:30'],
            'receptor.direccion' => ['nullable', 'string', 'max:255'],
            'receptor.comuna' => ['nullable', 'string', 'max:120'],
            'tipo_documento' => ['required', Rule::in(['factura', 'boleta', 'nota_credito', 'nota_debito'])],
            'folio' => ['required', 'string', 'max:100'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'exento' => ['nullable', 'integer', 'min:0'],
            'estado' => ['sometimes', Rule::in(['emitido', 'pendiente', 'pagado'])],
            'archivo' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.codigo' => ['nullable', 'string', 'max:100'],
            'detalles.*.descripcion' => ['required', 'string', 'max:255'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'detalles.*.descuento_porcentaje' => ['nullable', 'numeric', 'between:0,100'],
        ]);

        if (empty($data['tercero_id']) && !empty($data['receptor']['rut']) && !empty($data['receptor']['razon_social'])) {
            $receptor = $data['receptor'];
            $tercero = $centroMedico->terceros()->updateOrCreate(
                ['rut' => $receptor['rut']],
                [
                    'tipo' => 'cliente',
                    'razon_social' => $receptor['razon_social'],
                    'giro' => $receptor['giro'] ?? null,
                    'email' => $receptor['email'] ?? null,
                    'telefono' => $receptor['telefono'] ?? null,
                    'direccion' => $receptor['direccion'] ?? null,
                    'comuna' => $receptor['comuna'] ?? null,
                    'activo' => true,
                ]
            );
            $data['tercero_id'] = $tercero->id;
        }
        unset($data['receptor']);

        $data['naturaleza'] = 'venta';
        $data['estado'] = $data['estado'] ?? 'emitido';
        $data['observaciones'] = trim(($data['observaciones'] ?? '') . "\nOrigen API: factura emitida por institucion.");

        $request->merge($data);

        return $this->store($request, $centroMedico);
    }

    public function liquidar(Request $request, CentroMedico $centroMedico, DocumentoTributario $documento)
    {
        $this->assertCentro($centroMedico, $documento);
        abort_if(in_array($documento->estado, ['pagado', 'anulado'], true), 422, 'El documento no puede liquidarse.');

        $data = $request->validate([
            'fecha_pago' => ['required', 'date'],
            'medio_pago' => ['required', Rule::in(['efectivo', 'transferencia', 'tarjeta', 'cheque', 'otro'])],
            'referencia' => ['nullable', 'string', 'max:150'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $movimiento = DB::transaction(function () use ($data, $centroMedico, $documento) {
            $documento->update([
                'estado' => 'pagado',
                'fecha_pago' => $data['fecha_pago'],
            ]);

            return $centroMedico->movimientos()->create([
                'documento_tributario_id' => $documento->id,
                'tipo' => $documento->naturaleza === 'venta' ? 'ingreso' : 'egreso',
                'fecha' => $data['fecha_pago'],
                'categoria' => $documento->tipo_documento,
                'glosa' => ($documento->naturaleza === 'venta' ? 'Cobro ' : 'Pago ') .
                    ($documento->folio ?: "#{$documento->id}"),
                'monto' => $documento->total,
                'medio_pago' => $data['medio_pago'],
                'referencia' => $data['referencia'] ?? null,
                'estado' => 'pagado',
                'fecha_pago' => $data['fecha_pago'],
                'observaciones' => $data['observaciones'] ?? null,
            ]);
        });

        return response()->json([
            'documento' => $documento->fresh()->load(['tercero', 'detalles']),
            'movimiento' => $movimiento,
        ]);
    }

    private function assertCentro(CentroMedico $centroMedico, DocumentoTributario $documento): void
    {
        abort_unless($documento->centro_medico_id === $centroMedico->id, 404);
    }
}
