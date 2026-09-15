@extends('layouts.app')

@section('title', 'Planes comerciales')
@section('estilos', 'css/admin-planes.css')

@section('content')

<x-encabezado-pagina
    titulo="Planes comerciales"
    descripcion="Alimento automático, vacunas, historial clínico, voucher y placa QR."
    :volver="route('admin.dashboard') . '#operacion'" />

<nav class="module-nav" aria-label="Navegación planes">
    <a class="primary" href="{{ route('admin.clientes.index') }}">Clientes</a>
    <button type="button" class="success" data-modal-abrir="modal-nuevo-cliente">Crear cliente</button>
    <a href="{{ route('admin.mascotas.index') }}">Mascotas</a>
    <a class="active" href="{{ route('admin.planes.comerciales') }}">Planes comerciales</a>
    <a href="{{ route('admin.vouchers.index') }}">Vouchers relacionados</a>
</nav>

<section class="intro-panel">
    <div>
        <h2>Oferta sugerida para pago recurrente y fidelización</h2>
        <p>Estos planes combinan alimento automático, salud preventiva, QR, vouchers y servicios. Sirven como base comercial para vender beneficios mensuales y ordenar demanda, rutas, stock y atenciones por zona.</p>
    </div>
    <div class="intro-metrics">
        <div class="metric"><strong>{{ count($planes) }}</strong><span>Planes base</span></div>
        <div class="metric"><strong>QR</strong><span>Beneficios seguros</span></div>
        <div class="metric"><strong>Pago</strong><span>Mensual automático</span></div>
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
                    <h3>Operación</h3>
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
                <h3>Proyección comercial</h3>
                <p>{{ $plan['proyeccion'] }}</p>
            </div>

            <div class="voucher-benefit">
                <strong>Voucher sugerido del plan</strong>
                <span>{{ $plan['voucher']['titulo'] }} · {{ $plan['voucher']['tipo'] === 'porcentaje' ? $plan['voucher']['valor'] . '%' : '$' . number_format($plan['voucher']['valor'], 0, ',', '.') }} · {{ $plan['voucher']['usos'] }} usos · mínimo ${{ number_format($plan['voucher']['minimo'], 0, ',', '.') }}</span>
                {{-- Abre el modal "Crear voucher" con los datos sugeridos del plan --}}
                <button type="button" class="btn" data-modal-abrir="modal-nuevo-voucher" data-modal-rellenar="{{ json_encode([
                    'titulo' => $plan['voucher']['titulo'],
                    'tipo_descuento' => $plan['voucher']['tipo'],
                    'valor' => $plan['voucher']['valor'],
                    'usos_maximos' => $plan['voucher']['usos'],
                    'monto_minimo' => $plan['voucher']['minimo'],
                    'descripcion' => 'Beneficio incluido en ' . $plan['nombre'],
                ]) }}">Crear voucher para este plan</button>
            </div>
        </article>
    @endforeach
</div>

<div class="footer-note">
    Valores referenciales para propuesta comercial. Antes de producción conviene ajustar márgenes por categoría, costo logístico, comisión de pago, costo de voucher y descuento por volumen.
</div>

{{-- Modales de crear: voucher (botones "Crear voucher para este plan") y cliente ("Crear cliente" de la barra) --}}
@include('admin.modales.nuevo-voucher')
@include('admin.modales.nuevo-cliente')
@endsection
