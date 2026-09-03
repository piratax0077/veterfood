@extends('layouts.app')

@section('title', 'Emitir factura')

@section('content')
<style>
    .invoice-actions{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px}
    .invoice-sheet{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:28px;box-shadow:0 3px 10px rgba(15,23,42,.07);max-width:1050px;margin:0 auto}
    .invoice-head{display:grid;grid-template-columns:1fr 260px;gap:24px;align-items:start;border-bottom:2px solid #0f172a;padding-bottom:18px;margin-bottom:18px}
    .brand{display:flex;gap:14px;align-items:flex-start}.brand-logo{width:72px;height:72px;border-radius:14px;background:#0f766e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:900;box-shadow:0 8px 18px rgba(15,23,42,.16)}
    .brand h1{margin:0 0 6px;color:#061a3d;font-size:28px}.brand p,.client-box p,.muted-line{margin:2px 0;color:#475569}
    .folio-box{border:2px solid #dc2626;border-radius:8px;text-align:center;padding:14px;color:#7f1d1d}.folio-box h2{margin:0 0 8px;font-size:22px}.folio-box strong{font-size:24px;color:#111827}
    .invoice-meta{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px}.client-box{border:1px solid #dbe3ee;border-radius:8px;padding:16px}
    .totals{margin-left:auto;max-width:360px;border:1px solid #dbe3ee;border-radius:8px;padding:14px}.total-row{display:flex;justify-content:space-between;border-bottom:1px solid #e5e7eb;padding:8px 0}.total-row:last-child{border-bottom:0}.grand{font-size:24px;font-weight:900;color:#061a3d}
    .print-note{margin-top:18px;color:#64748b;font-size:13px}
    @media print{nav,.invoice-actions,.no-print{display:none!important}body{background:#fff}.invoice-sheet{box-shadow:none;border:0;margin:0;max-width:none}.container{max-width:none!important}}
    @media(max-width:760px){.invoice-head,.invoice-meta{grid-template-columns:1fr}.folio-box{text-align:left}}
</style>

@php
    $logoText = strtoupper(mb_substr($centroMedico->nombre_fantasia ?: $centroMedico->razon_social, 0, 1));
    $descuento = $documento->detalles->sum(function ($detalle) {
        $bruto = (float) $detalle->cantidad * (float) $detalle->precio_unitario;
        return max(0, (int) round($bruto - (int) $detalle->total));
    });
    $folio = $documento->folio ?: str_pad((string) $documento->id, 6, '0', STR_PAD_LEFT);
@endphp

<div class="invoice-actions">
    <a class="btn btn-secondary" href="{{ route('contabilidad.secciones.show', ['centroMedico' => $centroMedico->id, 'seccion' => 'factura']) }}">Volver a facturacion</a>
    <div class="compact-actions">
        <button class="btn" type="button" onclick="window.print()">Imprimir / PDF</button>
    </div>
</div>

<div class="invoice-sheet">
    <div class="invoice-head">
        <div class="brand">
            <div class="brand-logo">{{ $logoText }}</div>
            <div>
                <h1>{{ $centroMedico->nombre_fantasia ?: $centroMedico->razon_social }}</h1>
                <p><strong>RUT:</strong> {{ $centroMedico->rut }}</p>
                <p><strong>Giro:</strong> {{ $centroMedico->giro ?: 'Comercializacion de alimentos y servicios veterinarios' }}</p>
                <p><strong>Direccion:</strong> {{ $centroMedico->direccion ?: 'Direccion no registrada' }}, {{ $centroMedico->comuna ?: 'Comuna no registrada' }}</p>
                <p><strong>Contacto:</strong> {{ $centroMedico->email ?: 'Sin correo' }} {{ $centroMedico->telefono ? ' / '.$centroMedico->telefono : '' }}</p>
            </div>
        </div>
        <div class="folio-box">
            <h2>{{ strtoupper(str_replace('_', ' ', $documento->tipo_documento)) }}</h2>
            <p>Documento tributario electronico</p>
            <strong>N° {{ $folio }}</strong>
        </div>
    </div>

    <div class="invoice-meta">
        <div class="client-box">
            <h2>Cliente / receptor</h2>
            <p><strong>Razon social:</strong> {{ $documento->tercero?->razon_social ?: 'Cliente no registrado' }}</p>
            <p><strong>RUT:</strong> {{ $documento->tercero?->rut ?: 'Sin RUT' }}</p>
            <p><strong>Giro:</strong> {{ $documento->tercero?->giro ?: 'No informado' }}</p>
            <p><strong>Direccion:</strong> {{ $documento->tercero?->direccion ?: 'No informada' }} {{ $documento->tercero?->comuna ? ', '.$documento->tercero->comuna : '' }}</p>
        </div>
        <div class="client-box">
            <h2>Datos emision</h2>
            <p><strong>Fecha emision:</strong> {{ $documento->fecha_emision?->format('d-m-Y') }}</p>
            <p><strong>Fecha vencimiento:</strong> {{ $documento->fecha_vencimiento?->format('d-m-Y') ?: 'No aplica' }}</p>
            <p><strong>Naturaleza:</strong> {{ ucfirst($documento->naturaleza) }}</p>
            <p><strong>Estado:</strong> {{ ucfirst($documento->estado) }}</p>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Detalle</th>
                    <th>Cantidad</th>
                    <th>Precio unit.</th>
                    <th>Desc.</th>
                    <th>Total linea</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documento->detalles as $detalle)
                    @php
                        $brutoLinea = (float) $detalle->cantidad * (float) $detalle->precio_unitario;
                        $descuentoLinea = max(0, (int) round($brutoLinea - (int) $detalle->total));
                    @endphp
                    <tr>
                        <td>{{ $detalle->codigo ?: '-' }}</td>
                        <td>{{ $detalle->descripcion }}</td>
                        <td>{{ number_format((float) $detalle->cantidad, 2, ',', '.') }}</td>
                        <td>${{ number_format((float) $detalle->precio_unitario, 3, ',', '.') }}</td>
                        <td>{{ number_format((float) $detalle->descuento_porcentaje, 2, ',', '.') }}%<br>${{ number_format($descuentoLinea, 0, ',', '.') }}</td>
                        <td>${{ number_format($detalle->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Sin detalle registrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="totals">
        <div class="total-row"><span>Neto</span><strong>${{ number_format($documento->neto, 0, ',', '.') }}</strong></div>
        <div class="total-row"><span>Exento</span><strong>${{ number_format($documento->exento, 0, ',', '.') }}</strong></div>
        <div class="total-row"><span>Descuentos</span><strong>${{ number_format($descuento, 0, ',', '.') }}</strong></div>
        <div class="total-row"><span>IVA 19%</span><strong>${{ number_format($documento->impuesto, 0, ',', '.') }}</strong></div>
        <div class="total-row grand"><span>Total</span><strong>${{ number_format($documento->total, 0, ',', '.') }}</strong></div>
    </div>

    @if($documento->observaciones)
        <p class="print-note"><strong>Observaciones:</strong> {{ $documento->observaciones }}</p>
    @endif
    <p class="print-note">Vista preparada para emision interna, impresion o PDF. Para integracion SII real se debe conectar el proveedor tributario autorizado.</p>
</div>
@endsection
