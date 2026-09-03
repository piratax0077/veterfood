@extends('layouts.app')

@section('title', 'Servicios')

@section('content')
<style>
    .svc-head{display:grid;grid-template-columns:150px minmax(0,1fr) 180px;gap:18px;align-items:center;margin:0 0 20px}
    .svc-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .svc-icon{width:38px;height:38px;border-radius:10px;background:#1d4ed8;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:22px}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    #formulario-servicio{scroll-margin-top:90px}
    .svc-table th,.svc-table td{padding:12px 10px}
    .svc-pill{display:inline-flex;background:#1f2933;color:#fff;border-radius:6px;padding:4px 8px;font-size:12px;font-weight:800}
    .active-check{display:inline-flex;width:18px;height:18px;align-items:center;justify-content:center;background:#19d319;color:#fff;border:2px solid #065f08;font-weight:900;line-height:1}
    .svc-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .span-8{grid-column:span 8}.span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}
    @media(max-width:900px){.svc-head{grid-template-columns:1fr}.svc-title{font-size:28px}.span-8,.span-6,.span-4,.span-3{grid-column:span 12}}
</style>

<div class="svc-head">
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Volver</a>
    <h1 class="svc-title"><span class="svc-icon">S</span>Servicios</h1>
    <a class="btn" href="{{ route('admin.servicios.index') }}#formulario-servicio">Crear Servicio</a>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <table class="svc-table">
        <thead>
            <tr><th>Servicio</th><th>Tipo</th><th>Categoria</th><th>Modalidad</th><th>Duracion</th><th>Precio</th><th>Agenda</th><th>Activo</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            @forelse($servicios as $servicio)
                <tr>
                    <td><strong>{{ $servicio->nombre }}</strong><br><span class="muted">{{ $servicio->marca }}</span></td>
                    <td><span class="svc-pill">{{ $tiposServicio[$servicio->tipo_servicio] ?? $servicio->tipo_servicio ?? 'Sin tipo' }}</span></td>
                    <td>{{ $servicio->categoria }}</td>
                    <td>{{ $servicio->modalidad_servicio ?: 'Sin modalidad' }}</td>
                    <td>{{ $servicio->duracion_minutos ? $servicio->duracion_minutos . ' min' : 'Variable' }}</td>
                    <td>${{ number_format($servicio->precio, 0, ',', '.') }}</td>
                    <td>{{ $servicio->requiere_agenda ? 'Si' : 'No' }}</td>
                    <td>
                        @if($servicio->activo)
                            <span class="active-check">✓</span>
                        @else
                            <span class="muted">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a class="edit-btn" href="{{ route('admin.servicios.edit', $servicio) }}">Editar</a>
                            <form method="POST" action="{{ route('admin.servicios.estado', $servicio) }}">
                                @csrf
                                @method('PATCH')
                                <button class="{{ $servicio->activo ? 'inactive-btn' : 'active-btn' }}">{{ $servicio->activo ? 'Inactivar' : 'Activar' }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted">No hay servicios registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="formulario-servicio" class="classic-card" style="margin-top:26px">
    <h1 class="svc-title" style="font-size:32px"><span class="svc-icon">S</span>{{ $servicioEditar ? 'Editar Servicio' : 'Formulario de prestación veterinaria' }}</h1>
    <form method="POST" enctype="multipart/form-data" action="{{ $servicioEditar ? route('admin.servicios.update', $servicioEditar) : route('admin.servicios.store') }}" style="margin-top:18px">
        @csrf
        @if($servicioEditar)
            @method('PATCH')
        @endif
        <div class="svc-grid">
            <div class="span-4"><label class="floating-label-activo-sm">Nombre servicio</label><input class="form-control form-control-sm" name="nombre" value="{{ old('nombre', $servicioEditar?->nombre) }}" required></div>
            <div class="span-4"><label class="floating-label-activo-sm">Proveedor / Marca</label><input class="form-control form-control-sm" name="marca" value="{{ old('marca', $servicioEditar?->marca ?? 'VetChile Servicios') }}"></div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Categoria tienda</label>
                <select class="form-control form-control-sm" name="categoria" required>
                    @foreach(['servicio' => 'Servicios a domicilio', 'hotel' => 'Hoteles', 'paseo_diario' => 'Paseos diarios', 'cementerio' => 'Cementerio', 'cuidado' => 'Cuidados'] as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('categoria', $servicioEditar?->categoria ?? 'servicio') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Tipo de servicio que ofrece</label>
                <select class="form-control form-control-sm" name="tipo_servicio" required>
                    @foreach($tiposServicio as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('tipo_servicio', $servicioEditar?->tipo_servicio ?? 'veterinaria_domicilio') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4"><label class="floating-label-activo-sm">Modalidad</label><input class="form-control form-control-sm" name="modalidad_servicio" placeholder="Domicilio, consulta, retiro, agenda" value="{{ old('modalidad_servicio', $servicioEditar?->modalidad_servicio) }}"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Duracion minutos</label><input class="form-control form-control-sm" type="number" name="duracion_minutos" min="0" value="{{ old('duracion_minutos', $servicioEditar?->duracion_minutos) }}"></div>
            <div class="span-3"><label class="floating-label-activo-sm">Precio compra</label><input class="form-control form-control-sm" type="number" name="precio_compra" min="0" value="{{ old('precio_compra', $servicioEditar?->precio_compra ?? 0) }}"></div>
            <div class="span-3"><label class="floating-label-activo-sm">Precio venta</label><input class="form-control form-control-sm" type="number" name="precio" min="0" value="{{ old('precio', $servicioEditar?->precio) }}" required></div>
            <div class="span-3"><label class="floating-label-activo-sm">Cupos / stock</label><input class="form-control form-control-sm" type="number" name="stock" min="0" value="{{ old('stock', $servicioEditar?->stock ?? 20) }}"></div>
            <div class="span-3"><label class="floating-label-activo-sm">Stock minimo</label><input class="form-control form-control-sm" type="number" name="stock_minimo" min="0" value="{{ old('stock_minimo', $servicioEditar?->stock_minimo ?? 0) }}"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Sucursal destino</label><input class="form-control form-control-sm" name="sucursal_destino" value="{{ old('sucursal_destino', $servicioEditar?->sucursal_destino) }}"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Medio de envio / coordinación</label><input class="form-control form-control-sm" name="medio_envio" value="{{ old('medio_envio', $servicioEditar?->medio_envio ?? 'servicio_agendado') }}"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Foto</label><input class="form-control form-control-sm" type="file" name="foto" accept="image/*"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Requiere agenda</label><select class="form-control form-control-sm" name="requiere_agenda"><option value="1" @selected(old('requiere_agenda', $servicioEditar?->requiere_agenda ?? true))>Si</option><option value="0" @selected(!old('requiere_agenda', $servicioEditar?->requiere_agenda ?? true))>No</option></select></div>
            <div class="span-4"><label class="floating-label-activo-sm">Activo</label><select class="form-control form-control-sm" name="activo"><option value="1" @selected(old('activo', $servicioEditar?->activo ?? true))>Si</option><option value="0" @selected(!old('activo', $servicioEditar?->activo ?? true))>No</option></select></div>
            <div class="span-8"><label class="floating-label-activo-sm">Descripción del servicio</label><textarea class="form-control form-control-sm" name="descripcion">{{ old('descripcion', $servicioEditar?->descripcion) }}</textarea></div>
        </div>
        <div style="display:flex;justify-content:center;gap:12px;margin-top:18px">
            @if($servicioEditar)
                <a class="btn btn-secondary" href="{{ route('admin.servicios.index') }}">Cancelar</a>
            @endif
            <button class="btn-success">Guardar Servicio</button>
        </div>
    </form>
</div>
@endsection
