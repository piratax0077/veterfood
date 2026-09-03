@extends('layouts.app')

@section('title', 'Verificacion de vaucher')

@section('content')
<div class="card" style="max-width:760px;margin:30px auto">
    <h1>Verificacion de vaucher</h1>
    <p><strong>Codigo:</strong> {{ $voucher->codigo }}</p>
    <p><strong>Descuento:</strong> {{ $voucher->tipo_descuento === 'porcentaje' ? $voucher->valor . '%' : '$' . number_format($voucher->valor, 0, ',', '.') }}</p>
    <p><strong>Datos:</strong> {{ $voucher->descripcion ?: $voucher->titulo }}</p>
    <p><strong>Vigencia:</strong> {{ $voucher->valido_desde?->format('d-m-Y') ?? 'Hoy' }} al {{ $voucher->valido_hasta?->format('d-m-Y') ?? 'Sin termino' }}</p>
    <p><strong>Usos:</strong> {{ $voucher->usos_realizados }} / {{ $voucher->usos_maximos }}</p>
    @if($vigente)
        <div class="alert">Voucher valido. Firma segura verificada.</div>
    @else
        <div class="alert" style="background:#fee2e2;color:#991b1b">
            Voucher no valido.
            @if(!$firmaValida) Firma de seguridad incorrecta. @endif
            @if(!$voucher->activo) Voucher inactivo. @endif
            @if($voucher->usos_realizados >= $voucher->usos_maximos) Uso maximo alcanzado. @endif
        </div>
    @endif
    <p class="muted">Firma: {{ substr($voucher->firma_seguridad, 0, 24) }}...</p>
</div>
@endsection
