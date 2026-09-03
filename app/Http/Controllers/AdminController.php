<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use App\Models\CentroMedico;
use App\Models\Cliente;
use App\Models\ClienteNotificacion;
use App\Models\Existencia;
use App\Models\LocalVenta;
use App\Models\Mascota;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\PlanPedido;
use App\Models\Profesional;
use App\Models\Producto;
use App\Models\RutaReparto;
use App\Models\TrackingEvento;
use App\Models\User;
use App\Models\VoucherDescuento;
use App\Models\VoucherMovimiento;
use App\Services\VetSdiIntegrationService;
use App\Services\ContabilidadApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;

class AdminController extends Controller
{
    public function dashboard(VetSdiIntegrationService $vetSdi)
    {
        $stockCritico = Existencia::with(['producto', 'bodega', 'local'])
            ->get()
            ->filter(fn (Existencia $existencia) => $existencia->estaCritico());
        $relacionesContablesPendientes = DB::table('centro_medico_user as rel')
            ->join('centros_medicos as centros', 'centros.id', '=', 'rel.centro_medico_id')
            ->join('users', 'users.id', '=', 'rel.user_id')
            ->leftJoin('users as solicitante', 'solicitante.id', '=', 'rel.solicitado_por_id')
            ->where('rel.estado_relacion', 'pendiente_admin')
            ->select([
                'rel.id',
                'rel.user_id',
                'rel.centro_medico_id',
                'rel.created_at',
                'centros.razon_social',
                'centros.rut',
                'centros.valor_pactado_servicio',
                'users.name as contador_nombre',
                'users.email as contador_email',
                'solicitante.name as solicitante_nombre',
            ])
            ->orderByDesc('rel.created_at')
            ->get();

        return view('admin.dashboard', [
            'locales' => LocalVenta::latest()->get(),
            'bodegas' => Bodega::with('existencias.producto')->latest()->get(),
            'existencias' => Existencia::with(['producto', 'bodega', 'local'])->latest()->get(),
            'stockCritico' => $stockCritico,
            'usuarios' => User::with(['mascotas', 'localVenta'])->latest()->get(),
            'clientes' => User::with('perfilCliente')->whereIn('rol', ['cliente', 'dueno_mascota'])->orderBy('name')->get(),
            'vendedores' => User::where('rol', 'vendedor')->orderBy('name')->get(),
            'repartidores' => User::where('rol', 'repartidor')->orderBy('name')->get(),
            'productos' => Producto::orderBy('marca')->orderBy('nombre')->get(),
            'mascotas' => Mascota::with('cliente')->latest()->get(),
            'planes' => PlanPedido::with(['cliente', 'mascota', 'producto'])->latest()->get(),
            'pedidos' => Pedido::with(['items', 'repartidor', 'localVenta', 'bodega'])->latest()->take(30)->get(),
            'rutas' => RutaReparto::with(['repartidor', 'pedidos'])->latest()->get(),
            'relacionesContablesPendientes' => $relacionesContablesPendientes,
            'integracionVetSdi' => $vetSdi->status(),
        ]);
    }

    public function sincronizarVetSdi(VetSdiIntegrationService $vetSdi)
    {
        try {
            $resultado = $vetSdi->synchronize();
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['vet_sdi' => 'No fue posible sincronizar con VET-SDI. Revise la conexion y el registro del error.']);
        }

