@extends('layouts.app')

@section('title', $clienteEditar ? 'Editar cliente' : 'Crear cliente')

@section('content')
{{-- Crear un cliente se hace desde el modal (por pasos) de admin/clientes; esta pagina queda para editar --}}
<x-encabezado-pagina
    :titulo="$clienteEditar ? 'Editar cliente' : 'Crear cliente'"
    :descripcion="$clienteEditar ? $clienteEditar->name . ' · ' . $clienteEditar->email : 'Cliente de reparto mensual o VIP.'"
    :volver="route('admin.clientes.index')"
    volver-texto="Volver a Clientes" />

<div class="classic-card tarjeta-formulario">
    <form method="POST" action="{{ $clienteEditar ? route('admin.clientes.update', $clienteEditar) : route('admin.clientes.store') }}" data-keep-open="1">
        @csrf
        @if($clienteEditar)
            @method('PATCH')
        @endif

        @include('admin.partials.cliente-campos')

        <div class="form-actions">
            <a class="btn btn-cancelar" href="{{ route('admin.clientes.index') }}">Cancelar</a>
            <button type="submit" class="encabezado-boton"><x-icono nombre="guardar" />{{ $clienteEditar ? 'Guardar cambios' : 'Crear cliente' }}</button>
        </div>
    </form>
</div>

{{-- Modal "Inscribir mascota" (fuera del formulario del cliente): lo abre "Inscribir mascotas de este cliente" --}}
@include('admin.modales.nueva-mascota')
@endsection
