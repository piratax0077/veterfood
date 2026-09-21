@props(['tituloIzquierda' => null, 'tituloDerecha' => null])
@php $f = "'Nunito','Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif"; @endphp
{{-- Dos recuadros lado a lado; en el celular quedan uno sobre otro --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px 0;">
    <tr>
        <td class="col dm-tinte" width="48%" valign="top" bgcolor="#f5f9f9" style="padding:16px;background-color:#f5f9f9;border:1px solid #e4edee;border-radius:8px;font-family:{{ $f }};font-size:14px;line-height:24px;color:#35505a;">
            @if($tituloIzquierda)<p class="dm-suave" style="margin:0 0 4px 0;font-size:13px;line-height:20px;font-weight:700;color:#566d76;">{{ $tituloIzquierda }}</p>@endif
            <span class="dm-texto" style="color:#35505a;">{{ $izquierda }}</span>
        </td>
        <td class="col-sep" width="4%" style="width:4%;font-size:0;line-height:0;">&nbsp;</td>
        <td class="col dm-tinte" width="48%" valign="top" bgcolor="#f5f9f9" style="padding:16px;background-color:#f5f9f9;border:1px solid #e4edee;border-radius:8px;font-family:{{ $f }};font-size:14px;line-height:24px;color:#35505a;">
            @if($tituloDerecha)<p class="dm-suave" style="margin:0 0 4px 0;font-size:13px;line-height:20px;font-weight:700;color:#566d76;">{{ $tituloDerecha }}</p>@endif
            <span class="dm-texto" style="color:#35505a;">{{ $derecha }}</span>
        </td>
    </tr>
</table>