        return back()->with('ok', sprintf(
            'VET-SDI sincronizado: %d usuarios vinculados, %d mascotas y %d profesionales actualizados.',
            $resultado['usuarios'], $resultado['mascotas'], $resultado['profesionales']
        ));
    }

    public function aprobarRelacionContable(Request $request, CentroMedico $centroMedico, User $user)
    {
        $updated = DB::table('centro_medico_user')
            ->where('centro_medico_id', $centroMedico->id)
            ->where('user_id', $user->id)
            ->where('estado_relacion', 'pendiente_admin')
            ->update([
                'estado_relacion' => 'pendiente_contador',
                'aprobado_admin_por_id' => $request->user()->id,
                'aprobado_admin_at' => now(),
                'activo' => false,
                'updated_at' => now(),
            ]);

        abort_unless($updated > 0, 404);

        return redirect()
            ->route('admin.dashboard')
            ->with('ok', 'Relacion contable aprobada por administracion. Falta aceptacion final del contador.');
    }

    public function dashboardFinancieroAnalisis()
    {
        return view('admin.dashboard_financiero', [
            'financiero' => $this->dashboardFinanciero(),
        ]);
    }

    public function contabilidadIntegracion()
    {
        $contadoresApi = ['data' => [], 'error' => null];
        /** @var ContabilidadApiService $contabilidad */
        $contabilidad = app(ContabilidadApiService::class);
        if ($contabilidad->configurado()) {
            try {
                $contadoresApi['data'] = $contabilidad->contadores()['data'] ?? [];
            } catch (\Throwable) {
                $contadoresApi['error'] = 'No fue posible consultar los contadores. Verifique que el token incluya contabilidad:users.';
            }
        }

        return view('admin.contabilidad_integracion', [
            'local' => $this->resumenContableAlimentos(),
            'api' => $this->estadoApiContabilidad(),
            'externalWebUrl' => config('services.contabilidad.web_url'),
            'contadoresApi' => $contadoresApi,
        ]);
    }

    public function guardarContadorApi(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);
        $data['password_confirmation'] = $request->string('password_confirmation')->toString();

        try {
            app(ContabilidadApiService::class)->registrarContador($data);
        } catch (\Illuminate\Http\Client\RequestException $exception) {
            $errors = $exception->response->json('errors');
            return back()->withErrors(is_array($errors) ? $errors : [
                'contabilidad' => $exception->response->json('message', 'La API contable rechazó la creación del contador.'),
            ])->withInput($request->except(['password', 'password_confirmation']));
        } catch (\Throwable) {
            return back()->withErrors([
                'contabilidad' => 'No fue posible conectar con Contabilidad API para crear el contador.',
            ])->withInput($request->except(['password', 'password_confirmation']));
        }

        return redirect()->route('admin.contabilidad.integracion')
            ->with('ok', 'Contador creado correctamente en Contabilidad API.');
    }

    private function dashboardFinanciero(): array
    {
        $inicio = now()->subMonths(11)->startOfMonth();
        $meses = collect();

        for ($i = 0; $i < 12; $i++) {
            $fecha = $inicio->copy()->addMonths($i);
            $meses->push([
                'key' => $fecha->format('Y-m'),
                'label' => $fecha->format('m/Y'),
                'ingresos' => 0,
                'costos_productos' => 0,
                'egresos' => 0,
                'margen' => 0,
            ]);
        }

        $ingresos = DB::table('pedidos_comercio')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mes, SUM(total) as total, SUM(costo_envio) as despacho")
            ->where('created_at', '>=', $inicio)
            ->where('estado', '<>', 'cancelado')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        $costos = DB::table('pedido_comercio_items as items')
            ->join('pedidos_comercio as pedidos', 'pedidos.id', '=', 'items.pedido_id')
            ->leftJoin('productos', 'productos.id', '=', 'items.producto_id')
            ->selectRaw("DATE_FORMAT(pedidos.created_at, '%Y-%m') as mes, SUM(items.cantidad * COALESCE(productos.precio_compra, 0)) as costo")
            ->where('pedidos.created_at', '>=', $inicio)
            ->where('pedidos.estado', '<>', 'cancelado')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        $series = $meses->map(function (array $mes) use ($ingresos, $costos) {
            $ingreso = (int) ($ingresos[$mes['key']]->total ?? 0);
            $despacho = (int) ($ingresos[$mes['key']]->despacho ?? 0);
            $costoProductos = (int) ($costos[$mes['key']]->costo ?? 0);
            $egresos = $costoProductos + $despacho;

            return array_merge($mes, [
                'ingresos' => $ingreso,
                'costos_productos' => $costoProductos,
                'egresos' => $egresos,
                'margen' => $ingreso - $egresos,
            ]);
        });

        $mesActual = $series->last();
        $mesAnterior = $series->slice(-2, 1)->first() ?: $mesActual;
        $anioActual = now()->year;
        $anioAnterior = now()->subYear()->year;

        $ingresoAnualActual = (int) DB::table('pedidos_comercio')
            ->whereYear('created_at', $anioActual)
            ->where('estado', '<>', 'cancelado')
            ->sum('total');
        $ingresoAnualAnterior = (int) DB::table('pedidos_comercio')
            ->whereYear('created_at', $anioAnterior)
            ->where('estado', '<>', 'cancelado')
            ->sum('total');
        $costoAnualActual = (int) DB::table('pedido_comercio_items as items')
            ->join('pedidos_comercio as pedidos', 'pedidos.id', '=', 'items.pedido_id')
            ->leftJoin('productos', 'productos.id', '=', 'items.producto_id')
            ->whereYear('pedidos.created_at', $anioActual)
            ->where('pedidos.estado', '<>', 'cancelado')
            ->sum(DB::raw('items.cantidad * COALESCE(productos.precio_compra, 0)'));
        $costoAnualAnterior = (int) DB::table('pedido_comercio_items as items')
            ->join('pedidos_comercio as pedidos', 'pedidos.id', '=', 'items.pedido_id')
            ->leftJoin('productos', 'productos.id', '=', 'items.producto_id')
            ->whereYear('pedidos.created_at', $anioAnterior)
            ->where('pedidos.estado', '<>', 'cancelado')
            ->sum(DB::raw('items.cantidad * COALESCE(productos.precio_compra, 0)'));

        $maximo = max(1, $series->max('ingresos'), $series->max('egresos'), $series->max('costos_productos'));

        return [
            'series' => $series,
            'maximo' => $maximo,
            'totales' => [
                'ingresos' => $series->sum('ingresos'),
                'egresos' => $series->sum('egresos'),
                'costos_productos' => $series->sum('costos_productos'),
                'margen' => $series->sum('margen'),
            ],
            'variaciones' => [
                'ingresos_mes' => $this->variacionPorcentual($mesActual['ingresos'], $mesAnterior['ingresos']),
                'egresos_mes' => $this->variacionPorcentual($mesActual['egresos'], $mesAnterior['egresos']),
                'costos_mes' => $this->variacionPorcentual($mesActual['costos_productos'], $mesAnterior['costos_productos']),
                'ingresos_anual' => $this->variacionPorcentual($ingresoAnualActual, $ingresoAnualAnterior),
                'costos_anual' => $this->variacionPorcentual($costoAnualActual, $costoAnualAnterior),
            ],
        ];
    }

    private function resumenContableAlimentos(): array
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $pedidosMes = Pedido::whereBetween('created_at', [$inicioMes, $finMes]);
        $ingresos = (int) (clone $pedidosMes)->sum('total');
        $subtotal = (int) (clone $pedidosMes)->sum('subtotal');
        $descuentos = Schema::hasColumn('pedidos', 'descuento_total')
            ? (int) (clone $pedidosMes)->sum('descuento_total')
            : 0;
        $envios = (int) (clone $pedidosMes)->sum('costo_envio');
        $pedidos = (clone $pedidosMes)->count();

        $vouchers = (int) VoucherMovimiento::whereBetween('created_at', [$inicioMes, $finMes])
            ->where('tipo', 'canje')
            ->sum('monto');

        return [
            'periodo' => now()->format('m/Y'),
            'ingresos' => $ingresos,
            'subtotal' => $subtotal,
            'descuentos' => $descuentos + $vouchers,
            'envios' => $envios,
            'pedidos' => $pedidos,
            'pendiente_sincronizar' => $pedidos,
        ];
    }

    private function estadoApiContabilidad(): array
    {
        $baseUrl = rtrim((string) config('services.contabilidad.url'), '/');
        /** @var ContabilidadApiService $contabilidad */
        $contabilidad = app(ContabilidadApiService::class);

        if (!$contabilidad->configurado()) {
            return [
                'estado' => 'pendiente_configuracion',
                'mensaje' => 'Configure URL, token y UUID del cliente contable para activar la conexion.',
                'movimientos' => [],
                'ingresos' => 0,
                'egresos' => 0,
                'url' => $baseUrl ?: null,
            ];
        }

        $endpoint = "{$baseUrl}/clientes/{$contabilidad->clienteUuid()}/movimientos";

        try {
            $payload = $contabilidad->movimientos(['per_page' => 8]);
            $movimientos = collect($payload['data'] ?? $payload ?? [])
                ->take(8)
                ->map(function ($item) {
                    return [
                        'fecha' => $item['fecha'] ?? $item['date'] ?? $item['created_at'] ?? '',
                        'tipo' => $item['tipo'] ?? $item['type'] ?? '',
                        'descripcion' => $item['descripcion'] ?? $item['description'] ?? $item['glosa'] ?? 'Movimiento contable',
                        'monto' => (int) ($item['monto'] ?? $item['amount'] ?? 0),
                        'estado' => $item['estado'] ?? $item['status'] ?? 'Sin estado',
                    ];
                });

            return [
                'estado' => 'conectado',
                'mensaje' => 'Conexion API contable autenticada para el cliente Alimentos.',
                'movimientos' => $movimientos,
                'ingresos' => (int) $movimientos->where('tipo', 'ingreso')->sum('monto'),
                'egresos' => (int) $movimientos->where('tipo', 'egreso')->sum('monto'),
                'url' => $endpoint,
            ];
        } catch (\Throwable $exception) {
            $mensaje = str_contains($exception->getMessage(), '401')
                ? 'El token contable fue rechazado. Genere uno nuevo para el cliente configurado y actualice CONTABILIDAD_API_TOKEN.'
                : 'No fue posible conectar con la API contable. Revise el servicio y vuelva a intentar.';
            return [
                'estado' => 'sin_conexion',
                'mensaje' => $mensaje,
                'movimientos' => [],
                'ingresos' => 0,
                'egresos' => 0,
                'url' => $endpoint,
            ];
        }
    }

    private function variacionPorcentual(int $actual, int $anterior): ?float
    {
        if ($anterior === 0) {
            return $actual === 0 ? 0.0 : null;
        }

        return round((($actual - $anterior) / $anterior) * 100, 1);
    }

    public function usuarios()
    {
        $buscar = trim((string) request('buscar'));
        $rol = trim((string) request('rol'));
        $estado = (string) request('estado', '');
        $porPagina = in_array((int) request('por_pagina'), [15, 30, 50, 100], true)
            ? (int) request('por_pagina')
            : 15;

        $roles = User::query()
            ->selectRaw('rol, COUNT(*) as total')
            ->groupBy('rol')
            ->orderBy('rol')
            ->pluck('total', 'rol');

        return view('admin.usuarios', [
            'usuarios' => User::with('localVenta')
                ->when($buscar, fn ($query) => $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('name', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('telefono', 'like', "%{$buscar}%")
                        ->orWhere('rol', 'like', "%{$buscar}%");
                }))
                ->when($rol, fn ($query) => $query->where('rol', $rol))
                ->when($estado !== '', fn ($query) => $query->where('activo', $estado === '1'))
                ->latest()
                ->paginate($porPagina)
                ->withQueryString(),
            'buscar' => $buscar,
            'rolSeleccionado' => $rol,
            'estadoSeleccionado' => $estado,
            'porPagina' => $porPagina,
            'roles' => $roles,
            'totalUsuarios' => $roles->sum(),
            'usuariosActivos' => User::where('activo', true)->count(),
            'usuariosInactivos' => User::where('activo', false)->count(),
        ]);
    }

    public function crearUsuario()
    {
        return view('admin.usuario_form', [
            'locales' => LocalVenta::orderBy('nombre')->get(),
            'usuarioEditar' => null,
        ]);
    }

    public function editarUsuario(User $user)
    {
        return view('admin.usuario_form', [
            'locales' => LocalVenta::orderBy('nombre')->get(),
            'usuarioEditar' => $user,
        ]);
    }

    public function clientes()
    {
        $buscar = trim((string) request('buscar'));

        return view('admin.clientes', [
            'clientes' => User::with(['mascotas', 'direcciones', 'planesPedido'])
                ->whereIn('rol', ['cliente', 'dueno_mascota'])
                ->when($buscar, fn ($query) => $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('name', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('telefono', 'like', "%{$buscar}%")
                        ->orWhere('direccion', 'like', "%{$buscar}%");
                }))
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'buscar' => $buscar,
        ]);
    }

    public function crearCliente()
    {
        return view('admin.cliente_form', [
            'clienteEditar' => null,
        ]);
    }

    public function editarCliente(User $user)
    {
        abort_unless(in_array($user->rol, ['cliente', 'dueno_mascota'], true), 404);

        return view('admin.cliente_form', [
            'clienteEditar' => $user->load('direcciones'),
        ]);
    }

    public function guardarCliente(Request $request)
    {
        $data = $this->validarClienteAdmin($request);
        $direccion = $data['direccion_principal'] ?? null;
        $comuna = $data['comuna'] ?? null;
        $referencia = $data['referencia'] ?? null;
        unset($data['direccion_principal'], $data['comuna'], $data['referencia']);
        $data['georeferencia_url'] = $data['georeferencia_url'] ?: $this->urlMapaCliente($direccion, $comuna);

        $user = User::create(array_merge($data, [
            'rol' => 'cliente',
            'activo' => true,
            'direccion' => $direccion,
        ]));

        $cliente = Cliente::create([
            'nombre' => $user->name,
            'telefono' => $user->telefono,
            'email' => $user->email,
            'fecha_inscripcion' => now(),
        ]);
        $user->update(['cliente_id' => $cliente->id]);
        $this->guardarDireccionCliente($user, $direccion, $comuna, $referencia);

        return redirect()->route('admin.clientes.index')->with('ok', 'Cliente creado.');
    }

    public function actualizarCliente(Request $request, User $user)
    {
        abort_unless(in_array($user->rol, ['cliente', 'dueno_mascota'], true), 404);

        $data = $this->validarClienteAdmin($request, $user);
        $direccion = $data['direccion_principal'] ?? null;
        $comuna = $data['comuna'] ?? null;
        $referencia = $data['referencia'] ?? null;
        unset($data['direccion_principal'], $data['comuna'], $data['referencia']);
        $data['georeferencia_url'] = $data['georeferencia_url'] ?: $this->urlMapaCliente($direccion, $comuna);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['direccion'] = $direccion;
        $data['activo'] = (bool) ($data['activo'] ?? false);
        $user->update($data);

        if (!$user->cliente_id) {
            $cliente = Cliente::create([
                'nombre' => $user->name,
                'telefono' => $user->telefono,
                'email' => $user->email,
                'fecha_inscripcion' => now(),
            ]);
            $user->update(['cliente_id' => $cliente->id]);
        }

        $this->guardarDireccionCliente($user, $direccion, $comuna, $referencia);

        return redirect()->route('admin.clientes.index')->with('ok', 'Cliente actualizado.');
    }

    public function estadoCliente(User $user)
    {
        abort_unless(in_array($user->rol, ['cliente', 'dueno_mascota'], true), 404);
        $user->update(['activo' => !$user->activo]);

        return back()->with('ok', $user->activo ? 'Cliente activado.' : 'Cliente inactivado.');
    }

    public function planesComerciales()
    {
        return view('admin.planes_comerciales', [
            'planes' => $this->catalogoPlanesComerciales(),
        ]);
    }

    public function encuestasProfesionales()
    {
        $profesionales = Profesional::orderBy('nombre')->get();
        $clientes = User::whereIn('rol', ['cliente', 'dueno_mascota'])->orderBy('name')->get();
        $instituciones = LocalVenta::orderBy('nombre')->get();
        $respondidas = $profesionales->whereNotNull('encuesta_sistema_nacional');
        $respondidasClientes = $clientes->whereNotNull('encuesta_sistema_nacional');
        $respondidasInstituciones = $instituciones->whereNotNull('encuesta_sistema_nacional');
        $opciones = [
            'muy_interesante' => 'Muy interesante',
            'interesante' => 'Interesante',
            'neutral' => 'Neutral',
            'poco_interesante' => 'Poco interesante',
            'no_interesa' => 'No le interesa',
        ];

        return view('admin.encuestas_profesionales', [
            'profesionales' => $profesionales,
            'clientes' => $clientes,
            'instituciones' => $instituciones,
            'respondidas' => $respondidas,
            'respondidasClientes' => $respondidasClientes,
            'respondidasInstituciones' => $respondidasInstituciones,
            'opciones' => $opciones,
            'resumenEncuesta' => collect($opciones)->map(fn ($texto, $clave) => [
                'clave' => $clave,
                'texto' => $texto,
                'total' => $respondidas->where('encuesta_sistema_nacional', $clave)->count(),
                'porcentaje' => $respondidas->count() > 0
                    ? round(($respondidas->where('encuesta_sistema_nacional', $clave)->count() / $respondidas->count()) * 100, 1)
                    : 0,
            ])->values(),
            'resumenEncuestaClientes' => collect($opciones)->map(fn ($texto, $clave) => [
                'clave' => $clave,
                'texto' => $texto,
                'total' => $respondidasClientes->where('encuesta_sistema_nacional', $clave)->count(),
                'porcentaje' => $respondidasClientes->count() > 0
                    ? round(($respondidasClientes->where('encuesta_sistema_nacional', $clave)->count() / $respondidasClientes->count()) * 100, 1)
                    : 0,
            ])->values(),
            'resumenEncuestaInstituciones' => collect($opciones)->map(fn ($texto, $clave) => [
                'clave' => $clave,
                'texto' => $texto,
                'total' => $respondidasInstituciones->where('encuesta_sistema_nacional', $clave)->count(),
                'porcentaje' => $respondidasInstituciones->count() > 0
                    ? round(($respondidasInstituciones->where('encuesta_sistema_nacional', $clave)->count() / $respondidasInstituciones->count()) * 100, 1)
                    : 0,
            ])->values(),
            'promedioDescuento' => round((float) $profesionales->whereNotNull('porcentaje_descuento_voucher')->avg('porcentaje_descuento_voucher'), 1),
            'promedioDescuentoClientes' => round((float) $clientes->whereNotNull('porcentaje_descuento_voucher')->avg('porcentaje_descuento_voucher'), 1),
            'promedioDescuentoInstituciones' => round((float) $instituciones->whereNotNull('porcentaje_descuento_voucher')->avg('porcentaje_descuento_voucher'), 1),
        ]);
    }

    public function locales()
    {
        $buscar = trim((string) request('buscar'));

        return view('admin.locales', [
            'locales' => LocalVenta::query()
                ->withCount([
                    'usuarios as administradores_count' => fn ($query) => $query->where('rol', 'admin'),
                    'usuarios as clientes_count' => fn ($query) => $query->whereIn('rol', ['cliente', 'dueno_mascota']),
                    'usuarios as repartidores_count' => fn ($query) => $query->where('rol', 'repartidor'),
                    'bodegas',
                ])
                ->when($buscar, fn ($query) => $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('codigo', 'like', "%{$buscar}%")
                        ->orWhere('tipo', 'like', "%{$buscar}%")
                        ->orWhere('comuna', 'like', "%{$buscar}%")
                        ->orWhere('responsable', 'like', "%{$buscar}%");
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'tiposLocal' => $this->tiposLocalVenta(),
            'buscar' => $buscar,
        ]);
    }

    public function crearLocal()
    {
        return view('admin.local_form', [
            'localEditar' => null,
            'tiposLocal' => $this->tiposLocalVenta(),
        ]);
    }

    public function editarLocal(LocalVenta $local)
    {
        return view('admin.local_form', [
            'localEditar' => $local,
            'tiposLocal' => $this->tiposLocalVenta(),
        ]);
    }

    public function profesionales()
    {
        $buscar = trim((string) request('buscar'));

        return view('admin.profesionales', [
            'profesionales' => Profesional::query()
                ->when($buscar, fn ($query) => $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('rut', 'like', "%{$buscar}%")
                        ->orWhere('especialidad', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('codigo_geolocalizacion', 'like', "%{$buscar}%");
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'buscar' => $buscar,
        ]);
    }

    public function crearProfesional()
    {
        return view('admin.profesional_form', [
            'profesionalEditar' => null,
        ]);
    }

    public function mascotas()
    {
        $buscar = trim((string) request('buscar'));

        return view('admin.mascotas', [
            'mascotas' => Mascota::with('cliente')
                ->when($buscar, fn ($query) => $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('especie', 'like', "%{$buscar}%")
                        ->orWhere('raza', 'like', "%{$buscar}%")
                        ->orWhere('numero_chip', 'like', "%{$buscar}%")
                        ->orWhereHas('cliente', fn ($cliente) => $cliente->where('name', 'like', "%{$buscar}%"));
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'buscar' => $buscar,
        ]);
    }

    public function crearMascota()
    {
        return view('admin.mascota_form', [
            'clientes' => User::with('perfilCliente')->whereIn('rol', ['cliente', 'dueno_mascota'])->orderBy('name')->get(),
            'mascotaEditar' => null,
        ]);
    }

    public function editarMascota(Mascota $mascota)
    {
        return view('admin.mascota_form', [
            'clientes' => User::whereIn('rol', ['cliente', 'dueno_mascota'])->orderBy('name')->get(),
            'mascotaEditar' => $mascota,
        ]);
    }

    public function vendedores()
    {
        return $this->vistaUsuariosRol('vendedor');
    }

    public function crearVendedor()
    {
        return $this->formularioUsuarioRol('vendedor');
    }

    public function editarVendedor(User $user)
    {
        abort_unless($user->rol === 'vendedor', 404);

        return $this->formularioUsuarioRol('vendedor', $user);
    }

    public function guardarVendedor(Request $request)
    {
        return $this->guardarUsuarioRol($request, 'vendedor');
    }

    public function actualizarVendedor(Request $request, User $user)
    {
        return $this->actualizarUsuarioRol($request, $user, 'vendedor');
    }

    public function estadoVendedor(User $user)
    {
        return $this->cambiarEstadoUsuarioRol($user, 'vendedor');
    }

    public function repartidores()
    {
        return $this->vistaUsuariosRol('repartidor');
    }

    public function crearRepartidor()
    {
        return $this->formularioUsuarioRol('repartidor');
    }

    public function editarRepartidor(User $user)
    {
        abort_unless($user->rol === 'repartidor', 404);

        return $this->formularioUsuarioRol('repartidor', $user);
    }

    public function guardarRepartidor(Request $request)
    {
        return $this->guardarUsuarioRol($request, 'repartidor');
    }

    public function actualizarRepartidor(Request $request, User $user)
    {
        return $this->actualizarUsuarioRol($request, $user, 'repartidor');
    }

    public function estadoRepartidor(User $user)
    {
        return $this->cambiarEstadoUsuarioRol($user, 'repartidor');
    }

    public function historialRepartos(Request $request)
    {
        $query = Pedido::with(['items', 'tracking', 'repartidor', 'cliente'])
            ->whereNotNull('repartidor_id')
            ->latest();

        if ($request->filled('repartidor_id')) {
            $query->where('repartidor_id', $request->integer('repartidor_id'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->string('estado'));
        }

        if ($request->filled('feedback')) {
            if ($request->input('feedback') === 'reclamo') {
                $query->whereNotNull('cliente_reclamo')->where('cliente_reclamo', '<>', '');
            } else {
                $query->where('cliente_conformidad', $request->input('feedback'));
            }
        }

        return view('admin.repartos_historial', [
            'pedidos' => $query->paginate(30)->withQueryString(),
            'repartidores' => User::where('rol', 'repartidor')->orderBy('name')->get(),
            'filtros' => $request->only(['repartidor_id', 'estado', 'feedback']),
        ]);
    }

    public function actualizarFeedbackReparto(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'cliente_conformidad' => ['nullable', 'in:conforme,no_conforme,pendiente'],
            'cliente_reclamo' => ['nullable', 'string', 'max:2000'],
            'reclamo_estado' => ['nullable', 'in:abierto,en_revision,resuelto,cerrado'],
        ]);

        $data['feedback_cliente_at'] = now();
        $pedido->update($data);

        return back()->with('ok', 'Conformidad o reclamo actualizado.');
    }

    public function vouchers()
    {
        $buscar = trim((string) request('buscar'));

        return view('admin.vouchers', [
            'vouchers' => VoucherDescuento::with(['vendedor', 'producto'])
                ->when($buscar, fn ($query) => $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('codigo', 'like', "%{$buscar}%")
                        ->orWhere('titulo', 'like', "%{$buscar}%")
                        ->orWhere('destinatario_nombre', 'like', "%{$buscar}%")
                        ->orWhere('destinatario_email', 'like', "%{$buscar}%");
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'buscar' => $buscar,
        ]);
    }

    public function crearVoucher()
    {
        return view('admin.voucher_form', [
            'vendedores' => User::where('rol', 'vendedor')->where('activo', true)->orderBy('name')->get(),
            'productos' => Producto::where('activo', true)->orderBy('categoria')->orderBy('nombre')->get(),
            'categorias' => Producto::where('activo', true)->whereNotNull('categoria')->distinct()->orderBy('categoria')->pluck('categoria'),
            'locales' => LocalVenta::where('activo', true)->orderBy('nombre')->get(),
            'regiones' => DB::connection('vet_sdi')->table('regiones')->orderBy('id')->get(['id', 'nombre']),
            'comunas' => DB::connection('vet_sdi')->table('ciudades')->orderBy('nombre')->get(['id', 'nombre', 'id_region']),
            'usuariosDestino' => User::where('activo', true)->whereNotNull('vet_sdi_user_id')->orderBy('name')->get(['id', 'name', 'email', 'vet_sdi_user_id']),
            'mascotasDestino' => Mascota::with('cliente:id,name,email,vet_sdi_user_id')->orderBy('nombre')->get(),
        ]);
    }

    public function voucherDetalle(VoucherDescuento $voucher)
    {
        return redirect()->route('admin.vouchers.index', ['qr' => $voucher->id]);
    }

    public function guardarVoucher(Request $request)
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'tipo_descuento' => ['required', 'in:porcentaje,monto_fijo'],
            'valor' => ['required', 'integer', 'min:1'],
            'monto_minimo' => ['nullable', 'integer', 'min:0'],
            'usos_maximos' => ['required', 'integer', 'min:1'],
            'valido_desde' => ['nullable', 'date'],
            'valido_hasta' => ['nullable', 'date', 'after_or_equal:valido_desde'],
            'destinatario_nombre' => ['nullable', 'string', 'max:255'],
            'destinatario_email' => ['nullable', 'email', 'max:255'],
            'vendedor_id' => ['nullable', 'exists:users,id'],
            'local_venta_id' => ['nullable', 'exists:locales_venta,id'],
            'producto_id' => ['nullable', 'exists:productos,id'],
            'categoria_aplicable' => ['nullable', 'string', 'max:80'],
            'alcance_territorial' => ['required', 'in:nacional,regional,comunal'],
            'region_id' => ['nullable', 'integer', 'min:1'],
            'comuna_id' => ['nullable', 'integer', 'min:1'],
            'tipo_destinatario' => ['required', 'in:publico_general,tutores,clientes,profesionales,locales_comerciales'],
            'segmento_destinatario' => ['required', 'in:todos,todas_mascotas,mascotas_sector,mascota_especifica,usuarios_sector,usuario_especifico'],
            'mascota_id' => ['nullable', 'exists:mascotas,id'],
            'usuario_destinatario_id' => ['nullable', 'exists:users,id'],
            'tipo_beneficio' => ['required', 'in:general,alimentos,farmacia,consultas,procedimientos,servicios'],
        ]);

        if ($data['segmento_destinatario'] === 'mascota_especifica') {
            if (empty($data['mascota_id'])) return back()->withErrors(['mascota_id' => 'Debe seleccionar una mascota sincronizada.'])->withInput();
            $mascota = Mascota::with('cliente')->findOrFail($data['mascota_id']);
            $data['usuario_destinatario_id'] = $mascota->user_id;
            $data['destinatario_nombre'] = 'Mascota: ' . $mascota->nombre;
            $data['destinatario_email'] = $mascota->cliente?->email;
        } elseif ($data['segmento_destinatario'] === 'usuario_especifico') {
            if (empty($data['usuario_destinatario_id'])) return back()->withErrors(['usuario_destinatario_id' => 'Debe seleccionar un usuario sincronizado.'])->withInput();
            $usuarioDestino = User::findOrFail($data['usuario_destinatario_id']);
            $data['mascota_id'] = null;
            $data['destinatario_nombre'] = $usuarioDestino->name;
            $data['destinatario_email'] = $usuarioDestino->email;
        } else {
            $data['mascota_id'] = null; $data['usuario_destinatario_id'] = null; $data['destinatario_email'] = null;
        }

        if ($data['alcance_territorial'] === 'nacional') {
            $data['region_id'] = null;
            $data['comuna_id'] = null;
        } elseif ($data['alcance_territorial'] === 'regional') {
            $data['comuna_id'] = null;
        }

        if ($data['alcance_territorial'] !== 'nacional' && empty($data['region_id'])) {
            return back()->withErrors(['region_id' => 'Debe seleccionar una region para este alcance.'])->withInput();
        }
        if ($data['alcance_territorial'] === 'comunal' && empty($data['comuna_id'])) {
            return back()->withErrors(['comuna_id' => 'Debe seleccionar una comuna para este alcance.'])->withInput();
        }

        if (!empty($data['producto_id'])) {
            $data['categoria_aplicable'] = null;
        }

        $data['codigo'] = $this->generarCodigoVoucher();
        $data['monto_minimo'] = $data['monto_minimo'] ?? 0;
        $data['firma_seguridad'] = $this->firmaVoucher($data['codigo'], $data);
        $data['activo'] = true;

        VoucherDescuento::create($data);

        return redirect()->route('admin.vouchers.index')->with('ok', 'Voucher de descuento creado con QR seguro.');
    }

    public function estadoVoucher(VoucherDescuento $voucher)
    {
        $voucher->update(['activo' => !$voucher->activo]);

        return back()->with('ok', $voucher->activo ? 'Voucher activado.' : 'Voucher inactivado.');
    }

    public function servicios()
    {
        return view('admin.servicios', [
            'servicios' => Producto::whereIn('categoria', $this->categoriasServicio())->latest()->get(),
            'servicioEditar' => null,
            'tiposServicio' => $this->tiposServicio(),
        ]);
    }

    public function editarServicio(Producto $producto)
    {
        abort_unless(in_array($producto->categoria, $this->categoriasServicio(), true), 404);

        return view('admin.servicios', [
            'servicios' => Producto::whereIn('categoria', $this->categoriasServicio())->latest()->get(),
            'servicioEditar' => $producto,
            'tiposServicio' => $this->tiposServicio(),
        ]);
    }

    public function guardarServicio(Request $request)
    {
        $data = $this->validarServicio($request);

        if ($request->hasFile('foto')) {
            $data['foto_url'] = 'storage/' . $request->file('foto')->store('servicios', 'public');
        }

        unset($data['foto']);
        $data['marca'] = ($data['marca'] ?? null) ?: 'VetChile Servicios';
        $data['peso'] = ($data['peso'] ?? null) ?: 'agenda';
        $data['stock'] = $data['stock'] ?? 20;
        $data['requiere_agenda'] = $request->boolean('requiere_agenda');
        $data['activo'] = (bool) ($data['activo'] ?? true);

        Producto::create($data);

        return redirect()->route('admin.servicios.index')->with('ok', 'Servicio creado.');
    }

    public function actualizarServicio(Request $request, Producto $producto)
    {
        abort_unless(in_array($producto->categoria, $this->categoriasServicio(), true), 404);

        $data = $this->validarServicio($request);

        if ($request->hasFile('foto')) {
            $data['foto_url'] = 'storage/' . $request->file('foto')->store('servicios', 'public');
        }

        unset($data['foto']);
        $data['marca'] = ($data['marca'] ?? null) ?: 'VetChile Servicios';
        $data['peso'] = ($data['peso'] ?? null) ?: 'agenda';
        $data['requiere_agenda'] = $request->boolean('requiere_agenda');
        $data['activo'] = (bool) ($data['activo'] ?? false);
        $producto->update($data);

        return redirect()->route('admin.servicios.index')->with('ok', 'Servicio actualizado.');
    }

    public function estadoServicio(Producto $producto)
    {
        abort_unless(in_array($producto->categoria, $this->categoriasServicio(), true), 404);
        $producto->update(['activo' => !$producto->activo]);

        return back()->with('ok', $producto->activo ? 'Servicio activado.' : 'Servicio inactivado.');
    }

    public function finanzasVouchers(Request $request)
    {
        return $this->vistaFinanzasVouchers($request, $request->query('tabla', 'reparto'), 'Dashboard financiero de vouchers');
    }

    public function finanzasVouchersReparto(Request $request)
    {
        return $this->vistaFinanzasVouchers($request, 'reparto', 'Reparto de vouchers');
    }

    public function finanzasVouchersCanje(Request $request)
    {
        return $this->vistaFinanzasVouchers($request, 'canje', 'Canje de vouchers');
    }

    public function finanzasVouchersCobros(Request $request)
    {
        return $this->vistaFinanzasVouchers($request, 'cobro', 'Cobros de vouchers');
    }

    public function finanzasVouchersRendiciones(Request $request)
    {
        return $this->vistaFinanzasVouchers($request, 'rendicion', 'Rendiciones de vouchers');
    }

    public function finanzasVouchersLiquidaciones(Request $request)
    {
        return $this->vistaFinanzasVouchers($request, 'liquidacion', 'Liquidaciones de vouchers');
    }

    public function auditorVouchers(Request $request)
    {
        return $this->vistaFinanzasVouchers($request, 'auditoria', 'Auditor de vouchers');
    }

    public function auditorVouchersAlertas(Request $request)
    {
        return $this->vistaFinanzasVouchers($request, 'alertas', 'Alertas del auditor de vouchers');
    }

    private function vistaFinanzasVouchers(Request $request, string $tablaSolicitada, string $tituloModulo)
    {
        $secciones = [
            'reparto' => 'Reparto',
            'canje' => 'Canje',
            'cobro' => 'Cobros',
            'rendicion' => 'Rendiciones',
            'liquidacion' => 'Liquidaciones',
        ];
        if ($tablaSolicitada === 'auditoria' || $tablaSolicitada === 'alertas') {
            $secciones = auth()->user()?->tieneRol('auditor')
                ? [
                    'auditoria' => 'Auditoria',
                    'alertas' => 'Alertas',
                ]
                : [
                    'alertas' => 'Alertas',
                ];
        }
        $rutasSecciones = [
            'reparto' => 'admin.finanzas.vouchers.reparto',
            'canje' => 'admin.finanzas.vouchers.canje',
            'cobro' => 'admin.finanzas.vouchers.cobros',
            'rendicion' => 'admin.finanzas.vouchers.rendiciones',
            'liquidacion' => 'admin.finanzas.vouchers.liquidaciones',
            'auditoria' => 'auditor.vouchers.index',
            'alertas' => 'auditor.vouchers.alertas',
        ];
        $tablaActiva = array_key_exists($tablaSolicitada, $secciones) ? $tablaSolicitada : 'reparto';
        $busqueda = trim((string) $request->query('q', ''));

        $movimientosResumen = VoucherMovimiento::query()->get();
        $vouchersResumen = VoucherDescuento::with(['vendedor', 'movimientos'])->latest()->get();
        $alertas = $this->alertasVoucher($vouchersResumen, $movimientosResumen);

        $movimientosQuery = VoucherMovimiento::with(['voucher', 'responsable', 'profesional'])->latest();

        if ($tablaActiva !== 'alertas') {
            $movimientosQuery->where('tipo', $tablaActiva);
        }

        if ($busqueda !== '') {
            $movimientosQuery->where(function ($query) use ($busqueda) {
                $query->where('estado', 'like', "%{$busqueda}%")
                    ->orWhere('detalle', 'like', "%{$busqueda}%")
                    ->orWhere('firma_seguridad', 'like', "%{$busqueda}%")
                    ->orWhereHas('voucher', function ($voucherQuery) use ($busqueda) {
                        $voucherQuery->where('codigo', 'like', "%{$busqueda}%")
                            ->orWhere('titulo', 'like', "%{$busqueda}%");
                    })
                    ->orWhereHas('responsable', function ($responsableQuery) use ($busqueda) {
                        $responsableQuery->where('name', 'like', "%{$busqueda}%");
                    })
                    ->orWhereHas('profesional', function ($profesionalQuery) use ($busqueda) {
                        $profesionalQuery->where('nombre', 'like', "%{$busqueda}%");
                    });
            });
        }

        $moduloTotal = $tablaActiva === 'alertas' ? $alertas->count() : (clone $movimientosQuery)->count();
        $moduloMonto = $tablaActiva === 'alertas' ? 0 : (clone $movimientosQuery)->sum('monto');

        return view('admin.finanzas_vouchers', [
            'vouchers' => VoucherDescuento::where('activo', true)->latest()->get(),
            'movimientosPagina' => $tablaActiva === 'alertas'
                ? null
                : $movimientosQuery->paginate(12)->withQueryString(),
            'profesionales' => Profesional::orderBy('nombre')->get(),
            'responsables' => User::whereIn('rol', ['vendedor', 'repartidor', 'admin', 'central_ventas'])->orderBy('name')->get(),
            'resumen' => [
                'emitidos' => $vouchersResumen->count(),
                'canjes' => $movimientosResumen->where('tipo', 'canje')->count(),
                'cobros' => $movimientosResumen->where('tipo', 'cobro')->sum('monto'),
                'liquidaciones' => $movimientosResumen->where('tipo', 'liquidacion')->sum('monto'),
                'alertas' => $alertas->count(),
            ],
            'alertas' => $alertas,
            'secciones' => $secciones,
            'rutasSecciones' => $rutasSecciones,
            'tablaActiva' => $tablaActiva,
            'busqueda' => $busqueda,
            'tituloModulo' => $tituloModulo,
            'moduloTotal' => $moduloTotal,
            'moduloMonto' => $moduloMonto,
            'esModuloAuditor' => in_array($tablaActiva, ['auditoria', 'alertas'], true),
        ]);
    }

    public function guardarMovimientoVoucher(Request $request)
    {
        $data = $request->validate([
            'voucher_descuento_id' => ['required', 'exists:vouchers_descuento,id'],
            'tipo' => ['required', 'in:reparto,canje,cobro,rendicion,liquidacion,auditoria'],
            'estado' => ['required', 'string', 'max:80'],
            'monto' => ['nullable', 'integer', 'min:0'],
            'responsable_id' => ['nullable', 'exists:users,id'],
            'profesional_id' => ['nullable', 'exists:profesionales,id'],
            'pedido_id' => ['nullable', 'exists:pedidos,id'],
            'detalle' => ['nullable', 'string', 'max:1000'],
        ]);

        $voucher = VoucherDescuento::findOrFail($data['voucher_descuento_id']);
        $data['monto'] = $data['monto'] ?? 0;
        $data['firma_seguridad'] = hash_hmac('sha256', implode('|', [
            $voucher->codigo,
            $data['tipo'],
            $data['estado'],
            $data['monto'],
            $data['responsable_id'] ?? '',
            $data['profesional_id'] ?? '',
            now()->format('YmdHi'),
        ]), config('app.key'));

        DB::transaction(function () use ($data, $voucher) {
            VoucherMovimiento::create($data);

            if ($data['tipo'] === 'canje') {
                $voucher->increment('usos_realizados');
            }
        });

        return back()->with('ok', 'Movimiento de voucher registrado con firma de auditoria.');
    }

    public function voucherQr(VoucherDescuento $voucher)
    {
        $url = route('vouchers.verificar', [
            'codigo' => $voucher->codigo,
            'firma' => $voucher->firma_seguridad,
        ]);

        $result = (new Builder(
            writer: new SvgWriter(),
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 280,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            validateResult: false
        ))->build();

        return response($result->getString(), 200)->header('Content-Type', $result->getMimeType());
    }

    public function verificarVoucher(string $codigo, Request $request)
    {
        $voucher = VoucherDescuento::where('codigo', $codigo)->firstOrFail();
        $firmaValida = hash_equals($voucher->firma_seguridad, (string) $request->query('firma'));
        $vigente = $voucher->activo
            && $firmaValida
            && (!$voucher->valido_desde || $voucher->valido_desde->startOfDay()->lte(now()))
            && (!$voucher->valido_hasta || $voucher->valido_hasta->endOfDay()->gte(now()))
            && $voucher->usos_realizados < $voucher->usos_maximos;

        return view('admin.voucher_verificar', compact('voucher', 'firmaValida', 'vigente'));
    }

    public function editarProfesional(Profesional $profesional)
    {
        return view('admin.profesional_form', [
            'profesionalEditar' => $profesional,
        ]);
    }

    public function local(Request $request)
    {
        $data = $this->validarLocalVenta($request);
        $data['activo'] = (bool) ($data['activo'] ?? true);
        $data['georeferencia_url'] = $data['georeferencia_url'] ?: $this->urlMapaCliente($data['direccion'] ?? null, $data['comuna'] ?? null);

        LocalVenta::create($data);

        return redirect()->route('admin.locales.index')->with('ok', 'Lugar de venta creado.');
    }

    public function actualizarLocal(Request $request, LocalVenta $local)
    {
        $data = $this->validarLocalVenta($request, $local);
        $data['activo'] = (bool) ($data['activo'] ?? false);
        $data['georeferencia_url'] = $data['georeferencia_url'] ?: $this->urlMapaCliente($data['direccion'] ?? null, $data['comuna'] ?? null);
        $local->update($data);

        return redirect()->route('admin.locales.index')->with('ok', 'Lugar de venta actualizado.');
    }

    public function estadoLocal(LocalVenta $local)
    {
        $local->update(['activo' => !$local->activo]);

        return back()->with('ok', $local->activo ? 'Lugar de venta activado.' : 'Lugar de venta inactivado.');
    }

    public function bodega(Request $request)
    {
        Bodega::create($request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:40', 'unique:bodegas,codigo'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'tipo' => ['required', 'string', 'max:80'],
        ]));

        return back()->with('ok', 'Bodega creada.');
    }

    public function usuario(Request $request)
    {
        User::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'rol' => ['required', 'in:admin,central_ventas,vendedor,cliente,dueno_mascota,repartidor,auditor,contabilidad'],
            'telefono' => ['nullable', 'string', 'max:80'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'georeferencia_url' => ['nullable', 'string', 'max:1000'],
            'plan_preferido' => ['nullable', 'string', 'max:80'],
            'recibe_voucher' => ['nullable', 'boolean'],
            'porcentaje_descuento_voucher' => ['nullable', 'integer', 'min:0', 'max:100'],
            'encuesta_sistema_nacional' => ['nullable', 'in:muy_interesante,interesante,neutral,poco_interesante,no_interesa'],
            'comentario_sistema_nacional' => ['nullable', 'string', 'max:1000'],
            'fonavet_valor_mensual' => ['nullable', 'integer', 'min:0'],
            'local_venta_id' => ['nullable', 'exists:locales_venta,id'],
        ]) + ['recibe_voucher' => false]);

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuario creado.');
    }

    public function actualizarUsuario(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'rol' => ['required', 'in:admin,central_ventas,vendedor,cliente,dueno_mascota,repartidor,auditor,contabilidad'],
            'telefono' => ['nullable', 'string', 'max:80'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'georeferencia_url' => ['nullable', 'string', 'max:1000'],
            'plan_preferido' => ['nullable', 'string', 'max:80'],
            'recibe_voucher' => ['nullable', 'boolean'],
            'porcentaje_descuento_voucher' => ['nullable', 'integer', 'min:0', 'max:100'],
            'encuesta_sistema_nacional' => ['nullable', 'in:muy_interesante,interesante,neutral,poco_interesante,no_interesa'],
            'comentario_sistema_nacional' => ['nullable', 'string', 'max:1000'],
            'fonavet_valor_mensual' => ['nullable', 'integer', 'min:0'],
            'local_venta_id' => ['nullable', 'exists:locales_venta,id'],
            'activo' => ['nullable', 'boolean'],
        ]) + ['recibe_voucher' => false];

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['activo'] = (bool) ($data['activo'] ?? false);
        $user->update($data);

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    public function profesional(Request $request)
    {
        $data = $this->validarProfesional($request);

        if (!empty($data['password_acceso'])) {
            $data['password_acceso'] = Hash::make($data['password_acceso']);
        }

        if ($request->hasFile('foto')) {
            $data['foto_url'] = 'storage/' . $request->file('foto')->store('profesionales', 'public');
        }

        unset($data['foto']);
        Profesional::create($data);

        return redirect()->route('admin.profesionales.index')->with('ok', 'Profesional creado.');
    }

    public function actualizarProfesional(Request $request, Profesional $profesional)
    {
        $data = $this->validarProfesional($request, $profesional);

        if (!empty($data['password_acceso'])) {
            $data['password_acceso'] = Hash::make($data['password_acceso']);
        } else {
            unset($data['password_acceso']);
        }

        if ($request->hasFile('foto')) {
            $data['foto_url'] = 'storage/' . $request->file('foto')->store('profesionales', 'public');
        }

        unset($data['foto']);
        $profesional->update($data);

        return redirect()->route('admin.profesionales.index')->with('ok', 'Profesional actualizado.');
    }

    public function mascota(Request $request)
    {
        $data = $this->validarMascota($request);
        $enviarInvitacion = (bool) ($data['enviar_invitacion'] ?? false);
        unset($data['enviar_invitacion']);

        $user = User::findOrFail($data['user_id']);

        if (!$user->cliente_id) {
            $cliente = Cliente::create([
                'nombre' => $user->name,
                'telefono' => $user->telefono,
                'email' => $user->email,
                'fecha_inscripcion' => now(),
            ]);

            $user->update(['cliente_id' => $cliente->id]);
        }

        $data['esterilizado'] = $request->boolean('esterilizado');

        $mascota = Mascota::create(array_merge($data, ['cliente_id' => $user->cliente_id]));
        if ($enviarInvitacion) {
            $this->invitarTutorVetSdi($user, $mascota);
        }

        return redirect()->route('admin.mascotas.index')->with('ok', 'Mascota registrada.');
    }

    public function actualizarMascota(Request $request, Mascota $mascota)
    {
        $data = $this->validarMascota($request);
        $enviarInvitacion = (bool) ($data['enviar_invitacion'] ?? false);
        unset($data['enviar_invitacion']);
        $user = User::findOrFail($data['user_id']);

        if (!$user->cliente_id) {
            $cliente = Cliente::create([
                'nombre' => $user->name,
                'telefono' => $user->telefono,
                'email' => $user->email,
                'fecha_inscripcion' => now(),
            ]);

            $user->update(['cliente_id' => $cliente->id]);
            $user->refresh();
        }

        $data['esterilizado'] = $request->boolean('esterilizado');
        $data['cliente_id'] = $user->cliente_id;
        $mascota->update($data);
        if ($enviarInvitacion) {
            $this->invitarTutorVetSdi($user, $mascota);
        }

        return redirect()->route('admin.mascotas.index')->with('ok', 'Mascota actualizada.');
    }

    public function existencia(Request $request)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'bodega_id' => ['nullable', 'exists:bodegas,id'],
            'local_venta_id' => ['nullable', 'exists:locales_venta,id'],
            'cantidad' => ['required', 'integer', 'min:0'],
            'stock_critico' => ['required', 'integer', 'min:0'],
            'stock_objetivo' => ['required', 'integer', 'min:0'],
        ]);

        if (!$data['bodega_id'] && !$data['local_venta_id']) {
            return back()->withErrors(['existencia' => 'Debes elegir bodega o local.']);
        }

        Existencia::updateOrCreate(
            [
                'producto_id' => $data['producto_id'],
                'bodega_id' => $data['bodega_id'] ?: null,
                'local_venta_id' => $data['local_venta_id'] ?: null,
            ],
            $data
        );

        return back()->with('ok', 'Existencia actualizada.');
    }

    public function planPedido(Request $request)
    {
        PlanPedido::create($request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'mascota_id' => ['nullable', 'exists:mascotas,id'],
            'producto_id' => ['required', 'exists:productos,id'],
            'voucher_descuento_id' => ['nullable', 'exists:vouchers_descuento,id'],
            'frecuencia' => ['required', 'in:semanal,mensual'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'proxima_entrega' => ['required', 'date'],
            'direccion_entrega' => ['required', 'string', 'max:500'],
        ]));

        return back()->with('ok', 'Plan de pedido creado.');
    }

    public function generarPedidos(Request $request)
    {
        $data = $request->validate([
            'horizonte' => ['required', 'in:semanal,mensual'],
            'bodega_id' => ['nullable', 'exists:bodegas,id'],
            'local_venta_id' => ['nullable', 'exists:locales_venta,id'],
        ]);

        $hasta = $data['horizonte'] === 'semanal' ? now()->addWeek() : now()->addMonth();
        $planes = PlanPedido::with(['cliente', 'producto', 'voucher'])
            ->where('activo', true)
            ->whereDate('proxima_entrega', '<=', $hasta)
            ->get();

        $creados = 0;

        foreach ($planes as $plan) {
            DB::transaction(function () use ($plan, $data, &$creados) {
                $subtotal = $plan->producto->precio * $plan->cantidad;
                $costoEnvio = $subtotal < 50000 ? 3500 : 0;
                $descuento = $this->descuentoPlanVoucher($plan, $subtotal);
                $total = max(0, $subtotal + $costoEnvio - $descuento);

                $pedido = Pedido::create([
                    'codigo_tracking' => strtoupper(Str::random(10)),
                    'user_id' => $plan->user_id,
                    'bodega_id' => ($data['bodega_id'] ?? null) ?: null,
                    'local_venta_id' => ($data['local_venta_id'] ?? null) ?: $plan->cliente->local_venta_id,
                    'plan_pedido_id' => $plan->id,
                    'voucher_descuento_id' => $descuento > 0 ? $plan->voucher_descuento_id : null,
                    'cliente_nombre' => $plan->cliente->name,
                    'cliente_email' => $plan->cliente->email,
                    'cliente_telefono' => $plan->cliente->telefono,
                    'direccion_entrega' => $plan->direccion_entrega,
                    'fecha_entrega' => $plan->proxima_entrega,
                    'frecuencia' => $plan->frecuencia,
                    'prioridad' => 'normal',
                    'estado' => 'programado',
                    'estado_pago' => 'pendiente',
                    'subtotal' => $subtotal,
                    'costo_envio' => $costoEnvio,
                    'descuento_total' => $descuento,
                    'total' => $total,
                ]);

                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $plan->producto_id,
                    'producto_nombre' => $plan->producto->nombre,
                    'producto_marca' => $plan->producto->marca,
                    'precio_unitario' => $plan->producto->precio,
                    'cantidad' => $plan->cantidad,
                    'total' => $subtotal,
                ]);

                if ($descuento > 0 && $plan->voucher) {
                    VoucherMovimiento::create([
                        'voucher_descuento_id' => $plan->voucher->id,
                        'tipo' => 'canje',
                        'estado' => 'registrado',
                        'monto' => $descuento,
                        'pedido_id' => $pedido->id,
                        'detalle' => 'Descuento automatico aplicado desde plan de pago recurrente.',
                        'firma_seguridad' => hash('sha256', $plan->voucher->codigo.'|plan|'.$pedido->codigo_tracking.'|'.$descuento.'|'.now()->timestamp),
                    ]);

                    $plan->voucher->increment('usos_realizados');
                }

                TrackingEvento::create([
                    'pedido_id' => $pedido->id,
                    'estado' => 'programado',
                    'mensaje' => 'Pedido generado desde plan ' . $plan->frecuencia . '.',
                ]);

                $plan->update([
                    'proxima_entrega' => $plan->frecuencia === 'semanal'
                        ? $plan->proxima_entrega->addWeek()
                        : $plan->proxima_entrega->addMonth(),
                ]);

                $creados++;
            });
        }

        return back()->with('ok', "Pedidos generados: {$creados}.");
    }

    public function ruta(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'fecha' => ['required', 'date'],
            'repartidor_id' => ['nullable', 'exists:users,id'],
            'pedido_ids' => ['nullable', 'array'],
            'pedido_ids.*' => ['exists:pedidos,id'],
        ]);

        $ruta = RutaReparto::create($data);

        foreach ($data['pedido_ids'] ?? [] as $index => $pedidoId) {
            $ruta->pedidos()->attach($pedidoId, ['orden' => $index + 1]);
            Pedido::whereKey($pedidoId)->update([
                'repartidor_id' => $data['repartidor_id'] ?: null,
                'estado' => 'asignado',
            ]);
        }

        return back()->with('ok', 'Ruta de reparto creada.');
    }

    private function validarProfesional(Request $request, ?Profesional $profesional = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', 'max:40', 'unique:profesionales,rut,' . ($profesional?->id ?? 'NULL')],
            'telefono' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion_consulta' => ['nullable', 'string', 'max:500'],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'recibe_voucher' => ['nullable', 'boolean'],
            'porcentaje_descuento_voucher' => ['nullable', 'integer', 'min:0', 'max:100'],
            'encuesta_sistema_nacional' => ['nullable', 'in:muy_interesante,interesante,neutral,poco_interesante,no_interesa'],
            'comentario_sistema_nacional' => ['nullable', 'string', 'max:1000'],
            'visita_domiciliaria' => ['nullable', 'boolean'],
            'password_acceso' => [$profesional ? 'nullable' : 'required', 'string', 'min:8'],
            'foto' => ['nullable', 'image', 'max:4096'],
            'geolocalizacion' => ['nullable', 'string', 'max:255'],
            'codigo_geolocalizacion' => ['nullable', 'string', 'max:80'],
            'banco' => ['nullable', 'string', 'max:120'],
            'tipo_cuenta' => ['nullable', 'string', 'max:120'],
            'numero_cuenta' => ['nullable', 'string', 'max:120'],
            'titular_cuenta' => ['nullable', 'string', 'max:255'],
            'rut_cuenta' => ['nullable', 'string', 'max:40'],
            'activo' => ['nullable', 'boolean'],
        ]) + [
            'recibe_voucher' => false,
            'visita_domiciliaria' => false,
            'activo' => false,
        ];
    }

    private function validarMascota(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'especie' => ['required', 'string', 'max:80'],
            'raza' => ['nullable', 'string', 'max:120'],
            'sexo' => ['nullable', 'in:macho,hembra,desconocido'],
            'color' => ['nullable', 'string', 'max:120'],
            'peso_kg' => ['nullable', 'integer', 'min:0'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'numero_chip' => ['nullable', 'string', 'max:120'],
            'enviar_invitacion' => ['nullable', 'boolean'],
            'esterilizado' => ['nullable', 'boolean'],
            'alergias' => ['nullable', 'string', 'max:1000'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function invitarTutorVetSdi(User $user, Mascota $mascota): void
    {
        ClienteNotificacion::create([
            'user_id' => $user->id,
            'tipo' => 'invitacion_vet_sdi',
            'titulo' => 'Invitación para participar en VET-SDI',
            'mensaje' => 'La mascota ' . $mascota->nombre . ' fue inscrita. Activa o completa tu cuenta VET-SDI para acceder a ficha veterinaria única, recordatorios sanitarios, documentos, vouchers, descuentos y servicios conectados.',
            'url' => config('services.sdi_sso.vet_web_url'),
        ]);
    }

    private function descuentoPlanVoucher(PlanPedido $plan, int $subtotal): int
    {
        $voucher = $plan->voucher;

        if (!$voucher || !$voucher->activo) {
            return 0;
        }

        if ($voucher->valido_desde && $voucher->valido_desde->startOfDay()->gt(now())) {
            return 0;
        }

        if ($voucher->valido_hasta && $voucher->valido_hasta->endOfDay()->lt(now())) {
            return 0;
        }

        if ($voucher->usos_realizados >= $voucher->usos_maximos) {
            return 0;
        }

        if ($voucher->monto_minimo && $subtotal < $voucher->monto_minimo) {
            return 0;
        }

        if ($voucher->destinatario_email && strcasecmp($voucher->destinatario_email, $plan->cliente->email) !== 0) {
            return 0;
        }

        return $voucher->tipo_descuento === 'porcentaje'
            ? (int) round($subtotal * ($voucher->valor / 100))
            : min($subtotal, (int) $voucher->valor);
    }

    private function vistaUsuariosRol(string $rol)
    {
        $config = $this->configRolOperativo($rol);
        $buscar = trim((string) request('buscar'));

        return view('admin.usuarios_rol', [
            'usuarios' => User::with('localVenta')
                ->where('rol', $rol)
                ->when($buscar, fn ($query) => $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('name', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('telefono', 'like', "%{$buscar}%")
                        ->orWhere('vehiculo_patente', 'like', "%{$buscar}%");
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'rol' => $rol,
            'config' => $config,
            'buscar' => $buscar,
        ]);
    }

    private function formularioUsuarioRol(string $rol, ?User $usuarioEditar = null)
    {
        return view('admin.usuario_rol_form', [
            'locales' => LocalVenta::orderBy('nombre')->get(),
            'usuarioEditar' => $usuarioEditar,
            'rol' => $rol,
            'config' => $this->configRolOperativo($rol),
        ]);
    }

    private function guardarUsuarioRol(Request $request, string $rol)
    {
        $data = $this->validarUsuarioOperativo($request, $rol);
        $data['rol'] = $rol;
        $data['activo'] = (bool) ($data['activo'] ?? true);
        $data = $this->guardarArchivosRepartidor($request, $rol, $data);
        User::create($data);

        return redirect()->route("admin.{$this->configRolOperativo($rol)['ruta']}.index")->with('ok', $this->configRolOperativo($rol)['singular'] . ' creado.');
    }

    private function actualizarUsuarioRol(Request $request, User $user, string $rol)
    {
        abort_unless($user->rol === $rol, 404);

        $data = $this->validarUsuarioOperativo($request, $rol, $user);
        $data['rol'] = $rol;
        $data['activo'] = (bool) ($data['activo'] ?? false);
        $data = $this->guardarArchivosRepartidor($request, $rol, $data);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route("admin.{$this->configRolOperativo($rol)['ruta']}.index")->with('ok', $this->configRolOperativo($rol)['singular'] . ' actualizado.');
    }

    private function cambiarEstadoUsuarioRol(User $user, string $rol)
    {
        abort_unless($user->rol === $rol, 404);
        $user->update(['activo' => !$user->activo]);

        return back()->with('ok', $user->activo ? 'Usuario activado.' : 'Usuario inactivado.');
    }

    private function validarUsuarioOperativo(Request $request, string $rol, ?User $user = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . ($user?->id ?? 'NULL')],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'telefono' => ['nullable', 'string', 'max:80'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'local_venta_id' => ['nullable', 'exists:locales_venta,id'],
            'activo' => ['nullable', 'boolean'],
        ];

        if ($rol === 'repartidor') {
            $rules += [
                'foto' => ['nullable', 'image', 'max:4096'],
                'vehiculo_foto' => ['nullable', 'image', 'max:4096'],
                'vehiculo_patente' => ['nullable', 'string', 'max:30'],
                'vehiculo_marca' => ['nullable', 'string', 'max:255'],
                'vehiculo_modelo' => ['nullable', 'string', 'max:255'],
            ];
        }

        return $request->validate($rules);
    }

    private function validarClienteAdmin(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . ($user?->id ?? 'NULL')],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'telefono' => ['nullable', 'string', 'max:80'],
            'plan_preferido' => ['nullable', 'string', 'max:80'],
            'recibe_voucher' => ['nullable', 'boolean'],
            'porcentaje_descuento_voucher' => ['nullable', 'integer', 'min:0', 'max:100'],
            'encuesta_sistema_nacional' => ['nullable', 'in:muy_interesante,interesante,neutral,poco_interesante,no_interesa'],
            'comentario_sistema_nacional' => ['nullable', 'string', 'max:1000'],
            'direccion_principal' => ['nullable', 'string', 'max:500'],
            'comuna' => ['nullable', 'string', 'max:120'],
            'referencia' => ['nullable', 'string', 'max:500'],
            'georeferencia_url' => ['nullable', 'string', 'max:1000'],
            'activo' => ['nullable', 'boolean'],
        ]) + ['recibe_voucher' => false];
    }

    private function validarLocalVenta(Request $request, ?LocalVenta $local = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:40', 'unique:locales_venta,codigo,' . ($local?->id ?? 'NULL')],
            'tipo' => ['required', 'string', 'max:80'],
            'rut' => ['nullable', 'string', 'max:40'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:500'],
            'comuna' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'contacto_comercial' => ['nullable', 'string', 'max:255'],
            'modalidad_convenio' => ['nullable', 'in:operacion_propia,venta_directa,comision_venta,pago_mensual,mixto,comision_entrega,marketplace,otro'],
            'servicios_ofrecidos' => ['nullable', 'array'],
            'servicios_ofrecidos.*' => ['string', 'in:alimentos,farmacia,atencion_veterinaria,peluqueria,hotel_guarderia,retiro,despacho,marketplace'],
            'condiciones_convenio' => ['nullable', 'string', 'max:2000'],
            'publica_ofertas' => ['nullable', 'boolean'],
            'url_ofertas' => ['nullable', 'url', 'max:1000'],
            'georeferencia_url' => ['nullable', 'string', 'max:1000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'activo' => ['nullable', 'boolean'],
        ]) + ['servicios_ofrecidos' => [], 'publica_ofertas' => false];
    }

    private function tiposLocalVenta(): array
    {
        return [
            'sucursal' => 'Sucursal propia',
            'comercio_adherido' => 'Comercio adherido',
            'punto_retiro' => 'Punto de retiro',
            'farmacia' => 'Farmacia asociada',
            'clinica_veterinaria' => 'Clinica o veterinaria',
            'marketplace' => 'Convenio / marketplace',
            'otro' => 'Otro',
        ];
    }

    private function guardarDireccionCliente(User $user, ?string $direccion, ?string $comuna, ?string $referencia): void
    {
        if (!$direccion) {
            return;
        }

        $user->direcciones()->updateOrCreate(
            ['alias' => 'Principal'],
            [
                'direccion' => $direccion,
                'comuna' => $comuna,
                'referencia' => $referencia,
                'principal' => true,
            ]
        );
    }

    private function urlMapaCliente(?string $direccion, ?string $comuna): ?string
    {
        $texto = trim(implode(' ', array_filter([$direccion, $comuna])));

        return $texto ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($texto) : null;
    }

    private function catalogoPlanesComerciales(): array
    {
        return [
            [
                'nombre' => 'Plan Alimento Inscrito',
                'etiqueta' => 'Automatico mensual',
                'valor_inicial' => 14990,
                'valor_mensual' => 4990,
                'valor_futuro' => 3990,
                'descripcion' => 'Inscripcion de mascota, programacion de alimento y despacho recurrente.',
                'publico' => 'Clientes con compra mensual de alimento para perros, gatos u otras mascotas.',
                'destacado' => 'Evita quiebres de alimento y deja el despacho programado con pago automatico.',
                'operacion' => ['Alta de cliente y mascota', 'Producto inscrito por especie', 'Cobro recurrente', 'Ruta y tracking de entrega'],
                'seguridad' => ['Pago tokenizado', 'Aviso previo de cobro', 'Historial de entregas'],
                'proyeccion' => 'Ideal para fidelizar clientes y proyectar demanda de stock por bodega.',
                'incluye' => ['App cliente', 'Alimento inscrito automatico', 'Pago mensual con tarjeta', 'Recordatorio de entrega', 'Tracking de reparto'],
                'voucher' => ['titulo' => 'Voucher alimento inscrito', 'tipo' => 'porcentaje', 'valor' => 10, 'usos' => 12, 'minimo' => 25000],
            ],
            [
                'nombre' => 'Plan Salud Preventiva',
                'etiqueta' => 'Clinico basico',
                'valor_inicial' => 19990,
                'valor_mensual' => 6990,
                'valor_futuro' => 5990,
                'descripcion' => 'Control de vacunas, desparasitaciones y calendario sanitario.',
                'publico' => 'Mascotas con controles periodicos, vacunas pendientes o seguimiento sanitario.',
                'destacado' => 'Ordena vacunas, desparasitaciones y recordatorios para que el cliente no pierda fechas.',
                'operacion' => ['Ficha sanitaria', 'Calendario de vacunas', 'Recordatorios email', 'Derivacion a profesional'],
                'seguridad' => ['Registro historico', 'Alertas por vencimiento', 'Auditoria de cambios'],
                'proyeccion' => 'Puede escalar a convenio con clinicas y veterinarios por zona.',
                'incluye' => ['Carne de vacunas digital', 'Recordatorio de vacunas', 'Desparasitaciones programadas', 'Alertas por mascota', 'Historial sanitario'],
                'voucher' => ['titulo' => 'Voucher salud preventiva', 'tipo' => 'porcentaje', 'valor' => 15, 'usos' => 6, 'minimo' => 10000],
            ],
            [
                'nombre' => 'Plan Vaucher VetChile',
                'etiqueta' => 'Beneficios y QR',
                'valor_inicial' => 24990,
                'valor_mensual' => 8990,
                'valor_futuro' => 7490,
                'descripcion' => 'Sistema de beneficios con voucher seguro, QR y control de canje.',
                'publico' => 'Clientes frecuentes, comercios adheridos y profesionales autorizados.',
                'destacado' => 'Beneficios con QR, firma segura, control de usos y rendicion auditable.',
                'operacion' => ['Emision de voucher', 'Canje por QR', 'Rendicion a profesional', 'Auditoria de fraude'],
                'seguridad' => ['Firma SHA-256', 'Limite de usos', 'Vigencia y destinatario'],
                'proyeccion' => 'Base para alianzas comerciales, campanas y beneficios corporativos.',
                'incluye' => ['App voucher', 'QR de validacion', 'Descuentos por atencion', 'Auditoria de canjes', 'Rendiciones'],
                'voucher' => ['titulo' => 'Voucher VetChile plan QR', 'tipo' => 'porcentaje', 'valor' => 20, 'usos' => 12, 'minimo' => 15000],
            ],
            [
                'nombre' => 'Plan Identidad QR',
                'etiqueta' => 'Placa para collar',
                'valor_inicial' => 29990,
                'valor_mensual' => 2990,
                'valor_futuro' => 1990,
                'descripcion' => 'Placa fisica con QR para identificar mascota y datos de contacto del dueno.',
                'publico' => 'Mascotas que salen a paseos, hoteles, guarderias o traslados frecuentes.',
                'destacado' => 'Placa QR con datos de contacto y ficha visible para recuperar mascotas rapidamente.',
                'operacion' => ['Alta de placa', 'QR publico seguro', 'Datos de contacto', 'Actualizacion por cliente'],
                'seguridad' => ['Datos limitados', 'Token no editable', 'Bloqueo por extravio'],
                'proyeccion' => 'Producto de bajo costo mensual con alta retencion y valor percibido.',
                'incluye' => ['Placa QR collar', 'Datos del dueno', 'Direccion y telefono', 'Ficha de mascota', 'Pagina publica segura'],
                'voucher' => ['titulo' => 'Voucher placa QR collar', 'tipo' => 'monto_fijo', 'valor' => 5000, 'usos' => 1, 'minimo' => 15000],
            ],
            [
                'nombre' => 'Plan Historial Clinico Plus',
                'etiqueta' => 'Mascota completa',
                'valor_inicial' => 34990,
                'valor_mensual' => 9990,
                'valor_futuro' => 8490,
                'descripcion' => 'Ficha clinica, vacunas, desparasitaciones, alimentos y servicios conectados.',
                'publico' => 'Clientes VIP con una o varias mascotas y uso de servicios recurrentes.',
                'destacado' => 'Une alimento, salud, placa QR, vouchers y soporte en un plan integral.',
                'operacion' => ['Ficha completa', 'Plan de alimento', 'Servicios veterinarios', 'Beneficios y soporte'],
                'seguridad' => ['2FA administrativo', 'Trazabilidad clinica', 'Permisos por rol'],
                'proyeccion' => 'Plan premium para aumentar ticket mensual y consolidar informacion de la mascota.',
                'incluye' => ['Historial clinico mascota', 'Carne vacunas', 'Desparasitaciones', 'Alimento automatico', 'QR collar', 'Soporte VIP'],
                'voucher' => ['titulo' => 'Voucher historial clinico plus', 'tipo' => 'porcentaje', 'valor' => 25, 'usos' => 12, 'minimo' => 20000],
            ],
        ];
    }

    private function guardarArchivosRepartidor(Request $request, string $rol, array $data): array
    {
        if ($rol !== 'repartidor') {
            return $data;
        }

        if ($request->hasFile('foto')) {
            $data['foto_url'] = 'storage/' . $request->file('foto')->store('repartidores', 'public');
        }

        if ($request->hasFile('vehiculo_foto')) {
            $data['vehiculo_foto_url'] = 'storage/' . $request->file('vehiculo_foto')->store('repartidores/vehiculos', 'public');
        }

        unset($data['foto'], $data['vehiculo_foto']);

        return $data;
    }

    private function configRolOperativo(string $rol): array
    {
        return match ($rol) {
            'repartidor' => [
                'ruta' => 'repartidores',
                'titulo' => 'Repartidores',
                'singular' => 'Repartidor',
                'descripcion' => 'Registro de repartidores.',
                'icono' => 'R',
                'boton' => 'Ver repartidores',
            ],
            default => [
                'ruta' => 'vendedores',
                'titulo' => 'Vendedores',
                'singular' => 'Vendedor',
                'descripcion' => 'Vendedores autorizados para emitir vouchers.',
                'icono' => 'V',
                'boton' => 'Ver Vendedores',
            ],
        };
    }

    private function validarServicio(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'categoria' => ['required', 'in:servicio,hotel,paseo_diario,cementerio,cuidado'],
            'tipo_servicio' => ['required', 'string', 'max:120'],
            'modalidad_servicio' => ['nullable', 'string', 'max:120'],
            'duracion_minutos' => ['nullable', 'integer', 'min:0'],
            'peso' => ['nullable', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'precio_compra' => ['nullable', 'integer', 'min:0'],
            'precio' => ['required', 'integer', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'max:4096'],
            'foto_url' => ['nullable', 'string', 'max:500'],
            'sucursal_destino' => ['nullable', 'string', 'max:255'],
            'medio_envio' => ['nullable', 'string', 'max:120'],
            'requiere_agenda' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);
    }

    private function categoriasServicio(): array
    {
        return ['servicio', 'hotel', 'paseo_diario', 'cementerio', 'cuidado'];
    }

    private function tiposServicio(): array
    {
        return [
            'bano' => 'Bano',
            'peluqueria' => 'Peluqueria',
            'veterinaria_domicilio' => 'Veterinaria a domicilio',
            'consulta_clinica' => 'Consulta clinica',
            'hotel_dia' => 'Hotel dia',
            'hotel_noche' => 'Hotel noche',
            'paseo_diario' => 'Paseo diario',
            'cementerio' => 'Cementerio',
            'traslado' => 'Traslado',
            'cuidados' => 'Cuidados',
            'otro' => 'Otro',
        ];
    }

    private function alertasVoucher($vouchers, $movimientos)
    {
        $alertas = collect();

        foreach ($vouchers as $voucher) {
            if (!$voucher->firma_seguridad) {
                $alertas->push(['nivel' => 'alto', 'mensaje' => "Voucher {$voucher->codigo} sin firma de seguridad."]);
            }
            if ($voucher->usos_realizados > $voucher->usos_maximos) {
                $alertas->push(['nivel' => 'alto', 'mensaje' => "Voucher {$voucher->codigo} excedio usos permitidos."]);
            }
            if ($voucher->valido_hasta && $voucher->valido_hasta->endOfDay()->lt(now()) && $voucher->activo) {
                $alertas->push(['nivel' => 'medio', 'mensaje' => "Voucher {$voucher->codigo} vencido aun activo."]);
            }
        }

        $duplicados = $movimientos
            ->where('tipo', 'canje')
            ->groupBy('voucher_descuento_id')
            ->filter(fn ($items) => $items->count() > 1);

        foreach ($duplicados as $items) {
            $alertas->push(['nivel' => 'medio', 'mensaje' => 'Voucher con canjes repetidos: ' . $items->first()->voucher?->codigo]);
        }

        return $alertas;
    }

    private function generarCodigoVoucher(): string
    {
        do {
            $codigo = 'VCH-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        } while (VoucherDescuento::where('codigo', $codigo)->exists());

        return $codigo;
    }

    private function firmaVoucher(string $codigo, array $data): string
    {
        $payload = implode('|', [
            $codigo,
            $data['tipo_descuento'] ?? '',
            $data['valor'] ?? 0,
            $data['monto_minimo'] ?? 0,
            $data['usos_maximos'] ?? 1,
            $data['valido_hasta'] ?? '',
            $data['destinatario_email'] ?? '',
        ]);

        return hash_hmac('sha256', $payload, config('app.key'));
    }
}
