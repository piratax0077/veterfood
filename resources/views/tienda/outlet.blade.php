@extends('layouts.app')

@section('title', 'Ofertas')
@section('estilos', 'css/tienda-catalogo.css, css/tienda-outlet.css')

@section('content')
@if($productos->isNotEmpty())
    <div class="outlet-barra">
        <div>
            <h1 style="margin:0;font-size:1.5rem">¡Ofertas!</h1>
            <p class="outlet-cantidad">sobre {{ $productos->count() }} {{ $productos->count() === 1 ? 'producto en oferta' : 'productos en oferta' }}</p>
        </div>
        <form class="store-order" method="GET" action="{{ route('tienda.outlet') }}" data-orden-form>
            <label for="orden">Ordenar por:</label>
            <select id="orden" name="orden">
                <option value="descuento" @selected($orden === 'descuento')>Mayor descuento</option>
                <option value="precio_asc" @selected($orden === 'precio_asc')>Menor precio</option>
                <option value="precio_desc" @selected($orden === 'precio_desc')>Mayor precio</option>
            </select>
            <button class="store-order-enviar" type="submit" data-orden-enviar>Ordenar</button>
        </form>
    </div>

    <div class="productos-grid">
        @foreach($productos as $producto)
            @include('tienda.partials.tarjeta-producto')
        @endforeach
    </div>
@else
    <section class="panel-card outlet-vacio">
        <span class="outlet-vacio-icono" aria-hidden="true"><x-icono nombre="oferta" /></span>
        <h2>Pronto tendremos ofertas</h2>
        <p>Estamos preparando descuentos especiales para tus mascotas. Mientras tanto, revisa todo el catálogo.</p>
        <a class="btn btn-success" href="{{ route('tienda.catalogo') }}"><x-icono nombre="tienda" class="isdi-izq" />Ver catálogo</a>
    </section>
@endif
@endsection
