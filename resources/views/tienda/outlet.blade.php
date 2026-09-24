@extends('layouts.app')

@section('title', 'Ofertas')
@section('estilos', 'css/tienda-catalogo.css, css/tienda-outlet.css')

@section('content')
@if(count($tiposDisponibles))
    <div class="outlet-barra">
        <div>
            <h1 class="outlet-titulo">¡Ofertas!</h1>
        </div>
        <div class="store-tools">
            <button type="button" class="filtros-abrir" data-filtros-abrir aria-controls="filtros-panel" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h10M18 7h2M4 12h3M11 12h9M4 17h8M16 17h4"/><circle cx="16" cy="7" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="14" cy="17" r="2"/></svg>
                Filtros (<span data-filtros-total>{{ count($rangosPrecio) + count($marcasFiltro) + count($tiposFiltro) }}</span>)
            </button>
            <form class="store-order" method="GET" action="{{ route('tienda.outlet') }}" data-orden-form>
                <label for="orden">Ordenar por:</label>
                <select id="orden" name="orden" data-sin-buscador>
                    <option value="descuento" @selected($orden === 'descuento')>Mayor descuento</option>
                    <option value="precio_asc" @selected($orden === 'precio_asc')>Menor precio</option>
                    <option value="precio_desc" @selected($orden === 'precio_desc')>Mayor precio</option>
                </select>
                <button class="store-order-enviar" type="submit" data-orden-enviar>Ordenar</button>
            </form>
        </div>

        @include('partials.tienda-filtros', ['categoria' => null, 'busqueda' => null, 'orden' => $orden, 'rangosPrecio' => $rangosPrecio, 'marcasFiltro' => $marcasFiltro, 'marcasDisponibles' => $marcasDisponibles, 'tiposDisponibles' => $tiposDisponibles, 'tiposFiltro' => $tiposFiltro, 'categoriasTienda' => $categoriasTienda, 'accionFiltros' => route('tienda.outlet')])
    </div>

    @if($productos->isNotEmpty())
        <div class="productos-grid">
            @foreach($productos as $producto)
                @include('tienda.partials.tarjeta-producto')
            @endforeach
        </div>
    @else
        <section class="panel-card outlet-vacio">
            <span class="outlet-vacio-icono" aria-hidden="true"><x-icono nombre="oferta" /></span>
            <h2>Sin resultados para este filtro</h2>
            <p>Prueba con otra categoría, marca o rango de precio.</p>
            <a class="btn btn-success" href="{{ route('tienda.outlet') }}"><x-icono nombre="cerrar" class="isdi-izq" />Quitar filtros</a>
        </section>
    @endif
@else
    <section class="panel-card outlet-vacio">
        <span class="outlet-vacio-icono" aria-hidden="true"><x-icono nombre="oferta" /></span>
        <h2>Pronto tendremos ofertas</h2>
        <p>Estamos preparando descuentos especiales para tus mascotas. Mientras tanto, revisa todo el catálogo.</p>
        <a class="btn btn-success" href="{{ route('tienda.catalogo') }}"><x-icono nombre="tienda" class="isdi-izq" />Ver catálogo</a>
    </section>
@endif
@endsection
