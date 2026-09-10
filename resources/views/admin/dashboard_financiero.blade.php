@extends('layouts.app')

@section('title', 'Dashboard financiero')
@section('estilos', 'css/admin-financiero.css')

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

<x-encabezado-pagina
    titulo="Dashboard Financiero"
    descripcion="Ingresos, egresos, costos de productos y variaciones según pedidos registrados."
    :volver="route('admin.dashboard') . '#operacion'">
    <a class="encabezado-boton encabezado-boton--secundario" href="{{ route('admin.finanzas.vouchers') }}">Movimientos</a>
</x-encabezado-pagina>

<section class="finance-panel">

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
