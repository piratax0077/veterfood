@extends('layouts.app')

@section('title', 'Ingreso - ' . $config['titulo'])

@section('content')
@php
    $categoriaNombres = [
        'alimento_mascota' => 'Alimento', 'medicamento' => 'Farmacia', 'juguete' => 'Juguete',
        'cuidado' => 'Cuidado', 'utensilio' => 'Utiles', 'servicio' => 'Servicio',
        'hotel' => 'Hotel', 'paseo_diario' => 'Paseo', 'cementerio' => 'Cementerio',
    ];
@endphp

<style>
    .warehouse-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin:4px 0 18px}
    .warehouse-hero h1{font-size:36px;line-height:1;margin:0;color:#06152f}.warehouse-hero h1 span{font-weight:400}.warehouse-hero p{margin:8px 0 0;color:#64748b}
    .mode-actions{display:flex;gap:10px;flex-wrap:wrap}.mode-actions .btn{min-width:170px}
    .rubro-tabs{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:0 0 18px}
    .rubro-tabs a{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:12px 14px;color:#0f5132;font-weight:900;box-shadow:0 6px 14px rgba(15,23,42,.04)}
    .rubro-tabs a.active{background:#dcfce7;border-color:#86efac;color:#14532d}
    .warehouse-panel{max-width:920px;background:#fff;border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.06);padding:24px}
    .panel-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}.panel-title h2{font-size:24px;margin:0;color:#06152f}.panel-title p{margin:5px 0 0;color:#64748b}
    .section-label{margin:20px 0 12px;padding-top:16px;border-top:1px solid #e5ebf3;font-size:13px;font-weight:900;text-transform:uppercase;color:#64748b;letter-spacing:0}
    .stock-form-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}.span-12{grid-column:span 12}.span-6{grid-column:span 6}.span-4{grid-column:span 4}
    label{font-size:14px;margin:0 0 6px;color:#06152f}input,select,textarea{border-radius:8px;background:#fff}textarea{min-height:92px}
    .category-pill{display:inline-flex;background:#e0f2fe;color:#075985;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:900}
    .save-stock{width:100%;margin-top:20px;background:#166534}
    @media(max-width:1050px){.rubro-tabs{grid-template-columns:1fr 1fr}}@media(max-width:760px){.warehouse-hero{align-items:flex-start;flex-direction:column}.span-6,.span-4{grid-column:span 12}.rubro-tabs{grid-template-columns:1fr}}
</style>

<div class="warehouse-hero">
    <div>
        <h1>Ingreso <span>{{ $config['titulo'] }}</span></h1>
        <p>{{ $config['descripcion'] }}</p>
    </div>
    <div class="mode-actions">
        <a class="btn btn-secondary" href="{{ route('central.rubro', $rubroActivo) }}">Ver existencias</a>
        <a class="btn btn-secondary" href="{{ route('tienda.catalogo') }}">Ver tienda</a>
    </div>
</div>

<nav class="rubro-tabs" aria-label="Rubros de bodega">
    @foreach($rubros as $slug => $rubro)
        <a class="{{ $rubroActivo === $slug ? 'active' : '' }}" href="{{ route('central.ingreso', $slug) }}">{{ $rubro['titulo'] }}</a>
    @endforeach
</nav>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<section class="warehouse-panel">
    <div class="panel-title">
        <div>
            <h2>Formulario {{ strtolower($config['titulo']) }}</h2>
            <p>Ingreso de producto o servicio con subcategoria propia.</p>
        </div>
        <span class="category-pill">Nuevo</span>
    </div>

    <form method="POST" action="{{ route('central.productos') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="rubro" value="{{ $rubroActivo }}">
        @if(count($config['categorias']) === 1)
            <input type="hidden" name="categoria" value="{{ $config['categoria_default'] }}">
        @endif

        <div class="section-label">Clasificacion</div>
        <div class="stock-form-grid">
            <div class="span-6">
                <label class="floating-label-activo-sm">Subcategoria</label>
                <select class="form-control form-control-sm" name="subcategoria" required>
                    @foreach($config['subcategorias'] as $subcategoria)
                        <option value="{{ $subcategoria }}">{{ $subcategoria }}</option>
                    @endforeach
                </select>
            </div>
            @if(count($config['categorias']) > 1)
                <div class="span-6">
                    <label class="floating-label-activo-sm">Tipo interno</label>
                    <select class="form-control form-control-sm" name="categoria" required>
                        @foreach($config['categorias'] as $categoria)
                            <option value="{{ $categoria }}">{{ $categoriaNombres[$categoria] ?? $categoria }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="section-label">Producto</div>
        <div class="stock-form-grid">
            <div class="span-12"><label class="floating-label-activo-sm">Nombre</label><input class="form-control form-control-sm" name="nombre" value="{{ old('nombre') }}" required></div>
            <div class="span-6"><label class="floating-label-activo-sm">Fabricante / marca</label><input class="form-control form-control-sm" name="marca" value="{{ old('marca') }}"></div>
            <div class="span-6"><label class="floating-label-activo-sm">Formato / dosis / peso</label><input class="form-control form-control-sm" name="peso" value="{{ old('peso') }}"></div>
        </div>

        <div class="section-label">Valores y existencia</div>
        <div class="stock-form-grid">
            <div class="span-4"><label class="floating-label-activo-sm">Precio compra</label><input class="form-control form-control-sm" type="number" name="precio_compra" min="0" value="{{ old('precio_compra', 0) }}"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Precio venta</label><input class="form-control form-control-sm" type="number" name="precio" min="0" value="{{ old('precio', 0) }}" required></div>
            <div class="span-4"><label class="floating-label-activo-sm">Stock minimo</label><input class="form-control form-control-sm" type="number" name="stock_minimo" min="0" value="{{ old('stock_minimo', 0) }}"></div>
            <div class="span-12"><label class="floating-label-activo-sm">Stock inicial</label><input class="form-control form-control-sm" type="number" name="stock" min="0" value="{{ old('stock', 0) }}" required></div>
        </div>

        <div class="section-label">Imagen y descripcion</div>
        <div class="stock-form-grid">
            <div class="span-12"><label class="floating-label-activo-sm">Descripcion</label><textarea class="form-control form-control-sm" name="descripcion">{{ old('descripcion') }}</textarea></div>
            <div class="span-6"><label class="floating-label-activo-sm">Subir foto</label><input class="form-control form-control-sm" type="file" name="foto_producto" accept="image/*"></div>
            <div class="span-6"><label class="floating-label-activo-sm">URL foto</label><input class="form-control form-control-sm" name="foto_url" value="{{ old('foto_url') }}"></div>
        </div>

        <div class="section-label">Destino</div>
        <div class="stock-form-grid">
            <div class="span-6"><label class="floating-label-activo-sm">Sucursal destino</label><input class="form-control form-control-sm" name="sucursal_destino" value="{{ old('sucursal_destino') }}"></div>
            <div class="span-6">
                <label class="floating-label-activo-sm">Bodega destino</label>
                <select class="form-control form-control-sm" name="bodega_id">
                    <option value="">Bodega central</option>
                    @foreach($bodegas as $bodega)
                        <option value="{{ $bodega->id }}">{{ $bodega->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-12">
                <label class="floating-label-activo-sm">Medio de envio</label>
                <select class="form-control form-control-sm" name="medio_envio">
                    <option value="retiro_proveedor">Retiro proveedor</option>
                    <option value="despacho_proveedor">Despacho proveedor</option>
                    <option value="traslado_interno">Traslado interno</option>
                    <option value="servicio_agendado">Servicio agendado</option>
                </select>
            </div>
        </div>

        <button class="btn-success save-stock">Guardar en {{ strtolower($config['titulo']) }}</button>
    </form>
</section>
@endsection
