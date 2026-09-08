@extends('layouts.app')

@section('title', 'Tienda')

@section('content')
<style>
    .store-header{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:start}
    .store-header h1{font-size:32px;margin-bottom:8px}
    .category-tabs{display:flex;gap:10px;flex-wrap:wrap;margin:16px 0 28px}
    .category-tabs .btn{background:#e5e7eb;color:#111827}
    .category-tabs .active{background:#d1fae5;color:#14532d;box-shadow:inset 0 0 0 2px #166534}
    .store-filters{display:grid;grid-template-columns:minmax(220px,1.25fr) minmax(200px,1fr) minmax(190px,1fr) auto;gap:14px;align-items:end;margin:0 0 34px;padding:18px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.07)}
    .filter-field{display:flex;flex-direction:column;gap:7px;min-width:0}
    .filter-field label{margin:0;color:#475569;font-size:13px;font-weight:700}
    .filter-control-wrap{position:relative}
    .filter-control-wrap.search-control:before{content:"\1F50D";position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;opacity:.58;pointer-events:none}
    .filter-field input,.filter-field select{width:100%;height:44px;border:1px solid #cbd5e1;border-radius:9px;padding:8px 12px;background:#fff;color:#0f172a;outline:none;transition:border-color .18s,box-shadow .18s}
    .filter-field input{padding-left:39px}
    .filter-field input:focus,.filter-field select:focus{border-color:#14b8a6;box-shadow:0 0 0 3px rgba(20,184,166,.14)}
    .filter-actions{display:flex;gap:9px;align-items:center;min-height:44px}
    .filter-actions button,.filter-actions .btn{height:44px;display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;border-radius:9px;padding:0 22px}
    .product-photo{height:148px;border-radius:8px;margin:-4px -4px 14px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:linear-gradient(135deg,#eef6f3,#fdf3e7)}
    .product-photo img{width:100%;height:100%;object-fit:cover}
    .product-placeholder-icon{width:58px;height:58px;color:rgba(3,113,91,.3)}
    @media(max-width:1050px){.store-filters{grid-template-columns:repeat(2,minmax(0,1fr))}.filter-actions{grid-column:1/-1}}
    @media(max-width:900px){.store-header{grid-template-columns:1fr}}
    @media(max-width:620px){.store-filters{grid-template-columns:1fr;padding:14px}.filter-actions{grid-column:auto}.filter-actions button,.filter-actions .btn{flex:1}.category-tabs{margin-bottom:18px}}
</style>

@php
    $titulosCategoria = [
        'alimento_mascota' => ['titulo' => 'Alimentos', 'descripcion' => 'Alimentos, snacks, productos de rutina y compras rapidas para el hogar.'],
        'medicamento' => ['titulo' => 'Farmacia', 'descripcion' => 'Medicamentos, antiparasitarios, suplementos y apoyo sanitario.'],
        'juguete' => ['titulo' => 'Juguetes', 'descripcion' => 'Juguetes, mordedores, enrichment y accesorios para actividad diaria.'],
        'hotel' => ['titulo' => 'Hoteles', 'descripcion' => 'Reservas, estadias diarias y convenios de hoteleria para mascotas.'],
        'paseo_diario' => ['titulo' => 'Paseos diarios', 'descripcion' => 'Paseos programados, visitas y acompanamiento diario para mascotas.'],
        'cementerio' => ['titulo' => 'Cementerio', 'descripcion' => 'Servicios de despedida, retiro y apoyo respetuoso para mascotas.'],
        'cuidado' => ['titulo' => 'Cuidados y utiles', 'descripcion' => 'Higiene, limpieza, paseo, transporte y articulos utiles para mascotas.'],
        'servicio' => ['titulo' => 'Servicios a domicilio', 'descripcion' => 'Bano, peluqueria, veterinaria a domicilio y apoyos programables.'],
        'utensilio' => ['titulo' => 'Utiles', 'descripcion' => 'Camas, platos, correas, transporte y articulos utiles para mascotas.'],
    ];
    $cabecera = $titulosCategoria[$categoria] ?? ($secciones[$categoria] ?? ['titulo' => 'Tienda para mascotas', 'descripcion' => 'Venta online, carro, pago local, servicios y despacho con tracking.']);
@endphp

<div class="store-header">
    <div>
        <h1>{{ $cabecera['titulo'] }}</h1>
        <p class="muted">
            {{ $cabecera['descripcion'] }}
        </p>
    </div>
    <form class="store-order" method="GET" action="{{ route('tienda.catalogo') }}" data-orden-form>
        @if($categoria)
            <input type="hidden" name="categoria" value="{{ $categoria }}">
        @endif
        @if($busqueda)
            <input type="hidden" name="buscar" value="{{ $busqueda }}">
        @endif
        @if($filtroCategoria)
            <input type="hidden" name="tipo" value="{{ $filtroCategoria }}">
        @endif
        <label for="orden">Ordenar por:</label>
        <select id="orden" name="orden">
            <option value="">Normal</option>
            <option value="precio_asc" @selected($orden === 'precio_asc')>Menor a mayor</option>
            <option value="precio_desc" @selected($orden === 'precio_desc')>Mayor a menor</option>
        </select>
        <button class="store-order-enviar" type="submit" data-orden-enviar>Ordenar</button>
    </form>
</div>

@if($planExtra)
    <div class="alert">
        Estas agregando productos para complementar tu pedido mensual de {{ $planExtra->producto?->nombre }}.
        En el pago podras indicar si quieres incluirlos tambien en tu pedido mensual.
    </div>
@endif

<div class="category-tabs">
    <a class="btn {{ !$categoria || $categoria === 'general' ? 'active' : '' }}" href="{{ route('tienda.catalogo') }}">Todo</a>
    <a class="btn {{ $categoria === 'alimento_mascota' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'alimento_mascota']) }}">Alimentos</a>
    <a class="btn {{ $categoria === 'medicamento' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'medicamento']) }}">Farmacia</a>
    <a class="btn {{ $categoria === 'juguete' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'juguete']) }}">Juguetes</a>
    <a class="btn {{ $categoria === 'hotel' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'hotel']) }}">Hoteles</a>
    <a class="btn {{ $categoria === 'paseo_diario' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'paseo_diario']) }}">Paseos diarios</a>
    <a class="btn {{ $categoria === 'cementerio' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'cementerio']) }}">Cementerio</a>
    <a class="btn {{ $categoria === 'cuidado' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'cuidado']) }}">Cuidados</a>
    <a class="btn {{ $categoria === 'servicio' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'servicio']) }}">Servicios</a>
    <a class="btn {{ $categoria === 'utensilio' ? 'active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'utensilio']) }}">Utiles</a>
</div>

<form method="GET" action="{{ route('tienda.catalogo') }}" class="store-filters">
    @if($categoria)
        <input type="hidden" name="categoria" value="{{ $categoria }}">
    @endif
    <input type="hidden" name="orden" value="{{ $orden }}">
    <div class="filter-field">
        <label for="buscar">Buscar por nombre</label>
        <div class="filter-control-wrap search-control">
            <input id="buscar" type="search" name="buscar" value="{{ $busqueda }}" placeholder="Nombre del producto o servicio" aria-label="Buscar por nombre">
        </div>
    </div>
    <div class="filter-field">
        <label for="tipo">Categoría</label>
        <div class="filter-control-wrap">
        <select id="tipo" name="tipo" aria-label="Buscar por categoría">
            <option value="">Todas</option>
            @foreach($categoriasTienda as $slug => $titulo)
                <option value="{{ $slug }}" @selected($filtroCategoria === $slug)>{{ $titulo }}</option>
            @endforeach
        </select>
        </div>
    </div>
    <div class="filter-actions">
        <button class="btn-success">Aplicar</button>
        @if($busqueda || $filtroCategoria || $orden)
            <a class="btn btn-secondary" href="{{ route('tienda.catalogo', $categoria ? ['categoria' => $categoria] : []) }}">Limpiar</a>
        @endif
    </div>
</form>

<div class="grid">
@forelse($productos as $producto)
    <div class="col-3">
        <div class="card">
            <div class="product-photo">
                @if($producto->foto_url)
                    <img src="{{ asset($producto->foto_url) }}" alt="{{ $producto->nombre }}">
                @else
                    <x-icono nombre="mascota" class="product-placeholder-icon" />
                @endif
            </div>
            <span class="badge">{{ $producto->categoria }}</span>
            <h3 style="margin-top:12px">{{ $producto->nombre }}</h3>
            <p class="muted">{{ $producto->marca }} {{ $producto->peso ? '- '.$producto->peso : '' }}</p>
            <p>{{ $producto->descripcion }}</p>
            <div class="between">
                <strong>${{ number_format($producto->precio, 0, ',', '.') }}</strong>
                <span class="muted">Stock {{ $producto->stock }}</span>
            </div>
            <form method="POST" action="{{ route('tienda.agregar', $producto) }}" class="row" style="margin-top:14px">
                @csrf
                <div class="qty" data-qty>
                    <button class="qty-btn" type="button" data-qty-paso="-1" aria-label="Quitar una unidad">&minus;</button>
                    <input class="qty-campo" type="number" name="cantidad" value="1" min="1" max="{{ max(1, $producto->stock) }}" aria-label="Cantidad" data-qty-campo>
                    <button class="qty-btn" type="button" data-qty-paso="1" aria-label="Agregar una unidad">+</button>
                </div>
                <button class="btn-success"><x-icono nombre="plus" class="isdi-izq isdi-blanco" />Agregar</button>
            </form>
        </div>
    </div>
@empty
    <div class="col-12"><div class="card">No hay productos activos.</div></div>
@endforelse
</div>
@endsection
