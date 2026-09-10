@extends('layouts.app')

@section('title', 'Vouchers de descuento')
@section('estilos', 'css/admin-vouchers.css')

@section('content')
<x-encabezado-pagina
    titulo="Vouchers de descuento"
    descripcion="Creación, distribución, vigencia y seguimiento de vouchers."
    :volver="route('admin.dashboard') . '#vouchers'">
    <a class="encabezado-boton" href="{{ route('admin.vouchers.create') }}"><x-icono nombre="plus" />Crear nuevo voucher</a>
</x-encabezado-pagina>

<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.vouchers.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Codigo, titulo o destinatario">
            </div>
            <button class="btn boton-buscar">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary boton-buscar" href="{{ route('admin.vouchers.index') }}">Limpiar</a>@endif
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
                    <td><span class="badge tono-oscuro">{{ substr($voucher->firma_seguridad, 0, 8) }}...</span></td>
                    <td><span class="badge {{ $voucher->activo ? 'tono-verde' : 'tono-gris' }}">{{ $voucher->activo ? 'Activo' : 'Inactivo' }}</span></td>
                    <td>
                        <div class="tabla-acciones">
                            <x-boton-tabla tipo="qr" href="#qr-voucher-{{ $voucher->id }}">QR</x-boton-tabla>
                            <form method="POST" action="{{ route('admin.vouchers.estado', $voucher) }}">
                                @csrf
                                @method('PATCH')
                                <x-boton-tabla :tipo="$voucher->activo ? 'inactivar' : 'activar'">{{ $voucher->activo ? 'Inactivar' : 'Activar' }}</x-boton-tabla>
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
