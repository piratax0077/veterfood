@extends('layouts.app')

@section('title', 'Dashboard financiero')

@section('content')
@php
    $variacionTexto = function ($valor) {
        if ($valor === null) {
            return 'Nuevo periodo';
        }

        return ($valor > 0 ? '+' : '') . number_format($valor, 1, ',', '.') . '%';
    };
    $variacionClase = fn ($valor) => $valor === null || $valor == 0 ? 'trend-flat' : ($valor > 0 ? 'trend-up' : 'trend-down');
@endphp
<style>
    .finance-head{display:grid;grid-template-columns:170px minmax(0,1fr) 220px;gap:18px;align-items:center;margin-bottom:20px}
    .finance-title{margin:0;color:#061a3d;font-size:34px}
    .finance-panel{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .finance-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px}
    .finance-kpi{border:1px solid #e5e7eb;border-radius:6px;padding:16px;background:#f8fafc}
    .finance-kpi span{display:block;color:#657083;font-weight:700;margin-bottom:8px}
    .finance-kpi strong{display:block;color:#061a3d;font-size:24px}
    .finance-kpi small{display:block;margin-top:8px;font-weight:800}
    .trend-up{color:#166534}.trend-down{color:#b91c1c}.trend-flat{color:#657083}
    .chart-wrap{border:1px solid #e5e7eb;border-radius:6px;padding:18px;background:#fff;margin-bottom:18px}
    .chart{display:grid;grid-template-columns:repeat(12,minmax(54px,1fr));gap:10px;align-items:end;height:250px;overflow-x:auto;padding-bottom:8px}
    .chart-month{height:100%;display:flex;flex-direction:column;justify-content:flex-end;gap:5px;min-width:54px}
    .bar-stack{height:180px;display:flex;align-items:flex-end;gap:4px;justify-content:center}
    .bar{width:12px;min-height:2px;border-radius:4px 4px 0 0}
    .bar-income{background:#16a34a}.bar-expense{background:#dc2626}.bar-cost{background:#2563eb}
    .chart-label{text-align:center;font-size:11px;color:#657083;font-weight:700}
    .legend{display:flex;gap:14px;flex-wrap:wrap;margin-top:10px;color:#657083;font-weight:700}
    .legend i{display:inline-block;width:11px;height:11px;border-radius:3px;margin-right:5px}
    .finance-detail{display:grid;grid-template-columns:1.2fr .8fr;gap:16px}
    .mini-table th,.mini-table td{font-size:13px;padding:8px}
    @media(max-width:900px){.finance-head,.finance-kpis,.finance-detail{grid-template-columns:1fr}.finance-title{font-size:28px}}
</style>

<div class="finance-head">
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Volver</a>
    <h1 class="finance-title">Dashboard financiero</h1>
    <a class="btn" href="{{ route('admin.finanzas.vouchers') }}">Movimientos</a>
</div>

<section class="finance-panel">
    <p class="muted">Ingresos, egresos, costos de productos y variaciones segun pedidos registrados.</p>

    <div class="finance-kpis">
        <div class="finance-kpi">
            <span>Ingresos 12 meses</span>
            <strong>${{ number_format($financiero['totales']['ingresos'], 0, ',', '.') }}</strong>
            <small class="{{ $variacionClase($financiero['variaciones']['ingresos_mes']) }}">Mes: {{ $variacionTexto($financiero['variaciones']['ingresos_mes']) }}</small>
        </div>
        <div class="finance-kpi">
            <span>Egresos 12 meses</span>
            <strong>${{ number_format($financiero['totales']['egresos'], 0, ',', '.') }}</strong>
            <small class="{{ $variacionClase($financiero['variaciones']['egresos_mes']) }}">Mes: {{ $variacionTexto($financiero['variaciones']['egresos_mes']) }}</small>
        </div>
        <div class="finance-kpi">
            <span>Costos productos</span>
            <strong>${{ number_format($financiero['totales']['costos_productos'], 0, ',', '.') }}</strong>
            <small class="{{ $variacionClase($financiero['variaciones']['costos_anual']) }}">Anual: {{ $variacionTexto($financiero['variaciones']['costos_anual']) }}</small>
        </div>
        <div class="finance-kpi">
            <span>Margen bruto</span>
            <strong>${{ number_format($financiero['totales']['margen'], 0, ',', '.') }}</strong>
            <small class="{{ $variacionClase($financiero['variaciones']['ingresos_anual']) }}">Ingresos anual: {{ $variacionTexto($financiero['variaciones']['ingresos_anual']) }}</small>
        </div>
    </div>

    <div class="chart-wrap">
        <h2>Ingresos, egresos y costos por mes</h2>
        <div class="chart">
            @foreach($financiero['series'] as $mes)
                <div class="chart-month">
                    <div class="bar-stack">
                        <span class="bar bar-income" title="Ingresos ${{ number_format($mes['ingresos'], 0, ',', '.') }}" style="height:{{ max(2, ($mes['ingresos'] / $financiero['maximo']) * 180) }}px"></span>
                        <span class="bar bar-expense" title="Egresos ${{ number_format($mes['egresos'], 0, ',', '.') }}" style="height:{{ max(2, ($mes['egresos'] / $financiero['maximo']) * 180) }}px"></span>
                        <span class="bar bar-cost" title="Costos ${{ number_format($mes['costos_productos'], 0, ',', '.') }}" style="height:{{ max(2, ($mes['costos_productos'] / $financiero['maximo']) * 180) }}px"></span>
                    </div>
                    <div class="chart-label">{{ $mes['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="legend">
            <span><i class="bar-income"></i>Ingresos</span>
            <span><i class="bar-expense"></i>Egresos</span>
            <span><i class="bar-cost"></i>Costos productos</span>
        </div>
    </div>

    <div class="finance-detail">
        <div class="chart-wrap">
            <h2>Variaciones</h2>
            <table class="mini-table">
                <thead><tr><th>Indicador</th><th>Mensual</th><th>Anual</th></tr></thead>
                <tbody>
                    <tr>
                        <td>Ingresos</td>
                        <td class="{{ $variacionClase($financiero['variaciones']['ingresos_mes']) }}">{{ $variacionTexto($financiero['variaciones']['ingresos_mes']) }}</td>
                        <td class="{{ $variacionClase($financiero['variaciones']['ingresos_anual']) }}">{{ $variacionTexto($financiero['variaciones']['ingresos_anual']) }}</td>
                    </tr>
                    <tr>
                        <td>Egresos</td>
                        <td class="{{ $variacionClase($financiero['variaciones']['egresos_mes']) }}">{{ $variacionTexto($financiero['variaciones']['egresos_mes']) }}</td>
                        <td class="trend-flat">Calculado mes a mes</td>
                    </tr>
                    <tr>
                        <td>Costos productos</td>
                        <td class="{{ $variacionClase($financiero['variaciones']['costos_mes']) }}">{{ $variacionTexto($financiero['variaciones']['costos_mes']) }}</td>
                        <td class="{{ $variacionClase($financiero['variaciones']['costos_anual']) }}">{{ $variacionTexto($financiero['variaciones']['costos_anual']) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="chart-wrap">
            <h2>Lectura rapida</h2>
            <p class="muted">Egresos = costo de productos vendidos mas costo de despacho.</p>
            <p class="muted">Costos de productos se calculan con precio compra actual aplicado a productos vendidos.</p>
            <p class="muted">Margen bruto = ingresos menos egresos.</p>
        </div>
    </div>
</section>
@endsection
