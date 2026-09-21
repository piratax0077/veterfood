{{-- Tarjeta de producto de la tienda (catálogo y Ofertas). Muestra el descuento cuando el producto tiene precio de oferta. --}}
@php
    $pesos = fn ($monto) => '$' . number_format($monto, 0, ',', '.');
@endphp
<div @class(['card', 'producto-card', 'is-oferta' => $producto->en_oferta])>
    <div class="product-photo">
        @if($producto->foto_url)
            <img src="{{ asset($producto->foto_url) }}" alt="{{ $producto->nombre }}" loading="lazy">
        @else
            <x-icono nombre="mascota" class="product-placeholder-icon" />
        @endif
        @if($producto->en_oferta)
            <span class="producto-descuento" aria-label="{{ $producto->descuento_porcentaje }}% de descuento">-{{ $producto->descuento_porcentaje }}%</span>
        @endif
    </div>
    @include('tienda.partials.producto-programado', ['producto' => $producto, 'modo' => 'sello'])
    {{-- El enlace del nombre cubre toda la tarjeta (menos el contador y Agregar) --}}
    <h3 class="producto-nombre" title="{{ $producto->nombre }}"><a class="producto-enlace" href="{{ route('tienda.producto', $producto) }}">{{ $producto->nombre }}</a></h3>
    <p class="muted producto-marca">{{ $producto->marca }} {{ $producto->peso ? '- ' . $producto->peso : '' }}</p>
    <p class="producto-descripcion">{{ $producto->descripcion }}</p>
    <div class="producto-precios">
        <span class="producto-precio">
            @if($producto->en_oferta)
                <s class="producto-precio-normal" aria-label="Precio normal {{ $pesos($producto->precio) }}">{{ $pesos($producto->precio) }}</s>
            @endif
            <strong>{{ $pesos($producto->precio_final) }}</strong>
        </span>
    </div>
    <form method="POST" action="{{ route('tienda.agregar', $producto) }}" class="row producto-agregar">
        @csrf
        <div class="qty" data-qty>
            <button class="qty-btn" type="button" data-qty-paso="-1" aria-label="Quitar una unidad" @disabled($producto->stock < 1)>&minus;</button>
            <input class="qty-campo" type="number" name="cantidad" value="1" min="1" max="{{ max(1, $producto->stock) }}" aria-label="Cantidad" data-qty-campo @disabled($producto->stock < 1)>
            <button class="qty-btn" type="button" data-qty-paso="1" aria-label="Agregar una unidad" @disabled($producto->stock < 1)>+</button>
        </div>
        <button class="btn-success" @disabled($producto->stock < 1)>@if($producto->stock < 1) Agotado @else <x-icono nombre="carrito" class="isdi-izq isdi-blanco" />Agregar @endif</button>
    </form>
</div>
