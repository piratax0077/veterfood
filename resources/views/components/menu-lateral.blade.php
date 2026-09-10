{{--
    Menu lateral global. Uso:
    <x-menu-lateral etiqueta="Navegacion administrador" :grupos="[
        ['titulo' => 'Mi cuenta', 'items' => [
            ['seccion' => 'resumen', 'texto' => 'Resumen', 'icono' => 'inicio'],          // abre una seccion de la misma pagina
            ['url' => route('vouchers.usuario'), 'texto' => 'Vouchers', 'icono' => 'cupon'], // enlace a otra pagina
            ['seccion' => 'tracking', 'texto' => 'En despacho', 'icono' => 'seguimiento', 'destacado' => true],
            ['seccion' => 'aprobaciones', 'texto' => 'Aprobaciones', 'icono' => 'aprobacion', 'contador' => 3], // burbuja con cantidad pendiente (se oculta si es 0)
        ]],
    ]" />
    Props: grupos, etiqueta (texto para lectores de pantalla), activo (seccion inicial; por defecto la primera), salir (boton Cerrar Sesion).
--}}
@props(['grupos' => [], 'etiqueta' => 'Menú', 'activo' => null, 'salir' => true])

@php
    $activo ??= collect($grupos)->flatMap(fn ($grupo) => $grupo['items'] ?? [])->firstWhere('seccion')['seccion'] ?? null;
@endphp

<aside {{ $attributes->class('menu-lateral') }}>
    <nav class="menu-lateral-nav" aria-label="{{ $etiqueta }}">
        <div class="menu-lateral-lista">
            @foreach($grupos as $grupo)
                <div class="menu-lateral-grupo">
                    @if(!empty($grupo['titulo']))
                        <p class="menu-lateral-titulo">{{ $grupo['titulo'] }}</p>
                    @endif
                    @foreach($grupo['items'] ?? [] as $item)
                        @php
                            $clases = ['menu-lateral-item', 'is-activo' => isset($item['seccion']) && $item['seccion'] === $activo, 'is-destacado' => !empty($item['destacado'])];
                            $contador = (int) ($item['contador'] ?? 0);
                        @endphp
                        @if(isset($item['seccion']))
                            <button type="button" @class($clases) data-menu-ir="{{ $item['seccion'] }}" @if($item['seccion'] === $activo) aria-current="page" @endif>
                                @if(!empty($item['icono']))<x-icono :nombre="$item['icono']" />@endif{{ $item['texto'] }}
                                @if($contador > 0)<span class="menu-lateral-contador" aria-label="{{ $contador }} pendientes">{{ $contador > 99 ? '99+' : $contador }}</span>@endif
                            </button>
                        @else
                            <a @class($clases) href="{{ $item['url'] ?? '#' }}">
                                @if(!empty($item['icono']))<x-icono :nombre="$item['icono']" />@endif{{ $item['texto'] }}
                                @if($contador > 0)<span class="menu-lateral-contador" aria-label="{{ $contador }} pendientes">{{ $contador > 99 ? '99+' : $contador }}</span>@endif
                            </a>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
        @if($salir)
            <form class="menu-lateral-salir" method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="menu-lateral-item menu-lateral-item--salir" type="submit" title="Cerrar Sesión"><x-icono nombre="salir" /><span class="menu-lateral-texto">Cerrar Sesión</span></button>
            </form>
        @endif
    </nav>
</aside>
