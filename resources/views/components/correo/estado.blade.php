@props(['paso' => 1])
@php
    $f = "'Nunito','Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif";
    // Mismas etapas que la página de seguimiento del pedido
    $etapas = ['Pedido recibido', 'En preparación', 'Listo para despacho', 'En camino', 'Entregado'];
    $paso = max(1, min(count($etapas), (int) $paso));
    $terminado = $paso === count($etapas);
@endphp
{{-- Barra de avance del pedido: verde lo hecho, naranjo lo que está pasando ahora --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 8px 0;">
    <tr>
        @foreach($etapas as $i => $etapa)
            @php
                $n = $i + 1;
                $hecho = $n < $paso || $terminado;
                $color = $hecho ? '#03715b' : ($n === $paso ? '#f39200' : '#d7e4e7');
                $clase = $hecho ? 'dm-paso-hecho' : ($n === $paso ? '' : 'dm-paso-falta');
            @endphp
            <td width="20%" height="6" class="{{ $clase }}" bgcolor="{{ $color }}" style="height:6px;font-size:0;line-height:6px;mso-line-height-rule:exactly;background-color:{{ $color }};border-radius:3px;">&nbsp;</td>
            @if(!$loop->last)<td width="4" style="width:4px;font-size:0;line-height:0;">&nbsp;</td>@endif
        @endforeach
    </tr>
</table>
<p class="dm-suave" style="margin:0 0 24px 0;font-family:{{ $f }};font-size:13px;line-height:20px;color:#566d76;">Paso {{ $paso }} de {{ count($etapas) }} · <strong class="dm-titulo" style="color:#12313b;">{{ $etapas[$paso - 1] }}</strong></p>
