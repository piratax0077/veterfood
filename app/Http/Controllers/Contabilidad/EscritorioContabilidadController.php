<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\CentroMedico;
use App\Models\Contrato;
use App\Models\DocumentoTributario;
use App\Models\Finiquito;
use App\Models\MovimientoContable;
use App\Models\ObligacionLaboral;
use App\Models\RequerimientoContable;
use App\Models\Remuneracion;
use App\Models\SolicitudAusencia;
use App\Models\Tercero;
use App\Models\Trabajador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class EscritorioContabilidadController extends Controller
{
    public function instituciones(Request $request)
    {
        $user = $request->user();

        $alertCounts = [
            'requerimientosContables as requerimientos_abiertos_count' => fn ($query) => $query->whereIn('estado', ['pendiente', 'en_preparacion']),
            'requerimientosContables as requerimientos_firma_count' => fn ($query) => $query->where('estado', 'listo_para_firma'),
        ];

        $centros = $user->tieneRol('admin')
            ? CentroMedico::where('activo', true)
                ->withCount($alertCounts)
                ->orderBy('razon_social')
                ->get()
            : $user->centrosMedicos()
                ->where('centros_medicos.activo', true)
                ->withCount($alertCounts)
                ->orderBy('razon_social')
                ->get();

        return view('contabilidad.instituciones', [
            'centros' => $centros,
            'user' => $user,
        ]);
    }

    public function storeInstitucion(Request $request)
    {
        $data = $request->validate([
            'rut' => ['required', 'string', 'max:20', 'unique:centros_medicos,rut'],
            'rut_representante_legal' => ['nullable', 'string', 'max:20'],
            'representante_legal' => ['nullable', 'string', 'max:255'],
            'clave_serv_impuestos' => ['nullable', 'string', 'max:255'],
            'razon_social' => ['required', 'string', 'max:255'],
            'nombre_fantasia' => ['nullable', 'string', 'max:255'],
            'giro' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'comuna' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'contacto_comercial' => ['nullable', 'string', 'max:255'],
            'valor_pactado_servicio' => ['nullable', 'integer', 'min:0'],
            'sucursales_texto' => ['nullable', 'string', 'max:2000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $sucursales = collect(preg_split('/\r\n|\r|\n/', (string) ($data['sucursales_texto'] ?? '')))
            ->map(fn ($linea) => trim($linea))
            ->filter()
            ->values()
            ->all();

        $centro = CentroMedico::create([
            'rut' => $data['rut'],
            'rut_representante_legal' => $data['rut_representante_legal'] ?? null,
            'representante_legal' => $data['representante_legal'] ?? null,
            'clave_serv_impuestos' => filled($data['clave_serv_impuestos'] ?? null)
                ? Crypt::encryptString($data['clave_serv_impuestos'])
                : null,
            'razon_social' => $data['razon_social'],
            'nombre_fantasia' => $data['nombre_fantasia'] ?? null,
            'giro' => $data['giro'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'comuna' => $data['comuna'] ?? null,
            'region' => $data['region'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'contacto_comercial' => $data['contacto_comercial'] ?? null,
            'valor_pactado_servicio' => (int) ($data['valor_pactado_servicio'] ?? 0),
            'sucursales' => $sucursales,
            'observaciones' => $data['observaciones'] ?? null,
            'activo' => true,
        ]);

        $user = $request->user();
        $centro->usuarios()->syncWithoutDetaching([
            $user->id => [
                'rol' => $user->tieneRol('admin') ? 'administrador' : 'contador',
                'permisos' => json_encode(['contabilidad:read', 'contabilidad:write', 'contabilidad:pay']),
                'activo' => false,
                'estado_relacion' => $user->tieneRol('admin') ? 'pendiente_contador' : 'pendiente_admin',
                'solicitado_por_id' => $user->id,
                'aprobado_admin_por_id' => $user->tieneRol('admin') ? $user->id : null,
                'aprobado_admin_at' => $user->tieneRol('admin') ? now() : null,
            ],
        ]);

        return redirect()
            ->route('contabilidad.panel')
            ->with('ok', 'Institucion contable creada y vinculada al usuario.');
    }

    public function aceptarRelacion(Request $request, CentroMedico $centroMedico)
    {
        $user = $request->user();
        $membership = $user->centrosMedicos()
            ->where('centros_medicos.id', $centroMedico->id)
            ->firstOrFail();

        abort_unless(
            data_get($membership, 'pivot.estado_relacion') === 'pendiente_contador' &&
            data_get($membership, 'pivot.aprobado_admin_at'),
            403,
            'La relacion aun no esta aprobada por administracion.'
        );

        $centroMedico->usuarios()->updateExistingPivot($user->id, [
            'estado_relacion' => 'activo',
            'aprobado_contador_at' => now(),
            'activo' => true,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('contabilidad.escritorio', ['centroMedico' => $centroMedico->id])
            ->with('ok', 'Relacion contable aceptada. Escritorio habilitado.');
    }

    public function index(Request $request, CentroMedico $centroMedico)
    {
        $membership = $request->attributes->get('centro_medico_membership');
        if ($this->isClientOnlyAccess($request, $membership)) {
            return redirect()->route('contabilidad.cliente', ['centroMedico' => $centroMedico->id]);
        }

        [$desde, $hasta, $resumen] = $this->buildResumen($request, $centroMedico);

        $movimientosRecientes = MovimientoContable::where('centro_medico_id', $centroMedico->id)
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderByDesc('fecha')
            ->latest()
            ->take(12)
            ->get();

        $documentosRecientes = DocumentoTributario::with('tercero')
            ->where('centro_medico_id', $centroMedico->id)
            ->orderByDesc('fecha_emision')
            ->latest()
            ->take(12)
            ->get();

        $terceros = Tercero::where('centro_medico_id', $centroMedico->id)
            ->where('activo', true)
            ->orderBy('razon_social')
            ->take(80)
            ->get();

        $trabajadores = Trabajador::where('centro_medico_id', $centroMedico->id)
            ->where('activo', true)
            ->orderBy('apellido_paterno')
            ->take(80)
            ->get();

        $requerimientosPendientes = RequerimientoContable::with(['solicitante', 'trabajador'])
            ->where('centro_medico_id', $centroMedico->id)
            ->whereIn('estado', ['pendiente', 'en_preparacion'])
            ->orderByRaw("FIELD(prioridad, 'urgente', 'alta', 'normal')")
            ->orderBy('created_at')
            ->take(12)
            ->get();

        $requerimientosRecientes = RequerimientoContable::with(['solicitante', 'contador', 'trabajador'])
            ->where('centro_medico_id', $centroMedico->id)
            ->orderByDesc('created_at')
            ->take(12)
            ->get();

        return view('contabilidad.escritorio', compact(
            'centroMedico',
            'resumen',
            'desde',
            'hasta',
            'movimientosRecientes',
            'documentosRecientes',
            'terceros',
            'trabajadores',
            'requerimientosPendientes',
            'requerimientosRecientes'
        ));
    }

    public function cliente(Request $request, CentroMedico $centroMedico)
    {
        [$desde, $hasta, $resumen] = $this->buildResumen($request, $centroMedico);

        $documentos = DocumentoTributario::with('tercero')
            ->where('centro_medico_id', $centroMedico->id)
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $requerimientos = RequerimientoContable::with(['contador', 'trabajador', 'documento'])
            ->where('centro_medico_id', $centroMedico->id)
            ->orderByDesc('created_at')
            ->take(30)
            ->get();

        return view('contabilidad.cliente_dashboard', compact(
            'centroMedico',
            'resumen',
            'desde',
            'hasta',
            'documentos',
            'requerimientos'
        ));
    }

    public function entregarDocumentoContador(Request $request, CentroMedico $centroMedico)
    {
        if ($this->isClientOnlyAccess($request, $request->attributes->get('centro_medico_membership'))) {
            abort(403, 'Solo el contador puede entregar documentos preparados.');
        }

        $data = $request->validate([
            'requerimiento_id' => ['nullable', 'integer'],
            'tipo_entrega' => ['required', 'in:liquidacion_bonos,pago_vouchers,contrato,finiquito,remuneracion,impuesto,declaracion_impuestos,pago_cotizaciones,pago_seguro_cesantia,pago_caja_compensacion,pago_salud,respuesta_solicitud,otro'],
            'destinatario' => ['nullable', 'string', 'max:255'],
            'folio' => ['nullable', 'string', 'max:100'],
            'fecha_emision' => ['required', 'date'],
            'monto_total' => ['nullable', 'integer', 'min:0'],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xlsx,xls,xml,csv', 'max:10240'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $path = $request->file('archivo')->store("contabilidad/centro-{$centroMedico->id}/entregas-contador", 'local');
        $total = (int) ($data['monto_total'] ?? 0);

        $documento = DocumentoTributario::create([
            'centro_medico_id' => $centroMedico->id,
            'naturaleza' => in_array($data['tipo_entrega'], ['liquidacion_bonos', 'pago_vouchers', 'remuneracion', 'finiquito', 'impuesto', 'declaracion_impuestos', 'pago_cotizaciones', 'pago_seguro_cesantia', 'pago_caja_compensacion', 'pago_salud'], true) ? 'compra' : 'venta',
            'tipo_documento' => 'otro',
            'folio' => $data['folio'] ?: 'ENT-' . now()->format('ymd-His'),
            'fecha_emision' => $data['fecha_emision'],
            'neto' => $total,
            'exento' => 0,
            'impuesto' => 0,
            'total' => $total,
            'estado' => 'emitido',
            'archivo' => $path,
            'observaciones' => implode("\n", array_filter([
                'Documento entregado por contador: ' . str_replace('_', ' ', $data['tipo_entrega']),
                'Destinatario: ' . ($data['destinatario'] ?? 'Institucion'),
                $data['observaciones'] ?? null,
            ])),
        ]);

        if (!empty($data['requerimiento_id'])) {
            $requerimiento = RequerimientoContable::where('centro_medico_id', $centroMedico->id)
                ->where('id', $data['requerimiento_id'])
                ->first();

            if ($requerimiento) {
                $requerimiento->update([
                    'contador_id' => $request->user()?->id,
                    'documento_tributario_id' => $documento->id,
                    'archivo_respuesta' => $path,
                    'estado' => 'listo_para_firma',
                    'respondido_at' => now(),
                    'firmado_contador_at' => now(),
                    'firma_hash' => hash('sha256', $requerimiento->codigo . '|' . $documento->id . '|' . now()->timestamp),
                ]);
            }
        }

        return redirect()->back()->with('status', 'Documento preparado y disponible para la institucion.');
    }

    public function subirDocumentoCliente(Request $request, CentroMedico $centroMedico)
    {
        $data = $request->validate([
            'tipo_documento' => ['required', 'in:factura,boleta,nota_credito,nota_debito,guia_despacho,otro'],
            'naturaleza' => ['required', 'in:venta,compra'],
            'folio' => ['nullable', 'string', 'max:100'],
            'fecha_emision' => ['required', 'date'],
            'monto_total' => ['required', 'integer', 'min:0'],
            'clasificacion' => ['required', 'in:ventas,compras,remuneraciones,impuestos,contratos,finiquitos,otros'],
            'documento' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,xml,csv,xlsx,xls,doc,docx', 'max:10240'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $path = $request->file('documento')->store("contabilidad/centro-{$centroMedico->id}", 'local');
        $neto = in_array($data['tipo_documento'], ['factura', 'boleta', 'nota_debito', 'nota_credito'], true)
            ? (int) round($data['monto_total'] / 1.19)
            : (int) $data['monto_total'];
        $iva = max(0, (int) $data['monto_total'] - $neto);

        DocumentoTributario::create([
            'centro_medico_id' => $centroMedico->id,
            'naturaleza' => $data['naturaleza'],
            'tipo_documento' => $data['tipo_documento'],
            'folio' => $data['folio'],
            'fecha_emision' => $data['fecha_emision'],
            'neto' => $neto,
            'exento' => 0,
            'impuesto' => $iva,
            'total' => $data['monto_total'],
            'estado' => 'emitido',
            'archivo' => $path,
            'observaciones' => trim(($data['observaciones'] ?? '') . "\nClasificacion sugerida: {$data['clasificacion']}. Documento subido por cliente/institucion."),
        ]);

        return redirect()->back()->with('status', 'Documento recibido y agregado a la institucion.');
    }

    public function solicitarDocumentoCliente(Request $request, CentroMedico $centroMedico)
    {
        $data = $this->validarRequerimiento($request);

        $path = $request->hasFile('archivo')
            ? $request->file('archivo')->store("contabilidad/centro-{$centroMedico->id}/solicitudes", 'local')
            : null;

        RequerimientoContable::create([
            'centro_medico_id' => $centroMedico->id,
            'solicitante_id' => $request->user()?->id,
            'codigo' => 'REQ-' . now()->format('ymdHis') . '-' . random_int(100, 999),
            'tipo' => $data['tipo_solicitud'],
            'titulo' => $this->tituloRequerimiento($data),
            'prioridad' => $data['prioridad'] ?? 'normal',
            'estado' => 'pendiente',
            'datos' => $this->datosRequerimiento($data),
            'detalle' => $data['detalle'],
            'archivo_solicitud' => $path,
            'requiere_firma_institucion' => true,
            'requiere_firma_trabajador' => in_array($data['tipo_solicitud'], ['contrato', 'anexo_contrato', 'finiquito'], true),
            'fecha_requerida' => $data['fecha_requerida'] ?? $data['fecha_evento'] ?? null,
        ]);

        return redirect()->back()->with('status', 'Solicitud enviada al contador.');
    }

    public function requerimientos(Request $request, CentroMedico $centroMedico)
    {
        $estado = $request->input('estado');

        return RequerimientoContable::with(['solicitante', 'contador', 'trabajador', 'documento'])
            ->where('centro_medico_id', $centroMedico->id)
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->orderByDesc('created_at')
            ->paginate(30);
    }

    public function crearRequerimientoApi(Request $request, CentroMedico $centroMedico)
    {
        $data = $this->validarRequerimiento($request, false);

        $requerimiento = RequerimientoContable::create([
            'centro_medico_id' => $centroMedico->id,
            'solicitante_id' => $request->user()?->id,
            'codigo' => 'REQ-' . now()->format('ymdHis') . '-' . random_int(100, 999),
            'tipo' => $data['tipo_solicitud'],
            'titulo' => $this->tituloRequerimiento($data),
            'prioridad' => $data['prioridad'] ?? 'normal',
            'estado' => 'pendiente',
            'datos' => $this->datosRequerimiento($data),
            'detalle' => $data['detalle'],
            'requiere_firma_institucion' => true,
            'requiere_firma_trabajador' => in_array($data['tipo_solicitud'], ['contrato', 'anexo_contrato', 'finiquito'], true),
            'fecha_requerida' => $data['fecha_requerida'] ?? $data['fecha_evento'] ?? null,
        ]);

        return response()->json($requerimiento, 201);
    }

    public function firmarRequerimiento(Request $request, CentroMedico $centroMedico, RequerimientoContable $requerimiento)
    {
        abort_unless($requerimiento->centro_medico_id === $centroMedico->id, 404);

        $data = $request->validate([
            'firmante' => ['required', 'in:institucion,trabajador,contador'],
        ]);

        $updates = [
            'firma_hash' => $requerimiento->firma_hash ?: hash('sha256', $requerimiento->codigo . '|' . $centroMedico->id . '|' . now()->timestamp),
        ];

        if ($data['firmante'] === 'institucion') {
            $updates['firmado_institucion_at'] = now();
        }
        if ($data['firmante'] === 'trabajador') {
            $updates['firmado_trabajador_at'] = now();
        }
        if ($data['firmante'] === 'contador') {
            $updates['firmado_contador_at'] = now();
        }

        $requerimiento->update($updates);
        $requerimiento->refresh();

        $institucionOk = !$requerimiento->requiere_firma_institucion || $requerimiento->firmado_institucion_at;
        $trabajadorOk = !$requerimiento->requiere_firma_trabajador || $requerimiento->firmado_trabajador_at;
        if ($institucionOk && $trabajadorOk && $requerimiento->firmado_contador_at) {
            $requerimiento->update(['estado' => 'firmado']);
        }

        if (!$request->expectsJson()) {
            return redirect()->back()->with('status', 'Firma registrada en el requerimiento.');
        }

        return response()->json($requerimiento->fresh());
    }

    public function resumen(Request $request, CentroMedico $centroMedico)
    {
        [$desde, $hasta, $resumen] = $this->buildResumen($request, $centroMedico);

        return response()->json([
            'centro_medico_id' => $centroMedico->id,
            'centro' => $centroMedico->nombre_fantasia ?: $centroMedico->razon_social,
            'periodo' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],
            'resumen' => $resumen,
        ]);
    }

    public function seccion(Request $request, CentroMedico $centroMedico, string $seccion)
    {
        $modulos = $this->modulos();
        abort_unless(array_key_exists($seccion, $modulos), 404);
        if ($this->isClientOnlyAccess($request, $request->attributes->get('centro_medico_membership'))) {
            return redirect()->route('contabilidad.cliente', ['centroMedico' => $centroMedico->id]);
        }

        $buscar = trim((string) $request->input('buscar', ''));
        $tipoMovimiento = match ($seccion) {
            'ingresos' => 'ingreso',
            'egresos' => 'egreso',
            default => null,
        };

        $movimientos = MovimientoContable::query()
            ->where('centro_medico_id', $centroMedico->id)
            ->when($tipoMovimiento, fn ($query) => $query->where('tipo', $tipoMovimiento))
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('categoria', 'like', "%{$buscar}%")
                        ->orWhere('glosa', 'like', "%{$buscar}%")
                        ->orWhere('referencia', 'like', "%{$buscar}%");
                });
            })
            ->orderByDesc('fecha')
            ->paginate(12, ['*'], 'movimientos')
            ->withQueryString();

        $documentos = DocumentoTributario::with('tercero')
            ->where('centro_medico_id', $centroMedico->id)
            ->when(in_array($seccion, ['factura', 'ingresos'], true), fn ($query) => $query->where('naturaleza', 'venta'))
            ->when($seccion === 'egresos', fn ($query) => $query->where('naturaleza', 'compra'))
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('folio', 'like', "%{$buscar}%")
                        ->orWhere('tipo_documento', 'like', "%{$buscar}%")
                        ->orWhereHas('tercero', fn ($third) => $third->where('razon_social', 'like', "%{$buscar}%"));
                });
            })
            ->orderByDesc('fecha_emision')
            ->paginate(12, ['*'], 'documentos')
            ->withQueryString();

        $terceros = Tercero::where('centro_medico_id', $centroMedico->id)
            ->when($seccion === 'proveedores', fn ($query) => $query->whereIn('tipo', ['proveedor', 'ambos']))
            ->when($seccion === 'convenios', fn ($query) => $query->whereIn('tipo', ['cliente', 'ambos']))
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('razon_social', 'like', "%{$buscar}%")
                        ->orWhere('rut', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                });
            })
            ->where('activo', true)
            ->orderBy('razon_social')
            ->paginate(12, ['*'], 'terceros')
            ->withQueryString();

        $trabajadores = Trabajador::with(['contratoVigente', 'cuentasBancarias'])
            ->where('centro_medico_id', $centroMedico->id)
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                        ->orWhere('rut', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('apellido_paterno')
            ->paginate(12, ['*'], 'trabajadores')
            ->withQueryString();

        $remuneraciones = Remuneracion::with('contrato.trabajador')
            ->whereHas('contrato.trabajador', function ($query) use ($centroMedico, $buscar, $seccion) {
                $query->where('centro_medico_id', $centroMedico->id)
                    ->when($seccion === 'liquidaciones', fn ($worker) => $worker->where('tipo', 'profesional'))
                    ->when($buscar !== '', function ($worker) use ($buscar) {
                        $worker->where(function ($subquery) use ($buscar) {
                            $subquery->where('nombres', 'like', "%{$buscar}%")
                                ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                                ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                                ->orWhere('rut', 'like', "%{$buscar}%")
                                ->orWhere('email', 'like', "%{$buscar}%");
                        });
                    });
            })
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->paginate(12, ['*'], 'remuneraciones')
            ->withQueryString();

        $contratos = \App\Models\Contrato::with('trabajador')
            ->whereHas('trabajador', function ($query) use ($centroMedico, $buscar, $seccion) {
                $query->where('centro_medico_id', $centroMedico->id)
                    ->where('activo', true)
                    ->when($seccion === 'liquidaciones', fn ($worker) => $worker->where('tipo', 'profesional'))
                    ->when($buscar !== '', function ($worker) use ($buscar) {
                        $worker->where(function ($subquery) use ($buscar) {
                            $subquery->where('nombres', 'like', "%{$buscar}%")
                                ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                                ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                                ->orWhere('rut', 'like', "%{$buscar}%")
                                ->orWhere('email', 'like', "%{$buscar}%");
                        });
                    });
            })
            ->where('estado', 'vigente')
            ->orderByDesc('id')
            ->take(80)
            ->get();

        $estadisticas = $this->estadisticasContables($centroMedico);
        $impuestos = $this->impuestosContables($centroMedico);

        return view('contabilidad.seccion', [
            'centroMedico' => $centroMedico,
            'seccion' => $seccion,
            'modulo' => $modulos[$seccion],
            'modulos' => $modulos,
            'buscar' => $buscar,
            'movimientos' => $movimientos,
            'documentos' => $documentos,
            'terceros' => $terceros,
            'trabajadores' => $trabajadores,
            'remuneraciones' => $remuneraciones,
            'contratos' => $contratos,
            'estadisticas' => $estadisticas,
            'impuestos' => $impuestos,
        ]);
    }

    public function crearTrabajador(CentroMedico $centroMedico)
    {
        if ($this->isClientOnlyAccess(request(), request()->attributes->get('centro_medico_membership'))) {
            return redirect()->route('contabilidad.cliente', ['centroMedico' => $centroMedico->id]);
        }

        return view('contabilidad.trabajador_form', [
            'centroMedico' => $centroMedico,
            'trabajador' => new Trabajador(['activo' => true, 'tipo' => 'administrativo']),
            'modo' => 'crear',
        ]);
    }

    public function editarTrabajador(CentroMedico $centroMedico, Trabajador $trabajador)
    {
        abort_unless($trabajador->centro_medico_id === $centroMedico->id, 404);
        if ($this->isClientOnlyAccess(request(), request()->attributes->get('centro_medico_membership'))) {
            return redirect()->route('contabilidad.cliente', ['centroMedico' => $centroMedico->id]);
        }

        return view('contabilidad.trabajador_form', [
            'centroMedico' => $centroMedico,
            'trabajador' => $trabajador->load('cuentasBancarias'),
            'modo' => 'editar',
        ]);
    }

    public function gestionarTrabajador(CentroMedico $centroMedico, Trabajador $trabajador)
    {
        abort_unless($trabajador->centro_medico_id === $centroMedico->id, 404);
        if ($this->isClientOnlyAccess(request(), request()->attributes->get('centro_medico_membership'))) {
            return redirect()->route('contabilidad.cliente', ['centroMedico' => $centroMedico->id]);
        }

        $trabajador->load(['contratos.remuneraciones', 'contratoVigente', 'cuentasBancarias']);
        $contratos = Contrato::with(['remuneraciones', 'finiquito'])
            ->where('trabajador_id', $trabajador->id)
            ->orderByDesc('fecha_inicio')
            ->get();
        $contratoActivo = $trabajador->contratoVigente ?: $contratos->first();
        $remuneraciones = Remuneracion::with('contrato')
            ->whereHas('contrato', fn ($query) => $query->where('trabajador_id', $trabajador->id))
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->get();
        $finiquitos = Finiquito::with('contrato')
            ->whereHas('contrato', fn ($query) => $query->where('trabajador_id', $trabajador->id))
            ->orderByDesc('fecha_salida')
            ->get();

        return view('contabilidad.trabajador_gestion', compact(
            'centroMedico',
            'trabajador',
            'contratos',
            'contratoActivo',
            'remuneraciones',
            'finiquitos'
        ));
    }

    private function modulos(): array
    {
        return [
            'rrhh' => ['titulo' => 'Recursos humanos', 'descripcion' => 'Personal, contratos, cuentas y datos laborales.', 'icono' => 'RH', 'color' => '#2563eb'],
            'info-pago-sueldos' => ['titulo' => 'Info. sueldos personal', 'descripcion' => 'Datos bancarios, pagos y estado de remuneraciones.', 'icono' => '$', 'color' => '#0891b2'],
            'liquidaciones' => ['titulo' => 'Liquidaciones a profesionales', 'descripcion' => 'Pagos, honorarios y documentos pendientes.', 'icono' => 'L', 'color' => '#15803d'],
            'remuneraciones' => ['titulo' => 'Pago remuneraciones', 'descripcion' => 'Calculo mensual, descuentos y pagos.', 'icono' => 'R', 'color' => '#7c3aed'],
            'contable' => ['titulo' => 'Libro contable', 'descripcion' => 'Movimientos, conciliacion y resultado del centro.', 'icono' => 'LC', 'color' => '#0f172a'],
            'ingresos' => ['titulo' => 'Ingresos', 'descripcion' => 'Ventas, cobros y documentos emitidos.', 'icono' => '+', 'color' => '#16a34a'],
            'egresos' => ['titulo' => 'Egresos', 'descripcion' => 'Compras, pagos, costos y gastos operativos.', 'icono' => '-', 'color' => '#dc2626'],
            'impuestos' => ['titulo' => 'Impuestos', 'descripcion' => 'Resumen para declaracion y control tributario.', 'icono' => '%', 'color' => '#ea580c'],
            'convenios' => ['titulo' => 'Convenios', 'descripcion' => 'Instituciones, clientes convenio y acuerdos.', 'icono' => 'CV', 'color' => '#0d9488'],
            'proveedores' => ['titulo' => 'Proveedores', 'descripcion' => 'Proveedores, contacto y documentos asociados.', 'icono' => 'P', 'color' => '#64748b'],
            'factura' => ['titulo' => 'Facturar', 'descripcion' => 'Boletas, facturas, compras y ventas.', 'icono' => 'F', 'color' => '#f59e0b'],
            'estadisticas' => ['titulo' => 'Estadisticas', 'descripcion' => 'Indicadores mensuales y lectura financiera.', 'icono' => 'G', 'color' => '#db2777'],
        ];
    }

    private function estadisticasContables(CentroMedico $centroMedico): array
    {
        $desde = now()->subMonths(11)->startOfMonth();
        $hasta = now()->endOfMonth();

        $movimientos = MovimientoContable::where('centro_medico_id', $centroMedico->id)
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('estado', '!=', 'anulado')
            ->get();

        $meses = collect(range(0, 11))->map(function (int $offset) use ($desde, $movimientos) {
            $mes = $desde->copy()->addMonths($offset);
            $itemsMes = $movimientos->filter(fn ($movimiento) => $movimiento->fecha?->format('Y-m') === $mes->format('Y-m'));
            $ingresos = (int) $itemsMes->where('tipo', 'ingreso')->sum('monto');
            $egresos = (int) $itemsMes->where('tipo', 'egreso')->sum('monto');

            return [
                'periodo' => $mes->format('m/Y'),
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'resultado' => $ingresos - $egresos,
            ];
        });

        $ingresos = (int) $movimientos->where('tipo', 'ingreso')->sum('monto');
        $egresos = (int) $movimientos->where('tipo', 'egreso')->sum('monto');
        $resultado = $ingresos - $egresos;
        $maximoGrafico = max(1, $meses->max(fn ($mes) => max($mes['ingresos'], $mes['egresos'], abs($mes['resultado']))));

        return [
            'totales' => [
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'resultado' => $resultado,
                'margen' => $ingresos > 0 ? round(($resultado / $ingresos) * 100, 1) : 0,
            ],
            'meses' => $meses,
            'maximo_grafico' => $maximoGrafico,
            'categorias_ingreso' => $movimientos->where('tipo', 'ingreso')->groupBy('categoria')->map(fn ($items, $categoria) => [
                'categoria' => $categoria ?: 'Sin categoria',
                'total' => (int) $items->sum('monto'),
            ])->sortByDesc('total')->take(8)->values(),
            'categorias_egreso' => $movimientos->where('tipo', 'egreso')->groupBy('categoria')->map(fn ($items, $categoria) => [
                'categoria' => $categoria ?: 'Sin categoria',
                'total' => (int) $items->sum('monto'),
            ])->sortByDesc('total')->take(8)->values(),
            'iva_estimado' => [
                'debito' => (int) round($ingresos * 0.19),
                'credito' => (int) round($egresos * 0.19),
                'pago' => max(0, (int) round(($ingresos - $egresos) * 0.19)),
            ],
        ];
    }

    private function impuestosContables(CentroMedico $centroMedico): array
    {
        $desde = now()->startOfMonth();
        $hasta = now()->endOfMonth();

        $documentos = DocumentoTributario::with('tercero')
            ->where('centro_medico_id', $centroMedico->id)
            ->whereBetween('fecha_emision', [$desde, $hasta])
            ->get();

        $ventas = $documentos->where('naturaleza', 'venta');
        $compras = $documentos->where('naturaleza', 'compra');
        $debito = (int) $ventas->sum('impuesto');
        $credito = (int) $compras->sum('impuesto');
        $ivaPagar = max(0, $debito - $credito);

        $obligaciones = ObligacionLaboral::whereHas(
            'remuneracion.contrato.trabajador',
            fn ($query) => $query->where('centro_medico_id', $centroMedico->id)
        )
            ->whereBetween('fecha_vencimiento', [$desde, $hasta->copy()->addMonth()])
            ->orderBy('fecha_vencimiento')
            ->get();

        $pagosPendientes = MovimientoContable::where('centro_medico_id', $centroMedico->id)
            ->where('tipo', 'egreso')
            ->whereIn('estado', ['pendiente', 'pagado'])
            ->where(function ($query) {
                $query->where('categoria', 'like', '%impuesto%')
                    ->orWhere('categoria', 'like', '%cotizacion%')
                    ->orWhere('categoria', 'like', '%salud%')
                    ->orWhere('categoria', 'like', '%caja%');
            })
            ->orderByDesc('fecha')
            ->take(12)
            ->get();

        return [
            'periodo' => $desde->format('m/Y'),
            'resumen' => [
                'ventas_neto' => (int) $ventas->sum('neto'),
                'compras_neto' => (int) $compras->sum('neto'),
                'debito_iva' => $debito,
                'credito_iva' => $credito,
                'iva_pagar' => $ivaPagar,
                'documentos_venta' => $ventas->count(),
                'documentos_compra' => $compras->count(),
            ],
            'documentos_venta' => $ventas->sortByDesc('fecha_emision')->take(8)->values(),
            'documentos_compra' => $compras->sortByDesc('fecha_emision')->take(8)->values(),
            'obligaciones' => $obligaciones,
            'pagos_pendientes' => $pagosPendientes,
            'checklist' => [
                ['nombre' => 'Libro de ventas', 'estado' => $ventas->count() > 0 ? 'con datos' : 'sin documentos'],
                ['nombre' => 'Libro de compras', 'estado' => $compras->count() > 0 ? 'con datos' : 'sin documentos'],
                ['nombre' => 'IVA mensual', 'estado' => $ivaPagar > 0 ? 'por declarar/pagar' : 'sin pago estimado'],
                ['nombre' => 'Cotizaciones y salud', 'estado' => $obligaciones->where('estado', 'pendiente')->count() > 0 ? 'pendiente' : 'al dia'],
            ],
        ];
    }

    private function isClientOnlyAccess(Request $request, $membership): bool
    {
        $user = $request->user();
        if ($user?->tieneRol('admin')) {
            return false;
        }

        $permissions = $membership?->permisos ? json_decode($membership->permisos, true) ?: [] : [];

        return !in_array('contabilidad:write', $permissions, true)
            && !in_array('contabilidad:pay', $permissions, true);
    }

    private function buildResumen(Request $request, CentroMedico $centroMedico): array
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->input('desde')) : now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->input('hasta')) : now()->endOfMonth();

        $movimientos = MovimientoContable::query()
            ->where('centro_medico_id', $centroMedico->id)
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('estado', '!=', 'anulado');

        $resumen = [
            'trabajadores_activos' => Trabajador::where('centro_medico_id', $centroMedico->id)->where('activo', true)->count(),
            'ingresos' => (clone $movimientos)->where('tipo', 'ingreso')->sum('monto'),
            'egresos' => (clone $movimientos)->where('tipo', 'egreso')->sum('monto'),
            'remuneraciones_pendientes' => Remuneracion::whereHas(
                'contrato.trabajador',
                fn ($query) => $query->where('centro_medico_id', $centroMedico->id)
            )->whereIn('estado', ['borrador', 'calculada'])->sum('liquido_pagar'),
            'ausencias_pendientes' => SolicitudAusencia::whereHas(
                'trabajador',
                fn ($query) => $query->where('centro_medico_id', $centroMedico->id)
            )->where('estado', 'pendiente')->count(),
            'requerimientos_pendientes' => RequerimientoContable::where('centro_medico_id', $centroMedico->id)
                ->whereIn('estado', ['pendiente', 'en_preparacion'])
                ->count(),
            'cuentas_por_cobrar' => DocumentoTributario::where('centro_medico_id', $centroMedico->id)
                ->where('naturaleza', 'venta')->whereIn('estado', ['emitido', 'pendiente', 'vencido'])->sum('total'),
            'cuentas_por_pagar' => DocumentoTributario::where('centro_medico_id', $centroMedico->id)
                ->where('naturaleza', 'compra')->whereIn('estado', ['emitido', 'pendiente', 'vencido'])->sum('total'),
        ];

        $resumen['resultado'] = $resumen['ingresos'] - $resumen['egresos'];

        return [$desde, $hasta, $resumen];
    }

    private function validarRequerimiento(Request $request, bool $archivo = true): array
    {
        return $request->validate([
            'tipo_solicitud' => ['required', 'in:contrato,anexo_contrato,despido,vacaciones,variacion_sueldo,cambio_afp,licencia,finiquito,liquidacion,pago_vouchers,declaracion_impuestos,pago_cotizaciones,pago_seguro_cesantia,pago_caja_compensacion,pago_salud,otro'],
            'prioridad' => ['nullable', 'in:normal,alta,urgente'],
            'trabajador' => ['nullable', 'string', 'max:255'],
            'rut_trabajador' => ['nullable', 'string', 'max:20'],
            'email_trabajador' => ['nullable', 'email', 'max:150'],
            'telefono_trabajador' => ['nullable', 'string', 'max:30'],
            'cargo' => ['nullable', 'string', 'max:150'],
            'funciones' => ['nullable', 'string', 'max:2000'],
            'tipo_contrato' => ['nullable', 'in:indefinido,plazo_fijo,honorarios,prestacion_servicios'],
            'fecha_evento' => ['nullable', 'date'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_termino' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'fecha_requerida' => ['nullable', 'date'],
            'sueldo_base' => ['nullable', 'integer', 'min:0'],
            'monto_imponible' => ['nullable', 'integer', 'min:0'],
            'horas_semanales' => ['nullable', 'integer', 'between:1,60'],
            'afp' => ['nullable', 'string', 'max:150'],
            'salud_previsional' => ['nullable', 'string', 'max:150'],
            'tipo_salud' => ['nullable', 'in:fonasa,isapre,ffaa,otro'],
            'caja_compensacion' => ['nullable', 'string', 'max:150'],
            'mutualidad' => ['nullable', 'string', 'max:150'],
            'cargas_familiares' => ['nullable', 'integer', 'min:0', 'max:30'],
            'detalle' => ['required', 'string', 'max:3000'],
            'archivo' => [$archivo ? 'nullable' : 'prohibited', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xlsx,xls', 'max:10240'],
        ]);
    }

    private function tituloRequerimiento(array $data): string
    {
        $tipo = str_replace('_', ' ', $data['tipo_solicitud']);
        $trabajador = $data['trabajador'] ?? $data['rut_trabajador'] ?? 'trabajador no informado';

        return ucfirst($tipo) . ' - ' . $trabajador;
    }

    private function datosRequerimiento(array $data): array
    {
        return collect($data)
            ->except(['archivo', 'detalle'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }
}
