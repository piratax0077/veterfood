@extends('layouts.app')

@section('title', $usuarioEditar ? 'Editar usuario' : 'Crear usuario')

@section('content')
<style>
    .user-form-head{display:grid;grid-template-columns:170px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 20px}
    .user-form-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .user-form-icon{width:38px;height:34px;position:relative;display:inline-block}
    .user-form-icon:before,.user-form-icon:after{content:"";position:absolute;top:4px;width:18px;height:18px;border-radius:50%;background:#4b5563;border:3px solid #111827}
    .user-form-icon:before{left:3px}.user-form-icon:after{right:3px}
    .user-form-icon span{position:absolute;left:0;right:0;bottom:0;height:17px;border-radius:15px 15px 4px 4px;background:#4b5563;border:3px solid #111827}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .user-form-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .span-12{grid-column:span 12}.span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}.span-8{grid-column:span 8}
    .user-form-grid > [class^="span-"],
    .user-form-grid > [class*=" span-"]{position:relative;padding-top:8px}
    .user-form-grid label.floating-label-activo-sm{
        position:absolute!important;
        top:0!important;
        left:12px!important;
        right:auto!important;
        bottom:auto!important;
        z-index:2!important;
        display:inline-block!important;
        width:auto!important;
        margin:0!important;
        padding:0 5px!important;
        background:#fff!important;
        color:#374151!important;
        font-size:12px!important;
        font-weight:700!important;
        line-height:16px!important;
        transform:none!important;
        pointer-events:none!important;
    }
    .user-form-grid label .info-tip{pointer-events:auto}
    .user-form-grid .form-control{width:100%;min-height:36px}
    .user-form-grid textarea.form-control{min-height:58px;resize:vertical}
    .form-divider{grid-column:span 12;border-top:1px solid #dbe3ee;margin:10px 0 2px;padding-top:16px;font-size:20px;font-weight:900;color:#111827}
    .map-panel{grid-column:span 12;border:1px solid #dbe3ee;border-radius:8px;background:#f8fafc;padding:14px;margin-top:6px}
    .map-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .form-actions{display:flex;justify-content:center;gap:12px;margin-top:18px}
    @media(max-width:900px){.user-form-head{grid-template-columns:1fr}.user-form-title{font-size:28px}.span-12,.span-8,.span-6,.span-4,.span-3{grid-column:span 12}}
</style>

<div class="user-form-head">
    <a class="btn btn-secondary" href="{{ route('admin.usuarios.index') }}">Volver</a>
    <h1 class="user-form-title">
        <span class="user-form-icon"><span></span></span>
        {{ $usuarioEditar ? 'Editar usuario' : 'Formulario de inscripcion usuario' }}
    </h1>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <form method="POST" action="{{ $usuarioEditar ? route('admin.usuarios.update', $usuarioEditar) : route('admin.usuarios.store') }}">
        @csrf
        @if($usuarioEditar)
            @method('PATCH')
        @endif

        <div class="user-form-grid">
            <div class="form-divider">Datos de acceso y rol</div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Nombre</label>
                <input class="form-control form-control-sm" name="name" value="{{ old('name', $usuarioEditar?->name) }}" required>
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Email</label>
                <input class="form-control form-control-sm" type="email" name="email" value="{{ old('email', $usuarioEditar?->email) }}" required>
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Clave</label>
                <input class="form-control form-control-sm" name="password" placeholder="{{ $usuarioEditar ? 'Nueva clave opcional' : 'Clave temporal segura' }}" {{ $usuarioEditar ? '' : 'required' }}>
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Rol</label>
                <select class="form-control form-control-sm" name="rol" required>
                    @foreach(['admin' => 'Admin', 'central_ventas' => 'Central ventas', 'vendedor' => 'Vendedor', 'cliente' => 'Cliente', 'dueno_mascota' => 'Dueno mascota', 'repartidor' => 'Repartidor', 'auditor' => 'Auditor', 'contabilidad' => 'Contabilidad'] as $rol => $titulo)
                        <option value="{{ $rol }}" @selected(old('rol', $usuarioEditar?->rol ?? 'cliente') === $rol)>{{ $titulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Local asignado</label>
                <select class="form-control form-control-sm" name="local_venta_id">
                    <option value="">Sin local</option>
                    @foreach($locales as $local)
                        <option value="{{ $local->id }}" @selected((int) old('local_venta_id', $usuarioEditar?->local_venta_id) === $local->id)>{{ $local->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Telefono</label>
                <input class="form-control form-control-sm" name="telefono" value="{{ old('telefono', $usuarioEditar?->telefono) }}">
            </div>

            <div class="form-divider">Ubicacion y contacto operativo</div>
            <div class="span-8">
                <label class="floating-label-activo-sm">Direccion</label>
                <input class="form-control form-control-sm" id="direccion_usuario" name="direccion" value="{{ old('direccion', $usuarioEditar?->direccion) }}">
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Activo</label>
                <select class="form-control form-control-sm" name="activo">
                    <option value="1" @selected(old('activo', $usuarioEditar?->activo ?? true))>Si</option>
                    <option value="0" @selected(!old('activo', $usuarioEditar?->activo ?? true))>No</option>
                </select>
            </div>
            <input id="georeferencia_url" type="hidden" name="georeferencia_url" value="{{ old('georeferencia_url', $usuarioEditar?->georeferencia_url) }}">
            <div class="map-panel">
                <div class="map-actions">
                    <button type="button" class="btn" id="activar-mapa">Activar mapa por direccion</button>
                    <a class="btn btn-secondary" id="abrir-mapa" href="{{ old('georeferencia_url', $usuarioEditar?->georeferencia_url) ?: 'https://www.google.com/maps' }}" target="_blank" rel="noopener">Abrir mapa</a>
                    <span class="muted">Se usa si el usuario es cliente, local comercial, clinica, comercio adherido o punto operativo.</span>
                </div>
            </div>

            <div class="form-divider">Encuesta y propuesta FONAVET</div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Plan preferido</label>
                <select class="form-control form-control-sm" name="plan_preferido">
                    @foreach(['sin_plan' => 'Sin plan', 'fonavet_base' => 'FONAVET base', 'fonavet_integral' => 'FONAVET integral', 'alimento_mensual' => 'Alimento mensual', 'vip' => 'VIP'] as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('plan_preferido', $usuarioEditar?->plan_preferido ?? 'sin_plan') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Recibe voucher</label>
                <select class="form-control form-control-sm" name="recibe_voucher">
                    <option value="1" @selected(old('recibe_voucher', $usuarioEditar?->recibe_voucher ?? false))>Si recibe</option>
                    <option value="0" @selected(!old('recibe_voucher', $usuarioEditar?->recibe_voucher ?? false))>No recibe</option>
                </select>
            </div>
            <div class="span-3">
                <label class="floating-label-activo-sm">% descuento</label>
                <input class="form-control form-control-sm" type="number" name="porcentaje_descuento_voucher" min="0" max="100" value="{{ old('porcentaje_descuento_voucher', $usuarioEditar?->porcentaje_descuento_voucher) }}">
            </div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Pago mensual dispuesto</label>
                <input class="form-control form-control-sm" type="number" name="fonavet_valor_mensual" min="0" step="500" placeholder="Ej: 6990" value="{{ old('fonavet_valor_mensual', $usuarioEditar?->fonavet_valor_mensual) }}">
            </div>
            <div class="span-6">
                <label class="floating-label-activo-sm">
                    Opinion FONAVET
                    <span class="info-tip" tabindex="0" data-tip="FONAVET: Fondo Nacional Veterinario. Propuesta mensual desde $6.990 por mascota para atencion, vouchers, descuentos, historial clinico y beneficios.">i</span>
                </label>
                <select class="form-control form-control-sm" name="encuesta_sistema_nacional">
                    <option value="">Sin respuesta</option>
                    <option value="muy_interesante" @selected(old('encuesta_sistema_nacional', $usuarioEditar?->encuesta_sistema_nacional) === 'muy_interesante')>Muy interesante</option>
                    <option value="interesante" @selected(old('encuesta_sistema_nacional', $usuarioEditar?->encuesta_sistema_nacional) === 'interesante')>Interesante</option>
                    <option value="neutral" @selected(old('encuesta_sistema_nacional', $usuarioEditar?->encuesta_sistema_nacional) === 'neutral')>Neutral</option>
                    <option value="poco_interesante" @selected(old('encuesta_sistema_nacional', $usuarioEditar?->encuesta_sistema_nacional) === 'poco_interesante')>Poco interesante</option>
                    <option value="no_interesa" @selected(old('encuesta_sistema_nacional', $usuarioEditar?->encuesta_sistema_nacional) === 'no_interesa')>No le interesa</option>
                </select>
                <span class="field-help">Propuesta sugerida: $6.990 base o $9.990 integral por mascota.</span>
            </div>
            <div class="span-6">
                <label class="floating-label-activo-sm">Comentario encuesta</label>
                <textarea class="form-control form-control-sm" name="comentario_sistema_nacional">{{ old('comentario_sistema_nacional', $usuarioEditar?->comentario_sistema_nacional) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('admin.usuarios.index') }}">Cancelar</a>
            <button class="btn-success">{{ $usuarioEditar ? 'Guardar cambios' : 'Crear usuario' }}</button>
        </div>
    </form>
</div>
<script>
    const direccionInput = document.getElementById('direccion_usuario');
    const geoInput = document.getElementById('georeferencia_url');
    const abrirMapa = document.getElementById('abrir-mapa');
    document.getElementById('activar-mapa')?.addEventListener('click', () => {
        const texto = (direccionInput?.value || '').trim();
        const url = texto ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(texto)}` : 'https://www.google.com/maps';
        if (geoInput) geoInput.value = url;
        if (abrirMapa) abrirMapa.href = url;
    });
</script>
@endsection
