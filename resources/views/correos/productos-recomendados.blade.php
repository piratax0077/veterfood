{{-- Extra · Productos recomendados (estilo Shein / Mercado Libre: recomendación + despacho gratis por tiempo limitado). Asunto: Esto también te podría interesar --}}
@php
    $nombre ??= 'Camila';
    $email ??= 'camila.rojas@correo.cl';
    $vence ??= 'domingo 27 de septiembre';
    $urlCatalogo ??= route('tienda.catalogo');
    $productos ??= [
        ['nombre' => 'Purina One alimento húmedo para gatos', 'marca' => 'Purina', 'precio' => 2490, 'foto' => asset('images/tienda/gato/one-purina-humedo-gato.png'), 'url' => route('tienda.producto', 37)],
        ['nombre' => 'Juego interactivo con varita para gatos', 'marca' => 'PlayPet', 'precio' => 4990, 'foto' => asset('images/tienda/gato/juego-gato.jpg'), 'url' => route('tienda.producto', 38)],
        ['nombre' => 'Corta uñas profesional para gatos', 'marca' => 'CleanPet', 'precio' => 3990, 'foto' => asset('images/tienda/gato/corta-unas-gato.jpg'), 'url' => route('tienda.producto', 40)],
        ['nombre' => 'Pelota resistente', 'marca' => 'PlayPet', 'precio' => 5990, 'foto' => asset('images/productos/ofertas/pelota-resistente.jpg'), 'url' => route('tienda.producto', 7)],
        ['nombre' => 'Dispensador de alimento', 'marca' => 'HomePet', 'precio' => 21990, 'foto' => asset('images/productos/ofertas/dispensador-alimento.jpg'), 'url' => route('tienda.producto', 10)],
        ['nombre' => 'Rascador torre para gatos', 'marca' => 'PlayPet', 'precio' => 34990, 'foto' => asset('images/tienda/gato/rascador-torre-gato.jpg'), 'url' => route('tienda.producto', 26)],
    ];
@endphp
<x-correo.plantilla asunto="Esto también te podría interesar" preheader="Despacho gratis en tu próximo pedido si lo haces antes del domingo." contexto="Recomendado para ti" :email="$email">
    <x-correo.pastilla tono="naranjo">Recomendado para ti</x-correo.pastilla>
    <x-correo.titulo>Esto también te podría interesar, {{ $nombre }}</x-correo.titulo>
    <x-correo.texto>Elegimos estos productos según tu última compra. Tócalos para verlos y agregarlos a tu carro.</x-correo.texto>
    <x-correo.aviso tono="naranjo">Despacho gratis en tu próximo pedido si lo haces antes del <strong>{{ $vence }}</strong>.</x-correo.aviso>
    <x-correo.grilla-productos :productos="$productos" />
    <x-correo.boton :url="$urlCatalogo" variante="secundario">Ver todo el catálogo</x-correo.boton>
    <x-correo.texto suave>Te avisamos porque tienes cuenta en VeterFood. Si prefieres no recibir estas recomendaciones, adminístralo desde tu cuenta.</x-correo.texto>
</x-correo.plantilla>
