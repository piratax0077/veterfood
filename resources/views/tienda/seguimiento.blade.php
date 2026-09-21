@extends('layouts.app')

@section('title', 'Seguir pedido')
@section('estilos', 'css/tienda-seguimiento.css')

@section('content')

@php
    $estadosLegibles = [
        'recibido' => 'Pedido recibido', 'en_preparacion' => 'En preparación', 'preparando' => 'En preparación',
        'listo_despacho' => 'Listo para despacho', 'reparto_asignado' => 'Repartidor asignado', 'asignado' => 'Repartidor asignado',
        'en_camino' => 'En camino', 'en_ruta' => 'En camino',
    ];
    $esCliente = auth()->check() && auth()->user()->tieneRol('cliente', 'dueno_mascota');
@endphp

<div class="seguimiento-page">
    <section class="panel-card seguimiento-card">
        <span class="seguimiento-icono" aria-hidden="true"><x-icono nombre="seguimiento" /></span>
        <h1>Sigue tu pedido</h1>
        <p>Ingresa el número de seguimiento de tu compra para ver en qué etapa va y cuándo llega.</p>

        <form class="seguimiento-form" method="POST" action="{{ route('tienda.seguimiento.buscar') }}" data-keep-open="1" novalidate data-validar>
            @csrf
            <div class="seguimiento-campo {{ $errors->has('codigo') ? 'has-error' : '' }}">
                <label for="codigo_seguimiento">Número de seguimiento</label>
                <input id="codigo_seguimiento" name="codigo" value="{{ old('codigo') }}" placeholder="Ej: A1B2C3D4E5" maxlength="20" autocomplete="off" autocapitalize="characters" spellcheck="false" required aria-describedby="codigo_seguimiento_error" @if($errors->has('codigo')) aria-invalid="true" @endif autofocus>
                @error('codigo')<small class="seguimiento-error" id="codigo_seguimiento_error">{{ $message }}</small>@enderror
            </div>
            <button type="submit" class="btn btn-success"><x-icono nombre="lupa" />Buscar pedido</button>
        </form>

        <div class="seguimiento-ayuda">
            <h2>¿Dónde encuentro mi número?</h2>
            <ul>
                <li><x-icono nombre="encuesta" /><span>En el correo de confirmación que te enviamos al finalizar tu compra.</span></li>
                <li><x-icono nombre="compras" />
                    <span>
                        En Mis compras, dentro de tu cuenta.
                        @guest
                            Para visualizarlo debes <a href="{{ route('inicio') }}#inscripcion" data-modal-abrir="modal-crear-cuenta">registrarte</a>.
                        @endguest
                    </span>
                </li>
            </ul>
        </div>
    </section>

    @if($pedidosEnCurso->isNotEmpty())
        <section class="panel-card seguimiento-recientes">
            <h2>Tus pedidos en curso</h2>
            <ul class="seguimiento-lista">
                @foreach($pedidosEnCurso as $pedidoCurso)
                    <li>
                        <a href="{{ route('tracking.show', $pedidoCurso->codigo_tracking) }}">
                            <span>
                                <strong>{{ $pedidoCurso->codigo_tracking }}</strong><br>
                                <span>{{ $estadosLegibles[$pedidoCurso->estado] ?? ucfirst(str_replace('_', ' ', $pedidoCurso->estado)) }} · {{ $pedidoCurso->created_at->format('d-m-Y') }}</span>
                            </span>
                            <span class="ir">Ver seguimiento →</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endsection
