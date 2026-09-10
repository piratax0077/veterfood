{{--
    Campos del formulario de usuario (sin la etiqueta <form>). Lo usan el modal "Nuevo usuario" (admin/usuarios)
    y la pagina de edicion (admin/usuario_form). Recibe $usuarioEditar (null al crear) y $locales.
    Estilos: public/css/admin-usuario-form.css · Mapa: public/js/usuario-form.js
--}}
@php
    $conError = fn (string $campo) => $errors->has($campo) ? 'has-error' : '';
    $roles = ['admin' => 'Admin', 'central_ventas' => 'Central ventas', 'vendedor' => 'Vendedor', 'cliente' => 'Cliente', 'dueno_mascota' => 'Dueño mascota', 'repartidor' => 'Repartidor', 'auditor' => 'Auditor', 'contabilidad' => 'Contabilidad'];
    $planes = ['sin_plan' => 'Sin plan', 'fonavet_base' => 'FONAVET base', 'fonavet_integral' => 'FONAVET integral', 'alimento_mensual' => 'Alimento mensual', 'vip' => 'VIP'];
    $opinionesFonavet = ['muy_interesante' => 'Muy interesante', 'interesante' => 'Interesante', 'neutral' => 'Neutral', 'poco_interesante' => 'Poco interesante', 'no_interesa' => 'No le interesa'];
    $urlMapa = old('georeferencia_url', $usuarioEditar?->georeferencia_url);
@endphp

