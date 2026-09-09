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
    $totalFiltros = count($rangosPrecio ?? []) + count($marcasFiltro ?? []) + count($tiposFiltro ?? []);
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

        <form class="filtros-cuerpo" method="GET" action="{{ route('tienda.catalogo') }}" id="filtros-form" data-filtros-form>
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

            <fieldset class="filtros-grupo filtros-grupo--pendiente">
                <legend>Mascota</legend>
                <label class="filtros-opcion"><input type="checkbox" disabled><span>Perro</span></label>
                <label class="filtros-opcion"><input type="checkbox" disabled><span>Gato</span></label>
                <label class="filtros-opcion"><input type="checkbox" disabled><span>Ex&oacute;ticos</span></label>
                <p class="filtros-nota">Falta el dato de especie en el cat&aacute;logo.</p>
            </fieldset>

            <fieldset class="filtros-grupo filtros-grupo--pendiente">
                <legend>Vendido por</legend>
                <label class="filtros-opcion"><input type="checkbox" disabled><span>Vetersdi</span></label>
                <label class="filtros-opcion"><input type="checkbox" disabled><span>Marketplace</span></label>
                <p class="filtros-nota">Falta el dato de vendedor en el cat&aacute;logo.</p>
            </fieldset>
        </form>

        <footer class="filtros-pie">
            <button type="submit" form="filtros-form" class="filtros-aplicar">Filtrar</button>
            <a class="filtros-limpiar" href="{{ route('tienda.catalogo', array_filter(['categoria' => $categoria, 'buscar' => $busqueda, 'orden' => $orden])) }}">Limpiar filtro</a>
        </footer>
    </aside>
</div>
