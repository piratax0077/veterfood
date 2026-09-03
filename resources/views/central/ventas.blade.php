@extends('layouts.app')

@section('title', 'Central ventas - ' . $config['titulo'])

@section('content')
<style>
    .sales-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:18px}
    .sales-head h1{font-size:34px;color:#06152f;margin:0 0 8px}
    .sales-nav{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;padding:12px;background:#fff;border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 3px 8px rgba(15,23,42,.06)}
    .sales-nav a{min-height:42px;padding:11px 16px;border-radius:7px;background:#e5e7eb;color:#111827;text-decoration:none;font-weight:900}
    .sales-nav a.active{background:#2563eb;color:#fff}
    .sales-nav a.green{background:#15803d;color:#fff}.sales-nav a.orange{background:#f59e0b;color:#111827}.sales-nav a.pink{background:#db2777;color:#fff}
    .metric-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
    .metric{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:14px;box-shadow:0 3px 8px rgba(15,23,42,.06)}
    .metric strong{display:block;font-size:24px;color:#06152f}.metric span{display:block;color:#64748b;margin-top:4px}
    .sales-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:18px;box-shadow:0 3px 8px rgba(15,23,42,.07)}
    .sales-table-wrap{overflow:auto}.sales-table{min-width:1180px}.sales-table th{background:#f8fafc;color:#334155;font-size:13px;text-transform:uppercase}.sales-table th,.sales-table td{padding:12px 10px;vertical-align:top}
    .status-pill{display:inline-flex;border-radius:999px;background:#e0f2fe;color:#075985;font-size:12px;font-weight:900;padding:5px 9px}
    .paid-pill{background:#dcfce7;color:#166534}.warn-pill{background:#fef3c7;color:#92400e}.danger-pill{background:#fee2e2;color:#991b1b}
    .mini-actions{display:grid;gap:8px;min-width:180px}.mini-actions form{display:flex;gap:6px;align-items:center}.mini-actions select{min-height:36px;padding:7px;font-size:13px}.mini-actions button,.mini-actions a{min-height:36px;padding:8px 10px;border-radius:6px;font-size:13px;min-width:auto}
    .items-list{margin:0;padding-left:18px;color:#475569;line-height:1.45}.tracking-list{margin:0;padding-left:16px;color:#475569;font-size:13px}
    .pagination-wrap{margin-top:16px}
    @media(max-width:900px){.sales-head{align-items:flex-start;flex-direction:column}.metric-grid{grid-template-columns:1fr}.sales-nav a{width:100%;text-align:center}}
</style>

<div class="sales-head">
    <div>
        <h1>{{ $config['titulo'] }}</h1>
        <p class="muted">{{ $config['descripcion'] }}</p>
    </div>
    <div class="row">
        <a class="btn btn-secondary" href="{{ route('central.panel') }}">Stock</a>
        <a class="btn" href="{{ route('central.ingreso', 'alimentos') }}">Ingresar productos</a>
    </div>
</div>

<nav class="sales-nav" aria-label="Ventas">
    @foreach($vistas as $slug => $vista)
        @php($clase = match($slug) {'despacho' => 'green', 'tracking' => 'orange', 'abonados' => 'pink', default => ''})
        <a class="{{ $vistaActiva === $slug ? 'active' : $clase }}" href="{{ route('central.ventas', $slug) }}">{{ $vista['titulo'] }}</a>
    @endforeach
</nav>

<div class="metric-grid">
    <div class="metric"><strong>{{ $pedidos->total() }}</strong><span>Pedidos en vista</span></div>
    <div class="metric"><strong>{{ $pedidos->getCollection()->whereNotNull('plan_pedido_id')->count() }}</strong><span>Abonados en pagina</span></div>
    <div class="metric"><strong>{{ $pedidos->getCollection()->whereNull('repartidor_id')->count() }}</strong><span>Sin repartidor</span></div>
    <div class="metric"><strong>${{ number_format($pedidos->getCollection()->sum('total'), 0, ',', '.') }}</strong><span>Total pagina</span></div>
</div>

<section class="sales-card">
    <div class="sales-table-wrap">
        <table class="sales-table">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Productos</th>
                    <th>Entrega</th>
                    <th>Estado</th>
                    <th>Repartidor</th>
                    <th>Seguimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                    <tr>
                        <td>
                            <strong>{{ $pedido->codigo_tracking }}</strong><br>
                            <span class="muted">{{ optional($pedido->created_at)->format('d-m-Y H:i') }}</span><br>
                            <strong>${{ number_format($pedido->total, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <strong>{{ $pedido->cliente_nombre }}</strong><br>
                            <span class="muted">{{ $pedido->cliente_email }}</span><br>
                            <span class="muted">{{ $pedido->cliente_telefono ?: 'Sin telefono' }}</span>
                        </td>
                        <td>
                            @if($pedido->plan_pedido_id)
                                <span class="status-pill paid-pill">Abonado</span>
                            @else
                                <span class="status-pill">Esporadico</span>
                            @endif
                            <br><span class="muted">{{ $pedido->frecuencia }}</span>
                        </td>
                        <td>
                            <ul class="items-list">
                                @foreach($pedido->items as $item)
                                    <li>{{ $item->cantidad }} x {{ $item->producto_nombre }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            <strong>{{ optional($pedido->fecha_entrega)->format('d-m-Y') }}</strong><br>
                            <span class="muted">{{ $pedido->direccion_entrega }}</span>
                            @if($pedido->notas_entrega)<br><span class="muted">{{ \Illuminate\Support\Str::limit($pedido->notas_entrega, 90) }}</span>@endif
                        </td>
                        <td>
                            <span class="status-pill {{ $pedido->estado === 'cancelado' ? 'danger-pill' : ($pedido->estado === 'entregado' ? 'paid-pill' : 'warn-pill') }}">{{ str_replace('_', ' ', $pedido->estado) }}</span><br>
                            <span class="muted">Pago: {{ $pedido->estado_pago }}</span>
                        </td>
                        <td>{{ $pedido->repartidor?->name ?? 'Sin asignar' }}</td>
                        <td>
                            <ul class="tracking-list">
                                @foreach($pedido->tracking->take(-3) as $evento)
                                    <li>{{ $evento->estado }} · {{ optional($evento->created_at)->format('d-m H:i') }}</li>
                                @endforeach
                            </ul>
                            <a href="{{ route('tracking.show', $pedido->codigo_tracking) }}" target="_blank" rel="noopener">Tracking publico</a>
                        </td>
                        <td>
                            <div class="mini-actions">
                                @if(in_array($vistaActiva, ['preparar', 'despacho'], true))
                                    <form method="POST" action="{{ route('central.estado', $pedido) }}">
                                        @csrf
                                        <select class="form-control form-control-sm" name="estado">
                                            @foreach(['preparando' => 'Preparando', 'asignado' => 'Asignado', 'en_ruta' => 'En ruta', 'entregado' => 'Entregado', 'cancelado' => 'Cancelado'] as $estado => $texto)
                                                <option value="{{ $estado }}" @selected($pedido->estado === $estado)>{{ $texto }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn-success">Guardar</button>
                                    </form>
                                @endif

                                @if(in_array($vistaActiva, ['despacho', 'preparar'], true))
                                    <form method="POST" action="{{ route('central.asignar', $pedido) }}">
                                        @csrf
                                        <select class="form-control form-control-sm" name="repartidor_id">
                                            @foreach($repartidores as $repartidor)
                                                <option value="{{ $repartidor->id }}" @selected($pedido->repartidor_id === $repartidor->id)>{{ $repartidor->name }}</option>
                                            @endforeach
                                        </select>
                                        <button>Asignar</button>
                                    </form>
                                @endif

                                @if($pedido->plan_pedido_id)
                                    <form method="POST" action="{{ route('central.avisar.entrega', $pedido) }}">
                                        @csrf
                                        <button class="btn-success">Avisar entrega y extras</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="muted">No hay pedidos para esta vista.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $pedidos->links('vendor.pagination.admin') }}</div>
</section>
@endsection
