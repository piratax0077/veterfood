@extends('layouts.app')

@section('title', 'Usuarios del Sistema')

@section('content')
<style>
    .users-page{display:grid;gap:18px}
    .users-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-top:8px}
    .users-heading{display:flex;align-items:center;gap:13px;min-width:0}
    .users-heading h1{margin:0;font-size:30px;line-height:1.1;color:#111827}
    .users-heading p{margin:5px 0 0;color:#64748b;font-size:14px}
    .users-icon{width:42px;height:42px;flex:0 0 42px;border-radius:12px;background:#e8eefc;color:#1d4ed8;display:inline-flex;align-items:center;justify-content:center;font-size:22px}
    .head-actions{display:flex;gap:9px;align-items:center}.head-actions .btn{min-height:40px;padding:9px 14px;min-width:auto}
    .users-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .summary-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;box-shadow:0 3px 10px rgba(15,23,42,.04)}
    .summary-card span{display:block;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.summary-card strong{display:block;margin-top:4px;font-size:24px;color:#0f172a}
    .summary-card.roles strong{font-size:16px;margin-top:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .users-panel{background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 5px 16px rgba(15,23,42,.06);overflow:hidden}
    .filter-bar{display:grid;grid-template-columns:minmax(240px,2fr) minmax(150px,1fr) minmax(140px,.8fr) 110px auto;gap:10px;align-items:end;padding:16px;border-bottom:1px solid #e2e8f0;background:#f8fafc}
    .filter-field{position:relative;padding-top:7px}.filter-field label{position:absolute;z-index:2;top:0;left:10px;margin:0;padding:0 5px;background:#f8fafc;color:#1d4ed8;font-size:11px;font-weight:800}
    .filter-field input,.filter-field select{min-height:38px!important;height:38px;padding:7px 10px!important;background:#fff}
    .filter-actions{display:flex;gap:7px}.filter-actions .btn{min-width:auto;min-height:38px;padding:8px 13px}
    .results-info{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 16px;color:#64748b;font-size:13px;border-bottom:1px solid #edf1f5}
    .active-filters{display:flex;gap:6px;flex-wrap:wrap}.filter-chip{border-radius:999px;background:#eff6ff;color:#1d4ed8;padding:4px 9px;font-weight:700}
    .users-table-wrap{overflow:auto;max-height:62vh}.users-table{min-width:820px}.users-table thead{position:sticky;top:0;z-index:2;background:#f8fafc}
    .users-table th{padding:11px 14px;color:#475569;font-size:12px;text-transform:uppercase;letter-spacing:.03em;white-space:nowrap}.users-table td{padding:12px 14px;vertical-align:middle}
    .user-main{display:flex;align-items:center;gap:10px;min-width:220px}.user-avatar{width:36px;height:36px;flex:0 0 36px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#e8eefc;color:#1d4ed8;font-weight:900}
    .user-name{display:block;color:#0f172a;font-weight:800}.user-id{color:#94a3b8;font-size:12px}.contact-mail{display:block;color:#334155}.contact-phone{display:block;margin-top:2px;color:#64748b;font-size:12px}
    .role-pill{display:inline-flex;border-radius:999px;background:#eef2f7;color:#334155;padding:5px 9px;font-size:11px;font-weight:900;text-transform:capitalize}
    .status-pill{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:5px 9px;font-size:11px;font-weight:900}.status-pill:before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}.status-active{background:#dcfce7;color:#15803d}.status-inactive{background:#fee2e2;color:#b91c1c}
    .table-action{display:inline-flex;align-items:center;justify-content:center;min-height:32px;border:1px solid #bfdbfe;border-radius:7px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:900;padding:6px 11px}
    .empty-users{text-align:center;padding:50px 20px!important;color:#64748b}.pagination-wrap{padding:14px 16px;border-top:1px solid #edf1f5}
    @media(max-width:980px){.filter-bar{grid-template-columns:1fr 1fr}.filter-actions{grid-column:span 2}.users-summary{grid-template-columns:1fr 1fr}}
    @media(max-width:640px){.users-head{align-items:flex-start;flex-direction:column}.head-actions{width:100%}.head-actions .btn{flex:1}.users-heading h1{font-size:25px}.users-summary{grid-template-columns:1fr 1fr}.filter-bar{grid-template-columns:1fr}.filter-actions{grid-column:auto}.results-info{align-items:flex-start;flex-direction:column}}
</style>

<div class="users-page">
    <div class="users-head">
        <div class="users-heading">
            <span class="users-icon" aria-hidden="true">👥</span>
            <div><h1>Usuarios del sistema</h1><p>Administra accesos, roles y estados desde un solo lugar.</p></div>
        </div>
        <div class="head-actions">
            <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Volver</a>
            <a class="btn" href="{{ route('admin.usuarios.create') }}">+ Nuevo usuario</a>
        </div>
    </div>

    @if($errors->any())<div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>@endif

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
            <div class="filter-actions"><button class="btn" type="submit">Filtrar</button>@if($buscar || $rolSeleccionado || $estadoSeleccionado !== '')<a class="btn btn-secondary" href="{{ route('admin.usuarios.index') }}">Limpiar</a>@endif</div>
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
                        <td><span class="role-pill">{{ str_replace('_', ' ', $usuario->rol) }}</span></td>
                        <td>{{ $usuario->localVenta?->nombre ?? '—' }}</td>
                        <td><span class="status-pill {{ $usuario->activo ? 'status-active' : 'status-inactive' }}">{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        <td><a class="table-action" href="{{ route('admin.usuarios.edit', $usuario) }}">Editar</a></td>
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
@endsection
