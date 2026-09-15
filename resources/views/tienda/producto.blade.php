@extends('layouts.app')

@section('title', $producto->nombre)
@section('estilos', 'css/tienda-catalogo.css, css/tienda-producto.css')

@section('content')
@php
    $pesos = fn ($monto) => '$' . number_format($monto, 0, ',', '.');
    $normalizar = fn ($texto) => Str::lower(Str::ascii((string) $texto));
    $texto = $normalizar($producto->nombre . ' ' . $producto->descripcion);

    // Mascota y categoría de la tienda a partir del nombre y la descripción
    $seccion = match (true) {
        Str::contains($texto, ['gato', 'felino', 'gatito', 'kitten']) => 'gatos',
        Str::contains($texto, ['perro', 'canino', 'cachorro', 'puppy']) => 'perros',
        default => null,
    };
    $datosSeccion = $seccion ? config("tienda_categorias.{$seccion}") : null;
    $categoriaTienda = null;
    if ($datosSeccion) {
        foreach ($datosSeccion['categorias'] as $nombre => $info) {
            $filtro = $info['filtro'] ?? [];
            if ($producto->subcategoria && $normalizar($producto->subcategoria) === $normalizar($nombre)) { $categoriaTienda = $nombre; break; }
            if (empty($filtro['categorias']) && empty($filtro['palabras'])) continue;
            $porCategoria = empty($filtro['categorias']) || in_array($producto->categoria, $filtro['categorias'], true);
            $porPalabras = empty($filtro['palabras']) || Str::contains($texto, $filtro['palabras']);
            $sinExcluidas = empty($filtro['sin']) || !Str::contains($texto, $filtro['sin']);
            if ($porCategoria && $porPalabras && $sinExcluidas) { $categoriaTienda = $nombre; break; }
        }
    }
    $esAlimentoSeco = $categoriaTienda === 'Alimento seco';

    $etapa = match (true) {
        Str::contains($texto, ['cachorro', 'puppy', 'kitten', 'junior', 'gatito']) => $seccion === 'gatos' ? 'Gatito' : 'Cachorro',
        Str::contains($texto, ['senior', 'mayor']) => 'Senior',
        Str::contains($texto, ['adulto', 'adult']) => 'Adulto',
        default => null,
    };

    // Precio por kilo cuando el formato viene en kg o g
    $kilos = null;
    if (preg_match('/(\d+(?:[.,]\d+)?)\s*(kg|kilo|g)\b/i', (string) $producto->peso, $m)) {
        $cantidadPeso = (float) str_replace(',', '.', $m[1]);
        $kilos = Str::lower($m[2]) === 'g' ? $cantidadPeso / 1000 : $cantidadPeso;
    }
    $precioKilo = $kilos && $kilos >= 0.5 ? (int) round($producto->precio_final / $kilos) : null;

    $miga = [['Tienda', route('tienda.catalogo')]];
    if ($seccion) $miga[] = [$datosSeccion['titulo'], route('tienda.seccion', $seccion)];
    if ($categoriaTienda) $miga[] = [$categoriaTienda, route('tienda.seccion', [$seccion, Str::slug($categoriaTienda)])];
    $miga[] = [$producto->nombre, null];

    $caracteristicas = array_filter([
        'Marca' => $producto->marca,
        'Tipo de alimento' => $esAlimentoSeco ? 'Seco (croquetas)' : null,
        'Mascota' => ['perros' => 'Perro', 'gatos' => 'Gato'][$seccion] ?? null,
        'Etapa de vida' => $etapa,
        'Formato' => $producto->peso,
        'Categoría' => $categoriaTienda ?? $producto->categoria_etiqueta,
    ]);

    $ultimas = $producto->stock > 0 && $producto->stock <= 5;
    // Primero los de la misma mascota y nunca los de la otra (ej. nada de gatos en un producto de perro)
    $palabrasMascota = ['gatos' => ['gato', 'felino', 'gatito', 'kitten'], 'perros' => ['perro', 'canino', 'cachorro', 'puppy']];
    $relacionados = $relacionados
        ->reject(fn ($otro) => $seccion && Str::contains($normalizar($otro->nombre . ' ' . $otro->descripcion), $palabrasMascota[$seccion === 'gatos' ? 'perros' : 'gatos']))
        ->sortByDesc(fn ($otro) => $seccion && Str::contains($normalizar($otro->nombre . ' ' . $otro->descripcion), $palabrasMascota[$seccion]))
        ->take(5);
@endphp

<nav class="ficha-miga" aria-label="Estás en">
    <ol>
        @foreach($miga as [$textoMiga, $enlaceMiga])
            <li>@if($enlaceMiga)<a href="{{ $enlaceMiga }}">{{ $textoMiga }}</a>@else<span aria-current="page">{{ $textoMiga }}</span>@endif</li>
        @endforeach
    </ol>
</nav>

