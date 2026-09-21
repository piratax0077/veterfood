@extends('layouts.app')

@section('title', request()->routeIs('tienda.seccion') ? config('tienda_categorias.' . request()->route('seccion') . '.titulo') . ' | Tienda' : 'Tienda')
@section('estilos', 'css/tienda-catalogo.css')

@section('content')
@php
    $titulosCategoria = [
        'alimento_mascota' => ['titulo' => 'Alimentos', 'descripcion' => 'Alimentos, snacks, productos de rutina y compras rápidas para el hogar.'],
        'juguete' => ['titulo' => 'Accesorios y Juguetes', 'descripcion' => 'Juguetes y accesorios para entretener, cuidar y consentir a tu mascota.'],
        'hotel' => ['titulo' => 'Hoteles', 'descripcion' => 'Reservas, estadías diarias y convenios de hotelería para mascotas.'],
        'paseo_diario' => ['titulo' => 'Paseos diarios', 'descripcion' => 'Paseos programados, visitas y acompañamiento diario para mascotas.'],
        'cementerio' => ['titulo' => 'Cementerio', 'descripcion' => 'Servicios de despedida, retiro y apoyo respetuoso para mascotas.'],
        'cuidado' => ['titulo' => 'Cuidados y útiles', 'descripcion' => 'Higiene, limpieza, paseo, transporte y artículos útiles para mascotas.'],
        'servicio' => ['titulo' => 'Servicios a domicilio', 'descripcion' => 'Baño, peluquería, veterinaria a domicilio y apoyos programables.'],
        'utensilio' => ['titulo' => 'Accesorios', 'descripcion' => 'Camas, platos, correas, transporte y más accesorios para tu mascota.'],
    ];
    $cabecera = $titulosCategoria[$categoria] ?? ($secciones[$categoria] ?? null);
    if (!$cabecera) {
        // Sin categoría: el título cuenta lo que se está viendo
        $cantidadProductos = $productos->count();
        $textoCantidad = $cantidadProductos === 1 ? '1 producto' : $cantidadProductos . ' productos';
        $nombresEspecie = ['perro' => 'perros', 'gato' => 'gatos', 'exotico' => 'mascotas exóticas'];
        $cabecera = match (true) {
            filled($busqueda) => ['titulo' => 'Resultados para «' . $busqueda . '»', 'descripcion' => $cantidadProductos ? "Encontramos {$textoCantidad} que " . ($cantidadProductos === 1 ? 'coincide' : 'coinciden') . ' con tu búsqueda.' : 'No encontramos productos con ese nombre. Prueba con otra palabra.'],
            isset($nombresEspecie[$especie]) => ['titulo' => 'Todo para ' . $nombresEspecie[$especie], 'descripcion' => "Viendo {$textoCantidad}: alimento, accesorios y cuidado para tus " . $nombresEspecie[$especie] . '.'],
            default => ['titulo' => 'Todos los productos', 'descripcion' => "Viendo {$textoCantidad} para perros, gatos y exóticos, con despacho a tu casa."],
        };
    }

    // Página de mascota (por ahora Gatos): sus categorías en círculos y los productos filtrados por la elegida
    $seccionTienda = request()->routeIs('tienda.seccion') ? request()->route('seccion') : null;
    $categoriaSeccion = null;
    $circulos = [];
    if ($seccionTienda) {
        $datosSeccion = config("tienda_categorias.{$seccionTienda}");
        $categoriasSeccion = collect($datosSeccion['categorias'])->mapWithKeys(fn ($info, $nombre) => [Str::slug($nombre) => $info + ['titulo' => $nombre]]);
        $slugActiva = request()->route('grupo');
        if ($slugActiva) {
            $categoriaSeccion = $categoriasSeccion[$slugActiva] ?? abort(404);
        }

        $slugSub = request()->route('sub');
        $subSeccion = null;
        if ($slugSub) {
            $subSeccion = collect($categoriaSeccion['items'] ?? [])->first(fn ($item) => Str::slug($item) === $slugSub) ?? abort(404);
        }

        foreach ($categoriasSeccion as $slugCirculo => $info) {
            if (!empty($info['hijas'])) continue;
            $circulos[] = ['titulo' => $info['titulo'], 'url' => route('tienda.seccion', [$seccionTienda, $slugCirculo]), 'foto' => asset($info['circulo']), 'activa' => $slugCirculo === $slugActiva];
        }

        $cabecera = match (true) {
            (bool) $subSeccion => ['titulo' => $subSeccion, 'descripcion' => $subSeccion . ' para ' . mb_strtolower($categoriaSeccion['titulo']) . '.'],
            (bool) $categoriaSeccion => ['titulo' => $categoriaSeccion['titulo'], 'descripcion' => $categoriaSeccion['descripcion']],
            default => ['titulo' => $datosSeccion['titulo'], 'descripcion' => $datosSeccion['descripcion']],
        };

        // "Alimento" junta seco y húmedo; la subcategoría cargada en el producto manda sobre las reglas
        $reglas = null;
        if ($categoriaSeccion) {
            $reglas = collect($categoriaSeccion['hijas'] ?? [$categoriaSeccion['titulo']])
                ->map(fn ($nombre) => [$nombre, $datosSeccion['categorias'][$nombre]['filtro'] ?? []]);
        } elseif (!empty($datosSeccion['filtrar_todo'])) {
            // Exóticos: sin categoría elegida se muestran los productos de todas sus mascotas
            $reglas = collect($datosSeccion['categorias'])->map(fn ($info, $nombre) => [$nombre, $info['filtro'] ?? []])->values();
        }

        if ($reglas) {
            $normalizar = fn ($texto) => Str::lower(Str::ascii((string) $texto));

            $productos = $productos->filter(function ($producto) use ($reglas, $normalizar) {
                $texto = $normalizar($producto->nombre . ' ' . $producto->descripcion);
                foreach ($reglas as [$nombre, $filtro]) {
                    if ($producto->subcategoria && $normalizar($producto->subcategoria) === $normalizar($nombre)) return true;
                    if (empty($filtro['categorias']) && empty($filtro['palabras'])) continue;
                    $porCategoria = empty($filtro['categorias']) || in_array($producto->categoria, $filtro['categorias'], true);
                    $porPalabras = empty($filtro['palabras']) || Str::contains($texto, $filtro['palabras']);
                    $sinExcluidas = empty($filtro['sin']) || !Str::contains($texto, $filtro['sin']);
                    if ($porCategoria && $porPalabras && $sinExcluidas) return true;
                }
                return false;
            })->values();

            if ($subSeccion) {
                $raiz = rtrim($normalizar(Str::before($subSeccion, ' ')), 's');
                $productos = $productos->filter(fn ($producto) => $normalizar($producto->subcategoria) === $normalizar($subSeccion)
                    || Str::contains($normalizar($producto->nombre . ' ' . $producto->descripcion), $raiz))->values();
            }
        }
    }
    $accionTienda = $seccionTienda ? url()->current() : route('tienda.catalogo');
