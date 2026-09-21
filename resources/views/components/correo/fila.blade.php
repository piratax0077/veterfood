@aware(['modo' => 'texto'])
@props(['etiqueta', 'detalle' => null, 'destacado' => false, 'fuerte' => false])
@php
    $f = "'Nunito','Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif";
    $monto = $modo === 'monto';
    $oscura = $fuerte || $destacado;
@endphp
{{-- Una fila de datos: la etiqueta a la izquierda y el valor a la derecha.
     "fuerte" oscurece la etiqueta, "destacado" agranda el total y "detalle" es una línea suave bajo la etiqueta --}}
<tr>
    <td valign="top" width="{{ $monto ? '100%' : '40%' }}" class="dm-borde {{ $oscura ? 'dm-titulo' : 'dm-suave' }}" style="padding:8px 16px 8px 0;border-bottom:1px solid #e4edee;font-family:{{ $f }};font-size:{{ $destacado ? 16 : 14 }}px;line-height:24px;font-weight:{{ $oscura ? 700 : 400 }};color:{{ $oscura ? '#12313b' : '#566d76' }};">
        {{ $etiqueta }}
        @if($detalle)<br><span class="dm-suave" style="font-size:13px;line-height:20px;font-weight:400;color:#566d76;">{{ $detalle }}</span>@endif
    </td>
    <td valign="top" align="right" class="dm-borde dm-titulo" style="padding:8px 0;border-bottom:1px solid #e4edee;font-family:{{ $f }};font-size:{{ $destacado ? 18 : 14 }}px;line-height:24px;font-weight:{{ $destacado ? 800 : 700 }};color:#12313b;{{ $monto ? 'white-space:nowrap;' : '' }}">{{ $slot }}</td>
</tr>
