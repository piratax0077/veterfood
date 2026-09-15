@extends('layouts.app')

@section('title', 'Inicio VeterFood')
@section('estilos', 'css/tienda-catalogo.css, css/tienda-inicio.css')

@section('content')
@php
    $ofertas = \App\Models\Producto::where('activo', true)
        ->whereNotNull('precio_oferta')
        ->whereColumn('precio_oferta', '<', 'precio')
        ->orderByRaw('(precio - precio_oferta) / precio DESC')
        ->take(5)
        ->get();

    // Recién llegados: los últimos productos agregados, de cualquier categoría
    $recienLlegados = \App\Models\Producto::where('activo', true)
        ->latest()
        ->take(5)
        ->get();

    $marcas = [
        ['img' => 'royal-canin.png',      'nombre' => 'Royal Canin'],
        ['img' => 'proplan.png',           'nombre' => 'Pro Plan'],
        ['img' => 'acana.png',             'nombre' => 'Acana'],
        ['img' => 'orijen.png',            'nombre' => 'Orijen'],
        ['img' => 'brit.png',              'nombre' => 'Brit'],
        ['img' => 'diamond-naturals.png',  'nombre' => 'Diamond Naturals'],
        ['img' => 'nexgard.png',           'nombre' => 'NexGard'],
        ['img' => 'bravecto.png',          'nombre' => 'Bravecto'],
        ['img' => '9lives.png',            'nombre' => '9Lives'],
    ];

    // Banners con el texto incluido en la imagen ("solo_imagen": toda la imagen es un enlace)
    $diapositivas = [
        [
            'imagen' => 'images/inicio/banner1.jpg',
            'solo_imagen' => true,
            'alt' => '¡Nutre a tu perro y refuerza su organismo con Acana!',
            'url' => route('tienda.catalogo', ['buscar' => 'acana']),
        ],
        [
            'imagen' => 'images/inicio/banner3.jpg',
            'solo_imagen' => true,
            'posicion' => '50% 50%',
            'alt' => 'Déjalos ser free: alimento Bravery para gatos',
            'url' => route('tienda.catalogo', ['buscar' => 'bravery']),
        ],
        [
            'imagen' => 'images/inicio/banner2.jpg',
            'solo_imagen' => true,
            'posicion' => '50% 35%',
            'alt' => '¡Nuevo! Nómade Senior, alimento para perros de razas pequeñas, medianas y grandes',
            'url' => route('tienda.seccion', ['perros', 'alimento-seco']),
        ],
    ];
@endphp

{{-- 1. Carrusel --}}
<section class="inicio-carrusel" aria-label="Destacados de la tienda" data-carrusel>
    <div class="carrusel-pista">
        @foreach($diapositivas as $indice => $slide)
            <article @class(['carrusel-slide', 'is-activa' => $loop->first, 'is-solo-imagen' => !empty($slide['solo_imagen'])]) data-carrusel-slide @if(!$loop->first) aria-hidden="true" @endif>
                @if(!empty($slide['solo_imagen']))
                    <a class="carrusel-enlace" href="{{ $slide['url'] }}">
                        <img class="carrusel-foto" src="{{ asset($slide['imagen']) }}" alt="{{ $slide['alt'] }}" width="1600" height="650" @isset($slide['posicion']) style="object-position: {{ $slide['posicion'] }}" @endisset @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                    </a>
                @else
                    <img @class(['carrusel-foto', 'is-espejo' => !empty($slide['espejo'])]) src="{{ asset($slide['imagen']) }}" alt="" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                    <div class="carrusel-texto">
                        <h2>{{ $slide['titulo'] }}</h2>
                        <p>{{ $slide['texto'] }}</p>
                        <a class="btn btn-success" href="{{ $slide['url'] }}">{{ $slide['boton'] }}</a>
                    </div>
                @endif
            </article>
        @endforeach
    </div>

    <button type="button" class="carrusel-flecha carrusel-flecha--antes" data-carrusel-antes aria-label="Anterior">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
    </button>
    <button type="button" class="carrusel-flecha carrusel-flecha--despues" data-carrusel-despues aria-label="Siguiente">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
    </button>

    <div class="carrusel-puntos" role="tablist" aria-label="Elegir destacado">
        @foreach($diapositivas as $indice => $slide)
            <button type="button" @class(['carrusel-punto', 'is-activo' => $loop->first]) data-carrusel-punto="{{ $indice }}" role="tab" aria-label="Destacado {{ $indice + 1 }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"></button>
        @endforeach
    </div>
