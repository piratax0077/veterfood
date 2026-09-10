@extends('layouts.app')

@section('title', 'Encuestas comerciales')
@section('estilos', 'css/admin-encuestas.css')

@section('content')

<x-encabezado-pagina
    titulo="Encuestas comerciales"
    descripcion="Opinión sobre vouchers y FONAVET: propuesta mensual desde $6.990 para atención, beneficios y descuentos."
    :volver="route('admin.dashboard') . '#operacion'" />

<div class="survey-kpis">
    <div class="survey-kpi">
        <span>Registros totales</span>
        <strong>{{ $profesionales->count() + $clientes->count() + $instituciones->count() }}</strong>
    </div>
    <div class="survey-kpi">
        <span>Encuestas respondidas</span>
        <strong>{{ $respondidas->count() + $respondidasClientes->count() + $respondidasInstituciones->count() }}</strong>
    </div>
    <div class="survey-kpi">
        <span>Reciben voucher</span>
        <strong>{{ $profesionales->where('recibe_voucher', true)->count() + $clientes->where('recibe_voucher', true)->count() + $instituciones->where('recibe_voucher', true)->count() }}</strong>
    </div>
    <div class="survey-kpi">
        <span>Grupos medidos</span>
        <strong>3</strong>
    </div>
</div>

<div class="classic-card survey-section">
    <h2>Que mide FONAVET</h2>
    <p class="muted">FONAVET, Fondo Nacional Veterinario, se evalua como un sistema mensual pagado por el dueno de la mascota para acceder a atencion, beneficios, vouchers y descuentos veterinarios.</p>
    <div class="fonavet-grid">
        <div class="fonavet-box">
            <strong>$6.990 / mes</strong>
            <span class="muted">Plan base sugerido por mascota.</span>
            <ul>
                <li>Vouchers y descuentos controlados por QR.</li>
                <li>Recordatorio de vacunas y desparasitaciones.</li>
                <li>Acceso a profesionales y comercios adheridos.</li>
            </ul>
        </div>
        <div class="fonavet-box">
            <strong>$9.990 / mes</strong>
            <span class="muted">Plan integral recomendado.</span>
            <ul>
                <li>Historial clinico y carne sanitario digital.</li>
                <li>Beneficios en alimento inscrito y servicios.</li>
                <li>Placa QR de identificacion y datos del dueno.</li>
            </ul>
        </div>
        <div class="fonavet-box">
            <strong>Debe ofrecer</strong>
            <span class="muted">Valor claro para cliente y red adherida.</span>
            <ul>
                <li>Atencion preferente y agenda por zona.</li>
                <li>Canje auditable de beneficios.</li>
                <li>Convenios con veterinarias, farmacias, hoteles y cuidados.</li>
            </ul>
        </div>
    </div>
</div>

<div class="classic-card survey-section">
    <h2>Profesionales</h2>
    <p class="muted">Percepcion registrada en la inscripcion de profesionales. Promedio descuento: <strong>{{ $promedioDescuento }}%</strong>.</p>
    <div class="bar-list">
        @foreach($resumenEncuesta as $fila)
            <div class="bar-row">
                <strong>{{ $fila['texto'] }}</strong>
                <div class="bar-track"><div class="bar-fill" style="width:{{ $fila['porcentaje'] }}%"></div></div>
                <span class="muted">{{ $fila['total'] }} / {{ number_format($fila['porcentaje'], 1, ',', '.') }}%</span>
            </div>
        @endforeach
    </div>
</div>

