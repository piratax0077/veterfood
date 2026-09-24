{{-- Panel lateral de filtros --}}
@php
    $tramosPrecio = [
        '0-10000' => '$0 - $10.000',
        '10000-30000' => '$10.000 - $30.000',
        '30000-50000' => '$30.000 - $50.000',
        '50000-' => '$50.000 +',
    ];
    $etiquetasTipo = $categoriasTienda ?? [];
    $mostrarTipos = count($tiposDisponibles ?? []) > 1;
    // En Perros, Gatos y Exóticos la especie ya viene dada por la página
    $mostrarEspecies = isset($especiesFiltro) && !request()->routeIs('tienda.seccion');
    $mostrarVendedores = count($vendedoresDisponibles ?? []) > 0;
    $especiesTienda = ['perro' => 'Perro', 'gato' => 'Gato', 'exotico' => 'Exóticos'];
    $totalFiltros = count($rangosPrecio ?? []) + count($marcasFiltro ?? []) + count($tiposFiltro ?? [])
        + ($mostrarEspecies ? count($especiesFiltro) : 0) + count($vendedoresFiltro ?? []);
@endphp

<div class="filtros-panel" id="filtros-panel" role="dialog" aria-modal="true" aria-labelledby="filtros-titulo" aria-hidden="true" data-filtros-panel data-categoria="{{ $categoria ?? '' }}" data-filtros-activos="{{ $totalFiltros }}">
    <div class="filtros-fondo" data-filtros-cerrar></div>
    <aside class="filtros-caja">
        <header class="filtros-head">
            <h2 id="filtros-titulo">Filtrar</h2>
            <button type="button" class="filtros-cerrar" data-filtros-cerrar aria-label="Cerrar los filtros">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </header>

        <form class="filtros-cuerpo" method="GET" action="{{ $accionFiltros ?? route('tienda.catalogo') }}" id="filtros-form" data-filtros-form>
            @if($categoria)
                <input type="hidden" name="categoria" value="{{ $categoria }}">
            @endif
            @if($busqueda)
                <input type="hidden" name="buscar" value="{{ $busqueda }}">
            @endif
            @if($orden)
                <input type="hidden" name="orden" value="{{ $orden }}">
            @endif

            <fieldset class="filtros-grupo">
                <legend>Precio</legend>
                @foreach($tramosPrecio as $valor => $etiqueta)
                    <label class="filtros-opcion">
                        <input type="checkbox" name="precio[]" value="{{ $valor }}" @checked(in_array($valor, $rangosPrecio ?? [], true))>
                        <span>{{ $etiqueta }}</span>
                    </label>
                @endforeach
            </fieldset>

            @if(count($marcasDisponibles ?? []))
                <fieldset class="filtros-grupo">
                    <legend>Marca</legend>
                    @foreach($marcasDisponibles as $marca)
                        <label class="filtros-opcion">
                            <input type="checkbox" name="marcas[]" value="{{ $marca }}" @checked(in_array($marca, $marcasFiltro ?? [], true))>
                            <span>{{ $marca }}</span>
                        </label>
                    @endforeach
                </fieldset>
            @endif

            @if(($seccionTienda ?? null) === 'exoticos' && !empty($categoriaSeccion['items'] ?? []))
                <fieldset class="filtros-grupo">
                    <legend>Categoría</legend>
                    <a class="filtros-opcion filtros-opcion-link {{ !$subSeccion ? 'is-activa' : '' }}" href="{{ route('tienda.seccion', [$seccionTienda, $slugActiva]) }}">
                        <span>Todo</span>
                    </a>
                    @foreach($categoriaSeccion['items'] as $itemSeccion)
                        @php $slugItem = \Illuminate\Support\Str::slug($itemSeccion); @endphp
                        <a class="filtros-opcion filtros-opcion-link {{ $slugItem === $slugSub ? 'is-activa' : '' }}" href="{{ route('tienda.seccion', [$seccionTienda, $slugActiva, $slugItem]) }}">
                            <span>{{ $itemSeccion }}</span>
                        </a>
                    @endforeach
                </fieldset>
            @endif

            @if($mostrarTipos)
                <fieldset class="filtros-grupo">
                    <legend>Producto</legend>
                    @foreach($tiposDisponibles as $tipo)
                        <label class="filtros-opcion">
                            <input type="checkbox" name="tipos[]" value="{{ $tipo }}" @checked(in_array($tipo, $tiposFiltro ?? [], true))>
                            <span>{{ $etiquetasTipo[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo)) }}</span>
                        </label>
                    @endforeach
                </fieldset>
            @endif

            @if($mostrarEspecies)
                <fieldset class="filtros-grupo">
                    <legend>Mascota</legend>
                    @foreach($especiesTienda as $valor => $etiqueta)
                        <label class="filtros-opcion">
                            <input type="checkbox" name="especies[]" value="{{ $valor }}" @checked(in_array($valor, $especiesFiltro, true))>
                            <span>{{ $etiqueta }}</span>
                        </label>
                    @endforeach
                </fieldset>
            @endif

            @if($mostrarVendedores)
                <fieldset class="filtros-grupo">
                    <legend>Vendido por</legend>
                    @foreach($vendedoresDisponibles as $vendedor)
                        <label class="filtros-opcion">
                            <input type="checkbox" name="vendidos[]" value="{{ $vendedor }}" @checked(in_array($vendedor, $vendedoresFiltro ?? [], true))>
                            <span>{{ $vendedor }}</span>
                        </label>
                    @endforeach
                </fieldset>
            @endif
        </form>

        <footer class="filtros-pie">
            <button type="submit" form="filtros-form" class="filtros-aplicar">Filtrar</button>
            <a class="filtros-limpiar" href="{{ isset($accionFiltros) && $accionFiltros !== route('tienda.catalogo') ? $accionFiltros : route('tienda.catalogo', array_filter(['categoria' => $categoria, 'buscar' => $busqueda, 'orden' => $orden])) }}">Limpiar filtro</a>
        </footer>
    </aside>
</div>
