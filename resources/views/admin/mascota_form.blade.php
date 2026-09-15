@extends('layouts.app')

@section('title', $mascotaEditar ? 'Editar mascota' : 'Inscribir mascota')

@section('content')
{{-- Inscribir una mascota se hace desde el modal de admin/mascotas; esta pagina queda para editar --}}
<x-encabezado-pagina
    :titulo="$mascotaEditar ? 'Editar mascota' : 'Inscribir mascota'"
    :descripcion="$mascotaEditar ? $mascotaEditar->nombre . ' · Tutor: ' . ($mascotaEditar->cliente?->name ?? 'sin tutor') : 'Elige al cliente dueño y completa los datos de la mascota.'"
    :volver="route('admin.mascotas.index')"
    volver-texto="Volver a Mascotas" />

<div class="classic-card tarjeta-formulario">
    <form method="POST" action="{{ $mascotaEditar ? route('admin.mascotas.update', $mascotaEditar) : route('admin.mascotas.store') }}" data-keep-open="1">
        @csrf
        @if($mascotaEditar)
            @method('PATCH')
        @endif

        @include('admin.partials.mascota-campos')

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('admin.mascotas.index') }}">Cancelar</a>
            <button type="submit" class="encabezado-boton"><x-icono nombre="guardar" />Guardar mascota</button>
        </div>
    </form>
</div>
@endsection
