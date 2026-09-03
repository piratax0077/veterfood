@extends('layouts.app')

@section('title', 'Existencias - ' . $config['titulo'])

@section('content')
@php
    $categoriaNombres = [
        'alimento_mascota' => 'Alimento', 'medicamento' => 'Farmacia', 'juguete' => 'Juguete',
        'cuidado' => 'Cuidado', 'utensilio' => 'Utiles', 'servicio' => 'Servicio',
        'hotel' => 'Hotel', 'paseo_diario' => 'Paseo', 'cementerio' => 'Cementerio',
    ];
    $stockTotal = $productos->sum('stock');
    $stockCritico = $productos->filter(fn ($producto) => $producto->stock <= ($producto->stock_minimo ?? 0))->count();
@endphp

<style>
    .warehouse-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin:4px 0 18px}
    .warehouse-hero h1{font-size:36px;line-height:1;margin:0;color:#06152f}.warehouse-hero h1 span{font-weight:400}.warehouse-hero p{margin:8px 0 0;color:#64748b}
    .mode-actions{display:flex;gap:10px;flex-wrap:wrap}.mode-actions .btn{min-width:170px}
    .rubro-tabs{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:0 0 18px}
    .rubro-tabs a{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:12px 14px;color:#0f5132;font-weight:900;box-shadow:0 6px 14px rgba(15,23,42,.04)}
    .rubro-tabs a.active{background:#dcfce7;border-color:#86efac;color:#14532d}
    .warehouse-panel{background:#fff;border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.06);padding:18px}
    .stock-top{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:16px}.stock-top h2{margin:0;font-size:24px;color:#06152f}.stock-top p{margin:5px 0 0;color:#64748b}
    .stock-metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px}.metric{border:1px solid #e2e8f0;border-radius:8px;padding:13px;background:#f8fafc}.metric strong{display:block;font-size:24px;color:#06152f}.metric span{font-size:13px;color:#64748b;font-weight:700}
    .subcat-strip{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 16px}.subcat-strip span{background:#f1f5f9;color:#334155;border-radius:999px;padding:7px 10px;font-size:12px;font-weight:900}
    .table-wrap{overflow-x:auto;border:1px solid #e2e8f0;border-radius:8px}.stock-table{min-width:980px}.stock-table th{background:#f8fafc;color:#334155;font-size:13px;text-transform:uppercase;letter-spacing:0}.stock-table th,.stock-table td{padding:13px 12px;vertical-align:middle}.stock-table tbody tr:hover{background:#f8fafc}
    .product-cell{display:flex;align-items:center;gap:12px;min-width:250px}.stock-photo{width:58px;height:58px;border-radius:10px;background:linear-gradient(135deg,#ecfeff,#dcfce7);display:flex;align-items:center;justify-content:center;overflow:hidden;flex:0 0 auto}.stock-photo img{width:100%;height:100%;object-fit:cover}
    .stock-cube{width:34px;height:38px;border-radius:9px;background:linear-gradient(145deg,#16a34a,#2563eb);box-shadow:9px 10px 16px rgba(15,23,42,.18);transform:rotateX(12deg) rotateY(-22deg)}
    .product-name{font-weight:900;color:#06152f}.product-meta{color:#64748b;font-size:13px;margin-top:3px}.category-pill{display:inline-flex;background:#e0f2fe;color:#075985;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:900}.stock-number{font-size:20px;font-weight:900;color:#06152f}
    .critical{background:#fee2e2!important;color:#991b1b!important}.active-pill{background:#dcfce7!important;color:#166534!important}.store-link{display:inline-flex;align-items:center;justify-content:center;min-height:38px;border-radius:8px;background:#eef2ff;color:#1d4ed8;font-weight:900;padding:8px 12px}.empty-state{padding:28px;text-align:center;color:#64748b}
    @media(max-width:1050px){.stock-metrics,.rubro-tabs{grid-template-columns:1fr 1fr}}@media(max-width:760px){.warehouse-hero{align-items:flex-start;flex-direction:column}.rubro-tabs{grid-template-columns:1fr}}
</style>

<div class="warehouse-hero">
    <div>
        <h1>Existencias <span>{{ $config['titulo'] }}</span></h1>
        <p>Control de stock, precios, estado critico y disponibilidad por rubro.</p>
    </div>
    <div class="mode-actions">
        <a class="btn btn-success" href="{{ route('central.ventas', 'preparar') }}">Seccion ventas</a>
        <a class="btn" href="{{ route('central.ingreso', $rubroActivo) }}">Ingresar productos</a>
        <a class="btn btn-secondary" href="{{ route('tienda.catalogo') }}">Ver tienda</a>
    </div>
</div>

<nav class="rubro-tabs" aria-label="Rubros de bodega">
    @foreach($rubros as $slug => $rubro)
        <a class="{{ $rubroActivo === $slug ? 'active' : '' }}" href="{{ route('central.rubro', $slug) }}">{{ $rubro['titulo'] }}</a>
    @endforeach
</nav>

<section class="warehouse-panel">
    <div class="stock-top">
        <div>
            <h2>Tabla de existencias {{ $config['titulo'] }}</h2>
            <p>Listado exclusivo de este rubro.</p>
        </div>
    </div>

    <div class="stock-metrics">
        <div class="metric"><strong>{{ $productos->count() }}</strong><span>Productos del rubro</span></div>
        <div class="metric"><strong>{{ $stockTotal }}</strong><span>Unidades en stock</span></div>
        <div class="metric"><strong>{{ $stockCritico }}</strong><span>Alertas criticas</span></div>
    </div>

    <div class="subcat-strip">
        @foreach($config['subcategorias'] as $subcategoria)
            <span>{{ $subcategoria }}</span>
        @endforeach
    </div>

    <div class="table-wrap">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Subcategoria</th>
                    <th>Tipo</th>
                    <th>Fabricante</th>
                    <th>Compra</th>
                    <th>Venta</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Accion</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $producto)
                    <tr>
                        <td>
                            <div class="product-cell">
                                <div class="stock-photo">
                                    @if($producto->foto_url)
                                        <img src="{{ str_starts_with($producto->foto_url, 'http') ? $producto->foto_url : asset($producto->foto_url) }}" alt="{{ $producto->nombre }}">
                                    @else
                                        <div class="stock-cube"></div>
                                    @endif
                                </div>
                                <div>
                                    <div class="product-name">{{ $producto->nombre }}</div>
                                    <div class="product-meta">{{ $producto->peso ?: 'Sin formato' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="category-pill">{{ $producto->subcategoria ?: 'Sin subcategoria' }}</span></td>
                        <td>{{ $categoriaNombres[$producto->categoria] ?? $producto->categoria }}</td>
                        <td>{{ $producto->marca ?: 'Sin marca' }}</td>
                        <td>${{ number_format($producto->precio_compra ?? 0, 0, ',', '.') }}</td>
                        <td><strong>${{ number_format($producto->precio, 0, ',', '.') }}</strong></td>
                        <td><span class="stock-number">{{ $producto->stock }}</span><br><span class="muted">min {{ $producto->stock_minimo ?? 0 }}</span></td>
                        <td>
                            @if($producto->stock <= ($producto->stock_minimo ?? 0))
                                <span class="category-pill critical">Critico</span>
                            @else
                                <span class="category-pill active-pill">Activo</span>
                            @endif
                        </td>
                        <td><a class="store-link" href="{{ route('tienda.catalogo', ['categoria' => $producto->categoria]) }}">Ver tienda</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="empty-state">No hay productos en {{ strtolower($config['titulo']) }}.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
