@props(['tono' => 'neutro'])
@php
    $f = "'Nunito','Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif";
    $tonos = [
        'verde'   => ['fondo' => '#e3f7ee', 'borde' => '#a7e0cc', 'texto' => '#025443', 'punto' => '#03715b'],
        'naranjo' => ['fondo' => '#fff4e0', 'borde' => '#fcd9a0', 'texto' => '#8a5300', 'punto' => '#f39200'],
        'rojo'    => ['fondo' => '#fdecee', 'borde' => '#f5c2c7', 'texto' => '#9f2a33', 'punto' => '#c2414a'],
        'neutro'  => ['fondo' => '#eef3f4', 'borde' => '#d7e4e7', 'texto' => '#35505a', 'punto' => '#566d76'],
    ];
    $t = $tonos[$tono] ?? $tonos['neutro'];
@endphp
{{-- Etiqueta corta con el estado del correo --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 16px 0;">
    <tr>
        <td class="dm-t-{{ $tono }}" bgcolor="{{ $t['fondo'] }}" style="padding:4px 12px;background-color:{{ $t['fondo'] }};border:1px solid {{ $t['borde'] }};border-radius:999px;font-family:{{ $f }};font-size:13px;line-height:16px;font-weight:700;color:{{ $t['texto'] }};">
            <span style="font-size:10px;line-height:16px;color:{{ $t['punto'] }};">&#9679;</span>&nbsp;&nbsp;{{ $slot }}
        </td>
    </tr>
</table>
