@extends('layouts.app')

@section('title', $localEditar ? 'Editar lugar de venta' : 'Crear lugar de venta')

@section('content')
{{-- Crear un lugar de venta se hace desde el modal (por pasos) de admin/locales; esta pagina queda para editar --}}
<x-encabezado-pagina
    :titulo="$localEditar ? 'Editar lugar de venta' : 'Crear lugar de venta'"
    :descripcion="$localEditar ? $localEditar->nombre . ' · ' . $localEditar->codigo : 'Sucursal, comercio adherido, punto de retiro u otro lugar.'"
    :volver="route('admin.locales.index')"
    volver-texto="Volver a Locales" />

<div class="classic-card tarjeta-formulario">
    <form method="POST" action="{{ $localEditar ? route('admin.locales.update', $localEditar) : route('admin.locales.store') }}" data-keep-open="1">
        @csrf
        @if($localEditar)
            @method('PATCH')
        @endif

        @include('admin.partials.local-campos')

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('admin.locales.index') }}">Cancelar</a>
            <button type="submit" class="encabezado-boton"><x-icono nombre="guardar" />{{ $localEditar ? 'Guardar cambios' : 'Crear lugar de venta' }}</button>
        </div>
    </form>
</div>
@endsection
