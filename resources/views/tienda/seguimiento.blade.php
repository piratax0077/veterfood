@extends('layouts.app')

@section('title', 'Seguir pedido')

@section('content')
<style>
    .seguimiento-page{max-width:660px;margin:12px auto 48px}
    .seguimiento-card{padding:36px 34px 30px!important;text-align:center}
    .seguimiento-icono{display:inline-flex;align-items:center;justify-content:center;width:78px;height:78px;margin-bottom:14px;border-radius:50%;background:#e7f5f0}
    .seguimiento-icono .isdi{margin:0;color:var(--vet-green);font-size:38px}
    .seguimiento-card h1{margin:0 0 6px;color:#06152f;font-size:clamp(1.6rem,2.6vw,2rem)}
    .seguimiento-card>p{max-width:460px;margin:0 auto;color:#607780;font-size:15.5px;line-height:1.5}
    .seguimiento-form{display:flex;gap:10px;margin:24px 0 0;text-align:left}
    .seguimiento-campo{flex:1 1 auto;min-width:0}
    .seguimiento-campo label{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
    .seguimiento-campo input{width:100%;height:54px;min-height:54px;margin:0;padding:0 22px;border:1.5px solid #cbdbd6!important;border-radius:999px!important;background:#fff!important;color:#12313b;font-family:inherit;font-size:17px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
    .seguimiento-campo input::placeholder{color:#9aa8a4;font-weight:600;letter-spacing:.02em;text-transform:none}
    .seguimiento-campo input:focus{border-color:var(--vet-green)!important;box-shadow:0 0 0 3px rgba(3,113,91,.14)!important;outline:0}
    .seguimiento-campo.has-error input{border-color:#f04438!important}
    .seguimiento-error{display:block;margin:8px 0 0 18px;color:#b42318;font-size:13.5px;font-weight:700}
    .seguimiento-form .btn{flex:0 0 auto;min-width:0;height:54px;padding:0 26px;font-size:16px}
    .seguimiento-form .btn .isdi{margin:0 8px 0 0;font-size:19px}
    .seguimiento-ayuda{margin-top:26px;padding-top:20px;border-top:1px solid #e2e8f0;text-align:left}
    .seguimiento-ayuda h2{margin:0 0 10px;color:#06152f;font-size:16px}
    .seguimiento-ayuda ul{display:grid;gap:8px;margin:0;padding:0;list-style:none}
    .seguimiento-ayuda li{display:flex;align-items:flex-start;gap:10px;color:#475569;font-size:14.5px;line-height:1.45}
    .seguimiento-ayuda li .isdi{flex:0 0 auto;margin:1px 0 0;color:var(--vet-green);font-size:18px}
    .seguimiento-ayuda a{color:var(--vet-green);font-weight:800;text-decoration:underline}
    .seguimiento-recientes{margin-top:18px;padding:26px 30px!important}
    .seguimiento-recientes h2{margin:0 0 12px;color:#06152f;font-size:18px}
    .seguimiento-lista{display:grid;gap:10px;margin:0;padding:0;list-style:none}
    .seguimiento-lista a{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;color:#12313b;transition:border-color .15s ease,background .15s ease}
    .seguimiento-lista a:hover{border-color:#10a37f;background:#f0faf6}
    .seguimiento-lista strong{font-size:15px;letter-spacing:.06em}
    .seguimiento-lista span{color:#607780;font-size:13.5px;font-weight:600}
    .seguimiento-lista .ir{color:var(--vet-green);font-weight:800;white-space:nowrap}
    @media(max-width:600px){
        .seguimiento-card{padding:26px 18px 22px!important}
        .seguimiento-form{flex-direction:column}
        .seguimiento-form .btn{width:100%}
        .seguimiento-recientes{padding:20px 16px!important}
    }
</style>

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

        <form class="seguimiento-form" method="POST" action="{{ route('tienda.seguimiento.buscar') }}" data-keep-open="1" novalidate>
            @csrf
            <div class="seguimiento-campo {{ $errors->has('codigo') ? 'has-error' : '' }}">
                <label for="codigo_seguimiento">Número de seguimiento</label>
                <input id="codigo_seguimiento" name="codigo" value="{{ old('codigo') }}" placeholder="Ej: A1B2C3D4E5" maxlength="20" autocomplete="off" autocapitalize="characters" spellcheck="false" required aria-describedby="codigo_seguimiento_error" @if($errors->has('codigo')) aria-invalid="true" @endif autofocus>
                @error('codigo')<small class="seguimiento-error" id="codigo_seguimiento_error">{{ $message }}</small>@enderror
            </div>
            <button type="submit" class="btn btn-success"><x-icono nombre="seguimiento" />Buscar pedido</button>
        </form>

        <div class="seguimiento-ayuda">
            <h2>¿Dónde encuentro mi número?</h2>
            <ul>
                <li><x-icono nombre="encuesta" /><span>En el correo de confirmación que te enviamos al finalizar tu compra.</span></li>
                <li><x-icono nombre="compras" />
                    <span>
                        En Mis compras, dentro de tu cuenta.
                        @guest
                            Para visualizarlo debes <a href="{{ route('inicio') }}#inscripcion">registrarte</a>.
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
