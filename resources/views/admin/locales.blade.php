@extends('layouts.app')

@section('title', 'Locales y comercios')

@section('content')
<style>
    .local-head{display:grid;grid-template-columns:180px minmax(0,1fr) 260px;gap:18px;align-items:center;margin:0 0 20px}
    .local-title{display:flex;align-items:center;justify-content:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .local-icon{width:38px;height:38px;border-radius:12px;background:#0f766e;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:22px;box-shadow:0 4px 10px rgba(15,23,42,.18)}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .local-table th,.local-table td{padding:12px 10px}
    .active-check{display:inline-flex;width:18px;height:18px;align-items:center;justify-content:center;background:#19d319;color:#fff;border:2px solid #065f08;font-weight:900;line-height:1}
    .create-local-btn{width:100%;min-height:48px;line-height:1.2}
    .type-pill{display:inline-flex;border-radius:999px;background:#dcfce7;color:#166534;font-size:12px;font-weight:900;padding:5px 10px}
    .survey-pill{display:inline-flex;border-radius:999px;background:#ede9fe;color:#5b21b6;font-size:12px;font-weight:900;padding:5px 10px}
    .relation-counts{display:grid;grid-template-columns:1fr 1fr;gap:4px;min-width:190px}.relation-counts span{padding:4px 7px;border-radius:6px;background:#f1f5f9;color:#334155;font-size:11px;font-weight:800}
    .table-tools{display:flex;gap:12px;align-items:end;justify-content:space-between;margin-bottom:18px}
    .table-tools form{display:flex;gap:10px;align-items:end;min-width:min(620px,100%)}
    .table-tools input{min-width:340px}
    .pagination-wrap{margin-top:18px}
    @media(max-width:900px){
        .local-head{grid-template-columns:1fr;gap:12px}
        .local-title{justify-content:flex-start;font-size:28px}
        .table-tools{align-items:stretch;flex-direction:column}
        .table-tools form{display:grid;grid-template-columns:minmax(0,1fr) auto;min-width:0;width:100%}
        .table-tools form>div{min-width:0}.table-tools input{min-width:0;width:100%}
        .table-tools>.muted{align-self:flex-end}
    }
    @media(max-width:600px){
        .local-head{margin-bottom:16px}.local-title{font-size:27px;line-height:1.2;align-items:flex-start}
        .local-icon{flex:0 0 38px;margin-top:2px}.classic-card{padding:12px!important}
        .table-tools{margin-bottom:12px;gap:8px}.table-tools form{grid-template-columns:1fr!important;gap:8px}
        .table-tools form .btn{width:100%}.table-tools>.muted{align-self:flex-start;padding:0 2px}
        .responsive-table-shell{overflow:visible!important;border-radius:0!important}
        .local-table{display:block;width:100%;min-width:0!important}
        .local-table thead{display:none}
        .local-table tbody{display:grid;gap:12px;width:100%}
        .local-table tr{display:block;width:100%;overflow:hidden;border:1px solid #cfe1e5;border-radius:12px;background:#fff;box-shadow:0 5px 14px rgba(18,63,75,.08)}
        .local-table td{display:grid;grid-template-columns:102px minmax(0,1fr);gap:10px;align-items:start;width:100%;padding:10px 12px!important;border-bottom:1px solid #e7eff1;overflow-wrap:anywhere}
        .local-table td:last-child{border-bottom:0}
        .local-table td::before{content:attr(data-label);color:#58717a;font-size:11px;font-weight:900;line-height:1.35;text-transform:uppercase;letter-spacing:.035em}
        .local-table td[data-label="Nombre"]{display:block;padding:14px 12px!important;background:#edf7f8;color:#123f4b;font-size:17px;font-weight:900}
        .local-table td[data-label="Nombre"]::before{display:none}
        .local-table .relation-counts{grid-template-columns:1fr 1fr;min-width:0;width:100%}
        .local-table .actions{display:grid!important;grid-template-columns:1fr 1fr!important;gap:8px;width:100%}
        .local-table .actions>*{width:100%!important}.local-table .actions form{display:block!important}
        .local-table .actions a,.local-table .actions button{width:100%!important;min-width:0!important}
        .local-table tr:has(td[colspan]) td{display:block;text-align:center;padding:24px 12px!important}
        .local-table tr:has(td[colspan]) td::before{display:none}
    }
</style>

<div class="local-head">
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Volver</a>
    <h1 class="local-title"><span class="local-icon">L</span>Locales, sucursales y comercios</h1>
    <a class="btn create-local-btn" href="{{ route('admin.locales.create') }}">Crear lugar de venta</a>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.locales.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Nombre, codigo, tipo, comuna o responsable">
            </div>
            <button class="btn">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary" href="{{ route('admin.locales.index') }}">Limpiar</a>@endif
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
                    <td data-label="Tipo"><span class="type-pill">{{ $tiposLocal[$local->tipo] ?? $local->tipo ?? 'Sucursal propia' }}</span></td>
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
                        <span>{{ $local->administradores_count }} administradores</span>
                        <span>{{ $local->clientes_count }} clientes</span>
                        <span>{{ $local->bodegas_count }} bodegas</span>
                        <span>{{ $local->repartidores_count }} repartidores</span>
                    </div></td>
                    <td data-label="Convenio">
                        {{ $local->modalidad_convenio ? str_replace('_', ' ', ucfirst($local->modalidad_convenio)) : 'Sin modalidad' }}<br>
                        <span class="muted">{{ count($local->servicios_ofrecidos ?? []) }} servicios configurados</span><br>
                        <span class="survey-pill">{{ $local->publica_ofertas ? 'Publicado en ofertas' : 'Sin publicacion' }}</span>
                    </td>
                    <td data-label="Estado">
                        @if($local->activo)
                            <span class="active-check">✓</span>
                        @else
                            <span class="muted">Inactivo</span>
                        @endif
                    </td>
                    <td data-label="Acciones">
                        <div class="actions">
                            <a class="edit-btn" href="{{ route('admin.locales.edit', $local) }}">Editar</a>
                            <form method="POST" action="{{ route('admin.locales.estado', $local) }}">
                                @csrf
                                @method('PATCH')
                                <button class="{{ $local->activo ? 'inactive-btn' : 'active-btn' }}">{{ $local->activo ? 'Inactivar' : 'Activar' }}</button>
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
