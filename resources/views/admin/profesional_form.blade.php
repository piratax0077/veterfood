@extends('layouts.app')

@section('title', $profesionalEditar ? 'Editar profesional' : 'Crear profesional')

@section('content')
{{-- Crear un profesional se hace desde el modal (por pasos) de admin/profesionales; esta pagina queda para editar --}}
<x-encabezado-pagina
    :titulo="$profesionalEditar ? 'Editar profesional' : 'Crear profesional'"
    :descripcion="$profesionalEditar ? $profesionalEditar->nombre . ' · ' . $profesionalEditar->rut : 'Veterinario, laboratorio o centro autorizado.'"
    :volver="route('admin.profesionales.index')"
    volver-texto="Volver a Profesionales" />

<div class="classic-card tarjeta-formulario">
    <form method="POST" enctype="multipart/form-data" action="{{ $profesionalEditar ? route('admin.profesionales.update', $profesionalEditar) : route('admin.profesionales.store') }}" data-keep-open="1">
        @csrf
        @if($profesionalEditar)
            @method('PATCH')
        @endif

        @include('admin.partials.profesional-campos')

        <div class="form-actions">
            <a class="btn btn-cancelar" href="{{ route('admin.profesionales.index') }}">Cancelar</a>
            <button type="submit" class="encabezado-boton"><x-icono nombre="guardar" />Guardar profesional</button>
        </div>
    </form>
</div>
@endsection
