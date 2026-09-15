{{--
    Campos del lugar de venta, agrupados en pasos. Lo usan el modal "Crear lugar de venta" (con asistente por pasos)
    y la pagina de edicion (todas las secciones seguidas).
    Recibe $localEditar (null al crear), $tiposLocal y opcional $conPasos (true = asistente).
    Estilos: admin-formularios.css + wizard.css · Comportamiento: public/js/form-local.js · Mapa: public/js/mapa-direccion.js
--}}
@php
    $conError = fn (string $campo) => $errors->has($campo) ? 'has-error' : '';
    $serviciosSeleccionados = old('servicios_ofrecidos', $localEditar?->servicios_ofrecidos ?? []);
    $servicios = ['alimentos' => 'Venta de alimentos', 'farmacia' => 'Farmacia veterinaria', 'atencion_veterinaria' => 'Atención veterinaria', 'peluqueria' => 'Peluquería', 'hotel_guarderia' => 'Hotel o guardería', 'retiro' => 'Punto de retiro', 'despacho' => 'Despacho a domicilio', 'marketplace' => 'Venta en marketplace'];
    $urlMapa = old('georeferencia_url', $localEditar?->georeferencia_url);
@endphp

<div class="wizard" @if($conPasos ?? false) data-wizard @endif data-form-local>
    <section class="wizard-paso" data-titulo="Datos comerciales">
        <div class="campos">
            <div class="form-divider">Datos comerciales</div>
            <div class="span-6 {{ $conError('nombre') }}">
                <label class="floating-label-activo-sm">Nombre visible</label>
                <input class="form-control form-control-sm" name="nombre" value="{{ old('nombre', $localEditar?->nombre) }}" maxlength="255" required>
                @error('nombre')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-3 {{ $conError('codigo') }}">
                <label class="floating-label-activo-sm">Código interno</label>
                <input class="form-control form-control-sm" name="codigo" value="{{ old('codigo', $localEditar?->codigo) }}" maxlength="40" required>
                @error('codigo')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Tipo</label>
                <select class="form-control form-control-sm" name="tipo" required data-local-tipo>
                    @foreach($tiposLocal as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('tipo', $localEditar?->tipo ?? 'sucursal') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-3"><label class="floating-label-activo-sm">RUT comercio</label><input class="form-control form-control-sm" name="rut" value="{{ old('rut', $localEditar?->rut) }}" maxlength="40" placeholder="12.345.678-9"></div>
            <div class="span-6"><label class="floating-label-activo-sm">Razón social</label><input class="form-control form-control-sm" name="razon_social" value="{{ old('razon_social', $localEditar?->razon_social) }}" maxlength="255"></div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Activo</label>
                <select class="form-control form-control-sm" name="activo">
                    <option value="1" @selected(old('activo', $localEditar?->activo ?? true))>Sí</option>
                    <option value="0" @selected(!old('activo', $localEditar?->activo ?? true))>No</option>
                </select>
            </div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Contacto">
        <div class="campos">
            <div class="form-divider">Contacto y operación</div>
            <div class="span-4 {{ $conError('email') }}">
                <label class="floating-label-activo-sm">Correo</label>
                <input class="form-control form-control-sm" type="email" name="email" value="{{ old('email', $localEditar?->email) }}" maxlength="255">
                @error('email')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4 {{ $conError('telefono') }}">
                <x-campo-telefono name="telefono" :value="old('telefono', $localEditar?->telefono)" />
                @error('telefono')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4"><label class="floating-label-activo-sm">Responsable local</label><input class="form-control form-control-sm" name="responsable" value="{{ old('responsable', $localEditar?->responsable) }}" maxlength="255"></div>
            <div class="span-6"><label class="floating-label-activo-sm">Contacto comercial / convenio</label><input class="form-control form-control-sm" name="contacto_comercial" value="{{ old('contacto_comercial', $localEditar?->contacto_comercial) }}" maxlength="255"></div>
            <div class="span-6"><label class="floating-label-activo-sm">Observaciones</label><textarea class="form-control form-control-sm" name="observaciones" maxlength="2000">{{ old('observaciones', $localEditar?->observaciones) }}</textarea></div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Convenio y servicios">
        <div class="campos">
            <div class="form-divider">Convenio y servicios del lugar</div>
            <p class="nota-formulario" data-local-nota>El formulario se ajusta al tipo de lugar seleccionado.</p>
            <div class="span-4 {{ $conError('modalidad_convenio') }}">
                <label class="floating-label-activo-sm">Modalidad de convenio</label>
                <select class="form-control form-control-sm" name="modalidad_convenio" data-local-modalidad data-selected="{{ old('modalidad_convenio', $localEditar?->modalidad_convenio) }}"></select>
                @error('modalidad_convenio')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-8"><label class="floating-label-activo-sm">Condiciones comerciales u operativas</label><textarea class="form-control form-control-sm" name="condiciones_convenio" maxlength="2000" placeholder="Comisión, valor mensual, condiciones de pago, cobertura o restricciones">{{ old('condiciones_convenio', $localEditar?->condiciones_convenio) }}</textarea></div>
            <p class="titulo-opciones">Servicios que ofrece</p>
            <div class="opciones-casilla">
                @foreach($servicios as $valor => $nombre)
                    <label class="opcion-casilla" data-local-servicio="{{ $valor }}"><input type="checkbox" name="servicios_ofrecidos[]" value="{{ $valor }}" @checked(in_array($valor, $serviciosSeleccionados, true))><span>{{ $nombre }}</span></label>
                @endforeach
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">¿Publicar en página de ofertas?</label>
                <select class="form-control form-control-sm" name="publica_ofertas" data-local-ofertas>
                    <option value="0" @selected(!old('publica_ofertas', $localEditar?->publica_ofertas ?? false))>No publicar</option>
                    <option value="1" @selected(old('publica_ofertas', $localEditar?->publica_ofertas ?? false))>Sí, incluir en ofertas</option>
                </select>
            </div>
            <div class="span-8 campo-condicional {{ $conError('url_ofertas') }}" data-local-campo-url>
                <label class="floating-label-activo-sm">Página o enlace externo de ofertas (opcional)</label>
                <input class="form-control form-control-sm" type="url" name="url_ofertas" value="{{ old('url_ofertas', $localEditar?->url_ofertas) }}" placeholder="https://...">
                @error('url_ofertas')<small class="field-error">{{ $message }}</small>@enderror
            </div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Ubicación">
        <div class="campos">
            <div class="form-divider">Ubicación</div>
            <div class="span-8 {{ $conError('direccion') }}">
                <label class="floating-label-activo-sm">Dirección</label>
                <input class="form-control form-control-sm" name="direccion" value="{{ old('direccion', $localEditar?->direccion) }}" maxlength="500" required data-mapa-direccion>
                @error('direccion')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4"><label class="floating-label-activo-sm">Comuna</label><input class="form-control form-control-sm" name="comuna" value="{{ old('comuna', $localEditar?->comuna) }}" maxlength="120" data-mapa-comuna></div>
            <input type="hidden" name="georeferencia_url" value="{{ $urlMapa }}" data-mapa-url>
            <div class="map-panel">
                <div class="map-actions">
                    <button type="button" class="tabla-accion tono-azul" data-mapa-activar><x-icono nombre="locacion" />Activar mapa por dirección</button>
                    <a class="tabla-accion tono-gris" href="{{ $urlMapa ?: 'https://www.google.com/maps' }}" target="_blank" rel="noopener" data-mapa-abrir><x-icono nombre="seguimiento" />Abrir mapa</a>
                    <span class="muted">Se usa para despacho, retiro en tienda y control de comercios adheridos.</span>
                </div>
                <iframe class="map-frame" title="Mapa del lugar" loading="lazy" data-mapa-frame></iframe>
            </div>
        </div>
    </section>
</div>
<script src="{{ asset('js/form-local.js') }}?v={{ filemtime(public_path('js/form-local.js')) }}" defer></script>
