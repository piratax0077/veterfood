@php
    $secciones = config('tienda_categorias');
    $datos = $secciones[$seccion] ?? abort(404);
    $grupo = $grupo ?? null;
    $categoria = $categoria ?? null;

    $slug = fn ($texto) => Str::slug($texto);
    $url = fn ($uno = null, $dos = null) => route('tienda.categoria', array_filter(['seccion' => $seccion, 'grupo' => $uno, 'categoria' => $dos]));
    $imagen = fn ($ruta) => $ruta ? (Str::startsWith($ruta, 'http') ? $ruta : asset($ruta)) : null;

    $miga = [['Tienda', route('tienda.catalogo')], [$datos['titulo'], $url()]];
    $actual = ['titulo' => $datos['titulo'], 'descripcion' => $datos['descripcion'], 'foto' => $imagen($datos['foto'] ?? null), 'icono' => $datos['icono']];
    $tarjetas = [];
    $hermanas = [];
    $destacadas = [];
    $productosServicio = collect();
    $esFinal = false;
    $volver = null;

    if (isset($datos['grupos'])) {
        // Exóticos: sección > grupo > categoría
        $grupos = collect($datos['grupos'])->mapWithKeys(fn ($items, $nombre) => [$slug($nombre) => ['titulo' => $nombre, 'items' => $items]]);

        if (!$grupo) {
            foreach ($grupos as $slugGrupo => $g) {
                $tarjetas[] = ['titulo' => $g['titulo'], 'descripcion' => $datos['descripciones_grupo'][$g['titulo']] ?? '', 'url' => $url($slugGrupo), 'icono' => 'mascota', 'chips' => array_slice($g['items'], 0, 4), 'total' => count($g['items'])];
            }
        } else {
            $g = $grupos[$grupo] ?? abort(404);
            $items = collect($g['items'])->mapWithKeys(fn ($nombre) => [$slug($nombre) => $nombre]);
            $miga[] = [$g['titulo'], $url($grupo)];
            foreach ($items as $slugItem => $nombre) {
                $hermanas[] = [$nombre, $url($grupo, $slugItem), $slugItem === $categoria];
            }

            if ($categoria) {
                $nombre = $items[$categoria] ?? abort(404);
                $actual = ['antetitulo' => $g['titulo'], 'titulo' => $nombre, 'descripcion' => $nombre . ' para ' . mb_strtolower($g['titulo']) . ' seleccionados por nuestro equipo.', 'foto' => null, 'icono' => 'mascota'];
                $miga[] = [$nombre, null];
                $esFinal = true;
                $volver = [$g['titulo'], $url($grupo)];
            } else {
                $actual = ['antetitulo' => $datos['titulo'], 'titulo' => $g['titulo'], 'descripcion' => $datos['descripciones_grupo'][$g['titulo']] ?? '', 'foto' => null, 'icono' => 'mascota'];
                $miga[count($miga) - 1][1] = null;
                foreach ($items as $slugItem => $nombre) {
                    $tarjetas[] = ['titulo' => $nombre, 'descripcion' => null, 'url' => $url($grupo, $slugItem), 'icono' => 'mascota'];
                }
            }
        }
    } else {
        // Perros, Gatos y Servicios: sección > categoría
        if ($categoria) abort(404);
        $categorias = collect($datos['categorias'])->mapWithKeys(fn ($info, $nombre) => [$slug($nombre) => $info + ['titulo' => $nombre]]);

        foreach ($categorias as $slugCat => $c) {
            if (empty($c['hijas'])) $hermanas[] = [$c['titulo'], $url($slugCat), $slugCat === $grupo];
        }

        // Servicios: los circulos de categoria quedan siempre visibles (igual que Perros, Gatos y Exoticos), marcando la activa
        if ($seccion === 'servicios') {
            foreach ($categorias as $slugCat => $c) {
                if (!empty($c['hijas'])) continue;
                $tarjeta = ['titulo' => $c['titulo'], 'descripcion' => $c['descripcion'], 'url' => $url($slugCat), 'icono' => $c['icono'] ?? $datos['icono'], 'imagen' => $imagen($c['imagen'] ?? null), 'circulo' => $imagen($c['circulo'] ?? null), 'activa' => $slugCat === $grupo];
                if (isset($c['padre'])) $destacadas[] = $tarjeta; else $tarjetas[] = $tarjeta;
            }
        }

        // Servicios: los productos/servicios reales, filtrados por categoria y por palabras del nombre/descripcion
        if ($seccion === 'servicios') {
            $normalizarTxt = fn ($texto) => Str::lower(Str::ascii((string) $texto));
            $categoriasProducto = $categorias->pluck('filtro.categorias')->flatten()->filter()->unique()->values()->all();
            $productosServicio = \App\Models\Producto::where('activo', true)->whereIn('categoria', $categoriasProducto)->get();
        }

        if (!$grupo) {
            if ($seccion !== 'servicios') {
                foreach ($categorias as $slugCat => $c) {
                    if (!empty($c['hijas'])) continue;
                    $tarjeta = ['titulo' => $c['titulo'], 'descripcion' => $c['descripcion'], 'url' => $url($slugCat), 'icono' => $c['icono'] ?? $datos['icono'], 'imagen' => $imagen($c['imagen'] ?? null), 'circulo' => $imagen($c['circulo'] ?? null)];
                    if (isset($c['padre'])) $destacadas[] = $tarjeta; else $tarjetas[] = $tarjeta;
                }
            }
            $miga[count($miga) - 1][1] = null;
        } else {
            $c = $categorias[$grupo] ?? abort(404);
            if (isset($c['padre'])) $miga[] = [$c['padre'], $url($slug($c['padre']))];
            $miga[] = [$c['titulo'], null];
            $actual = ['antetitulo' => $datos['titulo'], 'titulo' => $c['titulo'], 'descripcion' => $c['descripcion'], 'foto' => $imagen($c['imagen'] ?? null), 'icono' => $c['icono'] ?? $datos['icono'], 'catalogo' => $c['catalogo'] ?? null];

            if (!empty($c['hijas'])) {
                foreach ($c['hijas'] as $hija) {
                    $h = $categorias[$slug($hija)];
                    $destacadas[] = ['titulo' => $h['titulo'], 'descripcion' => $h['descripcion'], 'url' => $url($slug($hija)), 'imagen' => $imagen($h['imagen'] ?? null)];
                }
            } elseif ($seccion === 'servicios') {
                $filtro = $c['filtro'] ?? [];
                $productosServicio = $productosServicio->filter(function ($producto) use ($filtro, $normalizarTxt) {
                    $texto = $normalizarTxt($producto->nombre . ' ' . $producto->descripcion);
                    $porCategoria = empty($filtro['categorias']) || in_array($producto->categoria, $filtro['categorias'], true);
                    $porPalabras = empty($filtro['palabras']) || Str::contains($texto, $filtro['palabras']);
                    $sinExcluidas = empty($filtro['sin']) || !Str::contains($texto, $filtro['sin']);
                    return $porCategoria && $porPalabras && $sinExcluidas;
                })->values();
                $esFinal = $productosServicio->isEmpty();
                $volver = [$datos['titulo'], $url()];
            } else {
                $esFinal = true;
                $volver = [$datos['titulo'], $url()];
            }
        }
    }

    // Servicios: precio, marca y orden (mismos filtros que en Perros y Gatos)
    $orden = null;
    $rangosPrecio = [];
    $marcasFiltro = [];
    $marcasDisponibles = [];
    $accionFiltros = url()->current();
    if ($seccion === 'servicios') {
        $orden = request('orden');
        $rangosPrecio = array_filter((array) request('precio', []));
        $marcasFiltro = array_filter((array) request('marcas', []));
        $marcasDisponibles = $productosServicio->pluck('marca')->filter()->unique()->sort()->values()->all();

        if ($rangosPrecio) {
            $productosServicio = $productosServicio->filter(function ($producto) use ($rangosPrecio) {
                foreach ($rangosPrecio as $rango) {
                    [$min, $max] = array_pad(explode('-', $rango), 2, null);
                    $min = (int) $min;
                    $max = ($max === '' || $max === null) ? null : (int) $max;
                    if ($producto->precio_final >= $min && ($max === null || $producto->precio_final <= $max)) return true;
                }
                return false;
            })->values();
        }

        if ($marcasFiltro) {
            $productosServicio = $productosServicio->whereIn('marca', $marcasFiltro)->values();
        }

        if ($orden === 'precio_asc') {
            $productosServicio = $productosServicio->sortBy('precio_final')->values();
        } elseif ($orden === 'precio_desc') {
            $productosServicio = $productosServicio->sortByDesc('precio_final')->values();
        }

        if ($grupo) {
            $esFinal = $productosServicio->isEmpty();
        }
    }
    $busqueda = null;

    $miga[count($miga) - 1][1] = null;
