@props(['tono' => 'neutro'])
@php
    $f = "'Nunito','Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif";
    $tonos = [
        'verde'   => ['fondo' => '#e3f7ee', 'borde' => '#a7e0cc', 'texto' => '#025443'],
        'naranjo' => ['fondo' => '#fff4e0', 'borde' => '#fcd9a0', 'texto' => '#8a5300'],
        'rojo'    => ['fondo' => '#fdecee', 'borde' => '#f5c2c7', 'texto' => '#9f2a33'],
        'neutro'  => ['fondo' => '#eef3f4', 'borde' => '#d7e4e7', 'texto' => '#35505a'],
    ];
    $t = $tonos[$tono] ?? $tonos['neutro'];
@endphp
{{-- Recuadro para una nota importante --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px 0;">
    <tr>
        <td class="dm-t-{{ $tono }}" bgcolor="{{ $t['fondo'] }}" style="padding:16px 24px;background-color:{{ $t['fondo'] }};border:1px solid {{ $t['borde'] }};border-radius:8px;font-family:{{ $f }};font-size:14px;line-height:24px;color:{{ $t['texto'] }};">{{ $slot }}</td>
    </tr>
</table>
