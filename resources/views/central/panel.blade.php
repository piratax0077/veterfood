@extends('layouts.app')

@section('title', 'Existencias - ' . $config['titulo'])
@section('estilos', 'css/central-panel.css')

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


{{-- El admin vuelve a su inicio; para la central de ventas esta es su pagina principal --}}
<x-encabezado-pagina
    :titulo="'Existencias ' . $config['titulo']"
    descripcion="Control de stock, precios, estado crítico y disponibilidad por rubro."
    :volver="auth()->user()?->tieneRol('admin') ? route('admin.dashboard') . '#operacion' : null">
    <a class="encabezado-boton encabezado-boton--secundario" href="{{ route('tienda.catalogo') }}"><x-icono nombre="tienda" />Ver tienda</a>
    <a class="encabezado-boton encabezado-boton--secundario" href="{{ route('central.ventas', 'preparar') }}">Sección ventas</a>
    <a class="encabezado-boton" href="{{ route('central.ingreso', $rubroActivo) }}"><x-icono nombre="plus" />Ingresar productos</a>
</x-encabezado-pagina>

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
                        <td><span class="badge tono-azul">{{ $producto->subcategoria ?: 'Sin subcategoria' }}</span></td>
                        <td>{{ $categoriaNombres[$producto->categoria] ?? $producto->categoria }}</td>
                        <td>{{ $producto->marca ?: 'Sin marca' }}</td>
                        <td>${{ number_format($producto->precio_compra ?? 0, 0, ',', '.') }}</td>
                        <td><strong>${{ number_format($producto->precio, 0, ',', '.') }}</strong></td>
                        <td><span class="stock-number">{{ $producto->stock }}</span><br><span class="muted">min {{ $producto->stock_minimo ?? 0 }}</span></td>
                        <td>
                            @if($producto->stock <= ($producto->stock_minimo ?? 0))
                                <span class="badge tono-rojo">Critico</span>
                            @else
                                <span class="badge tono-verde">Activo</span>
                            @endif
                        </td>
                        <td><x-boton-tabla tipo="tienda" href="{{ route('tienda.catalogo', ['categoria' => $producto->categoria]) }}">Ver tienda</x-boton-tabla></td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="empty-state">No hay productos en {{ strtolower($config['titulo']) }}.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
