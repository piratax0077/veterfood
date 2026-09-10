{{--
    Encabezado unico de pagina: enlace para volver, titulo, descripcion y acciones.
    Uso:
    <x-encabezado-pagina titulo="Vouchers de descuento" descripcion="..." :volver="route('admin.dashboard') . '#vouchers'">
        <a class="encabezado-boton" href="..."><x-icono nombre="plus" />Crear nuevo voucher</a>
    </x-encabezado-pagina>
    Props: titulo, descripcion, volver (url), volver-texto (por defecto "Volver al inicio").
    Estilos: public/css/encabezado-pagina.css
--}}
@props(['titulo', 'descripcion' => null, 'volver' => null, 'volverTexto' => 'Volver al inicio'])

<header {{ $attributes->class('encabezado-pagina') }}>
    @if($volver)
        <a class="encabezado-volver" href="{{ $volver }}"><x-icono nombre="volver" />{{ $volverTexto }}</a>
    @endif
    <div class="encabezado-fila">
        <div class="encabezado-titulo">
            <h1>{{ $titulo }}</h1>
            @if($descripcion)<p>{{ $descripcion }}</p>@endif
        </div>
        @if($slot->isNotEmpty())
            <div class="encabezado-acciones">{{ $slot }}</div>
        @endif
    </div>
</header>
