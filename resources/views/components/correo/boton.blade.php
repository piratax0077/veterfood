@props(['url' => '#', 'variante' => 'principal'])
@php $f = "'Nunito','Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif"; @endphp
{{-- Botón principal (relleno verde) o secundario (solo borde). Alto de 48 px para tocarlo bien con el dedo --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" class="boton-tabla" style="margin:0 0 24px 0;">
    <tr>
        @if($variante === 'secundario')
            <td align="center" class="boton-celda dm-boton-2" style="border:1px solid #03715b;border-radius:8px;mso-padding-alt:13px 32px;">
                <a href="{{ $url }}" target="_blank" class="boton-enlace-2" style="display:inline-block;padding:13px 31px;border-radius:8px;font-family:{{ $f }};font-size:16px;line-height:20px;font-weight:700;color:#03715b;text-decoration:none;">{{ $slot }}</a>
            </td>
        @else
            <td align="center" class="boton-celda dm-boton" bgcolor="#03715b" style="border-radius:8px;background-color:#03715b;mso-padding-alt:14px 32px;">
                <a href="{{ $url }}" target="_blank" class="boton-enlace" style="display:inline-block;padding:14px 32px;background-color:#03715b;border-radius:8px;font-family:{{ $f }};font-size:16px;line-height:20px;font-weight:700;color:#ffffff;text-decoration:none;">{{ $slot }}</a>
            </td>
        @endif
    </tr>
</table>