<div class="classic-card survey-section">
    <h2>Detalle por profesional</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Profesional</th>
                    <th>Especialidad</th>
                    <th>Voucher</th>
                    <th>% descuento</th>
                    <th>Encuesta</th>
                    <th>Comentario</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profesionales as $profesional)
                    <tr>
                        <td>{{ $profesional->nombre }}<br><span class="muted">{{ $profesional->email }}</span></td>
                        <td>{{ $profesional->especialidad }}</td>
                        <td>{{ $profesional->recibe_voucher ? 'Recibe' : 'No recibe' }}</td>
                        <td>{{ $profesional->porcentaje_descuento_voucher !== null ? $profesional->porcentaje_descuento_voucher . '%' : 'Sin dato' }}</td>
                        <td><span class="badge tono-celeste">{{ $opciones[$profesional->encuesta_sistema_nacional] ?? 'Sin respuesta' }}</span></td>
                        <td>{{ $profesional->comentario_sistema_nacional ?: 'Sin comentario' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">No hay profesionales registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="classic-card survey-section">
    <h2>Clientes</h2>
    <p class="muted">Interes de clientes en vouchers y FONAVET, Fondo Nacional Veterinario con pago mensual para atencion y descuentos. Promedio descuento esperado: <strong>{{ $promedioDescuentoClientes }}%</strong>.</p>
    <div class="bar-list">
        @foreach($resumenEncuestaClientes as $fila)
            <div class="bar-row">
                <strong>{{ $fila['texto'] }}</strong>
                <div class="bar-track"><div class="bar-fill" style="width:{{ $fila['porcentaje'] }}%"></div></div>
                <span class="muted">{{ $fila['total'] }} / {{ number_format($fila['porcentaje'], 1, ',', '.') }}%</span>
            </div>
        @endforeach
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Cliente</th><th>Plan</th><th>Voucher</th><th>% descuento</th><th>Encuesta</th><th>Comentario</th></tr></thead>
            <tbody>
                @forelse($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->name }}<br><span class="muted">{{ $cliente->email }}</span></td>
                        <td>{{ $cliente->plan_preferido ?: 'Sin plan' }}</td>
                        <td>{{ $cliente->recibe_voucher ? 'Recibe' : 'No recibe' }}</td>
                        <td>{{ $cliente->porcentaje_descuento_voucher !== null ? $cliente->porcentaje_descuento_voucher . '%' : 'Sin dato' }}</td>
                        <td><span class="badge tono-celeste">{{ $opciones[$cliente->encuesta_sistema_nacional] ?? 'Sin respuesta' }}</span></td>
                        <td>{{ $cliente->comentario_sistema_nacional ?: 'Sin comentario' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">No hay clientes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="classic-card survey-section">
    <h2>Instituciones y comercios adheridos</h2>
    <p class="muted">Interes de sucursales, comercios, clinicas, farmacias e instituciones en integrarse a FONAVET. Promedio descuento convenio: <strong>{{ $promedioDescuentoInstituciones }}%</strong>.</p>
    <div class="bar-list">
        @foreach($resumenEncuestaInstituciones as $fila)
            <div class="bar-row">
                <strong>{{ $fila['texto'] }}</strong>
                <div class="bar-track"><div class="bar-fill" style="width:{{ $fila['porcentaje'] }}%"></div></div>
                <span class="muted">{{ $fila['total'] }} / {{ number_format($fila['porcentaje'], 1, ',', '.') }}%</span>
            </div>
        @endforeach
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Institucion</th><th>Tipo</th><th>Voucher</th><th>% descuento</th><th>Encuesta</th><th>Comentario</th></tr></thead>
            <tbody>
                @forelse($instituciones as $institucion)
                    <tr>
                        <td>{{ $institucion->nombre }}<br><span class="muted">{{ $institucion->email }}</span></td>
                        <td>{{ $institucion->tipo }}</td>
                        <td>{{ $institucion->recibe_voucher ? 'Recibe' : 'No recibe' }}</td>
                        <td>{{ $institucion->porcentaje_descuento_voucher !== null ? $institucion->porcentaje_descuento_voucher . '%' : 'Sin dato' }}</td>
                        <td><span class="badge tono-celeste">{{ $opciones[$institucion->encuesta_sistema_nacional] ?? 'Sin respuesta' }}</span></td>
                        <td>{{ $institucion->comentario_sistema_nacional ?: 'Sin comentario' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">No hay instituciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
