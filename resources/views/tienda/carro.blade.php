@extends('layouts.app')

@section('title', 'Carro de compras')
@section('estilos', 'css/tienda-carro.css')

@section('content')
@php
    $pesos = fn ($monto) => '$' . number_format($monto, 0, ',', '.');
    $avanceEnvio = $subtotal > 0 ? min(100, round($subtotal / $envioGratisDesde * 100)) : 0;
@endphp

<div class="carro-pagina" data-carro-pagina>
    @if($items->isNotEmpty())
        @include('tienda.partials.pasos-compra', ['paso' => 1])
    @endif

    <header class="carro-encabezado">
        <h1>Carro de compras</h1>
        <p class="muted" data-carro-texto-unidades>
            @if($items->isEmpty())
                Aún no agregas productos.
            @else
                Tienes {{ $unidades }} {{ $unidades === 1 ? 'producto listo' : 'productos listos' }} para pagar.
            @endif
        </p>
    </header>

    @if($planExtra)
        <p class="carro-nota"><x-icono nombre="suscripcion" /><span>Estos extras quedarán asociados a tu pedido mensual de <strong>{{ $planExtra->producto?->nombre }}</strong>. Lo confirmas al pagar.</span></p>
    @endif

    <section class="panel-card carro-vacio-pagina" data-carro-vacio @if($items->isNotEmpty()) hidden @endif>
        <span class="carro-vacio-icono" aria-hidden="true"><x-icono nombre="carrito" /></span>
        <h2>Tu carro está vacío</h2>
        <p>Explora la tienda y agrega los productos que tu mascota necesita.</p>
        <a class="btn btn-success" href="{{ route('tienda.inicio') }}"><x-icono nombre="tienda" class="isdi-izq" />Ir a la tienda</a>
    </section>

    @if($items->isNotEmpty())
        <div class="carro-layout" data-carro-contenido>
            <section class="panel-card carro-productos" aria-label="Productos en tu carro">
                <div class="carro-cabecera-lista" aria-hidden="true">
                    <span>Producto</span><span>Cantidad</span><span>Subtotal</span>
                </div>

                <form method="POST" action="{{ route('tienda.carro.actualizar') }}" data-keep-open="1">
                    @csrf
                    <ul class="carro-filas">
                        @foreach($items as $item)
                            @php $producto = $item['producto']; @endphp
                            <li class="carro-fila" data-carro-linea data-url="{{ route('tienda.carro.item', $producto) }}" data-id="{{ $producto->id }}" data-precio="{{ $item['precio'] }}">
                                <span class="carro-foto">
                                    @if($item['foto'])
                                        <img src="{{ $item['foto'] }}" alt="{{ $producto->nombre }}" loading="lazy">
                                    @else
                                        <x-icono nombre="mascota" />
                                    @endif
                                </span>
                                <div class="carro-info">
                                    <strong class="carro-nombre">{{ $producto->nombre }}</strong>
                                    @if($producto->marca || $producto->peso)
                                        <span class="carro-detalle">{{ collect([$producto->marca, $producto->peso])->filter()->implode(' · ') }}</span>
                                    @endif
                                    <span class="carro-unitario">{{ $pesos($item['precio']) }} c/u @if($producto->en_oferta)<s class="carro-precio-normal">{{ $pesos($producto->precio) }}</s> <span class="carro-descuento">-{{ $producto->descuento_porcentaje }}%</span>@endif</span>
                                    <button type="button" class="carro-quitar" data-carro-quitar aria-label="Eliminar {{ $producto->nombre }} del carro"><x-icono nombre="eliminar" /><span class="carro-quitar-texto">Eliminar</span></button>
                                </div>
                                <div class="qty carro-cantidad" data-carro-qty>
                                    <button class="qty-btn" type="button" data-qty-paso="-1" aria-label="Quitar una unidad de {{ $producto->nombre }}">&minus;</button>
                                    <input class="qty-campo" type="number" name="cantidades[{{ $producto->id }}]" value="{{ $item['cantidad'] }}" min="1" max="{{ $item['maximo'] }}" aria-label="Cantidad de {{ $producto->nombre }}" data-qty-campo>
                                    <button class="qty-btn" type="button" data-qty-paso="1" aria-label="Agregar una unidad de {{ $producto->nombre }}">+</button>
                                </div>
                                <strong class="carro-subtotal" data-carro-linea-total>{{ $pesos($item['total']) }}</strong>
                            </li>
                        @endforeach
                    </ul>
                    <noscript><button class="btn btn-secondary carro-actualizar">Actualizar carro</button></noscript>
                </form>

                <a class="carro-volver" href="{{ route('tienda.catalogo') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                    Seguir comprando
                </a>
            </section>

            <aside class="carro-lateral">
                <section class="panel-card carro-resumen" aria-labelledby="carro-resumen-titulo">
                    <h2 id="carro-resumen-titulo">Resumen del pedido</h2>
                    <dl class="carro-lineas">
                        <div><dt>Subtotal</dt><dd data-carro-subtotal>{{ $pesos($subtotal) }}</dd></div>
                        <div><dt>Envío</dt><dd data-carro-envio class="{{ $costoEnvio ? '' : 'es-gratis' }}">{{ $costoEnvio ? $pesos($costoEnvio) : 'Gratis' }}</dd></div>
                    </dl>

                    <div class="carro-envio-gratis {{ $faltaEnvioGratis ? '' : 'is-logrado' }}" data-carro-envio-gratis>
                        <span data-carro-envio-texto>
                            @if($faltaEnvioGratis)
                                Te faltan <strong>{{ $pesos($faltaEnvioGratis) }}</strong> para el envío gratis.
                            @else
                                ¡Tienes envío gratis!
                            @endif
                        </span>
                        <span class="barra" aria-hidden="true"><span data-carro-envio-barra style="--avance:{{ $avanceEnvio }}%"></span></span>
                    </div>

                    <div class="carro-total">
                        <span>Total</span>
                        <strong data-carro-total>{{ $pesos($total) }}</strong>
                    </div>

                    <a class="btn btn-success carro-pagar" href="{{ route('tienda.checkout') }}">
                        Continuar al pago
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </section>

                <section class="carro-garantia">
                    <span class="carro-garantia-icono" aria-hidden="true"><x-icono nombre="seguimiento" /></span>
                    <div>
                        <strong>Sigue tu pedido en todo momento</strong>
                        <p>Al confirmar tu compra recibes un número de seguimiento para ver cada etapa del despacho.</p>
                    </div>
                </section>
            </aside>
        </div>
    @endif
</div>
@endsection
