{{--
    Campos de vendedor / repartidor, agrupados en pasos. Lo usan el modal de crear (con asistente por pasos)
    y la pagina de edicion (todas las secciones seguidas).
    Recibe $usuarioEditar (null al crear), $locales, $rol ('vendedor' | 'repartidor') y opcional $conPasos.
    El formulario que lo contiene debe tener enctype="multipart/form-data" (fotos del repartidor).
--}}
@php
    $conError = fn (string $campo) => $errors->has($campo) ? 'has-error' : '';
@endphp

<div class="wizard" @if($conPasos ?? false) data-wizard @endif>
    <section class="wizard-paso" data-titulo="Acceso">
        <div class="campos">
            <div class="form-divider">Datos de acceso</div>
            <div class="span-12 {{ $conError('name') }}">
                <label class="floating-label-activo-sm">Nombre</label>
                <input class="form-control form-control-sm" name="name" value="{{ old('name', $usuarioEditar?->name) }}" autocomplete="name" maxlength="255" required>
                @error('name')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-6 {{ $conError('email') }}">
                <label class="floating-label-activo-sm">Email</label>
                <input class="form-control form-control-sm" type="email" name="email" value="{{ old('email', $usuarioEditar?->email) }}" autocomplete="email" maxlength="255" required>
                @error('email')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-6 {{ $conError('password') }}">
                <label class="floating-label-activo-sm">Clave</label>
                <input class="form-control form-control-sm" name="password" placeholder="{{ $usuarioEditar ? 'Nueva clave opcional' : 'Clave temporal segura' }}" autocomplete="new-password" minlength="8" {{ $usuarioEditar ? '' : 'required' }}>
                <span class="field-help">Mínimo 8 caracteres.</span>
                @error('password')<small class="field-error">{{ $message }}</small>@enderror
            </div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Contacto y local">
        <div class="campos">
            <div class="form-divider">Contacto y local</div>
            <div class="span-4 {{ $conError('telefono') }}">
                <x-campo-telefono name="telefono" :value="old('telefono', $usuarioEditar?->telefono)" />
                @error('telefono')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-8 {{ $conError('direccion') }}">
                <label class="floating-label-activo-sm">Dirección</label>
                <input class="form-control form-control-sm" name="direccion" value="{{ old('direccion', $usuarioEditar?->direccion) }}" autocomplete="street-address" maxlength="500">
                @error('direccion')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-8 {{ $conError('local_venta_id') }}">
                <label class="floating-label-activo-sm">Local asignado</label>
                <select class="form-control form-control-sm" name="local_venta_id">
                    <option value="">Sin local</option>
                    @foreach($locales as $local)
                        <option value="{{ $local->id }}" @selected((int) old('local_venta_id', $usuarioEditar?->local_venta_id) === $local->id)>{{ $local->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Activo</label>
                <select class="form-control form-control-sm" name="activo">
                    <option value="1" @selected(old('activo', $usuarioEditar?->activo ?? true))>Sí</option>
                    <option value="0" @selected(!old('activo', $usuarioEditar?->activo ?? true))>No</option>
                </select>
            </div>
        </div>
    </section>

    @if($rol === 'repartidor')
        <section class="wizard-paso" data-titulo="Vehículo y fotos">
            <div class="campos">
                <div class="form-divider">Datos del repartidor y vehículo</div>
                <div class="span-6 {{ $conError('foto') }}">
                    <label class="floating-label-activo-sm">Foto del repartidor</label>
                    <input class="form-control form-control-sm" type="file" name="foto" accept="image/*">
                    <span class="field-help">Imagen de hasta 4 MB.</span>
                    @if($usuarioEditar?->foto_url)<img class="foto-actual" src="{{ asset($usuarioEditar->foto_url) }}" alt="Foto actual de {{ $usuarioEditar->name }}">@endif
                    @error('foto')<small class="field-error">{{ $message }}</small>@enderror
                </div>
                <div class="span-6 {{ $conError('vehiculo_foto') }}">
                    <label class="floating-label-activo-sm">Foto del vehículo</label>
                    <input class="form-control form-control-sm" type="file" name="vehiculo_foto" accept="image/*">
                    <span class="field-help">Imagen de hasta 4 MB.</span>
                    @if($usuarioEditar?->vehiculo_foto_url)<img class="foto-actual" src="{{ asset($usuarioEditar->vehiculo_foto_url) }}" alt="Vehículo de {{ $usuarioEditar->name }}">@endif
                    @error('vehiculo_foto')<small class="field-error">{{ $message }}</small>@enderror
                </div>
                <div class="span-4"><label class="floating-label-activo-sm">Patente vehículo</label><input class="form-control form-control-sm" name="vehiculo_patente" value="{{ old('vehiculo_patente', $usuarioEditar?->vehiculo_patente) }}" maxlength="30" placeholder="AB CD 12"></div>
                <div class="span-4"><label class="floating-label-activo-sm">Marca vehículo</label><input class="form-control form-control-sm" name="vehiculo_marca" value="{{ old('vehiculo_marca', $usuarioEditar?->vehiculo_marca) }}" maxlength="255"></div>
                <div class="span-4"><label class="floating-label-activo-sm">Modelo vehículo</label><input class="form-control form-control-sm" name="vehiculo_modelo" value="{{ old('vehiculo_modelo', $usuarioEditar?->vehiculo_modelo) }}" maxlength="255"></div>
            </div>
        </section>
    @endif
</div>
