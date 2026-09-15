{{--
    Campos del profesional, agrupados en pasos. Lo usan el modal "Crear profesional" (con asistente por pasos)
    y la pagina de edicion (todas las secciones seguidas).
    Recibe $profesionalEditar (null al crear) y opcional $conPasos. El formulario debe tener enctype multipart (foto).
    Estilos: admin-formularios.css + wizard.css
--}}
@php
    $conError = fn (string $campo) => $errors->has($campo) ? 'has-error' : '';
    $opinionesFonavet = ['muy_interesante' => 'Muy interesante', 'interesante' => 'Interesante', 'neutral' => 'Neutral', 'poco_interesante' => 'Poco interesante', 'no_interesa' => 'No le interesa'];
@endphp

<div class="wizard" @if($conPasos ?? false) data-wizard @endif>
    <section class="wizard-paso" data-titulo="Datos del profesional">
        <div class="campos">
            <div class="form-divider">Datos del profesional</div>
            <div class="span-8 {{ $conError('nombre') }}">
                <label class="floating-label-activo-sm">Nombre o clínica</label>
                <input class="form-control form-control-sm" name="nombre" value="{{ old('nombre', $profesionalEditar?->nombre) }}" maxlength="255" required>
                @error('nombre')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4 {{ $conError('rut') }}">
                <label class="floating-label-activo-sm">RUT</label>
                <input class="form-control form-control-sm" name="rut" value="{{ old('rut', $profesionalEditar?->rut) }}" maxlength="40" placeholder="12.345.678-9" required>
                @error('rut')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4"><label class="floating-label-activo-sm">Especialidad</label><input class="form-control form-control-sm" name="especialidad" value="{{ old('especialidad', $profesionalEditar?->especialidad) }}" maxlength="255" placeholder="Ej: Medicina general, laboratorio"></div>
            <div class="span-4 {{ $conError('email') }}">
                <label class="floating-label-activo-sm">Email</label>
                <input class="form-control form-control-sm" type="email" name="email" value="{{ old('email', $profesionalEditar?->email) }}" autocomplete="email" maxlength="255">
                @error('email')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4 {{ $conError('telefono') }}">
                <x-campo-telefono name="telefono" :value="old('telefono', $profesionalEditar?->telefono)" />
                @error('telefono')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Estado</label>
                <select class="form-control form-control-sm" name="activo">
                    <option value="1" @selected(old('activo', $profesionalEditar?->activo ?? true))>Activo</option>
                    <option value="0" @selected(!old('activo', $profesionalEditar?->activo ?? true))>Inactivo</option>
                </select>
            </div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Consulta y acceso">
        <div class="campos">
            <div class="form-divider">Consulta y acceso</div>
            <div class="span-8"><label class="floating-label-activo-sm">Dirección de la consulta</label><input class="form-control form-control-sm" name="direccion_consulta" value="{{ old('direccion_consulta', $profesionalEditar?->direccion_consulta) }}" maxlength="500"></div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Visita domiciliaria</label>
                <select class="form-control form-control-sm" name="visita_domiciliaria">
                    <option value="1" @selected(old('visita_domiciliaria', $profesionalEditar?->visita_domiciliaria ?? true))>Hace visita domiciliaria</option>
                    <option value="0" @selected(!old('visita_domiciliaria', $profesionalEditar?->visita_domiciliaria ?? true))>No hace visita domiciliaria</option>
                </select>
            </div>
            <div class="span-6"><label class="floating-label-activo-sm">Geolocalización de la consulta</label><input class="form-control form-control-sm" name="geolocalizacion" value="{{ old('geolocalizacion', $profesionalEditar?->geolocalizacion) }}" maxlength="255" placeholder="Ej: Providencia, sector Los Leones"></div>
            <div class="span-6"><label class="floating-label-activo-sm">Código de zona</label><input class="form-control form-control-sm" name="codigo_geolocalizacion" value="{{ old('codigo_geolocalizacion', $profesionalEditar?->codigo_geolocalizacion) }}" maxlength="80"></div>
            <div class="span-6 {{ $conError('password_acceso') }}">
                <label class="floating-label-activo-sm">Contraseña de acceso</label>
                <input class="form-control form-control-sm" name="password_acceso" placeholder="{{ $profesionalEditar ? 'Nueva contraseña opcional' : 'Contraseña temporal segura' }}" autocomplete="new-password" minlength="8" {{ $profesionalEditar ? '' : 'required' }}>
                <span class="field-help">Mínimo 8 caracteres.</span>
                @error('password_acceso')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-6 {{ $conError('foto') }}">
                <label class="floating-label-activo-sm">Foto</label>
                <input class="form-control form-control-sm" type="file" name="foto" accept="image/*">
                <span class="field-help">Imagen de hasta 4 MB.</span>
                @if($profesionalEditar?->foto_url)<img class="foto-actual" src="{{ asset($profesionalEditar->foto_url) }}" alt="Foto actual de {{ $profesionalEditar->nombre }}">@endif
                @error('foto')<small class="field-error">{{ $message }}</small>@enderror
            </div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Vouchers y FONAVET">
        <div class="campos">
            <div class="form-divider">Vouchers y FONAVET</div>
            <div class="span-6">
                <label class="floating-label-activo-sm">Recibe voucher</label>
                <select class="form-control form-control-sm" name="recibe_voucher">
                    <option value="1" @selected(old('recibe_voucher', $profesionalEditar?->recibe_voucher ?? true))>Recibe voucher</option>
                    <option value="0" @selected(!old('recibe_voucher', $profesionalEditar?->recibe_voucher ?? true))>No recibe voucher</option>
                </select>
            </div>
            <div class="span-6 {{ $conError('porcentaje_descuento_voucher') }}">
                <label class="floating-label-activo-sm">Descuento si recibe voucher (%)</label>
                <input class="form-control form-control-sm" type="number" name="porcentaje_descuento_voucher" min="0" max="100" value="{{ old('porcentaje_descuento_voucher', $profesionalEditar?->porcentaje_descuento_voucher) }}">
                @error('porcentaje_descuento_voucher')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-6">
                <label class="floating-label-activo-sm">
                    Opinión FONAVET
                    <span class="info-tip" tabindex="0" data-tip="FONAVET: Fondo Nacional Veterinario. Propuesta: desde $6.990 mensual por mascota, con vouchers, recordatorios sanitarios, descuentos en profesionales adheridos, historial clínico y beneficios en alimento o servicios.">i</span>
                </label>
                <select class="form-control form-control-sm" name="encuesta_sistema_nacional">
                    <option value="">Sin respuesta</option>
                    @foreach($opinionesFonavet as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('encuesta_sistema_nacional', $profesionalEditar?->encuesta_sistema_nacional) === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
                <span class="field-help">El profesional puede ofrecer descuentos, agenda preferente, controles preventivos y atención con voucher seguro.</span>
            </div>
            <div class="span-6"><label class="floating-label-activo-sm">Comentario sobre FONAVET</label><textarea class="form-control form-control-sm" name="comentario_sistema_nacional" maxlength="1000">{{ old('comentario_sistema_nacional', $profesionalEditar?->comentario_sistema_nacional) }}</textarea></div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Datos bancarios">
        <div class="campos">
            <div class="form-divider">Datos bancarios para liquidaciones</div>
            <div class="span-4"><label class="floating-label-activo-sm">Banco</label><input class="form-control form-control-sm" name="banco" value="{{ old('banco', $profesionalEditar?->banco) }}" maxlength="120"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Tipo de cuenta</label><input class="form-control form-control-sm" name="tipo_cuenta" value="{{ old('tipo_cuenta', $profesionalEditar?->tipo_cuenta) }}" maxlength="120" placeholder="Ej: Cuenta corriente, cuenta vista"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Número de cuenta</label><input class="form-control form-control-sm" name="numero_cuenta" value="{{ old('numero_cuenta', $profesionalEditar?->numero_cuenta) }}" maxlength="120"></div>
            <div class="span-8"><label class="floating-label-activo-sm">Titular de la cuenta</label><input class="form-control form-control-sm" name="titular_cuenta" value="{{ old('titular_cuenta', $profesionalEditar?->titular_cuenta) }}" maxlength="255"></div>
            <div class="span-4"><label class="floating-label-activo-sm">RUT de la cuenta</label><input class="form-control form-control-sm" name="rut_cuenta" value="{{ old('rut_cuenta', $profesionalEditar?->rut_cuenta) }}" maxlength="40" placeholder="12.345.678-9"></div>
            <p class="nota-formulario">Estos datos se usan para pagar las liquidaciones de vouchers al profesional. Puedes completarlos después.</p>
        </div>
    </section>
</div>