@endphp

@php
    $especies = [
        'perro' => ['label' => 'Perro', 'foto' => asset('images/tienda/perro/perro.jpg'), 'posicion' => '50% 32%'],
        'gato' => ['label' => 'Gato', 'foto' => asset('images/tienda/gato/gato.jpg'), 'posicion' => '55% 40%'],
        'exotico' => ['label' => 'Exóticos', 'foto' => asset('images/tienda/exoticos/exoticos-categoria.jpg')],
    ];
@endphp
@if($seccionTienda)
<nav class="especie-picker especie-picker--categorias" aria-label="Categorías de {{ $datosSeccion['titulo'] }}">
    <a class="especie-item {{ !$categoriaSeccion ? 'active' : '' }}" href="{{ route('tienda.seccion', $seccionTienda) }}" @if(!$categoriaSeccion) aria-current="page" @endif>
        <span class="especie-photo"><img src="{{ Str::startsWith($datosSeccion['foto'], 'http') ? $datosSeccion['foto'] : asset($datosSeccion['foto']) }}" alt="" loading="lazy" @isset($datosSeccion['foto_posicion']) style="object-position: {{ $datosSeccion['foto_posicion'] }}" @endisset></span>
        <span class="especie-nombre">Ver todo</span>
    </a>
    @foreach($circulos as $circulo)
        <a class="especie-item {{ $circulo['activa'] ? 'active' : '' }}" href="{{ $circulo['url'] }}" @if($circulo['activa']) aria-current="page" @endif>
            <span class="especie-photo"><img src="{{ $circulo['foto'] }}" alt="" loading="lazy"></span>
            <span class="especie-nombre">{{ $circulo['titulo'] }}</span>
        </a>
    @endforeach
