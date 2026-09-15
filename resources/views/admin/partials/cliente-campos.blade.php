{{--
    Campos del cliente, agrupados en pasos. Lo usan el modal "Crear cliente" (con asistente por pasos)
    y la pagina de edicion (todas las secciones seguidas, con el boton "Inscribir mascotas").
    Recibe $clienteEditar (null al crear) y opcional $conPasos (true = asistente).
    Estilos: admin-formularios.css + wizard.css · Mapa: public/js/mapa-direccion.js
--}}
@php
    $conError = fn (string $campo) => $errors->has($campo) ? 'has-error' : '';
    $direccion = $clienteEditar?->direcciones?->firstWhere('principal', true) ?? $clienteEditar?->direcciones?->first();
    $planes = ['sin_plan' => 'Sin plan', 'mensual' => 'Mensual', 'semanal' => 'Semanal', 'quincenal' => 'Quincenal', 'vip' => 'Cliente VIP'];
    $opinionesFonavet = ['muy_interesante' => 'Muy interesante', 'interesante' => 'Interesante', 'neutral' => 'Neutral', 'poco_interesante' => 'Poco interesante', 'no_interesa' => 'No le interesa'];
    $urlMapa = old('georeferencia_url', $clienteEditar?->georeferencia_url);
@endphp

<div class="wizard" @if($conPasos ?? false) data-wizard @endif>
    <section class="wizard-paso" data-titulo="Acceso y contacto">
        <div class="campos">
            <div class="form-divider">Datos de acceso y contacto</div>
            <div class="span-6 {{ $conError('name') }}">
                <label class="floating-label-activo-sm">Nombre cliente</label>
                <input class="form-control form-control-sm" name="name" value="{{ old('name', $clienteEditar?->name) }}" autocomplete="name" maxlength="255" required>
                @error('name')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-6 {{ $conError('email') }}">
                <label class="floating-label-activo-sm">Email</label>
                <input class="form-control form-control-sm" type="email" name="email" value="{{ old('email', $clienteEditar?->email) }}" autocomplete="email" maxlength="255" required>
                @error('email')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4 {{ $conError('password') }}">
                <label class="floating-label-activo-sm">Clave</label>
                <input class="form-control form-control-sm" name="password" placeholder="{{ $clienteEditar ? 'Nueva clave opcional' : 'Clave temporal segura' }}" autocomplete="new-password" minlength="8" {{ $clienteEditar ? '' : 'required' }}>
                <span class="field-help">Mínimo 8 caracteres.</span>
                @error('password')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4 {{ $conError('telefono') }}">
                <x-campo-telefono name="telefono" :value="old('telefono', $clienteEditar?->telefono)" />
                @error('telefono')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-2">
                <label class="floating-label-activo-sm">Plan</label>
                <select class="form-control form-control-sm" name="plan_preferido">
                    @foreach($planes as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('plan_preferido', $clienteEditar?->plan_preferido ?? 'sin_plan') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-2">
                <label class="floating-label-activo-sm">Activo</label>
                <select class="form-control form-control-sm" name="activo">
                    <option value="1" @selected(old('activo', $clienteEditar?->activo ?? true))>Sí</option>
                    <option value="0" @selected(!old('activo', $clienteEditar?->activo ?? true))>No</option>
                </select>
            </div>
            @if($clienteEditar)
                <div class="span-12">
                    {{-- Abre el modal "Inscribir mascota" con este cliente elegido como tutor --}}
                    <button type="button" class="tabla-accion tono-azul" data-modal-abrir="modal-nueva-mascota" data-modal-rellenar="{{ json_encode(['user_id' => $clienteEditar->id]) }}"><x-icono nombre="mascota" />Inscribir mascotas de este cliente</button>
                </div>
            @endif
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Dirección">
        <div class="campos">
            <div class="form-divider">Dirección principal</div>
            <div class="span-6 {{ $conError('direccion_principal') }}">
                <label class="floating-label-activo-sm">Dirección</label>
                <input class="form-control form-control-sm" name="direccion_principal" value="{{ old('direccion_principal', $direccion?->direccion ?? $clienteEditar?->direccion) }}" autocomplete="street-address" maxlength="500" data-mapa-direccion>
                @error('direccion_principal')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-3"><label class="floating-label-activo-sm">Comuna</label><input class="form-control form-control-sm" name="comuna" value="{{ old('comuna', $direccion?->comuna) }}" maxlength="120" data-mapa-comuna></div>
            <div class="span-3"><label class="floating-label-activo-sm">Referencia</label><input class="form-control form-control-sm" name="referencia" value="{{ old('referencia', $direccion?->referencia) }}" maxlength="500" placeholder="Depto, casa, portón..."></div>
            <input type="hidden" name="georeferencia_url" value="{{ $urlMapa }}" data-mapa-url>
            <div class="map-panel">
                <div class="map-actions">
                    <button type="button" class="tabla-accion tono-azul" data-mapa-activar><x-icono nombre="locacion" />Activar mapa por dirección</button>
                    <a class="tabla-accion tono-gris" href="{{ $urlMapa ?: 'https://www.google.com/maps' }}" target="_blank" rel="noopener" data-mapa-abrir><x-icono nombre="seguimiento" />Abrir mapa</a>
                    <span class="muted">La georreferencia se genera con la dirección y la comuna. Se usa para los despachos.</span>
                </div>
                <iframe class="map-frame" title="Mapa de la dirección del cliente" loading="lazy" data-mapa-frame></iframe>
            </div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Encuesta de vouchers">
        <div class="campos">
            <div class="form-divider">Encuesta comercial de vouchers</div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Recibe voucher</label>
                <select class="form-control form-control-sm" name="recibe_voucher">
                    <option value="1" @selected(old('recibe_voucher', $clienteEditar?->recibe_voucher ?? false))>Sí recibe voucher</option>
                    <option value="0" @selected(!old('recibe_voucher', $clienteEditar?->recibe_voucher ?? false))>No recibe voucher</option>
                </select>
            </div>
            <div class="span-4 {{ $conError('porcentaje_descuento_voucher') }}">
                <label class="floating-label-activo-sm">Descuento esperado (%)</label>
                <input class="form-control form-control-sm" type="number" name="porcentaje_descuento_voucher" min="0" max="100" value="{{ old('porcentaje_descuento_voucher', $clienteEditar?->porcentaje_descuento_voucher) }}">
                @error('porcentaje_descuento_voucher')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">
                    Opinión FONAVET
                    <span class="info-tip" tabindex="0" data-tip="FONAVET: Fondo Nacional Veterinario. Propuesta: desde $6.990 mensual por mascota, con vouchers, recordatorios sanitarios, descuentos en profesionales adheridos, historial clínico y beneficios en alimento o servicios.">i</span>
                </label>
                <select class="form-control form-control-sm" name="encuesta_sistema_nacional">
                    <option value="">Sin respuesta</option>
                    @foreach($opinionesFonavet as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('encuesta_sistema_nacional', $clienteEditar?->encuesta_sistema_nacional) === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-12">
                <label class="floating-label-activo-sm">Comentario encuesta</label>
                <textarea class="form-control form-control-sm" name="comentario_sistema_nacional" maxlength="1000">{{ old('comentario_sistema_nacional', $clienteEditar?->comentario_sistema_nacional) }}</textarea>
                <span class="field-help">Propuesta: desde $6.990 mensual por mascota, con vouchers, descuentos, recordatorios sanitarios, historial clínico y beneficios en alimento o servicios.</span>
            </div>
        </div>
    </section>
</div>
