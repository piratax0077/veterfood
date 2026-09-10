@extends('layouts.app')

@section('title', $usuarioEditar ? 'Editar usuario' : 'Crear usuario')
@section('estilos', 'css/admin-usuario-form.css')

@section('content')
{{-- Crear usuario se hace desde el modal de admin/usuarios; esta pagina queda para editar --}}
<x-encabezado-pagina
    :titulo="$usuarioEditar ? 'Editar usuario' : 'Nuevo usuario'"
    :descripcion="$usuarioEditar ? $usuarioEditar->name . ' · ' . $usuarioEditar->email : 'Completa los datos de acceso, rol y contacto.'"
    :volver="route('admin.usuarios.index')"
    volver-texto="Volver a Usuarios" />

<div class="classic-card user-form-card">
    <form method="POST" action="{{ $usuarioEditar ? route('admin.usuarios.update', $usuarioEditar) : route('admin.usuarios.store') }}" data-keep-open="1">
        @csrf
        @if($usuarioEditar)
            @method('PATCH')
        @endif

        @include('admin.partials.usuario-campos')

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('admin.usuarios.index') }}">Cancelar</a>
            <button type="submit" class="encabezado-boton"><x-icono nombre="guardar" />{{ $usuarioEditar ? 'Guardar cambios' : 'Crear usuario' }}</button>
        </div>
    </form>
</div>
@endsection
