@extends('layouts.app')

@section('title', 'Pago de plan')

@section('content')
<style>
    .plan-pay-head{display:grid;grid-template-columns:160px minmax(0,1fr);gap:18px;align-items:center;margin-bottom:18px}
    .plan-pay-head h1{margin:0;color:#06152f;font-size:34px}
    .pay-layout{display:grid;grid-template-columns:minmax(0,1fr) 420px;gap:18px;align-items:start}
    .pay-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:22px;box-shadow:0 3px 8px rgba(15,23,42,.07)}
    .pay-card h2{font-size:25px;color:#06152f;margin-bottom:10px}
    .plan-badge{display:inline-flex;border-radius:999px;background:#ede9fe;color:#5b21b6;font-size:12px;font-weight:900;padding:6px 10px;margin-bottom:12px}
    .price-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:16px 0}
    .price-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px}
    .price-box span{display:block;color:#64748b;font-size:12px;font-weight:900;text-transform:uppercase}
    .price-box strong{display:block;color:#06152f;font-size:26px;margin-top:5px}
    .include-list{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0 0;padding:0;list-style:none}
    .include-list li{background:#ecfdf5;color:#14532d;border-radius:999px;padding:7px 10px;font-weight:800;font-size:13px}
    .secure-note{background:#eff6ff;border:1px solid #bfdbfe;color:#1e3a8a;border-radius:8px;padding:12px;margin-top:14px;font-weight:800}
    @media(max-width:950px){.plan-pay-head,.pay-layout,.price-grid{grid-template-columns:1fr}}
</style>

<div class="plan-pay-head">
    <a class="btn btn-secondary" href="{{ route('cliente.panel') }}#mi-plan">Volver</a>
    <h1>Pago y mejora de plan</h1>
</div>

<div class="pay-layout">
    <div class="pay-card">
        <span class="plan-badge">{{ $plan['etiqueta'] }}</span>
        <h2>{{ $plan['nombre'] }}</h2>
        <p class="muted">{{ $plan['descripcion'] }}</p>

        <div class="price-grid">
            <div class="price-box">
                <span>Pago inicial</span>
                <strong>${{ number_format($plan['valor_inicial'], 0, ',', '.') }}</strong>
            </div>
            <div class="price-box">
                <span>Cargo mensual</span>
                <strong>${{ number_format($plan['valor_mensual'], 0, ',', '.') }}</strong>
            </div>
        </div>

        <h3>Incluye</h3>
        <ul class="include-list">
            @foreach($plan['incluye'] as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>

        <div class="secure-note">
            Este pago queda simulado en ambiente local. En servidor se conecta aqui Webpay, Mercado Pago u otra pasarela con tokenizacion de tarjeta.
        </div>
    </div>

    <div class="pay-card">
        <h2>Datos de pago</h2>
        @if($errors->any())
            <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('cliente.planes.pago.confirmar', $plan['slug']) }}">
            @csrf
            <label class="floating-label-activo-sm">Cliente</label>
            <input class="form-control form-control-sm" value="{{ $user->name }}" readonly>

            <label class="floating-label-activo-sm">Email</label>
            <input class="form-control form-control-sm" value="{{ $user->email }}" readonly>

            <label class="floating-label-activo-sm">Metodo de pago</label>
            <select class="form-control form-control-sm" name="metodo_pago">
                <option value="tarjeta_simulada">Tarjeta bancaria simulada</option>
                <option value="webpay_pendiente">Webpay pendiente integracion</option>
                <option value="mercadopago_pendiente">Mercado Pago pendiente integracion</option>
                <option value="transferencia">Transferencia</option>
            </select>

            <label class="row floating-label-activo-sm" style="font-weight:400;margin-top:14px">
                <input type="checkbox" name="acepta_cargo_mensual" value="1" style="width:auto;min-height:auto">
                Acepto el pago inicial y el cargo mensual automatico del plan.
            </label>

            <button class="btn-success" style="width:100%;margin-top:14px">Confirmar y pagar plan</button>
        </form>
    </div>
</div>
@endsection