<article class="ficha">
    <div class="ficha-galeria">
        <div class="ficha-foto">
            @if($producto->foto_url)
                <img src="{{ asset($producto->foto_url) }}" alt="{{ $producto->nombre }}">
            @else
                <x-icono nombre="mascota" class="product-placeholder-icon" />
            @endif
            @if($producto->en_oferta)
                <span class="producto-descuento">-{{ $producto->descuento_porcentaje }}%</span>
            @endif
        </div>
    </div>

    <div class="ficha-info">
        @if($producto->marca)<p class="ficha-marca">{{ $producto->marca }}</p>@endif
        <h1>{{ $producto->nombre }}</h1>

        <div class="ficha-etiquetas">
            @if($categoriaTienda)<span class="ficha-etiqueta ficha-etiqueta--verde">{{ $categoriaTienda }}</span>@endif
            @if($seccion)<span class="ficha-etiqueta">{{ $datosSeccion['titulo'] }}</span>@endif
            @if($etapa)<span class="ficha-etiqueta">{{ $etapa }}</span>@endif
        </div>

        <div @class(['ficha-precio', 'is-oferta' => $producto->en_oferta])>
            <strong>{{ $pesos($producto->precio_final) }}</strong>
            @if($producto->en_oferta)
                <s>{{ $pesos($producto->precio) }}</s>
                <span class="ficha-ahorro">Ahorras {{ $pesos($producto->precio - $producto->precio_final) }}</span>
            @endif
        </div>
        @if($precioKilo)<p class="ficha-precio-kilo">{{ $pesos($precioKilo) }} por kilo</p>@endif

        @if($producto->peso)
            <p class="ficha-rotulo">Formato</p>
            <div class="ficha-formatos">
                <span class="ficha-formato is-activo">{{ $producto->peso }}</span>
            </div>
        @endif

        <p @class(['ficha-stock', 'is-ultimas' => $ultimas, 'is-agotado' => $producto->stock < 1])>
            @if($producto->stock < 1)
                Sin stock por ahora
            @elseif($ultimas)
                ¡Últimas {{ $producto->stock }} unidades!
            @else
                Disponible · {{ $producto->stock }} unidades
            @endif
        </p>
        {{-- Mientras no exista la tienda del vendedor, se usa la sucursal del producto o VeterFood --}}
        <p class="ficha-vendedor">Vendido por: <span>{{ $producto->sucursal_destino ?: 'VeterFood' }}</span></p>

        <form method="POST" action="{{ route('tienda.agregar', $producto) }}" class="ficha-agregar">
            @csrf
            <div class="qty" data-qty>
                <button class="qty-btn" type="button" data-qty-paso="-1" aria-label="Quitar una unidad">&minus;</button>
                <input class="qty-campo" type="number" name="cantidad" value="1" min="1" max="{{ max(1, $producto->stock) }}" aria-label="Cantidad" data-qty-campo>
                <button class="qty-btn" type="button" data-qty-paso="1" aria-label="Agregar una unidad">+</button>
            </div>
            <button class="btn-success ficha-boton" @disabled($producto->stock < 1)><x-icono nombre="carrito" class="isdi-izq isdi-blanco" />Agregar al carro</button>
        </form>

        @if($esAlimentoSeco)
            <div class="ficha-plan">
                <span class="ficha-plan-icono" aria-hidden="true"><x-icono nombre="suscripcion" /></span>
                <div>
                    <strong>¿Lo compras todos los meses?</strong>
                    <span>Prográmalo con una suscripción de alimento y recíbelo en tu casa sin preocuparte.</span>
                </div>
                <a href="{{ route('cliente.panel') }}#mi-plan">Ver suscripciones</a>
            </div>
        @endif

        <div class="ficha-acordeon">
            <details>
                <summary>Descripción</summary>
                <div class="ficha-acordeon-cuerpo">
                    <p>{{ $producto->descripcion ?: 'Pronto agregaremos más información de este producto.' }}</p>
                </div>
            </details>

            <details>
                <summary>Especificaciones</summary>
                <div class="ficha-acordeon-cuerpo">
                    <dl class="ficha-tabla">
                        @foreach($caracteristicas as $dato => $valor)
                            <div><dt>{{ $dato }}</dt><dd>{{ $valor }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </details>

            <details>
                <summary>Despacho</summary>
                <div class="ficha-acordeon-cuerpo">
                    <ul class="ficha-envio">
                        <li><x-icono nombre="seguimiento" /><span><strong>Despacho gratis</strong> sobre $50.000 en la Región Metropolitana</span></li>
                        <li><x-icono nombre="tienda" /><span><strong>Retiro en tienda</strong> sin costo</span></li>
                        <li><x-icono nombre="tracking" /><span><strong>Sigue tu pedido</strong> en línea con tu número de seguimiento</span></li>
                    </ul>
                </div>
            </details>
        </div>
    </div>
</article>

@if($relacionados->isNotEmpty())
    <section class="ficha-relacionados">
        <h2>También te puede interesar</h2>
        <div class="productos-grid">
            @foreach($relacionados as $producto)
                @include('tienda.partials.tarjeta-producto')
            @endforeach
        </div>
    </section>
@endif
@endsection
