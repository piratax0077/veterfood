@extends('layouts.app')

@section('title', $localEditar ? 'Editar lugar de venta' : 'Crear lugar de venta')

@section('content')
<style>
    .local-form-head{display:grid;grid-template-columns:170px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 20px}
    .local-form-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .local-icon{width:38px;height:38px;border-radius:12px;background:#0f766e;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:22px;box-shadow:0 4px 10px rgba(15,23,42,.18)}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .local-form-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .span-12{grid-column:span 12}.span-8{grid-column:span 8}.span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}
    .form-divider{grid-column:span 12;border-top:1px solid #dbe3ee;margin:10px 0 2px;padding-top:16px;font-size:20px;font-weight:900;color:#111827}
    .map-panel{grid-column:span 12;border:1px solid #dbe3ee;border-radius:8px;background:#f8fafc;padding:14px;margin-top:6px}
    .map-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:12px}
    .map-frame{width:100%;height:260px;border:0;border-radius:8px;background:#e5e7eb}
    .form-actions{display:flex;justify-content:center;gap:12px;margin-top:18px}
    .adaptive-note{grid-column:span 12;border:1px solid #99f6e4;border-radius:8px;background:#f0fdfa;color:#115e59;padding:12px 14px;font-weight:700}
    .service-options{grid-column:span 12;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.service-option{display:flex;align-items:center;gap:9px;padding:12px;border:1px solid #dbe3ee;border-radius:8px;background:#f8fafc;font-weight:700}.service-option input{width:17px;height:17px}.service-option.is-hidden{display:none}
    .conditional-field.is-hidden{display:none}
    @media(max-width:900px){.local-form-head{grid-template-columns:1fr}.local-form-title{font-size:28px}.span-12,.span-8,.span-6,.span-4,.span-3{grid-column:span 12}.service-options{grid-template-columns:1fr 1fr}}
    @media(max-width:560px){.service-options{grid-template-columns:1fr}}
</style>

<div class="local-form-head">
    <a class="btn btn-secondary" href="{{ route('admin.locales.index') }}">Volver</a>
    <h1 class="local-form-title"><span class="local-icon">L</span>{{ $localEditar ? 'Editar lugar de venta' : 'Formulario de lugar de venta' }}</h1>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <form method="POST" action="{{ $localEditar ? route('admin.locales.update', $localEditar) : route('admin.locales.store') }}">
        @csrf
        @if($localEditar)
            @method('PATCH')
        @endif

        <div class="local-form-grid">
            <div class="form-divider">Datos comerciales</div>
            <div class="span-6"><label class="floating-label-activo-sm">Nombre visible</label><input class="form-control form-control-sm" name="nombre" value="{{ old('nombre', $localEditar?->nombre) }}" required></div>
            <div class="span-3"><label class="floating-label-activo-sm">Codigo interno</label><input class="form-control form-control-sm" name="codigo" value="{{ old('codigo', $localEditar?->codigo) }}" required></div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Tipo</label>
                <select class="form-control form-control-sm" id="tipo_local" name="tipo" required>
                    @foreach($tiposLocal as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('tipo', $localEditar?->tipo ?? 'sucursal') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-3"><label class="floating-label-activo-sm">RUT comercio</label><input class="form-control form-control-sm" name="rut" value="{{ old('rut', $localEditar?->rut) }}"></div>
            <div class="span-6"><label class="floating-label-activo-sm">Razon social</label><input class="form-control form-control-sm" name="razon_social" value="{{ old('razon_social', $localEditar?->razon_social) }}"></div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Activo</label>
                <select class="form-control form-control-sm" name="activo">
                    <option value="1" @selected(old('activo', $localEditar?->activo ?? true))>Si</option>
                    <option value="0" @selected(!old('activo', $localEditar?->activo ?? true))>No</option>
                </select>
            </div>

            <div class="form-divider">Contacto y operacion</div>
            <div class="span-4"><label class="floating-label-activo-sm">Correo</label><input class="form-control form-control-sm" type="email" name="email" value="{{ old('email', $localEditar?->email) }}"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Telefono</label><input class="form-control form-control-sm" name="telefono" value="{{ old('telefono', $localEditar?->telefono) }}"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Responsable local</label><input class="form-control form-control-sm" name="responsable" value="{{ old('responsable', $localEditar?->responsable) }}"></div>
            <div class="span-6"><label class="floating-label-activo-sm">Contacto comercial / convenio</label><input class="form-control form-control-sm" name="contacto_comercial" value="{{ old('contacto_comercial', $localEditar?->contacto_comercial) }}"></div>
            <div class="span-6"><label class="floating-label-activo-sm">Observaciones</label><textarea class="form-control form-control-sm" name="observaciones">{{ old('observaciones', $localEditar?->observaciones) }}</textarea></div>

            @php($serviciosSeleccionados = old('servicios_ofrecidos', $localEditar?->servicios_ofrecidos ?? []))
            <div class="form-divider">Convenio y servicios del lugar</div>
            <div class="adaptive-note" id="perfil_tipo_local">El formulario se ajustara al tipo de lugar seleccionado.</div>
            <div class="span-4 conditional-field" id="campo_modalidad">
                <label class="floating-label-activo-sm">Modalidad de convenio</label>
                <select class="form-control form-control-sm" id="modalidad_convenio" name="modalidad_convenio" data-selected="{{ old('modalidad_convenio', $localEditar?->modalidad_convenio) }}"></select>
            </div>
            <div class="span-8"><label class="floating-label-activo-sm">Condiciones comerciales u operativas</label><textarea class="form-control form-control-sm" name="condiciones_convenio" placeholder="Comision, valor mensual, condiciones de pago, cobertura o restricciones">{{ old('condiciones_convenio', $localEditar?->condiciones_convenio) }}</textarea></div>
            <div class="span-12"><strong>Servicios que ofrece</strong></div>
            <div class="service-options" id="servicios_tipo_local">
                @foreach(['alimentos'=>'Venta de alimentos','farmacia'=>'Farmacia veterinaria','atencion_veterinaria'=>'Atencion veterinaria','peluqueria'=>'Peluqueria','hotel_guarderia'=>'Hotel o guarderia','retiro'=>'Punto de retiro','despacho'=>'Despacho a domicilio','marketplace'=>'Venta en marketplace'] as $valorServicio => $nombreServicio)
                    <label class="service-option" data-service="{{ $valorServicio }}"><input type="checkbox" name="servicios_ofrecidos[]" value="{{ $valorServicio }}" @checked(in_array($valorServicio, $serviciosSeleccionados, true))><span>{{ $nombreServicio }}</span></label>
                @endforeach
            </div>
            <div class="span-4"><label class="floating-label-activo-sm">¿Publicar en pagina de ofertas?</label><select class="form-control form-control-sm" id="publica_ofertas" name="publica_ofertas"><option value="0" @selected(!old('publica_ofertas', $localEditar?->publica_ofertas ?? false))>No publicar</option><option value="1" @selected(old('publica_ofertas', $localEditar?->publica_ofertas ?? false))>Si, incluir en ofertas</option></select></div>
            <div class="span-8 conditional-field" id="campo_url_ofertas"><label class="floating-label-activo-sm">Pagina o enlace externo de ofertas (opcional)</label><input class="form-control form-control-sm" type="url" name="url_ofertas" value="{{ old('url_ofertas', $localEditar?->url_ofertas) }}" placeholder="https://..."></div>

            <div class="form-divider">Ubicacion</div>
            <div class="span-8"><label class="floating-label-activo-sm">Direccion</label><input class="form-control form-control-sm" id="direccion_local" name="direccion" value="{{ old('direccion', $localEditar?->direccion) }}" required></div>
            <div class="span-4"><label class="floating-label-activo-sm">Comuna</label><input class="form-control form-control-sm" name="comuna" value="{{ old('comuna', $localEditar?->comuna) }}"></div>
            <input id="georeferencia_url" type="hidden" name="georeferencia_url" value="{{ old('georeferencia_url', $localEditar?->georeferencia_url) }}">
            <div class="map-panel">
                <div class="map-actions">
                    <button type="button" class="btn" id="activar-mapa">Activar mapa por direccion</button>
                    <a class="btn btn-secondary" id="abrir-mapa" href="{{ old('georeferencia_url', $localEditar?->georeferencia_url) ?: 'https://www.google.com/maps' }}" target="_blank" rel="noopener">Abrir mapa</a>
                    <span class="muted">Se usa para despacho, retiro en tienda y control de comercios adheridos.</span>
                </div>
                <iframe class="map-frame" id="mapa-local" src=""></iframe>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('admin.locales.index') }}">Cancelar</a>
            <button class="btn-success">{{ $localEditar ? 'Guardar cambios' : 'Crear lugar de venta' }}</button>
        </div>
    </form>
</div>

<script>
    const tipoLocal = document.getElementById('tipo_local');
    const modalidadConvenio = document.getElementById('modalidad_convenio');
    const perfilTipoLocal = document.getElementById('perfil_tipo_local');
    const publicaOfertas = document.getElementById('publica_ofertas');
    const campoUrlOfertas = document.getElementById('campo_url_ofertas');
    const modalidadSeleccionada = modalidadConvenio.dataset.selected;
    const perfiles = {
        sucursal: {texto:'Sucursal propia: operacion, stock, venta, retiro y despacho administrados directamente.', modalidades:{operacion_propia:'Operacion propia',venta_directa:'Venta directa'}, servicios:['alimentos','farmacia','atencion_veterinaria','peluqueria','hotel_guarderia','retiro','despacho']},
        comercio_adherido: {texto:'Comercio adherido: defina comision, pago mensual o un esquema mixto.', modalidades:{comision_venta:'Comision por ventas',pago_mensual:'Pago mensual',mixto:'Mensual mas comision'}, servicios:['alimentos','farmacia','retiro','despacho','marketplace']},
        punto_retiro: {texto:'Punto de retiro: registre la forma de pago por entrega o mensual.', modalidades:{comision_entrega:'Pago por entrega',pago_mensual:'Pago mensual',mixto:'Mensual mas entrega'}, servicios:['retiro','despacho']},
        farmacia: {texto:'Farmacia asociada: configure el convenio comercial y los productos disponibles.', modalidades:{comision_venta:'Comision por ventas',pago_mensual:'Pago mensual',mixto:'Mensual mas comision'}, servicios:['farmacia','alimentos','retiro','despacho']},
        clinica_veterinaria: {texto:'Clinica o veterinaria: defina prestaciones, farmacia y beneficios publicados.', modalidades:{comision_venta:'Comision por servicios',pago_mensual:'Pago mensual',mixto:'Mensual mas comision'}, servicios:['atencion_veterinaria','farmacia','peluqueria','hotel_guarderia']},
        marketplace: {texto:'Convenio o marketplace: determine comision, catalogo y publicacion de ofertas.', modalidades:{marketplace:'Comision marketplace',pago_mensual:'Pago mensual',mixto:'Mensual mas comision'}, servicios:['marketplace','alimentos','farmacia','atencion_veterinaria']},
        otro: {texto:'Otro tipo de lugar: seleccione solo las prestaciones que correspondan.', modalidades:{otro:'Acuerdo personalizado'}, servicios:['alimentos','farmacia','atencion_veterinaria','peluqueria','hotel_guarderia','retiro','despacho','marketplace']}
    };

    function adaptarFormularioLocal() {
        const perfil = perfiles[tipoLocal.value] || perfiles.otro;
        perfilTipoLocal.textContent = perfil.texto;
        const valorActual = modalidadConvenio.value || modalidadSeleccionada;
        modalidadConvenio.innerHTML = '<option value="">Seleccione modalidad</option>';
        Object.entries(perfil.modalidades).forEach(([valor, texto]) => {
            const opcion = new Option(texto, valor, false, valor === valorActual);
            modalidadConvenio.add(opcion);
        });
        document.querySelectorAll('.service-option').forEach(opcion => {
            const visible = perfil.servicios.includes(opcion.dataset.service);
            opcion.classList.toggle('is-hidden', !visible);
            const checkbox = opcion.querySelector('input');
            checkbox.disabled = !visible;
            if (!visible) checkbox.checked = false;
        });
    }

    function adaptarOfertas() {
        const mostrar = publicaOfertas.value === '1';
        campoUrlOfertas.classList.toggle('is-hidden', !mostrar);
    }

    tipoLocal.addEventListener('change', adaptarFormularioLocal);
    publicaOfertas.addEventListener('change', adaptarOfertas);
    adaptarFormularioLocal();
    adaptarOfertas();

    const direccionInput = document.getElementById('direccion_local');
    const comunaInput = document.querySelector('input[name="comuna"]');
    const georefInput = document.getElementById('georeferencia_url');
    const mapFrame = document.getElementById('mapa-local');
    const mapLink = document.getElementById('abrir-mapa');
    const mapButton = document.getElementById('activar-mapa');

    function actualizarMapaLocal() {
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

    mapButton.addEventListener('click', actualizarMapaLocal);
    if (georefInput.value || direccionInput.value) {
        actualizarMapaLocal();
    }
</script>
@endsection
