@extends('layouts.app')

@section('title', 'Usuarios del Sistema')
@section('estilos', 'css/admin-usuarios.css, css/admin-usuario-form.css')

@section('content')

<div class="users-page">
    <x-encabezado-pagina
        titulo="Usuarios"
        descripcion="Administra accesos, roles y estados desde un solo lugar."
        :volver="route('admin.dashboard') . '#usuarios'">
        <button type="button" class="encabezado-boton" data-modal-abrir="modal-nuevo-usuario"><x-icono nombre="plus" />Nuevo usuario</button>
    </x-encabezado-pagina>


    <div class="users-summary">
        <div class="summary-card"><span>Total usuarios</span><strong>{{ number_format($totalUsuarios) }}</strong></div>
        <div class="summary-card"><span>Activos</span><strong>{{ number_format($usuariosActivos) }}</strong></div>
        <div class="summary-card"><span>Inactivos</span><strong>{{ number_format($usuariosInactivos) }}</strong></div>
        <div class="summary-card roles"><span>Roles configurados</span><strong>{{ $roles->count() }}</strong></div>
    </div>

    <div class="users-panel">
        <form class="filter-bar" method="GET" action="{{ route('admin.usuarios.index') }}" data-keep-open="1">
            <div class="filter-field"><label for="buscar">Buscar usuario</label><input id="buscar" name="buscar" value="{{ $buscar }}" placeholder="Nombre, correo, teléfono o ID"></div>
            <div class="filter-field"><label for="rol">Rol</label><select id="rol" name="rol"><option value="">Todos los roles</option>@foreach($roles as $nombreRol => $cantidad)<option value="{{ $nombreRol }}" @selected($rolSeleccionado === $nombreRol)>{{ str_replace('_', ' ', ucfirst($nombreRol)) }} ({{ $cantidad }})</option>@endforeach</select></div>
            <div class="filter-field"><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Todos</option><option value="1" @selected($estadoSeleccionado === '1')>Activos</option><option value="0" @selected($estadoSeleccionado === '0')>Inactivos</option></select></div>
            <div class="filter-field"><label for="por_pagina">Por página</label><select id="por_pagina" name="por_pagina">@foreach([15,30,50,100] as $cantidad)<option value="{{ $cantidad }}" @selected($porPagina === $cantidad)>{{ $cantidad }}</option>@endforeach</select></div>
            <div class="filter-actions"><button class="btn boton-buscar" type="submit">Filtrar</button>@if($buscar || $rolSeleccionado || $estadoSeleccionado !== '')<a class="btn btn-secondary boton-buscar" href="{{ route('admin.usuarios.index') }}">Limpiar</a>@endif</div>
        </form>

        <div class="results-info">
            <span>Mostrando <strong>{{ $usuarios->firstItem() ?? 0 }}–{{ $usuarios->lastItem() ?? 0 }}</strong> de <strong>{{ number_format($usuarios->total()) }}</strong> resultados</span>
            <div class="active-filters">@if($rolSeleccionado)<span class="filter-chip">Rol: {{ str_replace('_', ' ', $rolSeleccionado) }}</span>@endif @if($estadoSeleccionado !== '')<span class="filter-chip">{{ $estadoSeleccionado === '1' ? 'Activos' : 'Inactivos' }}</span>@endif</div>
        </div>

        <div class="users-table-wrap">
            <table class="users-table">
                <thead><tr><th>Usuario</th><th>Contacto</th><th>Rol</th><th>Local</th><th>Estado</th><th>Acción</th></tr></thead>
                <tbody>
                @forelse($usuarios as $usuario)
                    <tr>
                        <td><div class="user-main"><span class="user-avatar">{{ mb_strtoupper(mb_substr($usuario->name, 0, 1)) }}</span><div><span class="user-name">{{ $usuario->name }}</span><span class="user-id">ID #{{ $usuario->id }}</span></div></div></td>
                        <td><span class="contact-mail">{{ $usuario->email }}</span><span class="contact-phone">{{ $usuario->telefono ?: 'Sin teléfono registrado' }}</span></td>
                        <td><span class="badge tono-oscuro">{{ str_replace('_', ' ', $usuario->rol) }}</span></td>
                        <td>{{ $usuario->localVenta?->nombre ?? '—' }}</td>
                        <td><span class="badge {{ $usuario->activo ? 'tono-verde' : 'tono-gris' }}">{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        <td><x-boton-tabla tipo="editar" href="{{ route('admin.usuarios.edit', $usuario) }}">Editar</x-boton-tabla></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-users"><strong>No se encontraron usuarios</strong><br>Prueba cambiando o limpiando los filtros.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $usuarios->links('vendor.pagination.admin') }}</div>
    </div>
</div>

{{-- Se abre con "Nuevo usuario"; se reabre solo si al guardar hubo errores (conserva lo escrito) --}}
<x-modal
    id="modal-nuevo-usuario"
    titulo="Nuevo usuario"
    descripcion="Completa los datos de acceso, rol y contacto. Los campos de encuesta son opcionales."
    ancho="grande"
    :abierto="old('_modal') === 'nuevo-usuario' || request()->boolean('nuevo')">
    <form id="form-nuevo-usuario" method="POST" action="{{ route('admin.usuarios.store') }}" data-keep-open="1">
        @csrf
        <input type="hidden" name="_modal" value="nuevo-usuario">
        @include('admin.partials.usuario-campos', ['usuarioEditar' => null])
    </form>
    <x-slot:pie>
        <button type="button" class="btn btn-secondary" data-modal-cerrar>Cancelar</button>
        <button type="submit" class="encabezado-boton" form="form-nuevo-usuario"><x-icono nombre="plus" />Crear usuario</button>
    </x-slot:pie>
</x-modal>
@endsection
