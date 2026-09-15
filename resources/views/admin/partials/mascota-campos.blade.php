{{--
    Campos del formulario de mascota (sin <form>). Lo usan el modal "Inscribir mascota" y la pagina de edicion.
    Recibe $mascotaEditar (null al crear) y $clientes (tutores posibles).
    Estilos: public/css/admin-formularios.css · Ficha del tutor: public/js/form-mascota.js
--}}
@php
    $conError = fn (string $campo) => $errors->has($campo) ? 'has-error' : '';
    $especies = ['perro' => 'Perro', 'gato' => 'Gato', 'ave' => 'Ave', 'exotico' => 'Exótico', 'otro' => 'Otro'];
    $sexos = ['macho' => 'Macho', 'hembra' => 'Hembra', 'desconocido' => 'Desconocido'];
@endphp

<div class="campos" data-form-mascota>
    <div class="form-divider">Tutor</div>
    <div class="span-12 {{ $conError('user_id') }}">
        <label class="floating-label-activo-sm">Cliente dueño</label>
        <select class="form-control form-control-sm" name="user_id" required data-tutor-select>
            <option value="">Seleccione el cliente dueño</option>
            @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}" data-nombre="{{ $cliente->name }}" data-rut="{{ $cliente->perfilCliente?->rut }}" data-email="{{ $cliente->email }}" data-telefono="{{ $cliente->telefono ?: $cliente->perfilCliente?->telefono }}" data-sincronizado="{{ $cliente->vet_sdi_user_id ? 'Sincronizado' : 'Pendiente' }}" @selected((int) old('user_id', $mascotaEditar?->user_id) === $cliente->id)>{{ $cliente->name }} · {{ $cliente->email }}</option>
            @endforeach
        </select>
        @error('user_id')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="ficha-tutor" data-tutor-ficha>
        <div><span>Nombre del tutor</span><strong data-tutor="nombre">-</strong></div>
        <div><span>RUT</span><strong data-tutor="rut">No informado</strong></div>
        <div><span>Email</span><strong data-tutor="email">-</strong></div>
        <div><span>Teléfono</span><strong data-tutor="telefono">No informado</strong></div>
        <div><span>Estado VET-SDI</span><strong data-tutor="sincronizado">-</strong></div>
    </div>

    <div class="form-divider">Datos de la mascota</div>
    <div class="span-4 {{ $conError('nombre') }}">
        <label class="floating-label-activo-sm">Nombre mascota</label>
        <input class="form-control form-control-sm" name="nombre" value="{{ old('nombre', $mascotaEditar?->nombre) }}" required>
        @error('nombre')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-4">
        <label class="floating-label-activo-sm">Especie</label>
        <select class="form-control form-control-sm" name="especie" required>
            @foreach($especies as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('especie', $mascotaEditar?->especie ?? 'perro') === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
    </div>
    <div class="span-4"><label class="floating-label-activo-sm">Raza</label><input class="form-control form-control-sm" name="raza" value="{{ old('raza', $mascotaEditar?->raza) }}"></div>
    <div class="span-3">
        <label class="floating-label-activo-sm">Sexo</label>
        <select class="form-control form-control-sm" name="sexo">
            <option value="">Seleccionar</option>
            @foreach($sexos as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('sexo', $mascotaEditar?->sexo) === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
    </div>
    <div class="span-3"><label class="floating-label-activo-sm">Color</label><input class="form-control form-control-sm" name="color" value="{{ old('color', $mascotaEditar?->color) }}"></div>
    <div class="span-3 {{ $conError('peso_kg') }}">
        <label class="floating-label-activo-sm">Peso (kg)</label>
        <input class="form-control form-control-sm" type="number" name="peso_kg" min="0" value="{{ old('peso_kg', $mascotaEditar?->peso_kg) }}">
        @error('peso_kg')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-3 {{ $conError('fecha_nacimiento') }}">
        <label class="floating-label-activo-sm">Fecha nacimiento</label>
        <input class="form-control form-control-sm" type="date" name="fecha_nacimiento" max="{{ now()->toDateString() }}" value="{{ old('fecha_nacimiento', $mascotaEditar?->fecha_nacimiento?->toDateString()) }}">
        @error('fecha_nacimiento')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="span-6"><label class="floating-label-activo-sm">N° de chip</label><input class="form-control form-control-sm" name="numero_chip" value="{{ old('numero_chip', $mascotaEditar?->numero_chip) }}"></div>
    <div class="span-6">
        <label class="floating-label-activo-sm">Esterilizado</label>
        <select class="form-control form-control-sm" name="esterilizado">
            <option value="0" @selected(!old('esterilizado', $mascotaEditar?->esterilizado ?? false))>No</option>
            <option value="1" @selected(old('esterilizado', $mascotaEditar?->esterilizado ?? false))>Sí</option>
        </select>
    </div>
    <div class="span-6"><label class="floating-label-activo-sm">Alergias / restricciones</label><textarea class="form-control form-control-sm" name="alergias">{{ old('alergias', $mascotaEditar?->alergias) }}</textarea></div>
    <div class="span-6"><label class="floating-label-activo-sm">Observaciones</label><textarea class="form-control form-control-sm" name="observaciones">{{ old('observaciones', $mascotaEditar?->observaciones) }}</textarea></div>

    <div class="invitacion-vetsdi">
        <h3>Invitar al tutor a participar en VET-SDI</h3>
        <p class="muted">La invitación aparecerá en su escritorio y le permitirá completar o activar su participación.</p>
        <div class="beneficios">
            @foreach(['Ficha Veterinaria Única', 'Vacunas y recordatorios', 'Documentos y exámenes', 'Vouchers y descuentos', 'Alimentos y farmacia', 'Servicios conectados'] as $beneficio)
                <span class="badge tono-verde">{{ $beneficio }}</span>
            @endforeach
        </div>
        <label class="casilla"><input type="checkbox" name="enviar_invitacion" value="1" @checked(old('enviar_invitacion'))> Enviar invitación al guardar</label>
    </div>
</div>
<script src="{{ asset('js/form-mascota.js') }}?v={{ filemtime(public_path('js/form-mascota.js')) }}" defer></script>
