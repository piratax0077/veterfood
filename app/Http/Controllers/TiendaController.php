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
        $categoriasTienda = [
            'alimento_mascota' => 'Alimentos',
            'medicamento' => 'Farmacia',
            'juguete' => 'Accesorios y Juguetes',
            'hotel' => 'Hoteles',
            'paseo_diario' => 'Paseos diarios',
            'cementerio' => 'Cementerio',
            'cuidado' => 'Cuidados',
            'servicio' => 'Servicios',
            'utensilio' => 'Utiles',
        ];
        $secciones = [
            'general' => [
                'titulo' => 'Tienda general',
                'descripcion' => 'Alimentos, snacks, productos de rutina y compras rapidas para el hogar.',
                'categorias' => ['alimento_mascota', 'utensilio'],
            ],
            'farmacia' => [
                'titulo' => 'Farmacia',
                'descripcion' => 'Medicamentos, antiparasitarios, suplementos y apoyo sanitario.',
                'categorias' => ['medicamento'],
            ],
            'entretencion' => [
                'titulo' => 'Entretencion',
                'descripcion' => 'Juguetes, mordedores, enrichment y accesorios para actividad diaria.',
                'categorias' => ['juguete'],
            ],
            'hoteles' => [
                'titulo' => 'Hoteles',
                'descripcion' => 'Reservas, estadias diarias y convenios de hoteleria para mascotas.',
                'categorias' => ['hotel'],
            ],
            'paseos' => [
                'titulo' => 'Paseos diarios',
                'descripcion' => 'Paseos programados, visitas y acompanamiento diario para mascotas.',
                'categorias' => ['paseo_diario'],
            ],
            'cementerio' => [
                'titulo' => 'Cementerio',
                'descripcion' => 'Servicios de despedida, retiro y apoyo respetuoso para mascotas.',
                'categorias' => ['cementerio'],
            ],
            'cuidados' => [
                'titulo' => 'Cuidados y utiles',
                'descripcion' => 'Higiene, limpieza, paseo, transporte y articulos utiles para mascotas.',
                'categorias' => ['cuidado', 'utensilio'],
            ],
            'servicios' => [
                'titulo' => 'Servicios a domicilio',
                'descripcion' => 'Bano, peluqueria, veterinaria a domicilio y apoyos programables.',
                'categorias' => ['servicio'],
            ],
        ];

        $seccionActiva = $this->seccionDesdeCategoria($categoria);

        $categoriasFiltro = match ($categoria) {
            'adicional' => $categoriasAdicionales,
            'general', 'farmacia', 'entretencion', 'hoteles', 'paseos', 'cementerio', 'cuidados', 'servicios' => $secciones[$categoria]['categorias'],
            default => null,
        };

        $productos = Producto::where('activo', true)
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

    public function agregar(Request $request, Producto $producto)
    {
        $carro = $this->carroActual();
        $carro[$producto->id] = ($carro[$producto->id] ?? 0) + max(1, (int) $request->integer('cantidad', 1));
        session(['carro_alimentos' => $carro]);

        return back()->with('ok', 'Producto agregado al carro.');
    }

    public function carro()
    {
        return view('tienda.carro', $this->carroData());
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
            return back()->withErrors(['direccion_entrega' => 'Debes indicar direccion para despacho a domicilio.'])->withInput();
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
                    'precio_unitario' => $item['producto']->precio,
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
            'pedido' => Pedido::with(['items', 'tracking', 'repartidor'])->where('codigo_tracking', $codigo)->firstOrFail(),
        ]);
    }

    private function carroData(): array
    {
        $carro = $this->carroActual();
        $productos = Producto::whereIn('id', array_keys($carro))->get()->keyBy('id');

        $items = collect($carro)->map(function ($cantidad, $productoId) use ($productos) {
            $producto = $productos->get((int) $productoId);

            return $producto ? [
                'producto' => $producto,
                'cantidad' => (int) $cantidad,
                'total' => $producto->precio * (int) $cantidad,
            ] : null;
        })->filter()->values();

        $subtotal = $items->sum('total');
        $costoEnvio = $subtotal > 0 && $subtotal < 50000 ? 3500 : 0;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'costoEnvio' => $costoEnvio,
            'total' => $subtotal + $costoEnvio,
            'tituloCarro' => 'Toda la tienda',
            'planExtra' => $this->planExtraActual(),
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
            'farmacia', 'medicamento' => 'farmacia',
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
            'farmacia' => 'Farmacia',
            'entretencion' => 'Entretencion',
            'hoteles' => 'Hoteles',
            'paseos' => 'Paseos diarios',
            'cementerio' => 'Cementerio',
            'cuidados' => 'Cuidados y utiles',
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
