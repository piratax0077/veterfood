@extends('layouts.app')

@section('title', 'Gestion laboral trabajador')

@section('content')
<style>
    .worker-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px}
    .worker-title{display:flex;align-items:center;gap:12px;margin:0;color:#061a3d;font-size:34px}
    .worker-icon{width:44px;height:44px;border-radius:10px;background:#15803d;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900}
    .card-panel{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:20px;box-shadow:0 3px 10px rgba(15,23,42,.07);margin-bottom:16px}
    .worker-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.worker-summary div{border:1px solid #dbe3ee;border-radius:8px;padding:12px}
    .form-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;align-items:end}.span-2{grid-column:span 2}.span-3{grid-column:span 3}
    .tabs{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}.tabs button{background:#e5e7eb;color:#061a3d;border:0;border-radius:7px;padding:11px 16px;font-weight:900;text-decoration:none;cursor:pointer}
    .tabs button.active{background:#2563eb;color:#fff}.worker-tab-panel{display:none}.worker-tab-panel.active{display:block}.table-wrap{overflow-x:auto}.pill{display:inline-flex;border-radius:999px;background:#dbeafe;color:#1e3a8a;padding:5px 9px;font-weight:900;font-size:12px}
    @media(max-width:900px){.worker-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.form-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.span-3{grid-column:span 2}}
    @media(max-width:620px){.worker-summary,.form-grid{grid-template-columns:1fr}.span-2,.span-3{grid-column:auto}.worker-title{font-size:28px}}
</style>

<div class="worker-head">
    <a class="btn btn-secondary" href="{{ route('contabilidad.secciones.show', ['centroMedico' => $centroMedico->id, 'seccion' => 'rrhh']) }}">Volver a RRHH</a>
    <h1 class="worker-title"><span class="worker-icon">G</span>{{ $trabajador->nombre_completo }}</h1>
    <a class="btn" href="{{ route('contabilidad.trabajadores.edit', ['centroMedico' => $centroMedico->id, 'trabajador' => $trabajador->id]) }}">Editar ficha</a>
</div>


<div class="card-panel worker-summary">
    <div><strong>RUT</strong><br>{{ $trabajador->rut }}</div>
    <div><strong>Contacto</strong><br>{{ $trabajador->email ?: 'Sin email' }}<br>{{ $trabajador->telefono ?: 'Sin telefono' }}</div>
    <div><strong>Tipo</strong><br>{{ ucfirst($trabajador->tipo) }}</div>
    <div><strong>Contrato vigente</strong><br>{{ $contratoActivo?->cargo ?: 'Sin contrato vigente' }}</div>
</div>

<div class="tabs">
    <button class="active" type="button" data-worker-tab="contrato">Contrato</button>
    <button type="button" data-worker-tab="remuneracion">Remuneraciones</button>
    <button type="button" data-worker-tab="finiquito">Finiquito</button>
</div>

<div id="contrato" class="card-panel worker-tab-panel active">
    <h2>Contrato de trabajo</h2>
    <form method="POST" action="{{ route('contabilidad.contratos.store', ['centroMedico' => $centroMedico->id, 'trabajador' => $trabajador->id]) }}">
        @csrf
        <input type="hidden" name="_redirect_to" value="1">
        <div class="form-grid">
            <div><label class="floating-label-activo-sm">Tipo</label><select class="form-control form-control-sm" name="tipo" required><option value="indefinido">Indefinido</option><option value="plazo_fijo">Plazo fijo</option><option value="honorarios">Honorarios</option><option value="prestacion_servicios">Prestacion servicios</option></select></div>
            <div><label class="floating-label-activo-sm">Inicio</label><input class="form-control form-control-sm" name="fecha_inicio" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
            <div><label class="floating-label-activo-sm">Termino</label><input class="form-control form-control-sm" name="fecha_termino" type="date"></div>
            <div class="span-2"><label class="floating-label-activo-sm">Cargo</label><input class="form-control form-control-sm" name="cargo" value="{{ $trabajador->funcion }}" required></div>
            <div><label class="floating-label-activo-sm">Horas</label><input class="form-control form-control-sm" name="horas_semanales" type="number" min="1" max="60" value="45"></div>
            <div><label class="floating-label-activo-sm">Sueldo base</label><input class="form-control form-control-sm" name="sueldo_base" type="number" min="0" value="{{ $contratoActivo?->sueldo_base ?? 0 }}" required></div>
            <div><label class="floating-label-activo-sm">Monto imponible</label><input class="form-control form-control-sm" name="monto_imponible" type="number" min="0" value="{{ $contratoActivo?->monto_imponible ?? 0 }}"></div>
            <div><label class="floating-label-activo-sm">Cargas</label><input class="form-control form-control-sm" name="cargas_familiares" type="number" min="0" value="0"></div>
            <div><label class="floating-label-activo-sm">Entrada</label><input class="form-control form-control-sm" name="hora_entrada" type="time"></div>
            <div><label class="floating-label-activo-sm">Salida</label><input class="form-control form-control-sm" name="hora_salida" type="time"></div>
            <div><label class="floating-label-activo-sm">Estado</label><select class="form-control form-control-sm" name="estado"><option value="vigente">Vigente</option><option value="suspendido">Suspendido</option></select></div>
            <button class="btn" type="submit">Guardar contrato</button>
        </div>
    </form>

    <div class="table-wrap" style="margin-top:16px">
        <table>
            <thead><tr><th>Inicio</th><th>Tipo</th><th>Cargo</th><th>Sueldo</th><th>Estado</th></tr></thead>
            <tbody>
                @forelse($contratos as $contrato)
                    <tr><td>{{ $contrato->fecha_inicio?->format('d-m-Y') }}</td><td>{{ $contrato->tipo }}</td><td>{{ $contrato->cargo }}</td><td>${{ number_format($contrato->sueldo_base, 0, ',', '.') }}</td><td><span class="pill">{{ $contrato->estado }}</span></td></tr>
                @empty
                    <tr><td colspan="5" class="muted">Sin contratos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="remuneracion" class="card-panel worker-tab-panel">
    <h2>Remuneraciones</h2>
    @if($contratoActivo)
        <form method="POST" action="{{ route('contabilidad.remuneraciones.store', ['centroMedico' => $centroMedico->id]) }}">
            @csrf
            <input type="hidden" name="contrato_id" value="{{ $contratoActivo->id }}">
            <div class="form-grid">
                <div><label class="floating-label-activo-sm">Año</label><input class="form-control form-control-sm" name="anio" type="number" value="{{ now()->year }}" required></div>
                <div><label class="floating-label-activo-sm">Mes</label><input class="form-control form-control-sm" name="mes" type="number" min="1" max="12" value="{{ now()->month }}" required></div>
                <div><label class="floating-label-activo-sm">Sueldo base</label><input class="form-control form-control-sm" name="sueldo_base" type="number" value="{{ $contratoActivo->sueldo_base }}" min="0" required></div>
                <div><label class="floating-label-activo-sm">Bonos</label><input class="form-control form-control-sm" name="bonos" type="number" value="0" min="0"></div>
                <div><label class="floating-label-activo-sm">Descuentos</label><input class="form-control form-control-sm" name="otros_descuentos" type="number" value="0" min="0"></div>
                <div><label class="floating-label-activo-sm">Estado</label><select class="form-control form-control-sm" name="estado"><option value="calculada">Calculada</option><option value="borrador">Borrador</option></select></div>
                <button class="btn" type="submit">Guardar remuneracion</button>
            </div>
        </form>
    @else
        <p class="muted">Para calcular remuneraciones primero debe existir un contrato vigente.</p>
    @endif
    <div class="table-wrap" style="margin-top:16px">
        <table>
            <thead><tr><th>Periodo</th><th>Liquido</th><th>Estado</th><th>Pago</th></tr></thead>
            <tbody>
                @forelse($remuneraciones as $remuneracion)
                    <tr><td>{{ $remuneracion->mes }}/{{ $remuneracion->anio }}</td><td>${{ number_format($remuneracion->liquido_pagar, 0, ',', '.') }}</td><td><span class="pill">{{ $remuneracion->estado }}</span></td><td>{{ $remuneracion->fecha_pago?->format('d-m-Y') ?: '-' }}</td></tr>
                @empty
                    <tr><td colspan="4" class="muted">Sin remuneraciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="finiquito" class="card-panel worker-tab-panel">
    <h2>Finiquito</h2>
    @if($contratoActivo)
        <form method="POST" action="{{ route('contabilidad.finiquitos.store', ['centroMedico' => $centroMedico->id]) }}">
            @csrf
            <input type="hidden" name="contrato_id" value="{{ $contratoActivo->id }}">
            <div class="form-grid">
                <div class="span-2"><label class="floating-label-activo-sm">Causal</label><input class="form-control form-control-sm" name="causal" required></div>
                <div><label class="floating-label-activo-sm">Fecha salida</label><input class="form-control form-control-sm" name="fecha_salida" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                <div><label class="floating-label-activo-sm">Base calculo</label><input class="form-control form-control-sm" name="base_calculo" type="number" value="{{ $contratoActivo->sueldo_base }}"></div>
                <div><label class="floating-label-activo-sm">Vacaciones</label><input class="form-control form-control-sm" name="vacaciones" type="number" value="0"></div>
                <div><label class="floating-label-activo-sm">Mes aviso</label><input class="form-control form-control-sm" name="mes_aviso" type="number" value="0"></div>
                <div><label class="floating-label-activo-sm">Indemnizacion</label><input class="form-control form-control-sm" name="indemnizacion_anios_servicio" type="number" value="0"></div>
                <div><label class="floating-label-activo-sm">Rem. pendiente</label><input class="form-control form-control-sm" name="remuneracion_pendiente" type="number" value="0"></div>
                <div><label class="floating-label-activo-sm">Seguro cesantia</label><input class="form-control form-control-sm" name="descuento_seguro_cesantia" type="number" value="0"></div>
                <div><label class="floating-label-activo-sm">Otros desc.</label><input class="form-control form-control-sm" name="otros_descuentos" type="number" value="0"></div>
                <div><label class="floating-label-activo-sm">Estado</label><select class="form-control form-control-sm" name="estado"><option value="emitido">Emitido</option><option value="borrador">Borrador</option></select></div>
                <button class="btn" type="submit">Guardar finiquito</button>
            </div>
        </form>
    @else
        <p class="muted">Para emitir finiquito primero debe existir un contrato.</p>
    @endif
    <div class="table-wrap" style="margin-top:16px">
        <table>
            <thead><tr><th>Salida</th><th>Causal</th><th>Total</th><th>Estado</th></tr></thead>
            <tbody>
                @forelse($finiquitos as $finiquito)
                    <tr><td>{{ $finiquito->fecha_salida?->format('d-m-Y') }}</td><td>{{ $finiquito->causal }}</td><td>${{ number_format($finiquito->total, 0, ',', '.') }}</td><td><span class="pill">{{ $finiquito->estado }}</span></td></tr>
                @empty
                    <tr><td colspan="4" class="muted">Sin finiquitos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
document.querySelectorAll('[data-worker-tab]').forEach((button) => {
    button.addEventListener('click', () => {
        const target = button.dataset.workerTab;
        document.querySelectorAll('[data-worker-tab]').forEach((item) => item.classList.toggle('active', item === button));
        document.querySelectorAll('.worker-tab-panel').forEach((panel) => {
            panel.classList.toggle('active', panel.id === target);
        });
        history.replaceState(null, '', `#${target}`);
    });
});

const initialTab = window.location.hash?.replace('#', '');
if (initialTab) {
    document.querySelector(`[data-worker-tab="${initialTab}"]`)?.click();
}
</script>
@endsection
