@extends('layouts.app')

@section('title', 'Carro')

@section('content')
<div class="between">
    <h1>Carro total de la tienda</h1>
    <a class="btn btn-secondary" href="{{ route('tienda.catalogo') }}">Seguir comprando</a>
</div>

@if($planExtra)
    <div class="alert">
        Extras asociados al pedido mensual de {{ $planExtra->producto?->nombre }}. La confirmacion se hace en el pago.
    </div>
@endif

@if($items->isEmpty())
    <div class="card">El carro esta vacio.</div>
@else
<form method="POST" action="{{ route('tienda.carro.actualizar') }}">
    @csrf
    <div class="card">
        <table>
            <thead><tr><th>Producto</th><th>Seccion</th><th>Precio</th><th>Cantidad</th><th>Total</th><th>Eliminar</th></tr></thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td><strong>{{ $item['producto']->nombre }}</strong><br><span class="muted">{{ $item['producto']->marca }}</span></td>
                        <td><span class="badge">{{ $item['producto']->categoria }}</span></td>
                        <td>${{ number_format($item['producto']->precio, 0, ',', '.') }}</td>
                        <td><input class="form-control form-control-sm" type="number" min="0" name="cantidades[{{ $item['producto']->id }}]" value="{{ $item['cantidad'] }}"></td>
                        <td>${{ number_format($item['total'], 0, ',', '.') }}</td>
                        <td><button class="btn-secondary" type="button" onclick="this.closest('tr').querySelector('input').value=0;this.form.submit()">Eliminar</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="text-align:right;margin-top:16px">
            <p>Subtotal: <strong>${{ number_format($subtotal, 0, ',', '.') }}</strong></p>
            <p>Envio: <strong>${{ number_format($costoEnvio, 0, ',', '.') }}</strong></p>
            <h2>Total: ${{ number_format($total, 0, ',', '.') }}</h2>
            <div class="row" style="justify-content:flex-end">
                <button class="btn-secondary">Actualizar</button>
                <a class="btn btn-success" href="{{ route('tienda.checkout') }}">Pagar todo</a>
            </div>
        </div>
    </div>
</form>
@endif
@endsection
