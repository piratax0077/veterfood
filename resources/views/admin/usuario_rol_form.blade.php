@extends('layouts.app')

@section('title', $usuarioEditar ? 'Editar ' . $config['singular'] : 'Crear ' . $config['singular'])

@section('content')
<style>
    .role-head{display:grid;grid-template-columns:170px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 20px}
    .role-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .role-icon{width:38px;height:38px;border-radius:10px;background:#198754;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:22px;box-shadow:0 4px 10px rgba(15,23,42,.18)}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .role-form-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .span-12{grid-column:span 12}.span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}
    .form-divider{grid-column:span 12;border-top:1px solid #dbe3ee;margin:10px 0 2px;padding-top:16px;font-size:20px;font-weight:900;color:#111827}
    .photo-preview{width:92px;height:72px;object-fit:cover;border-radius:8px;border:1px solid #dbe3ee;margin-top:8px;background:#f8fafc}
    .form-actions{display:flex;justify-content:center;gap:12px;margin-top:18px}
    @media(max-width:900px){.role-head{grid-template-columns:1fr}.role-title{font-size:28px}.span-12,.span-6,.span-4,.span-3{grid-column:span 12}}
</style>

<div class="role-head">
    <a class="btn btn-secondary" href="{{ route('admin.' . $config['ruta'] . '.index') }}">Volver</a>
    <h1 class="role-title"><span class="role-icon">{{ $config['icono'] }}</span>{{ $usuarioEditar ? 'Editar ' . $config['singular'] : 'Formulario de inscripcion ' . strtolower($config['singular']) }}</h1>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <p class="muted">{{ $config['descripcion'] }}</p>
    <form method="POST" enctype="multipart/form-data" action="{{ $usuarioEditar ? route('admin.' . $config['ruta'] . '.update', $usuarioEditar) : route('admin.' . $config['ruta'] . '.store') }}" style="margin-top:18px">
        @csrf
        @if($usuarioEditar)
            @method('PATCH')
        @endif
        <div class="role-form-grid">
            <div class="span-4"><label class="floating-label-activo-sm">Nombre</label><input class="form-control form-control-sm" name="name" value="{{ old('name', $usuarioEditar?->name) }}" required></div>
            <div class="span-4"><label class="floating-label-activo-sm">Email</label><input class="form-control form-control-sm" type="email" name="email" value="{{ old('email', $usuarioEditar?->email) }}" required></div>
            <div class="span-4"><label class="floating-label-activo-sm">Clave</label><input class="form-control form-control-sm" name="password" placeholder="{{ $usuarioEditar ? 'Nueva clave opcional' : 'Clave temporal segura' }}" {{ $usuarioEditar ? '' : 'required' }}></div>
            <div class="span-3"><label class="floating-label-activo-sm">Telefono</label><input class="form-control form-control-sm" name="telefono" value="{{ old('telefono', $usuarioEditar?->telefono) }}"></div>
            <div class="span-6"><label class="floating-label-activo-sm">Direccion</label><input class="form-control form-control-sm" name="direccion" value="{{ old('direccion', $usuarioEditar?->direccion) }}"></div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Local asignado</label>
                <select class="form-control form-control-sm" name="local_venta_id">
                    <option value="">Sin local</option>
                    @foreach($locales as $local)
                        <option value="{{ $local->id }}" @selected((int) old('local_venta_id', $usuarioEditar?->local_venta_id) === $local->id)>{{ $local->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Activo</label>
                <select class="form-control form-control-sm" name="activo">
                    <option value="1" @selected(old('activo', $usuarioEditar?->activo ?? true))>Si</option>
                    <option value="0" @selected(!old('activo', $usuarioEditar?->activo ?? true))>No</option>
                </select>
            </div>

            @if($rol === 'repartidor')
                <div class="form-divider">Datos del repartidor y vehiculo</div>
                <div class="span-6">
                    <label class="floating-label-activo-sm">Foto del repartidor</label>
                    <input class="form-control form-control-sm" type="file" name="foto" accept="image/*">
                    @if($usuarioEditar?->foto_url)
                        <img class="photo-preview" src="{{ asset($usuarioEditar->foto_url) }}" alt="{{ $usuarioEditar->name }}">
                    @endif
                </div>
                <div class="span-6">
                    <label class="floating-label-activo-sm">Foto del vehiculo</label>
                    <input class="form-control form-control-sm" type="file" name="vehiculo_foto" accept="image/*">
                    @if($usuarioEditar?->vehiculo_foto_url)
                        <img class="photo-preview" src="{{ asset($usuarioEditar->vehiculo_foto_url) }}" alt="Vehiculo {{ $usuarioEditar->name }}">
                    @endif
                </div>
                <div class="span-4"><label class="floating-label-activo-sm">Patente vehiculo</label><input class="form-control form-control-sm" name="vehiculo_patente" value="{{ old('vehiculo_patente', $usuarioEditar?->vehiculo_patente) }}"></div>
                <div class="span-4"><label class="floating-label-activo-sm">Marca vehiculo</label><input class="form-control form-control-sm" name="vehiculo_marca" value="{{ old('vehiculo_marca', $usuarioEditar?->vehiculo_marca) }}"></div>
                <div class="span-4"><label class="floating-label-activo-sm">Modelo vehiculo</label><input class="form-control form-control-sm" name="vehiculo_modelo" value="{{ old('vehiculo_modelo', $usuarioEditar?->vehiculo_modelo) }}"></div>
            @endif
        </div>
        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('admin.' . $config['ruta'] . '.index') }}">Cancelar</a>
            <button class="btn-success">Guardar {{ $config['singular'] }}</button>
        </div>
    </form>
</div>
@endsection
