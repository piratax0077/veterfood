@props(['nombre', 'titulo' => null])

<span {{ $attributes->class('isdi') }}
      style="--isdi-src:url('{{ asset('iconos-sdi/' . $nombre . '.svg') }}')"
      @if($titulo) role="img" aria-label="{{ $titulo }}" @else aria-hidden="true" @endif></span>
