{{-- Estrellas de valoración, aceptan decimales: <x-estrellas :nota="4.6" />. Estilos: public/css/tienda-resenas.css --}}
@props(['nota' => 0])
@php
    $nota = max(0, min(5, (float) $nota));
@endphp
<span {{ $attributes->class('estrellas') }} style="--nota:{{ $nota }}" role="img" aria-label="{{ str_replace('.', ',', (string) round($nota, 1)) }} de 5 estrellas"></span>
