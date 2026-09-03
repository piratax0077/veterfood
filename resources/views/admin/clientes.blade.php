@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<style>
    .clients-head{display:grid;grid-template-columns:160px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 16px}
    .clients-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .client-icon{width:38px;height:38px;border-radius:12px;background:#f59e0b;color:#111827;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:22px;box-shadow:0 4px 10px rgba(15,23,42,.18)}
    .module-nav{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 18px;padding:12px;background:#fff;border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 3px 8px rgba(15,23,42,.06)}
    .module-nav a{min-height:42px;padding:11px 16px;border-radius:7px;background:#e5e7eb;color:#111827;text-decoration:none;font-weight:900}
    .module-nav a.active{background:#2563eb;color:#fff}
    .module-nav a.success{background:#15803d;color:#fff}
    .module-nav a.warn{background:#f59e0b;color:#111827}
    .summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
    .summary-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:16px;box-shadow:0 3px 8px rgba(15,23,42,.06)}
    .summary-card strong{display:block;font-size:26px;color:#06152f}
    .summary-card span{display:block;color:#64748b;margin-top:4px}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:22px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .client-table th,.client-table td{padding:12px 10px;vertical-align:top}
    .active-check{display:inline-flex;width:18px;height:18px;align-items:center;justify-content:center;background:#19d319;color:#fff;border:2px solid #065f08;font-weight:900;line-height:1}
    .count-pill{display:inline-flex;border-radius:999px;background:#e0f2fe;color:#075985;font-size:12px;font-weight:900;padding:5px 10px}
    .survey-pill{display:inline-flex;border-radius:999px;background:#ede9fe;color:#5b21b6;font-size:12px;font-weight:900;padding:5px 10px}
    .table-tools{display:flex;gap:12px;align-items:end;justify-content:space-between;margin-bottom:18px}
    .table-tools form{display:flex;gap:10px;align-items:end;min-width:min(560px,100%)}
    .table-tools input{min-width:300px}
    .actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .actions a,.actions button{min-height:36px;padding:9px 14px;border-radius:6px;font-weight:900}
    .table-scroll{overflow:auto}
    .pagination-wrap{margin-top:18px}
    @media(max-width:900px){.clients-head,.summary-grid{grid-template-columns:1fr}.clients-title{font-size:28px}.table-tools,.table-tools form{display:block}.table-tools input{min-width:0}.module-nav a{width:100%;text-align:center}}
</style>

<div class="clients-head">
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Volver</a>
    <h1 class="clients-title"><span class="client-icon">$</span>Clientes</h1>
</div>

<nav class="module-nav" aria-label="Navegacion clientes">
    <a class="active" href="{{ route('admin.clientes.index') }}">Listado de clientes</a>
    <a class="success" href="{{ route('admin.clientes.create') }}">Crear nuevo cliente</a>
    <a href="{{ route('admin.mascotas.index') }}">Mascotas inscritas</a>
    <a class="warn" href="{{ route('admin.planes.comerciales') }}">Planes comerciales</a>
</nav>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

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
            <button class="btn">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary" href="{{ route('admin.clientes.index') }}">Limpiar</a>@endif
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
                        <td><span class="count-pill">{{ $cliente->mascotas->count() }}</span></td>
                        <td><span class="count-pill">{{ $cliente->planesPedido->count() }}</span></td>
                        <td>
                            @php($opcionesEncuesta = ['muy_interesante' => 'Muy interesante', 'interesante' => 'Interesante', 'neutral' => 'Neutral', 'poco_interesante' => 'Poco interesante', 'no_interesa' => 'No le interesa'])
                            {{ $cliente->recibe_voucher ? 'Si recibe' : 'No recibe' }}<br>
                            <span class="muted">{{ $cliente->porcentaje_descuento_voucher !== null ? $cliente->porcentaje_descuento_voucher . '% descuento' : 'Sin % definido' }}</span><br>
                            <span class="survey-pill">{{ $opcionesEncuesta[$cliente->encuesta_sistema_nacional] ?? 'Sin respuesta' }}</span>
                        </td>
                        <td>
                            @if($cliente->activo)
                                <span class="active-check">✓</span>
                            @else
                                <span class="muted">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <a class="edit-btn" href="{{ route('admin.clientes.edit', $cliente) }}">Editar</a>
                                <form method="POST" action="{{ route('admin.clientes.estado', $cliente) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="{{ $cliente->activo ? 'inactive-btn' : 'active-btn' }}">{{ $cliente->activo ? 'Inactivar' : 'Activar' }}</button>
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
