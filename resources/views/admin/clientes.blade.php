@extends('layouts.app')

@section('title', 'Clientes')
@section('estilos', 'css/admin-clientes.css')

@section('content')

<x-encabezado-pagina
    titulo="Clientes"
    descripcion="Clientes de reparto mensual y clientes VIP."
    :volver="route('admin.dashboard') . '#red-comercial'">
    <a class="encabezado-boton" href="{{ route('admin.clientes.create') }}"><x-icono nombre="plus" />Crear nuevo cliente</a>
</x-encabezado-pagina>

<nav class="module-nav" aria-label="Navegacion clientes">
    <a class="active" href="{{ route('admin.clientes.index') }}">Listado de clientes</a>
    <a href="{{ route('admin.mascotas.index') }}">Mascotas inscritas</a>
    <a class="warn" href="{{ route('admin.planes.comerciales') }}">Planes comerciales</a>
</nav>


<div class="summary-grid">
    <div class="summary-card"><strong>{{ $clientes->total() }}</strong><span>Clientes registrados</span></div>
    <div class="summary-card"><strong>{{ $clientes->getCollection()->where('activo', true)->count() }}</strong><span>Activos en esta pagina</span></div>
    <div class="summary-card"><strong>{{ $clientes->getCollection()->sum(fn($cliente) => $cliente->mascotas->count()) }}</strong><span>Mascotas en esta pagina</span></div>
    <div class="summary-card"><strong>{{ $clientes->getCollection()->sum(fn($cliente) => $cliente->planesPedido->count()) }}</strong><span>Planes en esta pagina</span></div>
</div>

<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.clientes.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Nombre, email, telefono o direccion">
            </div>
            <button class="btn boton-buscar">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary boton-buscar" href="{{ route('admin.clientes.index') }}">Limpiar</a>@endif
        </form>
        <span class="muted">{{ $clientes->total() }} registros</span>
    </div>

    <div class="table-scroll">
        <table class="client-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th>Direccion</th>
                    <th>Mascotas</th>
                    <th>Planes</th>
                    <th>Voucher / encuesta</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                    @php($direccion = $cliente->direcciones->firstWhere('principal', true) ?? $cliente->direcciones->first())
                    <tr>
                        <td>{{ $cliente->id }}</td>
                        <td><strong>{{ $cliente->name }}</strong><br><span class="muted">{{ $cliente->rol }}</span></td>
                        <td>{{ $cliente->email }}<br><span class="muted">{{ $cliente->telefono ?: 'Sin telefono' }}</span></td>
                        <td>{{ $direccion?->direccion ?? $cliente->direccion ?? 'Sin direccion' }}<br><span class="muted">{{ $direccion?->comuna }}</span></td>
                        <td><span class="badge tono-azul">{{ $cliente->mascotas->count() }}</span></td>
                        <td><span class="badge tono-azul">{{ $cliente->planesPedido->count() }}</span></td>
                        <td>
                            @php($opcionesEncuesta = ['muy_interesante' => 'Muy interesante', 'interesante' => 'Interesante', 'neutral' => 'Neutral', 'poco_interesante' => 'Poco interesante', 'no_interesa' => 'No le interesa'])
                            {{ $cliente->recibe_voucher ? 'Si recibe' : 'No recibe' }}<br>
                            <span class="muted">{{ $cliente->porcentaje_descuento_voucher !== null ? $cliente->porcentaje_descuento_voucher . '% descuento' : 'Sin % definido' }}</span><br>
                            <span class="badge tono-celeste">{{ $opcionesEncuesta[$cliente->encuesta_sistema_nacional] ?? 'Sin respuesta' }}</span>
                        </td>
                        <td>
                            @if($cliente->activo)
                                <span class="badge tono-verde">Activo</span>
                            @else
                                <span class="badge tono-gris">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="tabla-acciones">
                                <x-boton-tabla tipo="editar" href="{{ route('admin.clientes.edit', $cliente) }}">Editar</x-boton-tabla>
                                <form method="POST" action="{{ route('admin.clientes.estado', $cliente) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-boton-tabla :tipo="$cliente->activo ? 'inactivar' : 'activar'">{{ $cliente->activo ? 'Inactivar' : 'Activar' }}</x-boton-tabla>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="muted">No hay clientes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $clientes->links('vendor.pagination.admin') }}</div>
</div>
@endsection