</section>

{{-- Franja de beneficios bajo el banner (solo en computador) --}}
<section class="inicio-beneficios" aria-label="Beneficios VeterFood">
    <ul>
        <li><x-icono nombre="seguimiento" /><span>Envíos a todo<br>Chile</span></li>
        <li><x-icono nombre="usuario" /><span>¡Regístrate y obtén<br>descuentos exclusivos!</span></li>
        <li><x-icono nombre="suscripcion" /><span>Contamos con<br>pedidos programados</span></li>
        <li><x-icono nombre="candado" /><span>Pago seguro<br>Transacciones 100% protegidas</span></li>
    </ul>
</section>

{{-- 1.1 Busca por mascota --}}
<section class="inicio-seccion" aria-labelledby="inicio-mascotas-titulo">
    <div class="inicio-seccion-cabecera inicio-seccion-cabecera--centrada" data-revelar>
        <h2 id="inicio-mascotas-titulo">Descubre productos para ellos</h2>
    </div>
    <nav class="especie-picker especie-picker--inicio" aria-label="Buscar por mascota" data-revelar-grupo>
        <a class="especie-item" href="{{ route('tienda.seccion', 'perros') }}">
            <span class="especie-photo"><img src="{{ asset('images/tienda/perro/perro.jpg') }}" alt="" loading="lazy"></span>
            <span class="especie-nombre">Perros</span>
        </a>
        <a class="especie-item" href="{{ route('tienda.seccion', 'gatos') }}">
            <span class="especie-photo"><img src="{{ asset('images/tienda/gato/gato.jpg') }}" alt="" loading="lazy"></span>
            <span class="especie-nombre">Gatos</span>
        </a>
        <a class="especie-item" href="{{ route('tienda.seccion', 'exoticos') }}">
            <span class="especie-photo"><img src="{{ asset('images/tienda/exoticos/exoticos-categoria.jpg') }}" alt="" loading="lazy"></span>
            <span class="especie-nombre">Exóticos</span>
        </a>
    </nav>
</section>

{{-- 2. Ofertas --}}
@if($ofertas->isNotEmpty())
    <section class="inicio-seccion" aria-labelledby="inicio-ofertas">
        <div class="inicio-seccion-cabecera" data-revelar>
            <div>
                <h2 id="inicio-ofertas">Precios rebajados esta semana</h2>
            </div>
            <a class="inicio-vertodo" href="{{ route('tienda.outlet') }}">Ver todas las ofertas</a>
        </div>
        <div class="productos-grid productos-grid--fila" data-revelar-grupo>
            @foreach($ofertas as $producto)
                @include('tienda.partials.tarjeta-producto')
            @endforeach
        </div>
    </section>
@endif

{{-- 3. Franja de despacho programado --}}
<section class="inicio-suscripcion" data-revelar>
    <img class="inicio-suscripcion-foto" src="{{ asset('images/tienda/inicio-promocional/banner-promocional-suscripcion.jpg') }}" alt="" loading="lazy">
    <div class="inicio-suscripcion-texto">
        <h2>Que nunca le falte su comida</h2>
        <p>Regístrate y activa tus pedidos programados: recíbelos automáticamente en casa y olvídate de hacer el pedido cada mes.</p>
    </div>
    <a class="btn btn-orange inicio-suscripcion-boton" href="{{ route('tienda.catalogo') }}">Armar mi pedido</a>
</section>

