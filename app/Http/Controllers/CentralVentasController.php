<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use App\Models\ClienteNotificacion;
use App\Models\Existencia;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\TrackingEvento;
use App\Models\User;
use App\Services\SdiRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CentralVentasController extends Controller
{
    public function panel()
    {
        return $this->rubro(request('rubro', 'alimentos'));
    }

    public function rubro(string $rubro)
    {
        return view('central.panel', $this->datosRubro($rubro));
    }

    public function ingreso(string $rubro)
    {
        return view('central.ingreso', $this->datosRubro($rubro));
    }

    public function ventas(?string $vista = 'preparar')
    {
        $vistas = [
            'preparar' => [
                'titulo' => 'Preparar pedidos',
                'descripcion' => 'Pedidos pagados o recibidos que deben ser revisados, embalados y dejados listos para despacho.',
                'estados' => ['recibido', 'pagado', 'preparando'],
            ],
            'despacho' => [
                'titulo' => 'Despacho',
                'descripcion' => 'Asignacion de repartidor, salida a ruta y control de entregas.',
                'estados' => ['preparando', 'asignado', 'en_ruta'],
            ],
            'tracking' => [
                'titulo' => 'Tracking y seguimiento',
                'descripcion' => 'Seguimiento operativo del pedido, eventos registrados y link publico.',
                'estados' => ['recibido', 'pagado', 'preparando', 'asignado', 'en_ruta', 'entregado'],
            ],
            'abonados' => [
                'titulo' => 'Clientes abonados',
                'descripcion' => 'Pedidos generados desde planes recurrentes o clientes con pago mensual.',
                'abonados' => true,
            ],
            'esporadicos' => [
                'titulo' => 'Clientes esporadicos',
                'descripcion' => 'Compras unicas de tienda, sin plan recurrente asociado.',
                'esporadicos' => true,
            ],
        ];

        $vista = array_key_exists($vista, $vistas) ? $vista : 'preparar';
        $config = $vistas[$vista];

        $pedidos = Pedido::with(['items', 'repartidor', 'tracking', 'cliente', 'planPedido'])
            ->when(isset($config['estados']), fn ($query) => $query->whereIn('estado', $config['estados']))
            ->when($config['abonados'] ?? false, fn ($query) => $query->whereNotNull('plan_pedido_id'))
            ->when($config['esporadicos'] ?? false, fn ($query) => $query->whereNull('plan_pedido_id'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('central.ventas', [
            'pedidos' => $pedidos,
            'vistas' => $vistas,
            'vistaActiva' => $vista,
            'config' => $config,
            'repartidores' => User::where('rol', 'repartidor')->where('activo', true)->orderBy('name')->get(),
        ]);
    }

    private function datosRubro(string $rubro): array
    {
        $rubros = $this->rubrosStock();
        abort_unless(isset($rubros[$rubro]), 404);
        $config = $rubros[$rubro];

        return [
            'pedidos' => Pedido::with(['items', 'repartidor', 'localVenta', 'bodega'])->latest()->get(),
            'productos' => Producto::whereIn('categoria', $config['categorias'])->orderBy('subcategoria')->orderBy('marca')->orderBy('nombre')->get(),
            'rubros' => $rubros,
            'rubroActivo' => $rubro,
            'config' => $config,
            'bodegas' => Bodega::orderBy('nombre')->get(),
            'repartidores' => User::where('rol', 'repartidor')->where('activo', true)->orderBy('name')->get(),
        ];
    }

    public function producto(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:255'],
            'subcategoria' => ['nullable', 'string', 'max:255'],
            'rubro' => ['nullable', 'string', 'max:80'],
            'peso' => ['nullable', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'precio_compra' => ['nullable', 'integer', 'min:0'],
            'precio' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'foto_producto' => ['nullable', 'image', 'max:4096'],
            'foto_url' => ['nullable', 'string', 'max:500'],
            'sucursal_destino' => ['nullable', 'string', 'max:255'],
            'medio_envio' => ['nullable', 'string', 'max:120'],
            'bodega_id' => ['nullable', 'exists:bodegas,id'],
        ]);

        if ($request->hasFile('foto_producto')) {
            $data['foto_url'] = 'storage/' . $request->file('foto_producto')->store('productos', 'public');
        }

        $bodegaId = ($data['bodega_id'] ?? null) ?: Bodega::where('tipo', 'central')->value('id');
        $rubro = $data['rubro'] ?: $this->rubroPorCategoria($data['categoria']);
        unset($data['foto_producto'], $data['bodega_id'], $data['rubro']);

        $producto = Producto::create(array_merge($data, [
            'precio_compra' => $data['precio_compra'] ?? 0,
            'stock_minimo' => $data['stock_minimo'] ?? 0,
            'activo' => true,
        ]));

        if ($bodegaId) {
            Existencia::updateOrCreate(
                ['producto_id' => $producto->id, 'bodega_id' => $bodegaId, 'local_venta_id' => null],
                [
                    'cantidad' => $producto->stock,
                    'stock_critico' => $producto->stock_minimo,
                    'stock_objetivo' => max($producto->stock, $producto->stock_minimo * 2),
                ]
            );
        }

        return redirect()->route('central.rubro', $rubro)->with('ok', 'Producto ingresado al stock.');
    }

    public function asignar(Request $request, Pedido $pedido)
    {
        $data = $request->validate(['repartidor_id' => ['required', 'exists:users,id']]);

        $pedido->update(['repartidor_id' => $data['repartidor_id'], 'estado' => 'asignado']);
        TrackingEvento::create([
            'pedido_id' => $pedido->id,
            'repartidor_id' => $data['repartidor_id'],
            'estado' => 'asignado',
            'mensaje' => 'Pedido asignado a repartidor.',
        ]);

        $this->notificar($pedido, 'Tu pedido fue asignado a reparto.');
        app(SdiRegistry::class)->syncPedido($pedido);

        return back()->with('ok', 'Repartidor asignado.');
    }

    public function estado(Request $request, Pedido $pedido)
    {
        $data = $request->validate(['estado' => ['required', 'string', 'max:60']]);
        $updates = ['estado' => $data['estado']];

        if ($data['estado'] === 'en_ruta') {
            $updates['despachado_at'] = now();
        }

        if ($data['estado'] === 'entregado') {
            $updates['entregado_at'] = now();
        }

        $pedido->update($updates);
        TrackingEvento::create([
            'pedido_id' => $pedido->id,
            'repartidor_id' => $pedido->repartidor_id,
            'estado' => $data['estado'],
            'mensaje' => 'Estado actualizado por central de ventas.',
        ]);

        $this->notificar($pedido, 'Actualizacion de entrega: ' . str_replace('_', ' ', $data['estado']));
        app(SdiRegistry::class)->syncPedido($pedido);

        return back()->with('ok', 'Estado actualizado.');
    }

    public function avisarEntregaMensual(Pedido $pedido)
    {
        abort_unless($pedido->plan_pedido_id && $pedido->user_id, 404);

        $pedido->load(['cliente', 'planPedido.producto']);
        $url = route('tienda.catalogo', [
            'categoria' => 'adicional',
            'plan_extra' => $pedido->plan_pedido_id,
        ]);
        $fecha = optional($pedido->fecha_entrega)->format('d-m-Y') ?: 'proxima fecha programada';
        $producto = $pedido->planPedido?->producto?->nombre ?: 'tu alimento mensual';
        $mensaje = "Tu pedido mensual de {$producto} sera enviado el {$fecha}. Puedes agregar farmacia, juguetes, utiles o servicios antes del despacho.";

        ClienteNotificacion::create([
            'user_id' => $pedido->user_id,
            'pedido_id' => $pedido->id,
            'plan_pedido_id' => $pedido->plan_pedido_id,
            'tipo' => 'entrega_mensual',
            'titulo' => 'Tu pedido mensual sera enviado pronto',
            'mensaje' => $mensaje,
            'url' => $url,
        ]);

        if ($pedido->cliente_email) {
            Mail::raw($mensaje . "\n\nAgregar productos extra: {$url}\nTracking: " . route('tracking.show', $pedido->codigo_tracking), function ($mail) use ($pedido) {
                $mail->to($pedido->cliente_email)->subject('Aviso de envio de pedido mensual');
            });
        }

        return back()->with('ok', 'Aviso enviado al email y a la app del cliente.');
    }

    private function notificar(Pedido $pedido, string $mensaje): void
    {
        if (!$pedido->cliente_email) {
            return;
        }

        Mail::raw($mensaje . "\nTracking: " . route('tracking.show', $pedido->codigo_tracking), function ($mail) use ($pedido) {
            $mail->to($pedido->cliente_email)->subject('Actualizacion de entrega');
        });
    }

    private function rubrosStock(): array
    {
        return [
            'alimentos' => [
                'titulo' => 'Alimentos',
                'descripcion' => 'Alimentos por especie, edad, receta y formato.',
                'categorias' => ['alimento_mascota'],
                'categoria_default' => 'alimento_mascota',
                'subcategorias' => ['Perros', 'Gatos', 'Conejos', 'Aves', 'Peces', 'Senior', 'Cachorros', 'Dietas especiales'],
            ],
            'farmacia' => [
                'titulo' => 'Farmacia',
                'descripcion' => 'Medicamentos y productos clinicos clasificados por familia.',
                'categorias' => ['medicamento'],
                'categoria_default' => 'medicamento',
                'subcategorias' => ['Antibioticos', 'Antiinflamatorios', 'Antiparasitarios', 'Dermatologicos', 'Oftalmicos', 'Suplementos', 'Receta retenida'],
            ],
            'juguetes' => [
                'titulo' => 'Juguetes',
                'descripcion' => 'Entretencion y enriquecimiento ambiental.',
                'categorias' => ['juguete'],
                'categoria_default' => 'juguete',
                'subcategorias' => ['Mordedores', 'Pelotas', 'Interactivos', 'Peluches', 'Rascadores', 'Premios educativos'],
            ],
            'cuidados' => [
                'titulo' => 'Cuidado y utiles',
                'descripcion' => 'Higiene, accesorios, utensilios y productos de uso diario.',
                'categorias' => ['cuidado', 'utensilio'],
                'categoria_default' => 'cuidado',
                'subcategorias' => ['Shampoo', 'Higiene dental', 'Camas', 'Platos', 'Correas', 'Transportadoras', 'Arena sanitaria'],
            ],
            'servicios' => [
                'titulo' => 'Servicios',
                'descripcion' => 'Prestaciones a domicilio o agenda comercial.',
                'categorias' => ['servicio'],
                'categoria_default' => 'servicio',
                'subcategorias' => ['Bano', 'Peluqueria', 'Veterinaria a domicilio', 'Vacunatorio', 'Consulta online'],
            ],
            'hoteles' => [
                'titulo' => 'Hoteles',
                'descripcion' => 'Estadias, guarderia y reservas para mascotas.',
                'categorias' => ['hotel'],
                'categoria_default' => 'hotel',
                'subcategorias' => ['Dia completo', 'Noche', 'Fin de semana', 'Guarderia', 'Traslado incluido'],
            ],
            'paseos' => [
                'titulo' => 'Paseos',
                'descripcion' => 'Paseos diarios y programas de actividad.',
                'categorias' => ['paseo_diario'],
                'categoria_default' => 'paseo_diario',
                'subcategorias' => ['30 minutos', '60 minutos', 'Paseo grupal', 'Paseo individual', 'Plan mensual'],
            ],
            'cementerio' => [
                'titulo' => 'Cementerio',
                'descripcion' => 'Servicios conmemorativos y de despedida.',
                'categorias' => ['cementerio'],
                'categoria_default' => 'cementerio',
                'subcategorias' => ['Retiro', 'Cremacion', 'Ceremonia', 'Urna', 'Memorial'],
            ],
        ];
    }

    private function rubroPorCategoria(string $categoria): string
    {
        foreach ($this->rubrosStock() as $slug => $config) {
            if (in_array($categoria, $config['categorias'], true)) {
                return $slug;
            }
        }

        return 'alimentos';
    }
}