</nav>
@if($categoriaSeccion && !empty($categoriaSeccion['items']))
    <nav class="categoria-chips" aria-label="Categorías de {{ $categoriaSeccion['titulo'] }}">
        <a @class(['categoria-chip', 'is-activa' => !$subSeccion]) href="{{ route('tienda.seccion', [$seccionTienda, $slugActiva]) }}" @if(!$subSeccion) aria-current="page" @endif>Todo</a>
        @foreach($categoriaSeccion['items'] as $itemSeccion)
            @php $slugItem = Str::slug($itemSeccion); @endphp
            <a @class(['categoria-chip', 'is-activa' => $slugItem === $slugSub]) href="{{ route('tienda.seccion', [$seccionTienda, $slugActiva, $slugItem]) }}" @if($slugItem === $slugSub) aria-current="page" @endif>{{ $itemSeccion }}</a>
        @endforeach
    </nav>
@endif
@else
<div class="especie-picker">
    <a class="especie-item {{ !$especie ? 'active' : '' }}" href="{{ route('tienda.catalogo', $categoria ? ['categoria' => $categoria] : []) }}">
        <span class="especie-photo"><x-icono nombre="mascota" class="product-placeholder-icon" /></span>
        <span class="especie-nombre">Todos</span>
    </a>
    @foreach($especies as $slug => $info)
        <a class="especie-item {{ $especie === $slug ? 'active' : '' }}" href="{{ route('tienda.catalogo', array_filter(['categoria' => $categoria, 'especie' => $slug])) }}">
            <span class="especie-photo"><img src="{{ $info['foto'] }}" alt="{{ $info['label'] }}" loading="lazy" @isset($info['posicion']) style="object-position: {{ $info['posicion'] }}" @endisset></span>
            <span class="especie-nombre">{{ $info['label'] }}</span>
        </a>
    @endforeach
</div>
@endif

<div class="store-header">
    <div>
        <h1>{{ $cabecera['titulo'] }}</h1>
        <p class="muted">
            {{ $cabecera['descripcion'] }}
        </p>
    </div>
    <div class="store-tools">
        <button type="button" class="filtros-abrir" data-filtros-abrir aria-controls="filtros-panel" aria-expanded="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h10M18 7h2M4 12h3M11 12h9M4 17h8M16 17h4"/><circle cx="16" cy="7" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="14" cy="17" r="2"/></svg>
            Filtros (<span data-filtros-total>{{ count($rangosPrecio ?? []) + count($marcasFiltro ?? []) + count($tiposFiltro ?? []) + ($seccionTienda ? 0 : count($especiesFiltro ?? [])) + count($vendedoresFiltro ?? []) }}</span>)
        </button>
        <form class="store-order" method="GET" action="{{ $accionTienda }}" data-orden-form>
        @if($categoria)
            <input type="hidden" name="categoria" value="{{ $categoria }}">
        @endif
        @if($busqueda)
            <input type="hidden" name="buscar" value="{{ $busqueda }}">
        @endif
        @if($filtroCategoria)
            <input type="hidden" name="tipo" value="{{ $filtroCategoria }}">
        @endif
        @if($especie)
            <input type="hidden" name="especie" value="{{ $especie }}">
        @endif
        <label for="orden">Ordenar por:</label>
        <select id="orden" name="orden">
            <option value="">Normal</option>
            <option value="precio_asc" @selected($orden === 'precio_asc')>Menor a mayor</option>
            <option value="precio_desc" @selected($orden === 'precio_desc')>Mayor a menor</option>
        </select>
        <button class="store-order-enviar" type="submit" data-orden-enviar>Ordenar</button>
        </form>
    </div>

    @include('partials.tienda-filtros', ['accionFiltros' => $accionTienda])
