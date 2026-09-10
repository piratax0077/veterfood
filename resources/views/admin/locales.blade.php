@extends('layouts.app')

@section('title', 'Locales y comercios')
@section('estilos', 'css/admin-locales.css')

@section('content')

<x-encabezado-pagina
    titulo="Locales y comercios"
    descripcion="Sucursales, puntos de venta, retiro y comercios adheridos."
    :volver="route('admin.dashboard') . '#operacion'">
    <a class="encabezado-boton" href="{{ route('admin.locales.create') }}"><x-icono nombre="plus" />Crear lugar de venta</a>
</x-encabezado-pagina>


<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.locales.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Nombre, codigo, tipo, comuna o responsable">
            </div>
            <button class="btn boton-buscar">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary boton-buscar" href="{{ route('admin.locales.index') }}">Limpiar</a>@endif
        </form>
        <span class="muted">{{ $locales->total() }} registros</span>
    </div>
    <table class="local-table">
        <thead>
            <tr>
                <th>Codigo</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Contacto</th>
                <th>Ubicacion</th>
                <th>Responsable</th>
                <th>Recursos del local</th>
                <th>Convenio y ofertas</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($locales as $local)
                <tr>
                    <td data-label="Código"><strong>{{ $local->codigo }}</strong><br><span class="muted">{{ $local->rut ?: 'Sin RUT' }}</span></td>
                    <td data-label="Nombre">{{ $local->nombre }}<br><span class="muted">{{ $local->razon_social ?: 'Sin razon social' }}</span></td>
                    <td data-label="Tipo"><span class="badge tono-azul">{{ $tiposLocal[$local->tipo] ?? $local->tipo ?? 'Sucursal propia' }}</span></td>
                    <td data-label="Contacto">{{ $local->email ?: 'Sin correo' }}<br><span class="muted">{{ $local->telefono ?: 'Sin telefono' }}</span></td>
                    <td data-label="Ubicación">
                        {{ $local->direccion }}<br>
                        <span class="muted">{{ $local->comuna ?: 'Sin comuna' }}</span>
                        @if($local->georeferencia_url)
                            <br><a href="{{ $local->georeferencia_url }}" target="_blank" rel="noopener">Ver mapa</a>
                        @endif
                    </td>
                    <td data-label="Responsable">{{ $local->responsable ?: 'Sin responsable' }}<br><span class="muted">{{ $local->contacto_comercial ?: 'Sin contacto comercial' }}</span></td>
                    <td data-label="Recursos"><div class="relation-counts">
                        <span class="badge tono-gris">{{ $local->administradores_count }} administradores</span>
                        <span class="badge tono-gris">{{ $local->clientes_count }} clientes</span>
                        <span class="badge tono-gris">{{ $local->bodegas_count }} bodegas</span>
                        <span class="badge tono-gris">{{ $local->repartidores_count }} repartidores</span>
                    </div></td>
                    <td data-label="Convenio">
                        {{ $local->modalidad_convenio ? str_replace('_', ' ', ucfirst($local->modalidad_convenio)) : 'Sin modalidad' }}<br>
                        <span class="muted">{{ count($local->servicios_ofrecidos ?? []) }} servicios configurados</span><br>
                        <span class="badge tono-celeste">{{ $local->publica_ofertas ? 'Publicado en ofertas' : 'Sin publicacion' }}</span>
                    </td>
                    <td data-label="Estado">
                        @if($local->activo)
                            <span class="badge tono-verde">Activo</span>
                        @else
                            <span class="badge tono-gris">Inactivo</span>
                        @endif
                    </td>
                    <td data-label="Acciones">
                        <div class="tabla-acciones">
                            <x-boton-tabla tipo="editar" href="{{ route('admin.locales.edit', $local) }}">Editar</x-boton-tabla>
                            <form method="POST" action="{{ route('admin.locales.estado', $local) }}">
                                @csrf
                                @method('PATCH')
                                <x-boton-tabla :tipo="$local->activo ? 'inactivar' : 'activar'">{{ $local->activo ? 'Inactivar' : 'Activar' }}</x-boton-tabla>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="muted">No hay lugares de venta registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $locales->links('vendor.pagination.admin') }}</div>
</div>
@endsection
