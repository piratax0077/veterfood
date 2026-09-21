{{-- Tarjetas de cupones con foto y código para copiar. Recibe $vouchers (VoucherDescuento con producto) y, si se quiere, $limite --}}
@php
    $hoy = now()->startOfDay();
    $pesos = fn ($monto) => '$' . number_format((int) $monto, 0, ',', '.');
    $diasHasta = fn ($fecha) => (int) round($hoy->diffInDays($fecha->copy()->startOfDay(), false));
    $plazo = function (int $dias) {
        return match (true) {
            $dias < 0 => 'hace ' . abs($dias) . (abs($dias) === 1 ? ' día' : ' días'),
            $dias === 0 => 'hoy',
            $dias === 1 => 'mañana',
            $dias <= 31 => 'en ' . $dias . ' días',
            default => 'en ' . intdiv($dias, 30) . (intdiv($dias, 30) === 1 ? ' mes' : ' meses'),
        };
    };

    // Cupones con fecha de vencimiento, del más próximo al más lejano
    $cupones = $vouchers->filter(fn ($voucher) => $voucher->valido_hasta)->sortBy('valido_hasta')->map(fn ($voucher) => [
        'codigo' => $voucher->codigo,
        'titulo' => $voucher->titulo,
        'valor' => $voucher->tipo_descuento === 'porcentaje' ? $voucher->valor . '%' : $pesos($voucher->valor),
        'unidad' => 'dcto.',
        'condicion' => collect([
            $voucher->producto ? 'En ' . $voucher->producto->nombre : null,
            $voucher->monto_minimo > 0 ? 'Compra mínima ' . $pesos($voucher->monto_minimo) : null,
        ])->filter()->implode(' · ') ?: 'En toda la tienda',
        'vence' => $voucher->valido_hasta,
        'imagen' => 'images/inicio/aviso-tienda.jpg',
        'ejemplo' => false,
    ])->values();
    if ($cupones->isEmpty()) {
        $cupones = collect([
            ['codigo' => 'VETERSDI10', 'titulo' => '10% en tu próxima compra', 'valor' => '10%', 'unidad' => 'dcto.', 'condicion' => 'En toda la tienda', 'imagen' => 'images/inicio/aviso-tienda.jpg', 'vence' => $hoy->copy()->addDays(5)],
            ['codigo' => 'SUSCRIBE5000', 'titulo' => '$5.000 en tu primera suscripción', 'valor' => '$5.000', 'unidad' => 'dcto.', 'condicion' => 'Compra mínima $30.000', 'imagen' => 'images/inicio/slide-2-foto.jpg', 'vence' => $hoy->copy()->addDays(18)],
            ['codigo' => 'ENVIOGRATIS', 'titulo' => 'Envío gratis', 'valor' => 'Envío', 'unidad' => 'gratis', 'condicion' => 'Despacho a domicilio', 'imagen' => 'images/inicio/aviso-pedido.jpg', 'vence' => $hoy->copy()->addDays(125)],
        ])->map(fn ($cupon) => $cupon + ['ejemplo' => true]);
    }
    $cupones = $cupones->map(function ($cupon) use ($diasHasta) {
        $cupon['dias'] = $diasHasta($cupon['vence']);
        $cupon['urgencia'] = $cupon['dias'] <= 7 ? 'rojo' : ($cupon['dias'] <= 30 ? 'naranjo' : 'verde');
        return $cupon;
    });
    if (! empty($limite)) {
        $cupones = $cupones->take($limite);
    }
@endphp
<ul class="cupones-lista">
    @foreach($cupones as $cupon)
        <li class="cupon cupon--{{ $cupon['urgencia'] }}">
            <span class="cupon-valor" style="--cupon-foto:url('{{ asset($cupon['imagen']) }}')"><strong>{{ $cupon['valor'] }}</strong><small>{{ $cupon['unidad'] }}</small></span>
            <span class="cupon-info">
                <strong>{{ $cupon['titulo'] }}</strong>
                <span>{{ $cupon['condicion'] }}</span>
                <button type="button" class="cupon-codigo" data-copiar="{{ $cupon['codigo'] }}" aria-label="Copiar el código {{ $cupon['codigo'] }}">{{ $cupon['codigo'] }}<x-icono nombre="copiar" /></button>
            </span>
            <span class="cupon-plazo">
                <span class="badge tono-{{ $cupon['urgencia'] }}">Vence {{ $plazo($cupon['dias']) }}</span>
                <small>Hasta el {{ $cupon['vence']->locale('es')->translatedFormat('j \d\e F') }}</small>
            </span>
        </li>
    @endforeach
</ul>
