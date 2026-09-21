@extends('layouts.app')

@section('title', $usuarioEditar ? 'Editar ' . strtolower($config['singular']) : 'Crear ' . strtolower($config['singular']))

@section('content')
{{-- Crear se hace desde el modal (por pasos) de la lista; esta pagina queda para editar --}}
<x-encabezado-pagina
    :titulo="($usuarioEditar ? 'Editar ' : 'Crear ') . strtolower($config['singular'])"
    :descripcion="$usuarioEditar ? $usuarioEditar->name . ' · ' . $usuarioEditar->email : $config['descripcion']"
    :volver="route('admin.' . $config['ruta'] . '.index')"
    :volver-texto="'Volver a ' . $config['titulo']" />

<div class="classic-card tarjeta-formulario">
    <form method="POST" enctype="multipart/form-data" action="{{ $usuarioEditar ? route('admin.' . $config['ruta'] . '.update', $usuarioEditar) : route('admin.' . $config['ruta'] . '.store') }}" data-keep-open="1">
        @csrf
        @if($usuarioEditar)
            @method('PATCH')
        @endif

        @include('admin.partials.usuario-rol-campos')

        <div class="form-actions">
            <a class="btn btn-cancelar" href="{{ route('admin.' . $config['ruta'] . '.index') }}">Cancelar</a>
            <button type="submit" class="encabezado-boton"><x-icono nombre="guardar" />Guardar {{ strtolower($config['singular']) }}</button>
        </div>
    </form>
</div>
@endsection
