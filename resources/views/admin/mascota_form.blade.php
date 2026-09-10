@extends('layouts.app')

@section('title', $mascotaEditar ? 'Editar Mascota' : 'Inscribir Mascota')

@section('content')
<style>
    .pets-head{display:grid;grid-template-columns:170px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 20px}
    .pets-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .paw-icon{width:36px;height:36px;border-radius:50%;background:#f97316;position:relative;display:inline-block;box-shadow:0 12px 0 -4px #f97316}
    .paw-icon:before{content:"";position:absolute;left:-9px;top:4px;width:14px;height:14px;border-radius:50%;background:#fb923c;box-shadow:15px -8px 0 #fb923c,30px 0 0 #fb923c}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .pet-form-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}
    .form-actions{display:flex;justify-content:center;gap:12px;margin-top:18px}
    .tutor-card{grid-column:span 12;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;border:1px solid #bfdbfe;border-radius:12px;background:#eff6ff;padding:16px}.tutor-field span{display:block;color:#64748b;font-size:12px;font-weight:800;margin-bottom:4px}.tutor-field strong{color:#172033}.benefits-card{grid-column:span 12;border:1px solid #99f6e4;border-radius:12px;background:#f0fdfa;padding:16px}.benefits-card h3{color:#0f766e;margin-bottom:8px}.benefit-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin:12px 0}.benefit-item{background:#fff;border:1px solid #ccfbf1;border-radius:8px;padding:9px;color:#115e59;font-weight:700}.invite-option{display:flex;align-items:center;gap:10px;font-weight:800;color:#0f766e}.invite-option input{width:18px;height:18px;min-height:18px;margin:0}
    @media(max-width:900px){.pets-head{grid-template-columns:1fr}.pets-title{font-size:28px}.span-6,.span-4,.span-3{grid-column:span 12}.tutor-card,.benefit-list{grid-template-columns:1fr 1fr}}
</style>

<div class="pets-head">
    <a class="btn btn-secondary" href="{{ route('admin.mascotas.index') }}">Volver</a>
    <h1 class="pets-title"><span class="paw-icon"></span>{{ $mascotaEditar ? 'Editar mascota' : 'Formulario de inscripcion de mascota' }}</h1>
</div>


<div class="classic-card">
    <form method="POST" action="{{ $mascotaEditar ? route('admin.mascotas.update', $mascotaEditar) : route('admin.mascotas.store') }}">
        @csrf
        @if($mascotaEditar)
            @method('PATCH')
        @endif
        <div class="pet-form-grid">
            <div class="span-4">
                <label class="floating-label-activo-sm">Cliente dueno</label>
                <select class="form-control form-control-sm" id="tutor_id" name="user_id" required>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" data-nombre="{{ $cliente->name }}" data-rut="{{ $cliente->perfilCliente?->rut }}" data-email="{{ $cliente->email }}" data-telefono="{{ $cliente->telefono ?: $cliente->perfilCliente?->telefono }}" data-sincronizado="{{ $cliente->vet_sdi_user_id ? 'Sí' : 'Pendiente' }}" @selected((int) old('user_id', $mascotaEditar?->user_id) === $cliente->id)>{{ $cliente->name }} - {{ $cliente->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tutor-card">
                <div class="tutor-field"><span>Nombre del tutor</span><strong id="tutor_nombre">-</strong></div>
                <div class="tutor-field"><span>RUT</span><strong id="tutor_rut">No informado</strong></div>
                <div class="tutor-field"><span>Email</span><strong id="tutor_email">-</strong></div>
                <div class="tutor-field"><span>Teléfono</span><strong id="tutor_telefono">No informado</strong></div>
                <div class="tutor-field"><span>Estado VET-SDI</span><strong id="tutor_sincronizado">-</strong></div>
            </div>
            <div class="span-4"><label class="floating-label-activo-sm">Nombre mascota</label><input class="form-control form-control-sm" name="nombre" value="{{ old('nombre', $mascotaEditar?->nombre) }}" required></div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Especie</label>
                <select class="form-control form-control-sm" name="especie" required>
                    @foreach(['perro' => 'Perro', 'gato' => 'Gato', 'ave' => 'Ave', 'exotico' => 'Exotico', 'otro' => 'Otro'] as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('especie', $mascotaEditar?->especie ?? 'perro') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-3"><label class="floating-label-activo-sm">Raza</label><input class="form-control form-control-sm" name="raza" value="{{ old('raza', $mascotaEditar?->raza) }}"></div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Sexo</label>
                <select class="form-control form-control-sm" name="sexo">
                    <option value="">Seleccionar</option>
                    <option value="macho" @selected(old('sexo', $mascotaEditar?->sexo) === 'macho')>Macho</option>
                    <option value="hembra" @selected(old('sexo', $mascotaEditar?->sexo) === 'hembra')>Hembra</option>
                    <option value="desconocido" @selected(old('sexo', $mascotaEditar?->sexo) === 'desconocido')>Desconocido</option>
                </select>
            </div>
            <div class="span-3"><label class="floating-label-activo-sm">Color</label><input class="form-control form-control-sm" name="color" value="{{ old('color', $mascotaEditar?->color) }}"></div>
            <div class="span-3"><label class="floating-label-activo-sm">Peso kg</label><input class="form-control form-control-sm" type="number" name="peso_kg" min="0" value="{{ old('peso_kg', $mascotaEditar?->peso_kg) }}"></div>
            <div class="span-3"><label class="floating-label-activo-sm">Fecha nacimiento</label><input class="form-control form-control-sm" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $mascotaEditar?->fecha_nacimiento?->toDateString()) }}"></div>
            <div class="span-3"><label class="floating-label-activo-sm">N de chip</label><input class="form-control form-control-sm" name="numero_chip" value="{{ old('numero_chip', $mascotaEditar?->numero_chip) }}"></div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Esterilizado</label>
                <select class="form-control form-control-sm" name="esterilizado">
                    <option value="0" @selected(!old('esterilizado', $mascotaEditar?->esterilizado ?? false))>No</option>
                    <option value="1" @selected(old('esterilizado', $mascotaEditar?->esterilizado ?? false))>Si</option>
                </select>
            </div>
            <div class="span-6"><label class="floating-label-activo-sm">Alergias / restricciones</label><textarea class="form-control form-control-sm" name="alergias">{{ old('alergias', $mascotaEditar?->alergias) }}</textarea></div>
            <div class="span-6"><label class="floating-label-activo-sm">Observaciones</label><textarea class="form-control form-control-sm" name="observaciones">{{ old('observaciones', $mascotaEditar?->observaciones) }}</textarea></div>
            <div class="benefits-card">
                <h3>Invitar al tutor a participar en VET-SDI</h3>
                <p class="muted">La invitación aparecerá en su escritorio y le permitirá completar o activar su participación.</p>
                <div class="benefit-list"><div class="benefit-item">Ficha Veterinaria Única</div><div class="benefit-item">Vacunas y recordatorios</div><div class="benefit-item">Documentos y exámenes</div><div class="benefit-item">Vouchers y descuentos</div><div class="benefit-item">Alimentos y farmacia</div><div class="benefit-item">Servicios conectados</div></div>
                <label class="invite-option"><input type="checkbox" name="enviar_invitacion" value="1" @checked(old('enviar_invitacion'))> Enviar invitación al guardar</label>
            </div>
        </div>
        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('admin.mascotas.index') }}">Cancelar</a>
            <button class="btn-success">Guardar mascota</button>
        </div>
    </form>
</div>
<script>
    const tutorSelect = document.getElementById('tutor_id');
    function mostrarTutor() {
        const tutor = tutorSelect.selectedOptions[0];
        ['nombre','rut','email','telefono','sincronizado'].forEach(campo => {
            const valor = tutor?.dataset[campo];
            document.getElementById('tutor_' + campo).textContent = valor || (campo === 'email' || campo === 'nombre' ? '-' : 'No informado');
        });
    }
    tutorSelect.addEventListener('change', mostrarTutor);
    mostrarTutor();
</script>
@endsection
