@extends('layouts.app')

@section('title', 'Historial de repartos')
@section('estilos', 'css/admin-repartos-historial.css')

@section('content')

<x-encabezado-pagina
    titulo="Historial de repartos"
    descripcion="Conformidad del cliente, reclamos y trazabilidad."
    :volver="route('admin.dashboard') . '#operacion'">
    <a class="encabezado-boton encabezado-boton--secundario" href="{{ route('admin.repartidores.index') }}">Administrar repartidores</a>
</x-encabezado-pagina>

<div class="classic-card" style="margin-bottom:18px">
    <form class="filters" method="GET" action="{{ route('admin.repartos.historial') }}">
        <div>
            <label class="floating-label-activo-sm">Repartidor</label>
            <select class="form-control form-control-sm" name="repartidor_id">
                <option value="">Todos</option>
                @foreach($repartidores as $repartidor)
                    <option value="{{ $repartidor->id }}" @selected((string)($filtros['repartidor_id'] ?? '') === (string)$repartidor->id)>{{ $repartidor->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="floating-label-activo-sm">Estado reparto</label>
            <select class="form-control form-control-sm" name="estado">
                <option value="">Todos</option>
                @foreach(['recibido', 'pagado', 'asignado', 'preparando', 'en_ruta', 'entregado', 'cancelado'] as $estado)
                    <option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="floating-label-activo-sm">Conformidad / reclamos</label>
            <select class="form-control form-control-sm" name="feedback">
                <option value="">Todos</option>
                <option value="conforme" @selected(($filtros['feedback'] ?? '') === 'conforme')>Conforme</option>
                <option value="no_conforme" @selected(($filtros['feedback'] ?? '') === 'no_conforme')>No conforme</option>
                <option value="pendiente" @selected(($filtros['feedback'] ?? '') === 'pendiente')>Pendiente</option>
                <option value="reclamo" @selected(($filtros['feedback'] ?? '') === 'reclamo')>Con reclamo</option>
            </select>
        </div>
        <button class="btn-success boton-buscar">Filtrar historial</button>
    </form>
</div>

<div class="classic-card table-wrap">
    <table class="history-table">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Cliente</th>
                <th>Repartidor</th>
                <th>Entrega</th>
                <th>Tracking</th>
                <th>Conformidad / reclamo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $pedido)
                @php
                    $ultimoEvento = $pedido->tracking->last();
                    $conformidad = $pedido->cliente_conformidad ?: 'pendiente';
                    $claseConformidad = $conformidad === 'conforme' ? 'tono-verde' : ($conformidad === 'no_conforme' ? 'tono-rojo' : 'tono-naranjo');
                @endphp
                <tr>
                    <td>
                        <strong>{{ $pedido->codigo_tracking }}</strong><br>
                        <span class="badge tono-azul">{{ strtoupper(str_replace('_', ' ', $pedido->estado)) }}</span><br>
                        <span class="muted">${{ number_format($pedido->total, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        {{ $pedido->cliente_nombre }}<br>
                        <span class="muted">{{ $pedido->cliente_telefono ?: $pedido->cliente_email }}</span><br>
                        <span class="muted">{{ $pedido->direccion_entrega }}</span>
                    </td>
                    <td>
                        <strong>{{ $pedido->repartidor?->name ?? 'Sin repartidor' }}</strong><br>
                        <span class="muted">{{ $pedido->repartidor?->vehiculo_patente ?: 'Sin patente' }}</span>
                    </td>
                    <td>
                        Programada: {{ $pedido->fecha_entrega?->format('d-m-Y') ?? 'Sin fecha' }}<br>
                        <span class="muted">Ruta: {{ $pedido->despachado_at?->format('d-m-Y H:i') ?? 'Sin salida' }}</span><br>
                        <span class="muted">Entrega: {{ $pedido->entregado_at?->format('d-m-Y H:i') ?? 'Pendiente' }}</span>
                    </td>
                    <td>
                        @if($ultimoEvento)
                            <strong>{{ ucfirst(str_replace('_', ' ', $ultimoEvento->estado)) }}</strong><br>
                            <span class="muted">{{ $ultimoEvento->created_at?->format('d-m-Y H:i') }}</span><br>
                            <span class="muted">{{ $ultimoEvento->mensaje }}</span>
                        @else
                            <span class="muted">Sin eventos</span>
                        @endif
                        <ul class="event-list">
                            @foreach($pedido->tracking->take(-3) as $evento)
                                <li><span class="muted">{{ $evento->created_at?->format('H:i') }} - {{ str_replace('_', ' ', $evento->estado) }}</span></li>
                            @endforeach
                        </ul>
                        <a href="{{ route('tracking.show', $pedido->codigo_tracking) }}">Ver tracking publico</a>
                    </td>
                    <td>
                        <span class="badge {{ $claseConformidad }}">{{ strtoupper(str_replace('_', ' ', $conformidad)) }}</span>
                        @if($pedido->cliente_reclamo)
                            <p><strong>Reclamo:</strong> {{ $pedido->cliente_reclamo }}</p>
                            <p class="muted">Estado reclamo: {{ str_replace('_', ' ', $pedido->reclamo_estado ?: 'abierto') }}</p>
                        @endif
                        <form class="feedback-form" method="POST" action="{{ route('admin.repartos.feedback', $pedido) }}">
                            @csrf
                            @method('PATCH')
                            <select class="form-control form-control-sm" name="cliente_conformidad">
                                <option value="pendiente" @selected($conformidad === 'pendiente')>Pendiente</option>
                                <option value="conforme" @selected($conformidad === 'conforme')>Conforme</option>
                                <option value="no_conforme" @selected($conformidad === 'no_conforme')>No conforme</option>
                            </select>
                            <select class="form-control form-control-sm" name="reclamo_estado">
                                <option value="abierto" @selected(($pedido->reclamo_estado ?: 'abierto') === 'abierto')>Abierto</option>
                                <option value="en_revision" @selected($pedido->reclamo_estado === 'en_revision')>En revision</option>
                                <option value="resuelto" @selected($pedido->reclamo_estado === 'resuelto')>Resuelto</option>
                                <option value="cerrado" @selected($pedido->reclamo_estado === 'cerrado')>Cerrado</option>
                            </select>
                            <textarea class="form-control form-control-sm" name="cliente_reclamo" placeholder="Reclamo u observacion del cliente">{{ $pedido->cliente_reclamo }}</textarea>
                            <x-boton-tabla tipo="guardar">Guardar</x-boton-tabla>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No hay repartos con los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:16px">
        {{ $pedidos->links() }}
    </div>
</div>
@endsection