<div class="user-form-grid">
    <div class="form-divider">Datos de acceso y rol</div>
    <div class="span-4 {{ $conError('name') }}">
        <label class="floating-label-activo-sm">Nombre</label>
        <input class="form-control form-control-sm" name="name" value="{{ old('name', $usuarioEditar?->name) }}" autocomplete="name" required>
        @error('name')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-4 {{ $conError('email') }}">
        <label class="floating-label-activo-sm">Email</label>
        <input class="form-control form-control-sm" type="email" name="email" value="{{ old('email', $usuarioEditar?->email) }}" autocomplete="email" required>
        @error('email')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-4 {{ $conError('password') }}">
        <label class="floating-label-activo-sm">Clave</label>
        <input class="form-control form-control-sm" name="password" placeholder="{{ $usuarioEditar ? 'Nueva clave opcional' : 'Clave temporal segura' }}" autocomplete="new-password" minlength="8" {{ $usuarioEditar ? '' : 'required' }}>
        @error('password')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-4 {{ $conError('rol') }}">
        <label class="floating-label-activo-sm">Rol</label>
        <select class="form-control form-control-sm" name="rol" required>
            @foreach($roles as $rol => $titulo)
                <option value="{{ $rol }}" @selected(old('rol', $usuarioEditar?->rol ?? 'cliente') === $rol)>{{ $titulo }}</option>
            @endforeach
        </select>
        @error('rol')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-4 {{ $conError('local_venta_id') }}">
        <label class="floating-label-activo-sm">Local asignado</label>
        <select class="form-control form-control-sm" name="local_venta_id">
            <option value="">Sin local</option>
            @foreach($locales as $local)
                <option value="{{ $local->id }}" @selected((int) old('local_venta_id', $usuarioEditar?->local_venta_id) === $local->id)>{{ $local->nombre }}</option>
            @endforeach
        </select>
        @error('local_venta_id')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-4 {{ $conError('telefono') }}">
        <label class="floating-label-activo-sm">Teléfono</label>
        <input class="form-control form-control-sm" name="telefono" value="{{ old('telefono', $usuarioEditar?->telefono) }}" autocomplete="tel">
        @error('telefono')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-divider">Ubicación y contacto operativo</div>
    <div class="span-8 {{ $conError('direccion') }}">
        <label class="floating-label-activo-sm">Dirección</label>
        <input class="form-control form-control-sm" name="direccion" value="{{ old('direccion', $usuarioEditar?->direccion) }}" autocomplete="street-address" data-mapa-direccion>
        @error('direccion')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-4">
        <label class="floating-label-activo-sm">Activo</label>
        <select class="form-control form-control-sm" name="activo">
            <option value="1" @selected(old('activo', $usuarioEditar?->activo ?? true))>Sí</option>
            <option value="0" @selected(!old('activo', $usuarioEditar?->activo ?? true))>No</option>
        </select>
    </div>
    <input type="hidden" name="georeferencia_url" value="{{ $urlMapa }}" data-mapa-url>
    <div class="map-panel">
        <div class="map-actions">
            <button type="button" class="tabla-accion tono-azul" data-mapa-activar><x-icono nombre="locacion" />Activar mapa por dirección</button>
            <a class="tabla-accion tono-gris" href="{{ $urlMapa ?: 'https://www.google.com/maps' }}" target="_blank" rel="noopener" data-mapa-abrir><x-icono nombre="seguimiento" />Abrir mapa</a>
            <span class="muted">Se usa si el usuario es cliente, local comercial, clínica, comercio adherido o punto operativo.</span>
        </div>
    </div>

    <div class="form-divider">Encuesta y propuesta FONAVET</div>
    <div class="span-3">
        <label class="floating-label-activo-sm">Plan preferido</label>
        <select class="form-control form-control-sm" name="plan_preferido">
            @foreach($planes as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('plan_preferido', $usuarioEditar?->plan_preferido ?? 'sin_plan') === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
    </div>
    <div class="span-3">
        <label class="floating-label-activo-sm">Recibe voucher</label>
        <select class="form-control form-control-sm" name="recibe_voucher">
            <option value="1" @selected(old('recibe_voucher', $usuarioEditar?->recibe_voucher ?? false))>Sí recibe</option>
            <option value="0" @selected(!old('recibe_voucher', $usuarioEditar?->recibe_voucher ?? false))>No recibe</option>
        </select>
    </div>
    <div class="span-3 {{ $conError('porcentaje_descuento_voucher') }}">
        <label class="floating-label-activo-sm">Descuento (%)</label>
        <input class="form-control form-control-sm" type="number" name="porcentaje_descuento_voucher" min="0" max="100" value="{{ old('porcentaje_descuento_voucher', $usuarioEditar?->porcentaje_descuento_voucher) }}">
        @error('porcentaje_descuento_voucher')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-3 {{ $conError('fonavet_valor_mensual') }}">
        <label class="floating-label-activo-sm">Pago mensual dispuesto</label>
        <input class="form-control form-control-sm" type="number" name="fonavet_valor_mensual" min="0" step="500" placeholder="Ej: 6990" value="{{ old('fonavet_valor_mensual', $usuarioEditar?->fonavet_valor_mensual) }}">
        @error('fonavet_valor_mensual')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-6">
        <label class="floating-label-activo-sm">
            Opinión FONAVET
            <span class="info-tip" tabindex="0" data-tip="FONAVET: Fondo Nacional Veterinario. Propuesta mensual desde $6.990 por mascota para atención, vouchers, descuentos, historial clínico y beneficios.">i</span>
        </label>
        <select class="form-control form-control-sm" name="encuesta_sistema_nacional">
            <option value="">Sin respuesta</option>
            @foreach($opinionesFonavet as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('encuesta_sistema_nacional', $usuarioEditar?->encuesta_sistema_nacional) === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
        <span class="field-help">Propuesta sugerida: $6.990 base o $9.990 integral por mascota.</span>
    </div>
    <div class="span-6">
        <label class="floating-label-activo-sm">Comentario encuesta</label>
        <textarea class="form-control form-control-sm" name="comentario_sistema_nacional">{{ old('comentario_sistema_nacional', $usuarioEditar?->comentario_sistema_nacional) }}</textarea>
    </div>
</div>
<script src="{{ asset('js/usuario-form.js') }}?v={{ filemtime(public_path('js/usuario-form.js')) }}" defer></script>
