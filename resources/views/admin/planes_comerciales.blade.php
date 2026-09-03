@extends('layouts.app')

@section('title', 'Planes comerciales')

@section('content')
<style>
    .plans-head{display:grid;grid-template-columns:160px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 16px}
    .plans-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .plans-icon{width:38px;height:38px;border-radius:12px;background:#7c3aed;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:22px;box-shadow:0 4px 10px rgba(15,23,42,.18)}
    .module-nav{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 18px;padding:12px;background:#fff;border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 3px 8px rgba(15,23,42,.06)}
    .module-nav a{min-height:42px;padding:11px 16px;border-radius:7px;background:#e5e7eb;color:#111827;text-decoration:none;font-weight:900}
    .module-nav a.active{background:#7c3aed;color:#fff}
    .module-nav a.success{background:#15803d;color:#fff}
    .module-nav a.primary{background:#2563eb;color:#fff}
    .intro-panel{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:20px;margin-bottom:18px;display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:18px;align-items:center;box-shadow:0 3px 8px rgba(15,23,42,.06)}
    .intro-panel h2{margin:0 0 8px;color:#06152f}
    .intro-panel p{margin:0;color:#475569;line-height:1.5}
    .intro-metrics{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .metric{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px}
    .metric strong{display:block;font-size:24px;color:#06152f}
    .metric span{display:block;color:#64748b;font-size:13px;margin-top:3px}
    .plans-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .plan-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:22px;box-shadow:0 8px 20px rgba(15,23,42,.07);display:grid;gap:16px}
    .plan-top{display:flex;justify-content:space-between;gap:14px;align-items:flex-start}
    .plan-card h2{margin:0;color:#06152f;font-size:24px}
    .plan-card p{margin:7px 0 0;color:#475569;line-height:1.45}
    .plan-badge{display:inline-flex;border-radius:999px;background:#ede9fe;color:#5b21b6;font-weight:900;font-size:12px;padding:7px 10px;white-space:nowrap}
    .highlight{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;color:#14532d;font-weight:900}
    .price-row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
    .price-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px}
    .price-box span{display:block;color:#64748b;font-size:12px;font-weight:900;text-transform:uppercase}
    .price-box strong{display:block;color:#06152f;font-size:22px;margin-top:5px}
    .plan-section{border-top:1px solid #e2e8f0;padding-top:14px}
    .plan-section h3{font-size:15px;margin:0 0 8px;color:#06152f;text-transform:uppercase}
    .pill-list{display:flex;gap:8px;flex-wrap:wrap;margin:0;padding:0;list-style:none}
    .pill-list li{background:#eef2ff;color:#3730a3;border-radius:999px;padding:7px 10px;font-weight:800;font-size:13px}
    .split-list{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .mini-list{margin:0;padding-left:18px;color:#475569;line-height:1.55}
    .voucher-benefit{background:#fdf2f8;border:1px solid #fbcfe8;border-radius:8px;padding:14px;display:grid;gap:10px}
    .voucher-benefit strong{color:#9d174d}
    .voucher-benefit .btn{width:100%;background:#ec4899}
    .footer-note{margin-top:18px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:16px;color:#7c2d12;font-weight:800}
    @media(max-width:950px){.plans-head,.intro-panel,.plans-grid,.price-row,.split-list{grid-template-columns:1fr}.plans-title{font-size:28px}.module-nav a{width:100%;text-align:center}}
</style>

<div class="plans-head">
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Volver al panel</a>
    <h1 class="plans-title"><span class="plans-icon">P</span>Planes comerciales</h1>
</div>

<nav class="module-nav" aria-label="Navegacion planes">
    <a class="primary" href="{{ route('admin.clientes.index') }}">Clientes</a>
    <a class="success" href="{{ route('admin.clientes.create') }}">Crear cliente</a>
    <a href="{{ route('admin.mascotas.index') }}">Mascotas</a>
    <a class="active" href="{{ route('admin.planes.comerciales') }}">Planes comerciales</a>
    <a href="{{ route('admin.vouchers.index') }}">Vouchers relacionados</a>
</nav>

<section class="intro-panel">
    <div>
        <h2>Oferta sugerida para pago recurrente y fidelizacion</h2>
        <p>Estos planes combinan alimento automatico, salud preventiva, QR, vouchers y servicios. Sirven como base comercial para vender beneficios mensuales y ordenar demanda, rutas, stock y atenciones por zona.</p>
    </div>
    <div class="intro-metrics">
        <div class="metric"><strong>{{ count($planes) }}</strong><span>Planes base</span></div>
        <div class="metric"><strong>QR</strong><span>Beneficios seguros</span></div>
        <div class="metric"><strong>Pago</strong><span>Mensual automatico</span></div>
        <div class="metric"><strong>Stock</strong><span>Demanda proyectada</span></div>
    </div>
</section>

<div class="plans-grid">
    @foreach($planes as $plan)
        <article class="plan-card">
            <div class="plan-top">
                <div>
                    <h2>{{ $plan['nombre'] }}</h2>
                    <p>{{ $plan['descripcion'] }}</p>
                </div>
                <span class="plan-badge">{{ $plan['etiqueta'] }}</span>
            </div>

            <div class="highlight">{{ $plan['destacado'] }}</div>

            <div class="price-row">
                <div class="price-box">
                    <span>Inicio</span>
                    <strong>${{ number_format($plan['valor_inicial'], 0, ',', '.') }}</strong>
                </div>
                <div class="price-box">
                    <span>Mensual</span>
                    <strong>${{ number_format($plan['valor_mensual'], 0, ',', '.') }}</strong>
                </div>
                <div class="price-box">
                    <span>Futuro</span>
                    <strong>${{ number_format($plan['valor_futuro'], 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="plan-section">
                <h3>Cliente objetivo</h3>
                <p>{{ $plan['publico'] }}</p>
            </div>

            <div class="plan-section">
                <h3>Incluye</h3>
                <ul class="pill-list">
                    @foreach($plan['incluye'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="plan-section split-list">
                <div>
                    <h3>Operacion</h3>
                    <ul class="mini-list">
                        @foreach($plan['operacion'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3>Seguridad</h3>
                    <ul class="mini-list">
                        @foreach($plan['seguridad'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="plan-section">
                <h3>Proyeccion comercial</h3>
                <p>{{ $plan['proyeccion'] }}</p>
            </div>

            <div class="voucher-benefit">
                <strong>Voucher sugerido del plan</strong>
                <span>{{ $plan['voucher']['titulo'] }} · {{ $plan['voucher']['tipo'] === 'porcentaje' ? $plan['voucher']['valor'] . '%' : '$' . number_format($plan['voucher']['valor'], 0, ',', '.') }} · {{ $plan['voucher']['usos'] }} usos · minimo ${{ number_format($plan['voucher']['minimo'], 0, ',', '.') }}</span>
                <a class="btn" href="{{ route('admin.vouchers.create', [
                    'titulo' => $plan['voucher']['titulo'],
                    'tipo_descuento' => $plan['voucher']['tipo'],
                    'valor' => $plan['voucher']['valor'],
                    'usos_maximos' => $plan['voucher']['usos'],
                    'monto_minimo' => $plan['voucher']['minimo'],
                    'descripcion' => 'Beneficio incluido en ' . $plan['nombre'],
                ]) }}">Crear voucher para este plan</a>
            </div>
        </article>
    @endforeach
</div>

<div class="footer-note">
    Valores referenciales para propuesta comercial. Antes de produccion conviene ajustar margenes por categoria, costo logistico, comision de pago, costo de voucher y descuento por volumen.
</div>
@endsection
