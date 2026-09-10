@extends('layouts.app')

@section('title', $config['titulo'])
@section('estilos', 'css/admin-usuarios-rol.css')

@section('content')

<x-encabezado-pagina
    :titulo="$config['titulo']"
    :descripcion="$config['descripcion']"
    :volver="route('admin.dashboard') . '#red-comercial'">
    @if($rol === 'repartidor')
        <a class="encabezado-boton encabezado-boton--secundario" href="{{ route('admin.repartos.historial') }}">Historial repartos</a>
    @endif
    <a class="encabezado-boton" href="{{ route('admin.' . $config['ruta'] . '.create') }}"><x-icono nombre="plus" />Crear {{ strtolower($config['singular']) }}</a>
</x-encabezado-pagina>


<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.' . $config['ruta'] . '.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Nombre, email, telefono o patente">
            </div>
            <button class="btn boton-buscar">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary boton-buscar" href="{{ route('admin.' . $config['ruta'] . '.index') }}">Limpiar</a>@endif
        </form>
        <span class="muted">{{ $usuarios->total() }} registros</span>
    </div>
    <table class="role-table">
        <thead>
            <tr>
                <th>ID</th>
                @if($rol === 'repartidor')
                    <th>Foto</th>
                @endif
                <th>Nombre</th>
                <th>Email</th>
                <th>Telefono</th>
                @if($rol === 'repartidor')
                    <th>Vehiculo</th>
                @endif
                <th>Local</th>
                <th>Rol</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->id }}</td>
                    @if($rol === 'repartidor')
                        <td>
                            @if($usuario->foto_url)
                                <img class="driver-photo" src="{{ asset($usuario->foto_url) }}" alt="{{ $usuario->name }}">
                            @else
                                <span class="driver-photo" style="display:inline-flex;align-items:center;justify-content:center;font-weight:900;color:#64748b">R</span>
                            @endif
                        </td>
                    @endif
                    <td>{{ $usuario->name }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->telefono }}</td>
                    @if($rol === 'repartidor')
                        <td>
                            <strong>{{ $usuario->vehiculo_patente ?: 'Sin patente' }}</strong><br>
                            <span class="muted">{{ trim(($usuario->vehiculo_marca ?? '') . ' ' . ($usuario->vehiculo_modelo ?? '')) ?: 'Sin vehiculo' }}</span>
                        </td>
                    @endif
                    <td>{{ $usuario->localVenta?->nombre ?? 'Sin local' }}</td>
                    <td><span class="badge tono-oscuro">{{ str_replace('_', ' ', $usuario->rol) }}</span></td>
                    <td>
                        @if($usuario->activo)
                            <span class="badge tono-verde">Activo</span>
                        @else
                            <span class="badge tono-gris">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <div class="tabla-acciones">
                            <x-boton-tabla tipo="editar" href="{{ route('admin.' . $config['ruta'] . '.edit', $usuario) }}">Editar</x-boton-tabla>
                            <form method="POST" action="{{ route('admin.' . $config['ruta'] . '.estado', $usuario) }}">
                                @csrf
                                @method('PATCH')
                                <x-boton-tabla :tipo="$usuario->activo ? 'inactivar' : 'activar'">{{ $usuario->activo ? 'Inactivar' : 'Activar' }}</x-boton-tabla>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $rol === 'repartidor' ? 10 : 8 }}" class="muted">No hay {{ strtolower($config['titulo']) }} registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $usuarios->links('vendor.pagination.admin') }}</div>
</div>
@endsection
