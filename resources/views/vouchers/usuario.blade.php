@extends('layouts.app')

@section('title', 'Mis vouchers disponibles')
@section('estilos', 'css/vouchers-usuario.css, css/cupones.css')

@section('content')
{{-- Solo si se llegó desde "Ver todos" de Mi cuenta; desde el menú no aparece --}}
@if(request('origen') === 'cuenta' && auth()->user()->tieneRol('cliente', 'dueno_mascota'))
    <a class="encabezado-volver" href="{{ route('cliente.panel') }}"><x-icono nombre="volver" />Volver a mi cuenta</a>
@endif
<section class="voucher-bloque">
    <h1 class="voucher-seccion">Cupones y vouchers</h1>
    <p class="voucher-seccion-nota">Úsalos antes de que expiren. Toca el código para copiarlo.</p>
    @include('partials.cupones-lista', ['vouchers' => $vouchers])
</section>

<script src="{{ asset('js/cliente-suscripciones.js') }}?v={{ filemtime(public_path('js/cliente-suscripciones.js')) }}" defer></script>
@endsection
