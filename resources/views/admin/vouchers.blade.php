@extends('layouts.app')

@section('title', 'Vauchers de descuento')

@section('content')
<style>
    .voucher-head{display:grid;grid-template-columns:170px minmax(0,1fr) 220px;gap:18px;align-items:center;margin:0 0 20px}
    .voucher-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .voucher-icon{width:38px;height:38px;border-radius:10px;background:#ec4899;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:24px}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .voucher-table th,.voucher-table td{padding:12px 10px}
    .secure-pill{display:inline-flex;background:#111827;color:#fff;border-radius:6px;padding:4px 8px;font-size:12px;font-weight:800}
    .qr-modal{position:fixed;inset:0;background:rgba(15,23,42,.58);display:none;align-items:center;justify-content:center;padding:24px;z-index:40}
    .qr-modal:target{display:flex}
    .qr-dialog{width:min(980px,100%);background:#fff;border-radius:8px;border:1px solid #dbe3ee;box-shadow:0 24px 70px rgba(15,23,42,.35);padding:32px;position:relative}
    .qr-dialog-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:18px}
    .qr-close{width:42px;height:42px;min-width:42px;min-height:42px;border-radius:50%;background:#e5e7eb;color:#111827;font-size:24px;padding:0}
    .qr-box{display:grid;grid-template-columns:260px minmax(0,1fr);gap:30px;align-items:start}
    .qr-box img{width:260px;height:260px;border:1px solid #e5e7eb;background:#fff;padding:12px}
    .qr-details{display:grid;gap:12px;min-width:0}
    .qr-field{border-bottom:1px solid #e5e7eb;padding-bottom:10px;min-width:0}
    .qr-field:last-child{border-bottom:0;padding-bottom:0}
    .qr-label{display:block;font-weight:900;color:#111827;margin-bottom:4px}
    .qr-code-text{font-family:Consolas,monospace;background:#111827;color:#fff;border-radius:6px;padding:8px 10px;font-size:12px;line-height:1.45;display:block;white-space:normal;overflow-wrap:anywhere;word-break:break-word;max-width:100%}
    .qr-url{display:block;overflow-wrap:anywhere;word-break:break-word;line-height:1.35}
    .security-note{background:#f8fafc;border:1px solid #dbe3ee;border-radius:8px;padding:12px;color:#475569}
    .create-voucher-btn{width:100%;min-height:48px;line-height:1.2}
    .table-tools{display:flex;gap:12px;align-items:end;justify-content:space-between;margin-bottom:18px}
    .table-tools form{display:flex;gap:10px;align-items:end;min-width:min(600px,100%)}
    .table-tools input{min-width:330px}
    .pagination-wrap{margin-top:18px}
    @media(max-width:900px){.voucher-head{grid-template-columns:1fr}.voucher-title{font-size:28px}.qr-dialog{padding:22px}.qr-box{grid-template-columns:1fr}.qr-box img{width:200px;height:200px}}
</style>

<div class="voucher-head">
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Volver</a>
    <h1 class="voucher-title"><span class="voucher-icon">%</span>Vauchers creados</h1>
    <a class="btn create-voucher-btn" href="{{ route('admin.vouchers.create') }}">Crear nuevo vaucher</a>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.vouchers.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Codigo, titulo o destinatario">
            </div>
            <button class="btn">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary" href="{{ route('admin.vouchers.index') }}">Limpiar</a>@endif
        </form>
        <span class="muted">{{ $vouchers->total() }} registros</span>
    </div>
    <table class="voucher-table">
        <thead>
            <tr><th>Codigo</th><th>Descuento</th><th>Aplica a</th><th>Vigencia</th><th>Destinatario</th><th>Usos</th><th>Seguridad</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            @forelse($vouchers as $voucher)
                <tr>
                    <td><strong>{{ $voucher->codigo }}</strong><br><span class="muted">{{ $voucher->titulo }}</span></td>
                    <td>{{ $voucher->tipo_descuento === 'porcentaje' ? $voucher->valor . '%' : '$' . number_format($voucher->valor, 0, ',', '.') }}<br><span class="muted">Min. ${{ number_format($voucher->monto_minimo, 0, ',', '.') }}</span></td>
                    <td>
                        @if($voucher->producto)<strong>{{ $voucher->producto->nombre }}</strong><br><span class="muted">Producto especifico</span>
                        @elseif($voucher->categoria_aplicable)<strong>{{ ucfirst(str_replace('_', ' ', $voucher->categoria_aplicable)) }}</strong><br><span class="muted">Categoria completa</span>
                        @else<span class="muted">Beneficio general</span>@endif
                    </td>
                    <td>{{ $voucher->valido_desde?->format('d-m-Y') ?? 'Hoy' }}<br>{{ $voucher->valido_hasta?->format('d-m-Y') ?? 'Sin termino' }}</td>
                    <td>{{ $voucher->destinatario_nombre ?: 'General' }}<br><span class="muted">{{ $voucher->destinatario_email }}</span></td>
                    <td>{{ $voucher->usos_realizados }} / {{ $voucher->usos_maximos }}</td>
                    <td><span class="secure-pill">{{ substr($voucher->firma_seguridad, 0, 8) }}...</span></td>
                    <td>{{ $voucher->activo ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        <div class="actions">
                            <a class="edit-btn" href="#qr-voucher-{{ $voucher->id }}">QR</a>
                            <form method="POST" action="{{ route('admin.vouchers.estado', $voucher) }}">
                                @csrf
                                @method('PATCH')
                                <button class="{{ $voucher->activo ? 'inactive-btn' : 'active-btn' }}">{{ $voucher->activo ? 'Inactivar' : 'Activar' }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted">No hay vouchers registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $vouchers->links('vendor.pagination.admin') }}</div>
</div>

@foreach($vouchers as $voucher)
    <div id="qr-voucher-{{ $voucher->id }}" class="qr-modal" aria-modal="true" role="dialog">
        <div class="qr-dialog">
            <div class="qr-dialog-head">
                <div>
                    <h2>QR y bono seguro</h2>
                    <p class="muted">{{ $voucher->titulo }} - {{ $voucher->codigo }}</p>
                </div>
                <a class="btn qr-close" href="{{ route('admin.vouchers.index') }}" aria-label="Cerrar">×</a>
            </div>
            <div class="qr-box">
                <img src="{{ route('vouchers.qr', $voucher) }}" alt="QR {{ $voucher->codigo }}">
                <div class="qr-details">
                    <div class="qr-field">
                        <span class="qr-label">Codigo</span>
                        {{ $voucher->codigo }}
                    </div>
                    <div class="qr-field">
                        <span class="qr-label">Firma de seguridad</span>
                        <span class="qr-code-text">{{ substr($voucher->firma_seguridad, 0, 10) }}...{{ substr($voucher->firma_seguridad, -6) }}</span>
                    </div>
                    <div class="qr-field security-note">
                        La firma completa va dentro del QR y no se muestra en pantalla para evitar copias o uso indebido del bono.
                    </div>
                    <div class="qr-field">
                        <span class="qr-label">Estado y usos</span>
                        {{ $voucher->activo ? 'Activo' : 'Inactivo' }} · {{ $voucher->usos_realizados }} / {{ $voucher->usos_maximos }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
