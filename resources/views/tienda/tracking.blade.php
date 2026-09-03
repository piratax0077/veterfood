@extends('layouts.app')

@section('title', 'Tracking')

@section('content')
<div class="between">
    <div>
        <h1>Pedido {{ $pedido->codigo_tracking }}</h1>
        <p class="muted">{{ $pedido->cliente_nombre }} - {{ $pedido->direccion_entrega }}</p>
    </div>
    <span class="badge">{{ strtoupper(str_replace('_', ' ', $pedido->estado)) }}</span>
</div>

<div class="grid">
    <div class="col-5">
        <div class="card">
            <h2>Detalle</h2>
            <p>Pago: <strong>{{ strtoupper($pedido->estado_pago) }}</strong></p>
            <p>Entrega: <strong>{{ $pedido->fecha_entrega?->format('d-m-Y') }}</strong></p>
            <p>Repartidor: <strong>{{ $pedido->repartidor?->name ?? 'Por asignar' }}</strong></p>
            @foreach($pedido->items as $item)
                <p class="between"><span>{{ $item->cantidad }} x {{ $item->producto_nombre }}</span><strong>${{ number_format($item->total, 0, ',', '.') }}</strong></p>
            @endforeach
            @if(($pedido->descuento_total ?? 0) > 0)
                <p class="between"><span>Voucher aplicado<br><span class="muted">{{ $pedido->voucher?->codigo }}</span></span><strong>- ${{ number_format($pedido->descuento_total, 0, ',', '.') }}</strong></p>
            @endif
            <h2 class="between"><span>Total</span><span>${{ number_format($pedido->total, 0, ',', '.') }}</span></h2>
        </div>
    </div>
    <div class="col-7">
        <div class="card">
            <h2>Seguimiento</h2>
            @foreach($pedido->tracking as $evento)
                <div style="border-left:3px solid var(--accent);padding:0 0 18px 14px">
                    <strong>{{ ucfirst(str_replace('_', ' ', $evento->estado)) }}</strong>
                    <div class="muted">{{ $evento->created_at->format('d-m-Y H:i') }}</div>
                    <p>{{ $evento->mensaje }}</p>
                    @if($evento->latitud && $evento->longitud)
                        <a target="_blank" href="https://maps.google.com/?q={{ $evento->latitud }},{{ $evento->longitud }}">Ver ubicacion</a>
                    @endif
                    @if($evento->foto_entrega)
                        <p><img src="{{ asset('storage/' . $evento->foto_entrega) }}" alt="Foto entrega" style="max-width:100%;max-height:220px;border-radius:8px"></p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
