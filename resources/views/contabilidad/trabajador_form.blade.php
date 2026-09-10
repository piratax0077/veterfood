@extends('layouts.app')

@section('title', $modo === 'crear' ? 'Crear trabajador' : 'Editar trabajador')

@section('content')
<style>
    .worker-head{display:grid;grid-template-columns:auto 1fr;align-items:center;gap:18px;margin-bottom:20px}
    .worker-title{display:flex;align-items:center;gap:12px;margin:0;color:#061a3d;font-size:34px}
    .worker-icon{width:48px;height:48px;border-radius:14px;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;box-shadow:0 10px 22px rgba(37,99,235,.25)}
    .form-card{background:#fff;border:1px solid #dbe3ee;border-radius:12px;box-shadow:0 14px 30px rgba(15,23,42,.08);margin-bottom:16px;overflow:hidden}
    .form-banner{display:flex;justify-content:space-between;align-items:center;gap:16px;background:#f8fafc;border-bottom:1px solid #dbe3ee;padding:18px 22px}
    .form-banner h2{margin:0;color:#061a3d;font-size:24px}
    .form-banner p{margin:4px 0 0;color:#52617a}
    .form-chip{background:#e8f1ff;color:#1d4ed8;border-radius:999px;padding:8px 12px;font-weight:900;white-space:nowrap}
    .form-section{padding:22px;border-top:1px solid #e5edf6}
    .form-section:first-of-type{border-top:0}
    .section-heading{display:flex;align-items:center;gap:10px;margin-bottom:16px}
    .section-badge{width:32px;height:32px;border-radius:9px;background:#e0f2fe;color:#0369a1;display:flex;align-items:center;justify-content:center;font-weight:900}
    .section-heading h2{margin:0;color:#061a3d;font-size:22px}
    .form-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px;align-items:end}
    .field{grid-column:span 3}.field-sm{grid-column:span 2}.field-md{grid-column:span 4}.field-lg{grid-column:span 6}.field-xl{grid-column:span 12}
    .field label,.field-sm label,.field-md label,.field-lg label,.field-xl label{display:block;margin-bottom:6px;font-weight:900;color:#061a3d}
    .field input,.field select,.field textarea,.field-sm input,.field-sm select,.field-md input,.field-md select,.field-lg input,.field-lg select,.field-xl input,.field-xl select,.field-xl textarea{width:100%;min-height:42px;border:1px solid #d5dfeb;border-radius:8px;background:#fff;padding:9px 11px}
    .actions{display:flex;gap:12px;justify-content:center;padding:20px 22px;background:#f8fafc;border-top:1px solid #dbe3ee;flex-wrap:wrap}
    @media(max-width:1000px){.field,.field-sm,.field-md,.field-lg{grid-column:span 6}.worker-head{grid-template-columns:1fr}.form-banner{align-items:flex-start;flex-direction:column}}
    @media(max-width:650px){.field,.field-sm,.field-md,.field-lg,.field-xl{grid-column:span 12}.worker-title{font-size:28px}}
</style>

<div class="worker-head">
    <a class="btn btn-secondary" href="{{ route('contabilidad.secciones.show', ['centroMedico' => $centroMedico->id, 'seccion' => 'rrhh']) }}">Volver a RRHH</a>
    <h1 class="worker-title"><span class="worker-icon">RH</span>{{ $modo === 'crear' ? 'Formulario trabajador' : 'Editar trabajador' }}</h1>
</div>


<form class="form-card" method="POST" action="{{ $modo === 'crear' ? route('contabilidad.trabajadores.store', ['centroMedico' => $centroMedico->id]) : route('contabilidad.trabajadores.update', ['centroMedico' => $centroMedico->id, 'trabajador' => $trabajador->id]) }}">
    @csrf
    @if($modo === 'editar')
        @method('PUT')
    @endif
    <input type="hidden" name="_redirect_to" value="1">

    <div class="form-banner">
        <div>
            <h2>{{ $modo === 'crear' ? 'Alta de trabajador' : 'Ficha laboral del trabajador' }}</h2>
            <p>Datos personales, laborales, previsionales y bancarios para contabilidad.</p>
        </div>
        <span class="form-chip">{{ $modo === 'crear' ? 'Nuevo registro' : 'Edicion' }}</span>
    </div>

    <div class="form-section">
        <div class="section-heading"><span class="section-badge">1</span><h2>Datos personales</h2></div>
        <div class="form-grid">
            <div class="field-md"><label class="floating-label-activo-sm">RUT</label><input class="form-control form-control-sm" name="rut" value="{{ old('rut', $trabajador->rut) }}" required></div>
            <div class="field-lg"><label class="floating-label-activo-sm">Nombres</label><input class="form-control form-control-sm" name="nombres" value="{{ old('nombres', $trabajador->nombres) }}" required></div>
            <div class="field-sm"><label class="floating-label-activo-sm">Sexo</label><select class="form-control form-control-sm" name="sexo"><option value="">No informa</option>@foreach(['femenino','masculino','otro','no_informa'] as $sexo)<option value="{{ $sexo }}" @selected(old('sexo', $trabajador->sexo) === $sexo)>{{ ucfirst(str_replace('_', ' ', $sexo)) }}</option>@endforeach</select></div>
            <div class="field"><label class="floating-label-activo-sm">Apellido paterno</label><input class="form-control form-control-sm" name="apellido_paterno" value="{{ old('apellido_paterno', $trabajador->apellido_paterno) }}" required></div>
            <div class="field"><label class="floating-label-activo-sm">Apellido materno</label><input class="form-control form-control-sm" name="apellido_materno" value="{{ old('apellido_materno', $trabajador->apellido_materno) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Fecha nacimiento</label><input class="form-control form-control-sm" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento', optional($trabajador->fecha_nacimiento)->format('Y-m-d')) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Activo</label><select class="form-control form-control-sm" name="activo"><option value="1" @selected(old('activo', $trabajador->activo) == 1)>Activo</option><option value="0" @selected(old('activo', $trabajador->activo) == 0)>Inactivo</option></select></div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-heading"><span class="section-badge">2</span><h2>Contacto y domicilio</h2></div>
        <div class="form-grid">
            <div class="field"><label class="floating-label-activo-sm">Email</label><input class="form-control form-control-sm" name="email" type="email" value="{{ old('email', $trabajador->email) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Telefono</label><input class="form-control form-control-sm" name="telefono" value="{{ old('telefono', $trabajador->telefono) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Telefono alternativo</label><input class="form-control form-control-sm" name="telefono_alternativo" value="{{ old('telefono_alternativo', $trabajador->telefono_alternativo) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Region</label><input class="form-control form-control-sm" name="region" value="{{ old('region', $trabajador->region) }}"></div>
            <div class="field-lg"><label class="floating-label-activo-sm">Direccion</label><input class="form-control form-control-sm" name="direccion" value="{{ old('direccion', $trabajador->direccion) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Numero</label><input class="form-control form-control-sm" name="numero_direccion" value="{{ old('numero_direccion', $trabajador->numero_direccion) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Comuna</label><input class="form-control form-control-sm" name="comuna" value="{{ old('comuna', $trabajador->comuna) }}"></div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-heading"><span class="section-badge">3</span><h2>Datos laborales contables</h2></div>
        <div class="form-grid">
            <div class="field"><label class="floating-label-activo-sm">Tipo</label><select class="form-control form-control-sm" name="tipo" required>@foreach(['administrativo','profesional','mantencion','otro'] as $tipo)<option value="{{ $tipo }}" @selected(old('tipo', $trabajador->tipo) === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach</select></div>
            <div class="field"><label class="floating-label-activo-sm">Profesion</label><input class="form-control form-control-sm" name="profesion" value="{{ old('profesion', $trabajador->profesion) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Especialidad</label><input class="form-control form-control-sm" name="especialidad" value="{{ old('especialidad', $trabajador->especialidad) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Funcion / cargo</label><input class="form-control form-control-sm" name="funcion" value="{{ old('funcion', $trabajador->funcion) }}"></div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-heading"><span class="section-badge">4</span><h2>Prevision y leyes sociales</h2></div>
        <div class="form-grid">
            <div class="field"><label class="floating-label-activo-sm">AFP</label><input class="form-control form-control-sm" name="afp" value="{{ old('afp', $trabajador->afp) }}" placeholder="Ej: Habitat, Capital, Modelo"></div>
            <div class="field"><label class="floating-label-activo-sm">Fecha afiliacion AFP</label><input class="form-control form-control-sm" name="fecha_afiliacion_afp" type="date" value="{{ old('fecha_afiliacion_afp', optional($trabajador->fecha_afiliacion_afp)->format('Y-m-d')) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Regimen previsional</label><select class="form-control form-control-sm" name="regimen_previsional"><option value="">Seleccionar</option>@foreach(['afp' => 'AFP', 'ips' => 'IPS', 'capredena' => 'CAPREDENA', 'dipreca' => 'DIPRECA', 'sin_regimen' => 'Sin regimen', 'otro' => 'Otro'] as $value => $label)<option value="{{ $value }}" @selected(old('regimen_previsional', $trabajador->regimen_previsional) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label class="floating-label-activo-sm">Seguro cesantia</label><select class="form-control form-control-sm" name="seguro_cesantia"><option value="1" @selected(old('seguro_cesantia', $trabajador->seguro_cesantia ?? true) == 1)>Si aplica</option><option value="0" @selected(old('seguro_cesantia', $trabajador->seguro_cesantia) == 0)>No aplica</option></select></div>
            <div class="field"><label class="floating-label-activo-sm">Sistema salud</label><select class="form-control form-control-sm" name="tipo_salud"><option value="">Seleccionar</option>@foreach(['fonasa' => 'FONASA', 'isapre' => 'ISAPRE', 'ffaa' => 'FF.AA.', 'otro' => 'Otro'] as $value => $label)<option value="{{ $value }}" @selected(old('tipo_salud', $trabajador->tipo_salud) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label class="floating-label-activo-sm">Institucion salud</label><input class="form-control form-control-sm" name="salud_previsional" value="{{ old('salud_previsional', $trabajador->salud_previsional) }}" placeholder="Fonasa tramo, Isapre u otra"></div>
            <div class="field"><label class="floating-label-activo-sm">Caja compensacion</label><input class="form-control form-control-sm" name="caja_compensacion" value="{{ old('caja_compensacion', $trabajador->caja_compensacion) }}" placeholder="Ej: Los Andes"></div>
            <div class="field"><label class="floating-label-activo-sm">Mutualidad</label><input class="form-control form-control-sm" name="mutualidad" value="{{ old('mutualidad', $trabajador->mutualidad) }}" placeholder="Ej: ACHS, Mutual, IST"></div>
            <div class="field-sm"><label class="floating-label-activo-sm">Tramo familiar</label><select class="form-control form-control-sm" name="tramo_asignacion_familiar"><option value="">No informa</option>@foreach(['A','B','C','D'] as $tramo)<option value="{{ $tramo }}" @selected(old('tramo_asignacion_familiar', $trabajador->tramo_asignacion_familiar) === $tramo)>Tramo {{ $tramo }}</option>@endforeach<option value="sin_tramo" @selected(old('tramo_asignacion_familiar', $trabajador->tramo_asignacion_familiar) === 'sin_tramo')>Sin tramo</option></select></div>
            <div class="field-sm"><label class="floating-label-activo-sm">Cargas</label><input class="form-control form-control-sm" name="cargas_familiares" type="number" min="0" max="30" value="{{ old('cargas_familiares', $trabajador->cargas_familiares ?? 0) }}"></div>
        </div>
    </div>

    <div class="form-section">
        <div class="section-heading"><span class="section-badge">5</span><h2>Cuenta bancaria para pagos</h2></div>
        @php $cuenta = $trabajador->cuentasBancarias->first(); @endphp
        <div class="form-grid">
            <div class="field"><label class="floating-label-activo-sm">Banco</label><input class="form-control form-control-sm" name="cuenta_bancaria[banco]" value="{{ old('cuenta_bancaria.banco', $cuenta?->banco) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Tipo cuenta</label><select class="form-control form-control-sm" name="cuenta_bancaria[tipo_cuenta]"><option value="">Seleccionar</option>@foreach(['corriente','vista','ahorro','rut','otra'] as $tipoCuenta)<option value="{{ $tipoCuenta }}" @selected(old('cuenta_bancaria.tipo_cuenta', $cuenta?->tipo_cuenta) === $tipoCuenta)>{{ ucfirst($tipoCuenta) }}</option>@endforeach</select></div>
            <div class="field"><label class="floating-label-activo-sm">Numero cuenta</label><input class="form-control form-control-sm" name="cuenta_bancaria[numero_cuenta]" value="{{ old('cuenta_bancaria.numero_cuenta', $cuenta?->numero_cuenta) }}"></div>
            <div class="field"><label class="floating-label-activo-sm">Email pago</label><input class="form-control form-control-sm" name="cuenta_bancaria[email_pago]" type="email" value="{{ old('cuenta_bancaria.email_pago', $cuenta?->email_pago) }}"></div>
        </div>
    </div>

    <div class="actions">
        <a class="btn btn-secondary" href="{{ route('contabilidad.secciones.show', ['centroMedico' => $centroMedico->id, 'seccion' => 'rrhh']) }}">Cancelar</a>
        <button class="btn" type="submit">{{ $modo === 'crear' ? 'Crear trabajador' : 'Guardar cambios' }}</button>
        @if($modo === 'editar')
            <a class="btn" href="{{ route('contabilidad.trabajadores.gestion', ['centroMedico' => $centroMedico->id, 'trabajador' => $trabajador->id]) }}">Gestion laboral</a>
        @endif
    </div>
</form>
@endsection