</div>

@if($planExtra)
    <div class="alert">
        Estás agregando productos para complementar tu pedido mensual de {{ $planExtra->producto?->nombre }}.
        En el pago podrás indicar si quieres incluirlos también en tu pedido mensual.
    </div>
@endif

<div class="category-tabs">
    <a class="btn {{ !$categoria || $categoria === 'general' ? 'active' : '' }}" href="{{ route('tienda.catalogo') }}">Todo</a>
    <a class="btn {{ $categoria === 'alimento_mascota' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'alimento_mascota']) }}">Alimentos</a>
    <a class="btn {{ $categoria === 'juguete' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'juguete']) }}">Accesorios y Juguetes</a>
    <a class="btn {{ $categoria === 'hotel' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'hotel']) }}">Hoteles</a>
    <a class="btn {{ $categoria === 'paseo_diario' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'paseo_diario']) }}">Paseos diarios</a>
    <a class="btn {{ $categoria === 'cementerio' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'cementerio']) }}">Cementerio</a>
    <a class="btn {{ $categoria === 'cuidado' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'cuidado']) }}">Cuidados</a>
    <a class="btn {{ $categoria === 'servicio' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'servicio']) }}">Servicios</a>
    <a class="btn {{ $categoria === 'utensilio' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'utensilio']) }}">Accesorios</a>
</div>

<form method="GET" action="{{ route('tienda.catalogo') }}" class="store-filters">
    @if($categoria)
        <input type="hidden" name="categoria" value="{{ $categoria }}">
    @endif
    @if($especie)
        <input type="hidden" name="especie" value="{{ $especie }}">
    @endif
    <input type="hidden" name="orden" value="{{ $orden }}">
    <div class="filter-field">
        <label for="buscar">Buscar por nombre</label>
        <div class="filter-control-wrap search-control">
            <input id="buscar" type="search" name="buscar" value="{{ $busqueda }}" placeholder="Nombre del producto o servicio" aria-label="Buscar por nombre">
        </div>
    </div>
    <div class="filter-field">
        <label for="tipo">Categoría</label>
        <div class="filter-control-wrap">
        <select id="tipo" name="tipo" aria-label="Buscar por categoría">
            <option value="">Todas</option>
            @foreach($categoriasTienda as $slug => $titulo)
                <option value="{{ $slug }}" @selected($filtroCategoria === $slug)>{{ $titulo }}</option>
            @endforeach
        </select>
        </div>
    </div>
    <div class="filter-actions">
        <button class="btn-success">Aplicar</button>
        @if($busqueda || $filtroCategoria || $orden)
            <a class="btn btn-secondary" href="{{ route('tienda.catalogo', $categoria ? ['categoria' => $categoria] : []) }}">Limpiar</a>
        @endif
    </div>
</form>

<div class="productos-grid">
@forelse($productos as $producto)
    @include('tienda.partials.tarjeta-producto')
@empty
    @if($seccionTienda)
        <div class="col-12">
            <div class="card productos-vacio">
                <span class="productos-vacio-icono" aria-hidden="true"><x-icono nombre="mascota" /></span>
                <strong>Pronto tendremos productos en {{ $cabecera['titulo'] }}</strong>
                <span>Mientras tanto, revisa todo lo que tenemos para {{ mb_strtolower($datosSeccion['titulo']) }}.</span>
                <a class="btn btn-success" href="{{ route('tienda.seccion', $seccionTienda) }}">Ver todo</a>
            </div>
        </div>
    @else
        <div class="col-12"><div class="card">No hay productos activos.</div></div>
    @endif
@endforelse
</div>
@endsection
