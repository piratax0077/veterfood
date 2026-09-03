@extends('layouts.app')

@section('title', $config['titulo'])

@section('content')
<style>
    .role-head{display:grid;grid-template-columns:170px minmax(0,1fr) 220px;gap:18px;align-items:center;margin:0 0 20px}
    .role-head.has-history{grid-template-columns:170px minmax(0,1fr) 220px 220px}
    .role-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .role-icon{width:38px;height:38px;border-radius:10px;background:#198754;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:22px;box-shadow:0 4px 10px rgba(15,23,42,.18)}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .role-table th,.role-table td{padding:12px 10px}
    .role-pill{display:inline-flex;background:#1f2933;color:#fff;border-radius:6px;padding:4px 8px;font-size:12px;font-weight:800}
    .active-check{display:inline-flex;width:18px;height:18px;align-items:center;justify-content:center;background:#19d319;color:#fff;border:2px solid #065f08;font-weight:900;line-height:1}
    .create-role-btn{width:100%;min-height:48px;line-height:1.2}
    .driver-photo{width:52px;height:52px;border-radius:8px;object-fit:cover;background:#e5e7eb;border:1px solid #dbe3ee}
    .table-tools{display:flex;gap:12px;align-items:end;justify-content:space-between;margin-bottom:18px}
    .table-tools form{display:flex;gap:10px;align-items:end;min-width:min(560px,100%)}
    .table-tools input{min-width:300px}
    .pagination-wrap{margin-top:18px}
    @media(max-width:900px){.role-head{grid-template-columns:1fr}.role-title{font-size:28px}}
</style>

<div class="role-head {{ $rol === 'repartidor' ? 'has-history' : '' }}">
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Volver</a>
    <h1 class="role-title"><span class="role-icon">{{ $config['icono'] }}</span>{{ $config['titulo'] }}</h1>
    @if($rol === 'repartidor')
        <a class="btn create-role-btn" href="{{ route('admin.repartos.historial') }}">Historial repartos</a>
    @endif
    <a class="btn create-role-btn" href="{{ route('admin.' . $config['ruta'] . '.create') }}">Crear {{ strtolower($config['singular']) }}</a>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.' . $config['ruta'] . '.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Nombre, email, telefono o patente">
            </div>
            <button class="btn">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary" href="{{ route('admin.' . $config['ruta'] . '.index') }}">Limpiar</a>@endif
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
                    <td><span class="role-pill">{{ $usuario->rol }}</span></td>
                    <td>
                        @if($usuario->activo)
                            <span class="active-check">✓</span>
                        @else
                            <span class="muted">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a class="edit-btn" href="{{ route('admin.' . $config['ruta'] . '.edit', $usuario) }}">Editar</a>
                            <form method="POST" action="{{ route('admin.' . $config['ruta'] . '.estado', $usuario) }}">
                                @csrf
                                @method('PATCH')
                                <button class="{{ $usuario->activo ? 'inactive-btn' : 'active-btn' }}">{{ $usuario->activo ? 'Inactivar' : 'Activar' }}</button>
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
