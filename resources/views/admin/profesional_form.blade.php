@extends('layouts.app')

@section('title', $profesionalEditar ? 'Editar Profesional' : 'Inscripcion Profesional')

@section('content')
<style>
    .pro-head{display:grid;grid-template-columns:120px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 20px}
    .pro-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .hospital-icon{width:38px;height:38px;border:3px solid #111827;background:#ecfeff;position:relative;display:inline-block}
    .hospital-icon:before{content:"";position:absolute;left:14px;top:5px;width:6px;height:23px;background:#ef4444}
    .hospital-icon:after{content:"";position:absolute;left:6px;top:13px;width:23px;height:6px;background:#ef4444}
    .hospital-icon span{position:absolute;right:-9px;bottom:5px;width:15px;height:21px;border:3px solid #111827;background:#fff}
    .hospital-icon span:before{content:"";position:absolute;left:4px;top:3px;width:3px;height:10px;background:#ef4444}
    .hospital-icon span:after{content:"";position:absolute;left:1px;top:6px;width:9px;height:3px;background:#ef4444}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .pro-form-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .span-12{grid-column:span 12}.span-8{grid-column:span 8}.span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}.span-2{grid-column:span 2}
    .pro-divider{border:0;border-top:1px solid #d1d5db;margin:24px 0 16px}
    .pro-actions{display:flex;justify-content:center;gap:12px;margin-top:18px}
    @media(max-width:900px){.pro-head{grid-template-columns:1fr}.pro-title{font-size:28px}.span-12,.span-8,.span-6,.span-4,.span-3,.span-2{grid-column:span 12}}
</style>

<div class="pro-head">
    <a class="btn btn-secondary" href="{{ route('admin.profesionales.index') }}">Volver</a>
    <h1 class="pro-title"><span class="hospital-icon"><span></span></span>{{ $profesionalEditar ? 'Editar Profesional' : 'Formulario de inscripcion profesional' }}</h1>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <form method="POST" enctype="multipart/form-data" action="{{ $profesionalEditar ? route('admin.profesionales.update', $profesionalEditar) : route('admin.profesionales.store') }}">
        @csrf
        @if($profesionalEditar)
            @method('PATCH')
        @endif
        <div class="pro-form-grid">
            <div class="span-8"><input class="form-control form-control-sm" name="nombre" placeholder="Nombre / Clinica" value="{{ old('nombre', $profesionalEditar?->nombre) }}" required></div>
            <div class="span-4"><input class="form-control form-control-sm" name="rut" placeholder="RUT" value="{{ old('rut', $profesionalEditar?->rut) }}" required></div>
            <div class="span-3"><input class="form-control form-control-sm" name="telefono" placeholder="Telefono" value="{{ old('telefono', $profesionalEditar?->telefono) }}"></div>
            <div class="span-3"><input class="form-control form-control-sm" type="email" name="email" placeholder="Email" value="{{ old('email', $profesionalEditar?->email) }}"></div>
            <div class="span-4"><input class="form-control form-control-sm" name="direccion_consulta" placeholder="Direccion consulta" value="{{ old('direccion_consulta', $profesionalEditar?->direccion_consulta) }}"></div>
            <div class="span-2"><input class="form-control form-control-sm" name="especialidad" placeholder="Especialidad" value="{{ old('especialidad', $profesionalEditar?->especialidad) }}"></div>
            <div class="span-4">
                <select class="form-control form-control-sm" name="recibe_voucher">
                    <option value="1" @selected(old('recibe_voucher', $profesionalEditar?->recibe_voucher ?? true))>Recibe Vaucher</option>
                    <option value="0" @selected(!old('recibe_voucher', $profesionalEditar?->recibe_voucher ?? true))>No recibe Vaucher</option>
                </select>
            </div>
            <div class="span-4"><input class="form-control form-control-sm" type="number" name="porcentaje_descuento_voucher" min="0" max="100" placeholder="% descuento si recibe vaucher" value="{{ old('porcentaje_descuento_voucher', $profesionalEditar?->porcentaje_descuento_voucher) }}"></div>
            <div class="span-4">
                <select class="form-control form-control-sm" name="visita_domiciliaria">
                    <option value="1" @selected(old('visita_domiciliaria', $profesionalEditar?->visita_domiciliaria ?? true))>Hace visita domiciliaria</option>
                    <option value="0" @selected(!old('visita_domiciliaria', $profesionalEditar?->visita_domiciliaria ?? true))>No hace visita domiciliaria</option>
                </select>
            </div>
            <div class="span-4"><input class="form-control form-control-sm" name="password_acceso" placeholder="{{ $profesionalEditar ? 'Nueva contrasena opcional' : 'Contrasena temporal segura' }}" {{ $profesionalEditar ? '' : 'required' }}></div>
            <div class="span-4"><input class="form-control form-control-sm" type="file" name="foto" accept="image/*"></div>
            <div class="span-4"><input class="form-control form-control-sm" name="geolocalizacion" placeholder="Geolocalizacion consulta" value="{{ old('geolocalizacion', $profesionalEditar?->geolocalizacion) }}"></div>
            <div class="span-4"><input class="form-control form-control-sm" name="codigo_geolocalizacion" placeholder="Codigo zona / geolocalizacion" value="{{ old('codigo_geolocalizacion', $profesionalEditar?->codigo_geolocalizacion) }}"></div>
            <div class="span-4">
                <select class="form-control form-control-sm" name="activo">
                    <option value="1" @selected(old('activo', $profesionalEditar?->activo ?? true))>Activo</option>
                    <option value="0" @selected(!old('activo', $profesionalEditar?->activo ?? true))>Inactivo</option>
                </select>
            </div>
            <div class="span-6">
                <label class="floating-label-activo-sm">
                    Opinion sistema nacional tipo FONAVET
                    <span class="info-tip" tabindex="0" data-tip="FONAVET: Fondo Nacional Veterinario. Propuesta: desde $6.990 mensual por mascota, con vouchers, recordatorios sanitarios, descuentos en profesionales adheridos, historial clinico y beneficios en alimento o servicios.">i</span>
                </label>
                <select class="form-control form-control-sm" name="encuesta_sistema_nacional">
                    <option value="">Sin respuesta</option>
                    <option value="muy_interesante" @selected(old('encuesta_sistema_nacional', $profesionalEditar?->encuesta_sistema_nacional) === 'muy_interesante')>Muy interesante</option>
                    <option value="interesante" @selected(old('encuesta_sistema_nacional', $profesionalEditar?->encuesta_sistema_nacional) === 'interesante')>Interesante</option>
                    <option value="neutral" @selected(old('encuesta_sistema_nacional', $profesionalEditar?->encuesta_sistema_nacional) === 'neutral')>Neutral</option>
                    <option value="poco_interesante" @selected(old('encuesta_sistema_nacional', $profesionalEditar?->encuesta_sistema_nacional) === 'poco_interesante')>Poco interesante</option>
                    <option value="no_interesa" @selected(old('encuesta_sistema_nacional', $profesionalEditar?->encuesta_sistema_nacional) === 'no_interesa')>No le interesa</option>
                </select>
                <span class="field-help">Propuesta: desde $6.990 mensual por mascota. El profesional puede ofrecer descuentos, agenda preferente, controles preventivos y atencion con voucher seguro.</span>
            </div>
            <div class="span-6"><input class="form-control form-control-sm" name="comentario_sistema_nacional" placeholder="Comentario sobre sistema nacional tipo FONAVET" value="{{ old('comentario_sistema_nacional', $profesionalEditar?->comentario_sistema_nacional) }}"></div>
        </div>
        <hr class="pro-divider">
        <div class="pro-form-grid">
            <div class="span-3"><input class="form-control form-control-sm" name="banco" placeholder="Banco" value="{{ old('banco', $profesionalEditar?->banco) }}"></div>
            <div class="span-3"><input class="form-control form-control-sm" name="tipo_cuenta" placeholder="Tipo de cuenta" value="{{ old('tipo_cuenta', $profesionalEditar?->tipo_cuenta) }}"></div>
            <div class="span-3"><input class="form-control form-control-sm" name="numero_cuenta" placeholder="Numero de cuenta" value="{{ old('numero_cuenta', $profesionalEditar?->numero_cuenta) }}"></div>
            <div class="span-3"><input class="form-control form-control-sm" name="titular_cuenta" placeholder="Titular cuenta" value="{{ old('titular_cuenta', $profesionalEditar?->titular_cuenta) }}"></div>
            <div class="span-3"><input class="form-control form-control-sm" name="rut_cuenta" placeholder="RUT cuenta" value="{{ old('rut_cuenta', $profesionalEditar?->rut_cuenta) }}"></div>
        </div>
        <div class="pro-actions">
            <a class="btn btn-secondary" href="{{ route('admin.profesionales.index') }}">Cancelar</a>
            <button class="btn">Guardar Profesional</button>
        </div>
    </form>
</div>
@endsection
