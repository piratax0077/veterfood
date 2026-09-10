@extends('layouts.app')

@section('title', 'Instituciones contables')

@section('content')
<style>
    .selector-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:20px}
    .selector-title{display:flex;align-items:center;gap:12px;margin:0;color:#061a3d;font-size:34px}
    .selector-icon{width:44px;height:44px;border-radius:10px;background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900}
    .selector-intro,.institution-card{background:#fff;border:1px solid #dbe3ee;border-radius:12px;padding:20px;box-shadow:0 3px 10px rgba(15,23,42,.07)}
    .selector-intro p{max-width:900px;line-height:1.5;margin-bottom:0}
    .selector-intro{margin-bottom:16px}.institution-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
    .institution-card{display:flex;flex-direction:column;gap:12px;min-height:220px}.institution-card h2{margin:0;color:#061a3d}.institution-card p{margin:0;color:#52617a;line-height:1.35}
    .institution-card.has-alert{border-color:#f59e0b;box-shadow:0 10px 24px rgba(245,158,11,.18)}
    .institution-card.has-urgent{border-color:#dc2626;box-shadow:0 10px 24px rgba(220,38,38,.20)}
    .badge-row{display:flex;gap:8px;flex-wrap:wrap}.badge{display:inline-flex;border-radius:999px;background:#dbeafe;color:#1e3a8a;padding:6px 10px;font-weight:900;font-size:12px}
    .badge-alert{background:#fef3c7;color:#92400e}.badge-urgent{background:#fee2e2;color:#991b1b}.badge-ready{background:#dcfce7;color:#166534}
    .enter-btn{margin-top:auto;background:#2563eb;color:#fff;text-decoration:none;text-align:center;border-radius:7px;padding:12px;font-weight:900}
    .empty-state{background:#fff;border:1px dashed #94a3b8;border-radius:8px;padding:28px;text-align:center;color:#475569}
    .selector-actions{display:block;margin:18px 0}.new-client-panel{background:#fff;border:1px solid #dbe3ee;border-radius:12px;padding:22px;box-shadow:0 3px 10px rgba(15,23,42,.07);width:100%}
    .client-form{display:none;margin-top:20px;padding-top:20px;border-top:1px solid #e2e8f0}.client-form.is-open{display:block}.client-form-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:16px}.client-form-grid>div{position:relative;min-width:0}.client-form-grid .floating-label-activo-sm{position:static!important;display:block!important;background:transparent!important;color:#334155!important;margin:0 0 7px!important;padding:0!important;font-size:13px!important}.client-form-grid .form-control{width:100%;min-height:42px}.field-3{grid-column:span 3}.field-4{grid-column:span 4}.field-6{grid-column:span 6}.field-8{grid-column:span 8}.field-12{grid-column:span 12}
    .form-note{color:#64748b;font-size:14px;line-height:1.35}.money-hint{font-size:12px;color:#64748b;margin-top:4px}
    @media(max-width:1000px){.institution-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.field-3,.field-4{grid-column:span 6}.field-8{grid-column:span 12}}
    @media(max-width:650px){.institution-grid{grid-template-columns:1fr}.selector-title{font-size:28px}.field-3,.field-4,.field-6,.field-8{grid-column:span 12}.selector-actions{display:block}}
</style>

<div class="selector-head">
    <a class="btn btn-secondary" href="{{ route('redirect.role') }}">Volver</a>
    <h1 class="selector-title"><span class="selector-icon">C</span>Mis instituciones contables</h1>
    @if($user->tieneRol('admin'))
        <a class="btn" href="{{ route('admin.contabilidad.integracion') }}">Integracion API</a>
    @endif
</div>

<div class="selector-intro">
    <h2>Seleccione la institucion a trabajar</h2>
    <p class="muted">Cada contador entra solo a las instituciones donde tiene contrato, membresia activa o permisos contables. Los datos quedan separados por centro y la API usa el mismo control de acceso.</p>
</div>


<div class="selector-actions">
    <section class="new-client-panel">
        <div class="between">
            <div>
                <h2>Agregar cliente contable</h2>
                <p class="form-note">Registra la empresa, representante, acceso tributario cifrado, contacto, sucursales y valor pactado del servicio.</p>
            </div>
            <button type="button" id="toggleClientForm">Agregar nuevo cliente</button>
        </div>
        <form class="client-form" id="clientForm" method="POST" action="{{ route('contabilidad.instituciones.store') }}">
            @csrf
            <div class="client-form-grid">
                <div class="field-3"><label class="floating-label-activo-sm">RUT empresa</label><input class="form-control form-control-sm" name="rut" value="{{ old('rut') }}" required></div>
                <div class="field-3"><label class="floating-label-activo-sm">RUT representante</label><input class="form-control form-control-sm" name="rut_representante_legal" value="{{ old('rut_representante_legal') }}"></div>
                <div class="field-6"><label class="floating-label-activo-sm">Representante legal</label><input class="form-control form-control-sm" name="representante_legal" value="{{ old('representante_legal') }}"></div>
                <div class="field-6"><label class="floating-label-activo-sm">Nombre empresa / razon social</label><input class="form-control form-control-sm" name="razon_social" value="{{ old('razon_social') }}" required></div>
                <div class="field-6"><label class="floating-label-activo-sm">Nombre fantasia</label><input class="form-control form-control-sm" name="nombre_fantasia" value="{{ old('nombre_fantasia') }}"></div>
                <div class="field-8"><label class="floating-label-activo-sm">Giro empresa</label><input class="form-control form-control-sm" name="giro" value="{{ old('giro') }}"></div>
                <div class="field-4"><label class="floating-label-activo-sm">Valor pactado mensual</label><input class="form-control form-control-sm" type="number" name="valor_pactado_servicio" min="0" step="1" value="{{ old('valor_pactado_servicio', 0) }}"><div class="money-hint">Monto acordado por servicio contable.</div></div>
                <div class="field-8"><label class="floating-label-activo-sm">Direccion</label><input class="form-control form-control-sm" name="direccion" value="{{ old('direccion') }}"></div>
                <div class="field-4"><label class="floating-label-activo-sm">Comuna</label><input class="form-control form-control-sm" name="comuna" value="{{ old('comuna') }}"></div>
                <div class="field-4"><label class="floating-label-activo-sm">Region</label><input class="form-control form-control-sm" name="region" value="{{ old('region') }}"></div>
                <div class="field-4"><label class="floating-label-activo-sm">Telefono</label><input class="form-control form-control-sm" name="telefono" value="{{ old('telefono') }}"></div>
                <div class="field-4"><label class="floating-label-activo-sm">Email</label><input class="form-control form-control-sm" type="email" name="email" value="{{ old('email') }}"></div>
                <div class="field-6"><label class="floating-label-activo-sm">Contacto comercial</label><input class="form-control form-control-sm" name="contacto_comercial" value="{{ old('contacto_comercial') }}"></div>
                <div class="field-6"><label class="floating-label-activo-sm">Clave Serv. Impuestos</label><input class="form-control form-control-sm" type="password" name="clave_serv_impuestos" autocomplete="new-password"><div class="money-hint">Se guarda cifrada.</div></div>
                <div class="field-12"><label class="floating-label-activo-sm">Sucursales / comercios asociados</label><textarea class="form-control form-control-sm" name="sucursales_texto" rows="3" placeholder="Una sucursal por linea">{{ old('sucursales_texto') }}</textarea></div>
                <div class="field-12"><label class="floating-label-activo-sm">Observaciones</label><textarea class="form-control form-control-sm" name="observaciones" rows="2">{{ old('observaciones') }}</textarea></div>
            </div>
            <div class="row" style="margin-top:14px">
                <button type="submit" class="btn-success">Guardar cliente contable</button>
                <button type="button" class="btn-secondary" id="cancelClientForm">Cancelar</button>
            </div>
        </form>
    </section>
</div>

@if($centros->isEmpty())
    <div class="empty-state">
        <h2>Sin instituciones asignadas</h2>
        <p>Este usuario contador aun no tiene instituciones activas asociadas. Un administrador debe vincularlo al centro correspondiente.</p>
    </div>
@else
    <div class="institution-grid">
        @foreach($centros as $centro)
            @php
                $permisosRaw = data_get($centro, 'pivot.permisos', '[]');
                $permisos = collect(is_array($permisosRaw) ? $permisosRaw : (json_decode($permisosRaw ?: '[]', true) ?: []));
                $rolContrato = data_get($centro, 'pivot.rol') ?? ($user->tieneRol('admin') ? 'administrador' : 'contador');
                $estadoRelacion = data_get($centro, 'pivot.estado_relacion', $user->tieneRol('admin') ? 'activo' : 'pendiente_admin');
                $relacionActiva = (bool) data_get($centro, 'pivot.activo', $user->tieneRol('admin'));
                $pendientes = (int) ($centro->requerimientos_abiertos_count ?? 0);
                $firma = (int) ($centro->requerimientos_firma_count ?? 0);
            @endphp
            <article class="institution-card {{ $pendientes > 0 ? ($pendientes >= 3 ? 'has-urgent' : 'has-alert') : '' }}">
                <h2>{{ $centro->nombre_fantasia ?: $centro->razon_social }}</h2>
                <p><strong>RUT:</strong> {{ $centro->rut }}</p>
                <p><strong>Giro:</strong> {{ $centro->giro ?: 'No informado' }}</p>
                <p><strong>Direccion:</strong> {{ $centro->direccion ?: 'No informada' }} {{ $centro->comuna ? ', '.$centro->comuna : '' }}</p>
                <p><strong>Valor pactado:</strong> ${{ number_format((int) ($centro->valor_pactado_servicio ?? 0), 0, ',', '.') }}</p>
                <div class="badge-row">
                    <span class="badge">{{ $rolContrato }}</span>
                    @if(! $relacionActiva)
                        <span class="badge badge-alert">
                            {{ $estadoRelacion === 'pendiente_contador' ? 'pendiente aceptacion contador' : 'pendiente aprobacion admin' }}
                        </span>
                    @endif
                    @foreach($permisos->take(3) as $permiso)
                        <span class="badge">{{ $permiso }}</span>
                    @endforeach
                    @if($permisos->isEmpty() && $user->tieneRol('admin'))
                        <span class="badge">acceso total</span>
                    @endif
                    @if($pendientes > 0)
                        <span class="badge {{ $pendientes >= 3 ? 'badge-urgent' : 'badge-alert' }}">{{ $pendientes }} requerimientos</span>
                    @endif
                    @if($firma > 0)
                        <span class="badge badge-ready">{{ $firma }} por firmar</span>
                    @endif
                </div>
                @if($relacionActiva || $user->tieneRol('admin'))
                    <a class="enter-btn" href="{{ route('contabilidad.escritorio', ['centroMedico' => $centro->id]) }}">Entrar al escritorio contable</a>
                @elseif($estadoRelacion === 'pendiente_contador')
                    <form method="POST" action="{{ route('contabilidad.instituciones.aceptar', ['centroMedico' => $centro->id]) }}" class="inline-form" style="margin-top:auto">
                        @csrf
                        <button type="submit" class="enter-btn" style="width:100%">Aceptar relacion y activar</button>
                    </form>
                @else
                    <span class="enter-btn" style="background:#e5e7eb;color:#334155">Esperando aprobacion administracion</span>
                @endif
            </article>
        @endforeach
    </div>
@endif
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('clientForm');
    const open = document.getElementById('toggleClientForm');
    const cancel = document.getElementById('cancelClientForm');
    if (!form || !open) return;
    const show = () => {
        form.classList.add('is-open');
        open.textContent = 'Ocultar formulario';
    };
    const hide = () => {
        form.classList.remove('is-open');
        open.textContent = 'Agregar nuevo cliente';
    };
    open.addEventListener('click', () => form.classList.contains('is-open') ? hide() : show());
    cancel?.addEventListener('click', hide);
    @if($errors->any())
        show();
    @endif
});
</script>
@endsection
