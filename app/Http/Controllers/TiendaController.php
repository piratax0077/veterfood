<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\PlanPedido;
use App\Models\Producto;
use App\Models\TrackingEvento;
use App\Models\Bodega;
use App\Services\SdiRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TiendaController extends Controller
{
    private const ENVIO_GRATIS_DESDE = 50000;
    private const COSTO_ENVIO = 3500;

    public function catalogo()
    {
        if (request()->filled('plan_extra') && auth()->check()) {
            $planExtraId = (int) request('plan_extra');
            $planPerteneceCliente = PlanPedido::where('id', $planExtraId)
                ->where('user_id', auth()->id())
                ->exists();

            if ($planPerteneceCliente) {
                session(['plan_extra_id' => $planExtraId]);
            }
        }

        $categoria = request('categoria');
        $busqueda = trim((string) request('buscar', ''));
        $filtroCategoria = request('tipo');
        $especie = request('especie');
        $especiesSubcategorias = [
            'perro' => ['Perros', 'Cachorros'],
            'gato' => ['Gatos'],
            'exotico' => ['Conejos', 'Aves', 'Peces'],
        ];
        $especiesPalabras = [
            'perro' => ['perro', 'perros', 'canino', 'cachorro'],
            'gato' => ['gato', 'gatos', 'felino', 'gatito'],
            'exotico' => ['conejo', 'conejos', 'ave', 'aves', 'pez', 'peces', 'exotico', 'exótico', 'hamster', 'roedor'],
        ];
        $orden = request('orden');
        $rangosPrecio = array_filter((array) request('precio', []));
        $marcasFiltro = array_filter((array) request('marcas', []));
        $tiposFiltro = array_filter((array) request('tipos', []));
        $categoriasAdicionales = ['medicamento', 'juguete', 'utensilio', 'hotel', 'paseo_diario', 'cementerio', 'cuidado', 'servicio'];
        $categoriasTienda = Producto::CATEGORIAS;
        $secciones = [
            'general' => [
                'titulo' => 'Tienda general',
                'descripcion' => 'Alimentos, snacks, productos de rutina y compras rápidas para el hogar.',
                'categorias' => ['alimento_mascota', 'utensilio'],
            ],
            'entretencion' => [
                'titulo' => 'Entretención',
                'descripcion' => 'Juguetes, mordedores, enrichment y accesorios para actividad diaria.',
                'categorias' => ['juguete'],
            ],
            'hoteles' => [
                'titulo' => 'Hoteles',
                'descripcion' => 'Reservas, estadías diarias y convenios de hotelería para mascotas.',
                'categorias' => ['hotel'],
            ],
            'paseos' => [
                'titulo' => 'Paseos diarios',
                'descripcion' => 'Paseos programados, visitas y acompañamiento diario para mascotas.',
                'categorias' => ['paseo_diario'],
            ],
            'cementerio' => [
                'titulo' => 'Cementerio',
                'descripcion' => 'Servicios de despedida, retiro y apoyo respetuoso para mascotas.',
                'categorias' => ['cementerio'],
            ],
            'cuidados' => [
                'titulo' => 'Cuidados y útiles',
                'descripcion' => 'Higiene, limpieza, paseo, transporte y artículos útiles para mascotas.',
                'categorias' => ['cuidado', 'utensilio'],
            ],
            'servicios' => [
                'titulo' => 'Servicios a domicilio',
                'descripcion' => 'Baño, peluquería, veterinaria a domicilio y apoyos programables.',
                'categorias' => ['servicio'],
            ],
        ];

        $seccionActiva = $this->seccionDesdeCategoria($categoria);

        $categoriasFiltro = match ($categoria) {
            'adicional' => $categoriasAdicionales,
            'general', 'entretencion', 'hoteles', 'paseos', 'cementerio', 'cuidados', 'servicios' => $secciones[$categoria]['categorias'],
            default => null,
        };

        $productos = Producto::where('activo', true)
                ->whereNotNull('foto_url')->where('foto_url', '!=', '')
                ->when($categoriasFiltro, fn ($query) => $query->whereIn('categoria', $categoriasFiltro))
                ->when($categoria && !$categoriasFiltro, fn ($query) => $query->where('categoria', $categoria))
                ->when($filtroCategoria, fn ($query) => $query->where('categoria', $filtroCategoria))
                ->when($especie && isset($especiesSubcategorias[$especie]), function ($query) use ($especie, $especiesSubcategorias, $especiesPalabras) {
                    $query->where(function ($grupo) use ($especie, $especiesSubcategorias, $especiesPalabras) {
                        $grupo->whereIn('subcategoria', $especiesSubcategorias[$especie]);
                        foreach ($especiesPalabras[$especie] as $palabra) {
                            $grupo->orWhere('nombre', 'like', "%{$palabra}%")
                                ->orWhere('descripcion', 'like', "%{$palabra}%");
                        }
                    });
                })
                ->when($tiposFiltro, fn ($query) => $query->whereIn('categoria', $tiposFiltro))
                ->when($marcasFiltro, fn ($query) => $query->whereIn('marca', $marcasFiltro))
                ->when($rangosPrecio, function ($query) use ($rangosPrecio) {
                    $query->where(function ($grupo) use ($rangosPrecio) {
                        foreach ($rangosPrecio as $rango) {
                            [$desde, $hasta] = array_pad(explode('-', (string) $rango), 2, null);
                            $grupo->orWhere(function ($tramo) use ($desde, $hasta) {
                                $tramo->where('precio', '>=', (int) $desde);
                                if ($hasta !== null && $hasta !== '') {
                                    $tramo->where('precio', '<=', (int) $hasta);
                                }
                            });
                        }
                    });
                })
                ->when($busqueda !== '', function ($query) use ($busqueda) {
                    $query->where(function ($subquery) use ($busqueda) {
                        $subquery->where('nombre', 'like', "%{$busqueda}%")
                            ->orWhere('marca', 'like', "%{$busqueda}%")
                            ->orWhere('descripcion', 'like', "%{$busqueda}%");
                    });
                });

        match ($orden) {
            'precio_asc' => $productos->orderBy('precio'),
            'precio_desc' => $productos->orderByDesc('precio'),
            default => $productos->orderBy('categoria')->orderBy('marca')->orderBy('nombre'),
        };

        $alcance = fn () => Producto::where('activo', true)
            ->whereNotNull('foto_url')->where('foto_url', '!=', '')
            ->when($categoriasFiltro, fn ($query) => $query->whereIn('categoria', $categoriasFiltro))
            ->when($categoria && !$categoriasFiltro, fn ($query) => $query->where('categoria', $categoria));

        $marcasDisponibles = $alcance()->whereNotNull('marca')->where('marca', '!=', '')
            ->distinct()->orderBy('marca')->pluck('marca')->all();

        $tiposDisponibles = $alcance()->distinct()->orderBy('categoria')->pluck('categoria')->all();

        return view('tienda.catalogo', [
            'productos' => $productos->get(),
            'categoria' => $categoria,
            'seccionActiva' => $seccionActiva,
            'secciones' => $secciones,
            'categoriasTienda' => $categoriasTienda,
            'busqueda' => $busqueda,
            'filtroCategoria' => $filtroCategoria,
            'especie' => $especie,
            'orden' => $orden,
            'rangosPrecio' => $rangosPrecio,
            'marcasFiltro' => $marcasFiltro,
            'tiposFiltro' => $tiposFiltro,
            'marcasDisponibles' => $marcasDisponibles,
            'tiposDisponibles' => $tiposDisponibles,
            'carro' => $this->carroActual(),
            'carros' => $this->resumenCarros($secciones),
            'planExtra' => $this->planExtraActual(),
        ]);
    }

    /** Outlet: solo productos con precio de oferta. Por defecto primero los de mayor descuento. */
    public function outlet(Request $request)
    {
        $orden = $request->query('orden', 'descuento');

        $productos = Producto::where('activo', true)
            ->enOutlet()
            ->when($orden === 'precio_asc', fn ($query) => $query->orderBy('precio_oferta'))
            ->when($orden === 'precio_desc', fn ($query) => $query->orderByDesc('precio_oferta'))
            ->when(!in_array($orden, ['precio_asc', 'precio_desc'], true), fn ($query) => $query->orderByRaw('(precio - precio_oferta) / precio DESC'))
            ->get();

        return view('tienda.outlet', [
            'productos' => $productos,
            'orden' => $orden,
            'descuentoMaximo' => (int) $productos->max('descuento_porcentaje'),
        ]);
    }

    public function agregar(Request $request, Producto $producto)
    {
        $carro = $this->carroActual();
        $carro[$producto->id] = ($carro[$producto->id] ?? 0) + max(1, (int) $request->integer('cantidad', 1));
        session(['carro_alimentos' => $carro]);

        return back()->with('ok', 'Producto agregado al carro.');
    }

    public function carro(Request $request)
    {
        // El panel lateral del carro pide los mismos datos en formato JSON.
        if ($request->wantsJson()) {
            return response()->json($this->carroJson());
        }

        return view('tienda.carro', $this->carroData());
    }

    /** Cambia la cantidad de un producto del carro (0 lo elimina). Lo usan el panel lateral y la pagina del carro. */
    public function actualizarItemCarro(Request $request, Producto $producto)
    {
        $cantidad = max(0, (int) $request->integer('cantidad'));
        $carro = $this->carroActual();

        if ($cantidad === 0) {
            unset($carro[$producto->id]);
            $mensaje = $producto->nombre . ' se quitó del carro.';
        } else {
            $carro[$producto->id] = min($cantidad, max(1, (int) $producto->stock));
            $mensaje = 'Cantidad actualizada.';
        }

        session(['carro_alimentos' => $carro]);

        if ($request->wantsJson()) {
            return response()->json($this->carroJson() + ['mensaje' => $mensaje]);
        }

        return redirect()->route('tienda.carro')->with('ok', $mensaje);
    }

    public function actualizarCarro(Request $request)
    {
        $carro = [];

        foreach ($request->input('cantidades', []) as $productoId => $cantidad) {
            if ((int) $cantidad > 0) {
                $carro[(int) $productoId] = (int) $cantidad;
            }
        }

        session(['carro_alimentos' => $carro]);

        return redirect()->route('tienda.carro')->with('ok', 'Carro actualizado.');
    }

    public function checkout()
    {
        $data = $this->carroData();

        if ($data['items']->isEmpty()) {
            return redirect()->route('tienda.catalogo');
        }

        $data['regiones'] = DB::table($this->tablaVet('regiones'))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        // La tarjeta predeterminada vigente queda seleccionada automaticamente.
        $data['tarjetasPago'] = auth()->user()?->tarjetas()->get() ?? collect();
        $data['tarjetaSugerida'] = $data['tarjetasPago']->reject->vencida->first();

        return view('tienda.checkout', $data);
    }

    public function ciudadesPorRegion(int $region)
    {
        $existe = DB::table($this->tablaVet('regiones'))->where('id', $region)->exists();
        abort_unless($existe, 404);

        return response()->json(
            DB::table($this->tablaVet('ciudades'))
                ->where('id_region', $region)
                ->orderBy('nombre')
                ->get(['id', 'nombre'])
        );
    }

    public function regiones()
    {
        return response()->json(
            DB::table($this->tablaVet('regiones'))->orderBy('id')->get(['id', 'nombre'])
        );
    }

    /** Guarda la comuna de despacho elegida en el menu "Ubicacion" (base para filtrar productos por zona). */
    public function guardarUbicacion(Request $request)
    {
        $data = $request->validate([
            'region_id' => ['required', 'integer'],
            'ciudad_id' => ['required', 'integer'],
        ], [
            'region_id.required' => 'Selecciona una región.',
            'ciudad_id.required' => 'Selecciona una comuna.',
        ]);

        $ubicacion = DB::table($this->tablaVet('ciudades') . ' as c')
            ->join($this->tablaVet('regiones') . ' as r', 'r.id', '=', 'c.id_region')
            ->where('c.id', $data['ciudad_id'])
            ->where('r.id', $data['region_id'])
            ->first(['c.id as ciudad_id', 'c.nombre as ciudad', 'r.id as region_id', 'r.nombre as region']);

        if (!$ubicacion) {
            return $request->expectsJson()
                ? response()->json(['message' => 'La comuna no pertenece a la región seleccionada.'], 422)
                : back()->withErrors(['ciudad_id' => 'La comuna no pertenece a la región seleccionada.']);
        }

        session(['ubicacion_despacho' => (array) $ubicacion]);

        return $request->expectsJson()
            ? response()->json(['ubicacion' => $ubicacion, 'message' => 'Mostraremos opciones de despacho para ' . $ubicacion->ciudad . '.'])
            : back()->with('ok', 'Ubicación guardada: ' . $ubicacion->ciudad . '.');
    }

    public function seguimiento()
    {
        $pedidosEnCurso = auth()->check()
            ? Pedido::where('user_id', auth()->id())
                ->whereNotIn('estado', ['entregado', 'cancelado'])
                ->latest()
                ->take(3)
                ->get(['codigo_tracking', 'estado', 'created_at', 'total'])
            : collect();

        return view('tienda.seguimiento', ['pedidosEnCurso' => $pedidosEnCurso]);
    }

    public function buscarSeguimiento(Request $request)
    {
        $request->merge(['codigo' => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->input('codigo')))]);

        $data = $request->validate([
            'codigo' => ['required', 'string', 'min:6', 'max:20'],
        ], [
            'codigo.required' => 'Ingresa tu número de seguimiento.',
            'codigo.min' => 'El número de seguimiento está incompleto.',
            'codigo.max' => 'El número de seguimiento es demasiado largo.',
        ]);

        $pedido = Pedido::where('codigo_tracking', $data['codigo'])->first(['codigo_tracking']);

        if (!$pedido) {
            return back()->withInput()->withErrors([
                'codigo' => 'No encontramos un pedido con ese número. Revisa que esté bien escrito.',
            ]);
        }

        return redirect()->route('tracking.show', $pedido->codigo_tracking);
    }

    // Dirección nueva escrita en el pago: se guarda en la cuenta si el cliente dejó activo "Guardar esta dirección"
    private function guardarDireccionDelPedido(Request $request, array $validated): void
    {
        $usuario = auth()->user();

        if (!$usuario?->tieneRol('cliente', 'dueno_mascota')
            || $validated['entrega_tipo'] !== 'despacho'
            || !$request->boolean('guardar_direccion')
            || ($validated['direccion_guardada'] ?? 'nueva') !== 'nueva'
            || empty($validated['ciudad_id'])) {
            return;
        }

        $yaGuardada = $usuario->direcciones()
            ->where('direccion', $validated['direccion_entrega'])
            ->where('comuna_id', $validated['ciudad_id'])
            ->exists();

        if ($yaGuardada) {
            return;
        }

        $primera = !$usuario->direcciones()->exists();

        $usuario->direcciones()->create([
            'alias' => $primera ? 'Casa' : 'Dirección ' . ($usuario->direcciones()->count() + 1),
            'direccion' => $validated['direccion_entrega'],
            'region_id' => $validated['region_id'],
            'region' => $validated['region_nombre'] ?? null,
            'comuna_id' => $validated['ciudad_id'],
            'comuna' => $validated['ciudad_nombre'] ?? null,
            'referencia' => $validated['direccion_referencia'] ?? null,
            'principal' => $primera,
        ]);

        if ($primera) {
            $usuario->update(['direccion' => $validated['direccion_entrega']]);
        }
    }

    public function confirmar(Request $request)
    {
        $data = $this->carroData();

        if ($data['items']->isEmpty()) {
            return redirect()->route('tienda.catalogo');
        }

        $validated = $request->validate([
            'cliente_nombre' => ['required', 'string', 'max:255'],
            'cliente_email' => ['nullable', 'email', 'max:255'],
            'cliente_telefono' => ['nullable', 'string', 'max:50'],
            'direccion_entrega' => ['nullable', 'string', 'max:500'],
            'region_id' => ['nullable', 'integer'],
            'ciudad_id' => ['nullable', 'integer'],
            'notas_entrega' => ['nullable', 'string', 'max:1000'],
            'fecha_entrega' => ['nullable', 'date'],
            'horario_preferencia' => ['nullable', 'string', 'max:120'],
            'entrega_tipo' => ['required', 'in:despacho,retiro'],
            'metodo_pago' => ['required', 'string', 'max:60'],
            'tarjeta_id' => ['nullable', 'string', 'max:20'],
            'georeferencia_url' => ['nullable', 'url', 'max:1000'],
            'incluir_en_plan_mensual' => ['nullable', 'boolean'],
            'direccion_referencia' => ['nullable', 'string', 'max:500'],
            'direccion_guardada' => ['nullable', 'string', 'max:20'],
            'guardar_direccion' => ['nullable', 'boolean'],
            'cuotas' => ['nullable', 'integer', 'in:1,3,6,12,18,24,36'],
            'punto_retiro' => ['nullable', 'string', 'max:60'],
            'retira_rut' => ['nullable', 'string', 'max:12'],
            'retira_nombre' => ['nullable', 'string', 'max:120'],
            'retira_telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $tarjetaPago = null;
        if (!empty($validated['tarjeta_id']) && $validated['tarjeta_id'] !== 'otro') {
            $tarjetaPago = auth()->user()?->tarjetas()->whereKey((int) $validated['tarjeta_id'])->first();

            if (!$tarjetaPago || $tarjetaPago->vencida) {
                return back()->withErrors(['tarjeta_id' => 'Selecciona una tarjeta guardada vigente o elige otro medio de pago.'])->withInput();
            }

            $validated['metodo_pago'] = 'tarjeta_guardada';
        }

        if (($validated['entrega_tipo'] ?? 'despacho') === 'retiro') {
            $validated['direccion_entrega'] = 'Retiro en tienda';
            $validated['region_id'] = null;
            $validated['ciudad_id'] = null;
            $validated['region_nombre'] = null;
            $validated['ciudad_nombre'] = null;
        } elseif (empty($validated['direccion_entrega'])) {
            return back()->withErrors(['direccion_entrega' => 'Debes indicar dirección para despacho a domicilio.'])->withInput();
        } else {
            if (empty($validated['region_id']) || empty($validated['ciudad_id'])) {
                return back()->withErrors(['region_id' => 'Selecciona la región y la ciudad de despacho.'])->withInput();
            }

            $region = DB::table($this->tablaVet('regiones'))->where('id', $validated['region_id'])->first();
            $ciudad = DB::table($this->tablaVet('ciudades'))
                ->where('id', $validated['ciudad_id'])
                ->where('id_region', $validated['region_id'])
                ->first();

            if (!$region || !$ciudad) {
                return back()->withErrors(['ciudad_id' => 'La ciudad no pertenece a la región seleccionada.'])->withInput();
            }

            $validated['region_nombre'] = $region->nombre;
            $validated['ciudad_nombre'] = $ciudad->nombre;
        }

        $notas = trim($validated['notas_entrega'] ?? '');
        $notas .= ($notas ? "\n" : '') . 'Modalidad: ' . ($validated['entrega_tipo'] === 'retiro' ? 'Retiro en tienda' : 'Despacho a domicilio');
        if (!empty($validated['horario_preferencia'])) {
            $notas .= "\nHorario preferido: " . $validated['horario_preferencia'];
        }
        if ($validated['entrega_tipo'] === 'retiro') {
            if (!empty($validated['punto_retiro'])) {
                $notas .= "\nPunto de retiro: " . $validated['punto_retiro'];
            }
            if (!empty($validated['retira_nombre'])) {
                $notas .= "\nRetira: " . $validated['retira_nombre']
                    . (!empty($validated['retira_rut']) ? ' · RUT ' . $validated['retira_rut'] : '')
                    . (!empty($validated['retira_telefono']) ? ' · ' . $validated['retira_telefono'] : '');
            }
        }
        $pagoCredito = $validated['metodo_pago'] === 'tarjeta_credito' || ($tarjetaPago && $tarjetaPago->tipo !== 'debito');
        if ($pagoCredito && ($validated['cuotas'] ?? 1) > 1) {
            $notas .= "\nPago en " . $validated['cuotas'] . ' cuotas';
        }
        if (!empty($validated['georeferencia_url'])) {
            $notas .= "\nMapa despacho: " . $validated['georeferencia_url'];
        }
        if ($request->boolean('incluir_en_plan_mensual') && $this->planExtraActual()) {
            $notas .= "\nCliente solicita incluir estos extras en su pedido mensual #" . $this->planExtraActual()->id . ".";
        }
        $validated['notas_entrega'] = $notas;

        $pedido = DB::transaction(function () use ($validated, $data, $tarjetaPago) {
            $pedido = Pedido::create([
                'codigo_tracking' => strtoupper(Str::random(10)),
                'user_id' => auth()->id(),
                'bodega_id' => Bodega::where('tipo', 'central')->value('id'),
                'local_venta_id' => auth()->user()?->local_venta_id,
                'cliente_nombre' => $validated['cliente_nombre'],
                'cliente_email' => $validated['cliente_email'] ?? auth()->user()?->email,
                'cliente_telefono' => $validated['cliente_telefono'] ?? null,
                'direccion_entrega' => $validated['direccion_entrega'],
                'region_id' => $validated['region_id'] ?? null,
                'ciudad_id' => $validated['ciudad_id'] ?? null,
                'region_nombre' => $validated['region_nombre'] ?? null,
                'ciudad_nombre' => $validated['ciudad_nombre'] ?? null,
                'notas_entrega' => $validated['notas_entrega'] ?? null,
                'fecha_entrega' => $validated['fecha_entrega'] ?? now()->addDay()->toDateString(),
                'frecuencia' => 'unico',
                'prioridad' => 'normal',
                'estado' => 'recibido',
                'estado_pago' => 'pagado',
                'subtotal' => $data['subtotal'],
                'costo_envio' => $data['costoEnvio'],
                'total' => $data['total'],
                'pagado_at' => now(),
            ]);

            foreach ($data['items'] as $item) {
                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item['producto']->id,
                    'producto_nombre' => $item['producto']->nombre,
                    'producto_marca' => $item['producto']->marca,
                    'precio_unitario' => $item['precio'],
                    'cantidad' => $item['cantidad'],
                    'total' => $item['total'],
                ]);

                $item['producto']->decrement('stock', $item['cantidad']);
            }

            Pago::create([
                'pedido_id' => $pedido->id,
                'metodo' => $validated['metodo_pago'],
                'estado' => 'pagado',
                'monto' => $data['total'],
                'referencia' => 'LOCAL-' . now()->format('YmdHis'),
                // Copia de los datos visibles de la tarjeta para que el historial sobreviva si se elimina.
                'detalle' => array_filter([
                    'gateway' => 'simulado_local',
                    'tarjeta' => $tarjetaPago ? [
                        'id' => $tarjetaPago->id,
                        'marca' => $tarjetaPago->marca,
                        'tipo' => $tarjetaPago->tipo,
                        'ultimos_digitos' => $tarjetaPago->ultimos_digitos,
                        'descripcion' => $tarjetaPago->descripcion,
                    ] : null,
                ]),
                'pagado_at' => now(),
            ]);

            TrackingEvento::create([
                'pedido_id' => $pedido->id,
                'estado' => 'recibido',
                'mensaje' => 'Pedido recibido y pago confirmado.',
            ]);

            return $pedido;
        });

        $this->guardarDireccionDelPedido($request, $validated);

        session()->forget('carro_alimentos');
        session()->forget('plan_extra_id');
        $this->notificar($pedido, 'Recordatorio de entrega de productos', 'Tu pedido fue confirmado.');
        app(SdiRegistry::class)->syncPedido($pedido);

        return redirect()->route('tracking.show', $pedido->codigo_tracking)->with('ok', 'Pedido creado y pagado.');
    }

    private function tablaVet(string $tabla): string
    {
        $base = preg_replace('/[^A-Za-z0-9_]/', '', (string) config('database.connections.vet_sdi.database', 'vetsdi_veterchile'));

        return $base.'.'.$tabla;
    }

    public function tracking(string $codigo)
    {
        return view('tienda.tracking', [
            'pedido' => Pedido::with(['items.producto', 'tracking', 'repartidor', 'pago', 'voucher'])->where('codigo_tracking', $codigo)->firstOrFail(),
        ]);
    }

    private function carroData(): array
    {
        $carro = $this->carroActual();
        $productos = Producto::whereIn('id', array_keys($carro))->get()->keyBy('id');

        $items = collect($carro)->map(function ($cantidad, $productoId) use ($productos) {
            $producto = $productos->get((int) $productoId);

            // Los productos del Outlet se cobran a su precio de oferta.
            return $producto ? [
                'producto' => $producto,
                'cantidad' => (int) $cantidad,
                'precio' => $producto->precio_final,
                'total' => $producto->precio_final * (int) $cantidad,
                'foto' => $producto->foto_url ? asset($producto->foto_url) : null,
                'maximo' => max(1, (int) $producto->stock),
            ] : null;
        })->filter()->values();

        $subtotal = $items->sum('total');
        $costoEnvio = $subtotal > 0 && $subtotal < self::ENVIO_GRATIS_DESDE ? self::COSTO_ENVIO : 0;

        return [
            'items' => $items,
            'unidades' => $items->sum('cantidad'),
            'subtotal' => $subtotal,
            'costoEnvio' => $costoEnvio,
            'total' => $subtotal + $costoEnvio,
            'envioGratisDesde' => self::ENVIO_GRATIS_DESDE,
            'faltaEnvioGratis' => $subtotal > 0 ? max(0, self::ENVIO_GRATIS_DESDE - $subtotal) : 0,
            'tituloCarro' => 'Toda la tienda',
            'planExtra' => $this->planExtraActual(),
        ];
    }

    private function carroJson(): array
    {
        $data = $this->carroData();

        return [
            'items' => $data['items']->map(fn ($item) => [
                'id' => $item['producto']->id,
                'nombre' => $item['producto']->nombre,
                'detalle' => trim(collect([$item['producto']->marca, $item['producto']->peso])->filter()->implode(' · ')),
                'precio' => $item['precio'],
                'precioNormal' => $item['producto']->en_oferta ? $item['producto']->precio : null,
                'cantidad' => $item['cantidad'],
                'maximo' => $item['maximo'],
                'total' => $item['total'],
                'foto' => $item['foto'],
                'url' => route('tienda.carro.item', $item['producto']),
            ])->all(),
            'unidades' => $data['unidades'],
            'subtotal' => $data['subtotal'],
            'costoEnvio' => $data['costoEnvio'],
            'total' => $data['total'],
            'faltaEnvioGratis' => $data['faltaEnvioGratis'],
        ];
    }

    private function planExtraActual(): ?PlanPedido
    {
        $planId = session('plan_extra_id');

        if (!$planId || !auth()->check()) {
            return null;
        }

        return PlanPedido::with('producto')
            ->where('id', $planId)
            ->where('user_id', auth()->id())
            ->first();
    }

    private function carroActual(): array
    {
        $carro = session('carro_alimentos', []);

        foreach (['general', 'farmacia', 'entretencion', 'hoteles', 'paseos', 'cementerio', 'cuidados', 'servicios'] as $seccion) {
            foreach (session($this->sessionKey($seccion), []) as $productoId => $cantidad) {
                $carro[(int) $productoId] = ($carro[(int) $productoId] ?? 0) + (int) $cantidad;
            }
            session()->forget($this->sessionKey($seccion));
        }

        session(['carro_alimentos' => $carro]);

        return $carro;
    }

    private function resumenCarros(array $secciones): array
    {
        $resumen = [];
        $total = array_sum($this->carroActual());

        foreach (array_keys($secciones) as $seccion) {
            $resumen[$seccion] = $total;
        }

        return $resumen;
    }

    private function sessionKey(string $seccion): string
    {
        return 'carro_alimentos_' . $seccion;
    }

    private function seccionDesdeCategoria(?string $categoria): string
    {
        return match ($categoria) {
            'entretencion', 'juguete' => 'entretencion',
            'hoteles', 'hotel' => 'hoteles',
            'paseos', 'paseo_diario' => 'paseos',
            'cementerio' => 'cementerio',
            'cuidados', 'cuidado', 'utensilio' => 'cuidados',
            'servicios', 'servicio' => 'servicios',
            default => 'general',
        };
    }

    private function tituloSeccion(string $seccion): string
    {
        return match ($seccion) {
            'entretencion' => 'Entretención',
            'hoteles' => 'Hoteles',
            'paseos' => 'Paseos diarios',
            'cementerio' => 'Cementerio',
            'cuidados' => 'Cuidados y útiles',
            'servicios' => 'Servicios a domicilio',
            default => 'Tienda general',
        };
    }

    private function notificar(Pedido $pedido, string $asunto, string $mensaje): void
    {
        if (!$pedido->cliente_email) {
            return;
        }

        Mail::raw($mensaje . "\nPedido: {$pedido->codigo_tracking}\nTracking: " . route('tracking.show', $pedido->codigo_tracking), function ($mail) use ($pedido, $asunto) {
            $mail->to($pedido->cliente_email)->subject($asunto);
        });
    }
}