{{-- 3.1 Recién llegados --}}
@if($recienLlegados->isNotEmpty())
    <section class="inicio-seccion" aria-labelledby="inicio-nuevos">
        <div class="inicio-seccion-cabecera" data-revelar>
            <div>
                <h2 id="inicio-nuevos">Recién llegados</h2>
            </div>
        </div>
        <div class="productos-grid productos-grid--fila" data-revelar-grupo>
            @foreach($recienLlegados as $producto)
                @include('tienda.partials.tarjeta-producto')
            @endforeach
        </div>
    </section>
@endif

{{-- 3.2 Banner de marca --}}
<section class="inicio-banner-marca" data-revelar>
    <a href="{{ route('tienda.catalogo', ['buscar' => 'Nutrience']) }}">
        <img src="{{ asset('images/tienda/inicio-promocional/nutrience-promocion.png') }}" alt="Nutrience" loading="lazy">
    </a>
</section>

{{-- 4. Accesos rápidos --}}
<section class="inicio-avisos" aria-label="Accesos rápidos" data-revelar-grupo>
    <a class="inicio-aviso" href="{{ route('tienda.catalogo') }}">
        <img class="is-espejo" src="{{ asset('images/inicio/aviso-tienda.jpg') }}" alt="" loading="lazy">
        <div class="inicio-aviso-texto">
            <strong>Encuentra todo para tu mascota</strong>
            <em class="inicio-aviso-boton">Ver todos los productos</em>
        </div>
    </a>
    <a class="inicio-aviso" href="{{ route('tienda.categoria', 'servicios') }}">
        <img src="{{ asset('images/inicio/aviso-servicios.jpg') }}" alt="" loading="lazy">
        <div class="inicio-aviso-texto">
            <strong>Baño, corte, paseos y más</strong>
            <em class="inicio-aviso-boton">Agendar un servicio</em>
        </div>
    </a>
</section>

{{-- 5. Marcas --}}
<section class="inicio-marcas" aria-labelledby="inicio-marcas-titulo" data-revelar>
    <h2 id="inicio-marcas-titulo">Marcas destacadas</h2>
    <div class="marcas-cinta" data-marcas>
        <ul class="marcas-pista">
            @foreach($marcas as $marca)
                <li><span class="marca-chip"><img src="{{ asset('images/tienda/marcas/' . $marca['img']) }}" alt="{{ $marca['nombre'] }}" loading="lazy"></span></li>
            @endforeach
        </ul>
        <ul class="marcas-pista" aria-hidden="true">
            @foreach($marcas as $marca)
                <li><span class="marca-chip"><img src="{{ asset('images/tienda/marcas/' . $marca['img']) }}" alt="{{ $marca['nombre'] }}" loading="lazy"></span></li>
            @endforeach
        </ul>
    </div>
</section>

{{-- Suscripción a noticias y promociones --}}
<section class="inicio-suscribete" aria-labelledby="inicio-suscribete-titulo" data-revelar>
    <div class="inicio-suscribete-texto">
        <span class="inicio-suscribete-icono" aria-hidden="true"><x-icono nombre="correo" /></span>
        <div>
            <h2 id="inicio-suscribete-titulo">Suscríbete a nuestras novedades</h2>
            <p>Recibe las últimas noticias, promociones y descuentos exclusivos para tu mascota.</p>
        </div>
    </div>
    <form class="inicio-suscribete-form" data-suscripcion novalidate>
        <label class="visually-hidden" for="suscripcion_email">Tu correo electrónico</label>
        <input class="form-control" type="email" id="suscripcion_email" name="email" placeholder="Tu correo electrónico" autocomplete="email" required data-escritura-animada>
        <button type="submit" class="btn inicio-suscribete-boton">Enviar</button>
        <small class="inicio-suscribete-nota">Puedes darte de baja cuando quieras.</small>
    </form>
</section>
{{-- 6. Mascotas al pie de la página --}}
<section class="inicio-pie" aria-label="Mascotas VeterFood">
    <img src="{{ asset('images/tienda/inicio-promocional/imagen-pie.png') }}" alt="Perro, gato, loro, conejo y cachorro juntos" width="1408" height="768" loading="lazy" data-revelar>
</section>
@endsection
