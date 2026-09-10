@extends('layouts.app')

@section('title', $clienteEditar ? 'Editar cliente' : 'Crear cliente')

@section('content')
@php($direccion = $clienteEditar?->direcciones?->firstWhere('principal', true) ?? $clienteEditar?->direcciones?->first())
<style>
    .client-form-head{display:grid;grid-template-columns:170px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 20px}
    .client-form-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .client-icon{width:38px;height:38px;border-radius:12px;background:#f59e0b;color:#111827;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:22px;box-shadow:0 4px 10px rgba(15,23,42,.18)}
    .module-nav{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 18px;padding:12px;background:#fff;border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 3px 8px rgba(15,23,42,.06)}
    .module-nav a{min-height:42px;padding:11px 16px;border-radius:7px;background:#e5e7eb;color:#111827;text-decoration:none;font-weight:900}
    .module-nav a.active{background:#15803d;color:#fff}
    .module-nav a.primary{background:#2563eb;color:#fff}
    .module-nav a.warn{background:#f59e0b;color:#111827}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .client-form-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .span-12{grid-column:span 12}.span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}
    .form-divider{grid-column:span 12;border-top:1px solid #dbe3ee;margin:10px 0 2px;padding-top:16px;font-size:20px;font-weight:900;color:#111827}
    .inline-actions{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}
    .pet-link{width:100%;background:#0ea5e9}
    .map-panel{grid-column:span 12;border:1px solid #dbe3ee;border-radius:8px;background:#f8fafc;padding:14px;margin-top:6px}
    .map-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:12px}
    .map-frame{width:100%;height:260px;border:0;border-radius:8px;background:#e5e7eb}
    .form-actions{display:flex;justify-content:center;gap:12px;margin-top:18px}
    @media(max-width:900px){.client-form-head{grid-template-columns:1fr}.client-form-title{font-size:28px}.span-12,.span-6,.span-4,.span-3{grid-column:span 12}.module-nav a{width:100%;text-align:center}}
</style>

<div class="client-form-head">
    <a class="btn btn-secondary" href="{{ route('admin.clientes.index') }}">Volver</a>
    <h1 class="client-form-title"><span class="client-icon">$</span>{{ $clienteEditar ? 'Editar cliente' : 'Formulario de inscripcion cliente' }}</h1>
</div>

<nav class="module-nav" aria-label="Navegacion clientes">
    <a class="primary" href="{{ route('admin.clientes.index') }}">Listado de clientes</a>
    <a class="active" href="{{ route('admin.clientes.create') }}">Crear nuevo cliente</a>
    <a href="{{ route('admin.mascotas.index') }}">Mascotas inscritas</a>
    <a class="warn" href="{{ route('admin.planes.comerciales') }}">Planes comerciales</a>
</nav>


<div class="classic-card">
    <form method="POST" action="{{ $clienteEditar ? route('admin.clientes.update', $clienteEditar) : route('admin.clientes.store') }}">
        @csrf
        @if($clienteEditar)
            @method('PATCH')
        @endif

        <div class="client-form-grid">
            <div class="form-divider">Datos de acceso y contacto</div>
            <div class="span-4"><label class="floating-label-activo-sm">Nombre cliente</label><input class="form-control form-control-sm" name="name" value="{{ old('name', $clienteEditar?->name) }}" required></div>
            <div class="span-4"><label class="floating-label-activo-sm">Email</label><input class="form-control form-control-sm" type="email" name="email" value="{{ old('email', $clienteEditar?->email) }}" required></div>
            <div class="span-4"><label class="floating-label-activo-sm">Clave</label><input class="form-control form-control-sm" name="password" placeholder="{{ $clienteEditar ? 'Nueva clave opcional' : 'Clave temporal segura' }}" {{ $clienteEditar ? '' : 'required' }}></div>
            <div class="span-4"><label class="floating-label-activo-sm">Telefono</label><input class="form-control form-control-sm" name="telefono" value="{{ old('telefono', $clienteEditar?->telefono) }}"></div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Planes</label>
                <select name="plan_preferido" class="plan-select form-control form-control-sm">
                    @foreach(['sin_plan' => 'Sin plan', 'mensual' => 'Mensual', 'semanal' => 'Semanal', 'quincenal' => 'Quincenal', 'vip' => 'Cliente VIP'] as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('plan_preferido', $clienteEditar?->plan_preferido ?? 'sin_plan') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Activo</label>
                <select class="form-control form-control-sm" name="activo">
                    <option value="1" @selected(old('activo', $clienteEditar?->activo ?? true))>Si</option>
                    <option value="0" @selected(!old('activo', $clienteEditar?->activo ?? true))>No</option>
                </select>
            </div>
            <div class="span-4 inline-actions">
                <a class="btn pet-link" href="{{ route('admin.mascotas.create') }}">Inscribir mascotas</a>
            </div>

            <div class="form-divider">Encuesta comercial de vouchers</div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Recibe voucher</label>
                <select class="form-control form-control-sm" name="recibe_voucher">
                    <option value="1" @selected(old('recibe_voucher', $clienteEditar?->recibe_voucher ?? false))>Si recibe voucher</option>
                    <option value="0" @selected(!old('recibe_voucher', $clienteEditar?->recibe_voucher ?? false))>No recibe voucher</option>
                </select>
            </div>
            <div class="span-3"><label class="floating-label-activo-sm">Descuento esperado (%)</label><input class="form-control form-control-sm" type="number" name="porcentaje_descuento_voucher" min="0" max="100" value="{{ old('porcentaje_descuento_voucher', $clienteEditar?->porcentaje_descuento_voucher) }}"></div>
            <div class="span-6">
                <label class="floating-label-activo-sm">
                    Opinion sistema nacional tipo FONAVET
                    <span class="info-tip" tabindex="0" data-tip="FONAVET: Fondo Nacional Veterinario. Propuesta: desde $6.990 mensual por mascota, con vouchers, recordatorios sanitarios, descuentos en profesionales adheridos, historial clinico y beneficios en alimento o servicios.">i</span>
                </label>
                <select class="form-control form-control-sm" name="encuesta_sistema_nacional">
                    <option value="">Sin respuesta</option>
                    <option value="muy_interesante" @selected(old('encuesta_sistema_nacional', $clienteEditar?->encuesta_sistema_nacional) === 'muy_interesante')>Muy interesante</option>
                    <option value="interesante" @selected(old('encuesta_sistema_nacional', $clienteEditar?->encuesta_sistema_nacional) === 'interesante')>Interesante</option>
                    <option value="neutral" @selected(old('encuesta_sistema_nacional', $clienteEditar?->encuesta_sistema_nacional) === 'neutral')>Neutral</option>
                    <option value="poco_interesante" @selected(old('encuesta_sistema_nacional', $clienteEditar?->encuesta_sistema_nacional) === 'poco_interesante')>Poco interesante</option>
                    <option value="no_interesa" @selected(old('encuesta_sistema_nacional', $clienteEditar?->encuesta_sistema_nacional) === 'no_interesa')>No le interesa</option>
                </select>
                <span class="field-help">Propuesta: desde $6.990 mensual por mascota. Debe ofrecer vouchers, descuentos, recordatorios sanitarios, historial clinico y beneficios en alimento o servicios.</span>
            </div>
            <div class="span-12"><label class="floating-label-activo-sm">Comentario encuesta</label><textarea class="form-control form-control-sm" name="comentario_sistema_nacional">{{ old('comentario_sistema_nacional', $clienteEditar?->comentario_sistema_nacional) }}</textarea></div>

            <div class="form-divider">Direccion principal</div>
            <div class="span-6"><label class="floating-label-activo-sm">Direccion</label><input class="form-control form-control-sm" id="direccion_principal" name="direccion_principal" value="{{ old('direccion_principal', $direccion?->direccion ?? $clienteEditar?->direccion) }}"></div>
            <div class="span-3"><label class="floating-label-activo-sm">Comuna</label><input class="form-control form-control-sm" name="comuna" value="{{ old('comuna', $direccion?->comuna) }}"></div>
            <div class="span-3"><label class="floating-label-activo-sm">Referencia</label><input class="form-control form-control-sm" name="referencia" value="{{ old('referencia', $direccion?->referencia) }}"></div>
            <input id="georeferencia_url" type="hidden" name="georeferencia_url" value="{{ old('georeferencia_url', $clienteEditar?->georeferencia_url) }}">
            <div class="map-panel">
                <div class="map-actions">
                    <button type="button" class="btn" id="activar-mapa">Activar mapa por direccion</button>
                    <a class="btn btn-secondary" id="abrir-mapa" href="{{ old('georeferencia_url', $clienteEditar?->georeferencia_url) ?: 'https://www.google.com/maps' }}" target="_blank" rel="noopener">Abrir mapa</a>
                    <span class="muted">La georreferencia se genera usando direccion y comuna.</span>
                </div>
                <iframe class="map-frame" id="mapa-cliente" src=""></iframe>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('admin.clientes.index') }}">Cancelar</a>
            <button class="btn-success">{{ $clienteEditar ? 'Guardar cambios' : 'Crear cliente' }}</button>
        </div>
    </form>
</div>
<script>
    const direccionInput = document.getElementById('direccion_principal');
    const comunaInput = document.querySelector('input[name="comuna"]');
    const georefInput = document.getElementById('georeferencia_url');
    const mapFrame = document.getElementById('mapa-cliente');
    const mapLink = document.getElementById('abrir-mapa');
    const mapButton = document.getElementById('activar-mapa');

    function actualizarMapaCliente() {
        const texto = [direccionInput.value, comunaInput.value].filter(Boolean).join(' ').trim();
        if (!texto) {
            mapFrame.removeAttribute('src');
            mapLink.setAttribute('href', 'https://www.google.com/maps');
            georefInput.value = '';
            return;
        }
        const query = encodeURIComponent(texto);
        const url = 'https://www.google.com/maps/search/?api=1&query=' + query;
        georefInput.value = url;
        mapLink.setAttribute('href', url);
        mapFrame.setAttribute('src', 'https://maps.google.com/maps?q=' + query + '&output=embed');
    }

    mapButton.addEventListener('click', actualizarMapaCliente);
    if (georefInput.value || direccionInput.value) {
        actualizarMapaCliente();
    }
</script>
@endsection