@endphp

@extends('layouts.app')

@section('title', $actual['titulo'] . ' | Tienda')
@section('estilos', 'css/tienda-catalogo.css, css/tienda-categoria.css')

@section('content')
<nav class="categoria-miga" aria-label="Estás en">
    <ol>
        @foreach($miga as [$texto, $enlace])
            <li>
                @if($enlace)
                    <a href="{{ $enlace }}">{{ $texto }}</a>
                @else
                    <span aria-current="page">{{ $texto }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

@if($seccion !== 'servicios' && $grupo)
    <div class="categoria-titulo-simple">
        @isset($actual['antetitulo'])<p class="categoria-antetitulo">{{ $actual['antetitulo'] }}</p>@endisset
        <h1>{{ $actual['titulo'] }}</h1>
        <p>{{ $actual['descripcion'] }}</p>
        @if(isset($datos['especie']))
            <div class="categoria-cabecera-acciones">
                <a class="btn btn-success" href="{{ route('tienda.catalogo', ['especie' => $datos['especie']]) }}">Ver productos para {{ mb_strtolower($datos['titulo']) }}</a>
            </div>
        @endif
    </div>
@endif

@if($hermanas && $grupo && $seccion !== 'servicios')
    <nav class="categoria-chips" aria-label="Categorías de {{ $miga[1][0] }}">
        @foreach($hermanas as [$texto, $enlace, $activa])
            <a @class(['categoria-chip', 'is-activa' => $activa]) href="{{ $enlace }}" @if($activa) aria-current="page" @endif>{{ $texto }}</a>
        @endforeach
    </nav>
@endif

@if($destacadas)
    <section class="categoria-seccion">
        <h2>Alimento</h2>
        <div class="categoria-destacadas">
            @foreach($destacadas as $tarjeta)
                <a class="panel-card categoria-destacada" href="{{ $tarjeta['url'] }}">
                    <img src="{{ $tarjeta['imagen'] }}" alt="" loading="lazy">
                    <span class="categoria-destacada-texto">
                        <strong>{{ $tarjeta['titulo'] }}</strong>
                        <span>{{ $tarjeta['descripcion'] }}</span>
                    </span>
                    <span class="categoria-flecha" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
@endif

@if($tarjetas)
    @if(empty($tarjetas[0]['chips']))
        {{-- Perros, Gatos y Servicios: mismos círculos de categoría que en las demás secciones --}}
        <section class="categoria-seccion">
            <nav class="especie-picker especie-picker--categorias" aria-label="Categorías de {{ $datos['titulo'] }}">
                <a class="especie-item {{ !$grupo ? 'active' : '' }}" href="{{ $url() }}" @if(!$grupo) aria-current="page" @endif>
                    <span class="especie-photo"><x-icono :nombre="$datos['icono']" class="product-placeholder-icon" /></span>
                    <span class="especie-nombre">Ver todo</span>
                </a>
                @foreach($tarjetas as $tarjeta)
                    <a class="especie-item {{ !empty($tarjeta['activa']) ? 'active' : '' }}" href="{{ $tarjeta['url'] }}" @if(!empty($tarjeta['activa'])) aria-current="page" @endif>
                        <span class="especie-photo">
                            @if($tarjeta['circulo'])
                                <img src="{{ $tarjeta['circulo'] }}" alt="" loading="lazy">
                            @else
                                <x-icono :nombre="$tarjeta['icono']" class="product-placeholder-icon" />
                            @endif
                        </span>
                        <span class="especie-nombre">{{ $tarjeta['titulo'] }}</span>
                    </a>
                @endforeach
            </nav>
        </section>
    @else
        <section class="categoria-seccion">
            @if($destacadas)<h2>Más categorías</h2>@endif
            <div class="categoria-grilla categoria-grilla--grupos">
                @foreach($tarjetas as $tarjeta)
                    <a class="panel-card categoria-tarjeta" href="{{ $tarjeta['url'] }}">
                        <span class="categoria-tarjeta-icono" aria-hidden="true"><x-icono :nombre="$tarjeta['icono']" /></span>
                        <span class="categoria-tarjeta-texto">
                            <strong>{{ $tarjeta['titulo'] }}</strong>
                            @if($tarjeta['descripcion'])<span>{{ $tarjeta['descripcion'] }}</span>@endif
                            @if(!empty($tarjeta['chips']))
                                <span class="categoria-tarjeta-chips">
                                    @foreach($tarjeta['chips'] as $chip)<em>{{ $chip }}</em>@endforeach
                                    @if($tarjeta['total'] > count($tarjeta['chips']))<em>+{{ $tarjeta['total'] - count($tarjeta['chips']) }}</em>@endif
                                </span>
                            @endif
                        </span>
                        <span class="categoria-flecha" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endif

@if($seccion === 'servicios')
    <div class="store-header">
        <div>
            <h1>{{ $actual['titulo'] }}</h1>
            <p class="muted">{{ $actual['descripcion'] }}</p>
        </div>
        <div class="store-tools">
            <button type="button" class="filtros-abrir" data-filtros-abrir aria-controls="filtros-panel" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h10M18 7h2M4 12h3M11 12h9M4 17h8M16 17h4"/><circle cx="16" cy="7" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="14" cy="17" r="2"/></svg>
                Filtros (<span data-filtros-total>{{ count($rangosPrecio) + count($marcasFiltro) }}</span>)
            </button>
            <form class="store-order" method="GET" action="{{ $accionFiltros }}" data-orden-form>
                <label for="orden">Ordenar por:</label>
                <select id="orden" name="orden" data-sin-buscador>
                    <option value="">Normal</option>
                    <option value="precio_asc" @selected($orden === 'precio_asc')>Menor a mayor</option>
                    <option value="precio_desc" @selected($orden === 'precio_desc')>Mayor a menor</option>
                </select>
                <button class="store-order-enviar" type="submit" data-orden-enviar>Ordenar</button>
            </form>
        </div>

        @include('partials.tienda-filtros', ['categoria' => null, 'busqueda' => null, 'orden' => $orden, 'rangosPrecio' => $rangosPrecio, 'marcasFiltro' => $marcasFiltro, 'marcasDisponibles' => $marcasDisponibles, 'tiposDisponibles' => [], 'tiposFiltro' => [], 'categoriasTienda' => [], 'accionFiltros' => $accionFiltros])
    </div>
@endif

@if($productosServicio->isNotEmpty())
    <section class="categoria-seccion">
        <div class="productos-grid">
            @foreach($productosServicio as $producto)
                @include('tienda.partials.tarjeta-producto')
            @endforeach
        </div>
    </section>
@endif

@if($esFinal)
    <section class="panel-card categoria-vacia">
        <span class="categoria-vacia-icono" aria-hidden="true"><x-icono nombre="mascota" /></span>
        <h2>Pronto tendremos productos aquí</h2>
        <p>Estamos preparando la categoría <strong>{{ $actual['titulo'] }}</strong>. Mientras tanto, revisa todo lo que tenemos en la tienda.</p>
        <div class="categoria-vacia-acciones">
            <a class="btn btn-success" href="{{ route('tienda.catalogo') }}">Ver toda la tienda</a>
            <a class="btn btn-secondary" href="{{ $volver[1] }}">Volver a {{ $volver[0] }}</a>
        </div>
    </section>
@endif
@endsection
