@extends('layouts.app')

@section('title', 'Pedidos programados')
@section('estilos', 'css/tienda-pedido-programado.css')

@php
    $esCliente = auth()->check() && auth()->user()->tieneRol('cliente', 'dueno_mascota');
    $urlAlimentos = route('tienda.catalogo', ['categoria' => 'alimento_mascota']);
@endphp

@section('content')
{{-- Portada --}}
<section class="susc-hero">
    <img class="susc-hero-foto" src="{{ asset('images/tienda/inicio-promocional/banner-promocional-suscripcion.jpg') }}" alt="" loading="lazy">
    <div class="susc-hero-contenido">
        <p class="susc-hero-antetitulo">Pedidos programados VeterFood</p>
        <h1>Que a tu mascota nunca le falte su alimento 🐾</h1>
        <p class="susc-hero-texto">Elige el alimento y los snacks que tu mascota ya ama, y recíbelos en tu casa en la fecha que tú decidas. Sin recordatorios, sin compras de último minuto.</p>
        <p class="susc-hero-texto">Regístrate en VeterFood y activa tu 5% de descuento en alimentos y snacks.</p>
        <div class="susc-hero-acciones">
            @if($esCliente)
                <a class="btn btn-orange" href="{{ $urlAlimentos }}">Quiero mis pedidos programados</a>
            @else
                <a class="btn btn-orange" href="#inscripcion" data-modal-abrir="modal-crear-cuenta">Quiero mis pedidos programados</a>
            @endif
        </div>
    </div>
</section>

{{-- Cómo funciona --}}
<section class="susc-seccion" aria-labelledby="susc-como-titulo">
    <h2 id="susc-como-titulo">¿Cómo funciona? Es muy fácil</h2>
    <div class="susc-pasos">
        <article class="susc-paso">
            <span class="susc-paso-numero">1</span>
            <h3>Elige lo que tu mascota usa siempre</h3>
            <p>Selecciona su alimento o snack favorito y cada cuánto quieres recibirlo.</p>
        </article>
        <article class="susc-paso">
            <span class="susc-paso-numero">2</span>
            <h3>Crea tu cuenta o inicia sesión</h3>
            <p>Los pedidos programados son para clientes registrados en VeterFood. Solo toma un minuto.</p>
        </article>
        <article class="susc-paso">
            <span class="susc-paso-numero">3</span>
            <h3>Relájate, nosotros nos encargamos</h3>
            <p>Tu pedido llega a tu casa en la fecha que elegiste. Tú disfrutas a tu mascota, nosotros nos ocupamos del resto.</p>
        </article>
    </div>
</section>

{{-- Beneficios --}}
<section class="susc-seccion" aria-labelledby="susc-beneficios-titulo">
    <h2 id="susc-beneficios-titulo">¿Por qué programar tus pedidos?</h2>
    <div class="susc-beneficios">
        <article class="susc-beneficio">
            <span class="susc-beneficio-icono" aria-hidden="true"><x-icono nombre="cupon" /></span>
            <h3>Ahorras en cada compra</h3>
            <p>Recibes 5% de descuento en alimentos y snacks por ser cliente registrado.</p>
        </article>
        <article class="susc-beneficio">
            <span class="susc-beneficio-icono" aria-hidden="true"><x-icono nombre="suscripcion" /></span>
            <h3>Ganas tiempo y tranquilidad</h3>
            <p>Lo programas una sola vez y olvidas las carreras de último momento.</p>
        </article>
        <article class="susc-beneficio">
            <span class="susc-beneficio-icono" aria-hidden="true"><x-icono nombre="candado" /></span>
            <h3>Pagas con total seguridad</h3>
            <p>Todas tus transacciones están 100% protegidas.</p>
        </article>
    </div>
</section>

{{-- Preguntas frecuentes --}}
<section class="susc-seccion" aria-labelledby="susc-faq-titulo">
    <h2 id="susc-faq-titulo">Preguntas frecuentes</h2>
    <div class="susc-faq">
        <details>
            <summary>¿Necesito tener cuenta en VeterFood para suscribirme?</summary>
            <p>Sí. Los pedidos programados son exclusivos para clientes registrados en VeterFood. Si aún no tienes cuenta, puedes crearla en un momento y sin costo.</p>
        </details>
        <details>
            <summary>¿A qué productos aplica el 5% de descuento?</summary>
            <p>Al alimento seco, alimento húmedo y a los snacks o premios que suscribas dentro de tus pedidos programados.</p>
        </details>
        <details>
            <summary>¿Para qué mascotas está disponible?</summary>
            <p>Para perros, gatos y mascotas exóticas: el beneficio aplica a la categoría de alimentos y snacks para todas ellas.</p>
        </details>
        <details>
            <summary>¿Puedo elegir cada cuánto tiempo llega mi pedido?</summary>
            <p>Sí, tú eliges la frecuencia al armar tu pedido programado desde tu cuenta VeterFood.</p>
        </details>
    </div>
</section>

{{-- Último llamado --}}
<section class="susc-unete">
    <h2>Únete hoy a los pedidos programados</h2>
    <p>Tu mascota merece tener siempre su comida favorita, y tú mereces olvidarte de acordarte.</p>
    @if($esCliente)
        <a class="btn btn-orange" href="{{ $urlAlimentos }}">Armar mi pedido programado</a>
    @else
        <a class="btn btn-orange" href="#inscripcion" data-modal-abrir="modal-crear-cuenta">Registrarme y ahorrar 5%</a>
    @endif
</section>
@endsection
