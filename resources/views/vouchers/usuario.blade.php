@extends('layouts.app')

@section('title', 'Mis vouchers disponibles')

@section('content')
<style>
    .voucher-user-hero{display:flex;justify-content:space-between;gap:18px;align-items:center;background:linear-gradient(120deg,#03715b,#10a37f);color:#fff;border-radius:16px;padding:24px;margin-bottom:20px;box-shadow:0 12px 30px rgba(3,113,91,.22)}
    .voucher-user-hero h1{margin:0 0 7px;color:#fff}.voucher-user-hero p{margin:0}.voucher-count{font-size:30px;font-weight:900;background:rgba(255,255,255,.18);border-radius:14px;padding:12px 18px;text-align:center}
    .voucher-user-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(520px,1fr));gap:16px}.voucher-user-card{background:#fff;border:2px solid #bfe0d6;border-radius:14px;padding:18px;box-shadow:0 8px 20px rgba(15,23,42,.07);display:grid;grid-template-columns:minmax(0,1fr) 160px;gap:20px;align-items:center}.voucher-user-info{display:grid;gap:12px;min-width:0}.voucher-user-qr{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:12px;border:1px solid #bfe0d6;border-radius:12px;background:#f4fbf8}.voucher-user-qr img{display:block;width:140px;height:140px;object-fit:contain;background:#fff}.voucher-user-qr span{color:#03715b;font-size:12px;font-weight:800;text-align:center}
    .voucher-value{font-size:30px;font-weight:900;color:#03715b}.voucher-code{font-family:Consolas,monospace;background:#111827;color:#fff;border-radius:8px;padding:9px 11px;font-weight:800;letter-spacing:.05em}.voucher-meta{display:grid;gap:6px;color:#475569;font-size:14px}.voucher-origin{display:inline-block;background:#d9f3ee;color:#03715b;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:800;width:max-content}
    .voucher-empty{grid-column:1/-1;background:#fff;border:1px dashed #cbd5e1;border-radius:14px;padding:35px;text-align:center;color:#64748b}
    @media(max-width:900px){.voucher-user-grid{grid-template-columns:1fr}.voucher-user-hero{align-items:flex-start;flex-direction:column}}
    @media(max-width:620px){.voucher-user-card{grid-template-columns:1fr}.voucher-user-qr{justify-self:stretch}.voucher-user-qr img{width:170px;height:170px}}
</style>
<div class="voucher-user-hero"><div><h1>Mis vouchers disponibles</h1><p>Beneficios vigentes para tu perfil, ubicación y entidades asociadas.</p></div><div class="voucher-count">{{ $vouchers->count() }}</div></div>
<div class="voucher-user-grid">
@forelse($vouchers as $voucher)
    <article class="voucher-user-card">
        <div class="voucher-user-info">
        <span class="voucher-origin">{{ $voucher->localVenta?->nombre ?: 'Administración central' }}</span>
        <div><h2>{{ $voucher->titulo }}</h2><div class="voucher-value">{{ $voucher->tipo_descuento === 'porcentaje' ? $voucher->valor . '%' : '$' . number_format($voucher->valor, 0, ',', '.') }}</div></div>
        <div class="voucher-meta">
            <span><strong>Beneficio:</strong> {{ ucfirst(str_replace('_', ' ', $voucher->tipo_beneficio)) }}</span>
            <span><strong>Cobertura:</strong> {{ ucfirst($voucher->alcance_territorial) }}</span>
            @if($voucher->producto)<span><strong>Producto:</strong> {{ $voucher->producto->nombre }}</span>@elseif($voucher->categoria_aplicable)<span><strong>Categoria:</strong> {{ ucfirst(str_replace('_', ' ', $voucher->categoria_aplicable)) }}</span>@endif
            <span><strong>Compra mínima:</strong> ${{ number_format($voucher->monto_minimo, 0, ',', '.') }}</span>
            <span><strong>Vigencia:</strong> hasta {{ $voucher->valido_hasta?->format('d-m-Y') ?: 'agotar disponibilidad' }}</span>
        </div>
        <div class="voucher-code">{{ $voucher->codigo }}</div>
        </div>
        <div class="voucher-user-qr">
            <img src="{{ route('vouchers.qr', $voucher) }}" alt="Código QR del voucher {{ $voucher->codigo }}">
            <span>Presenta este QR para usar el beneficio</span>
        </div>
    </article>
@empty
    <div class="voucher-empty"><h2>Aún no tienes vouchers compatibles</h2><p>Cuando exista un beneficio para tu perfil o ubicación aparecerá aquí automáticamente.</p></div>
@endforelse
</div>
@endsection
