@props(['url' => '#', 'bajo' => 'Mala', 'alto' => 'Excelente'])
@php
    $f = "'Nunito','Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif";
    $union = str_contains($url, '?') ? '&' : '?';
@endphp
{{-- Notas del 1 al 5: cada número es un enlace que lleva la nota elegida --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 8px 0;">
    <tr>
        @foreach(range(1, 5) as $nota)
            <td width="18%" align="center" class="nota-enlace dm-borde" style="border:1px solid #d7e4e7;border-radius:8px;mso-padding-alt:12px 0;">
                <a href="{{ $url . $union . 'nota=' . $nota }}" target="_blank" aria-label="Calificar con {{ $nota }} de 5" class="dm-enlace" style="display:block;padding:12px 0;font-family:{{ $f }};font-size:20px;line-height:24px;font-weight:800;color:#03715b;text-decoration:none;">{{ $nota }}</a>
            </td>
            @if($nota < 5)<td width="2%" style="width:2%;font-size:0;line-height:0;">&nbsp;</td>@endif
        @endforeach
    </tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px 0;">
    <tr>
        <td align="left" class="dm-suave" style="font-family:{{ $f }};font-size:13px;line-height:20px;color:#566d76;">1 · {{ $bajo }}</td>
        <td align="right" class="dm-suave" style="font-family:{{ $f }};font-size:13px;line-height:20px;color:#566d76;">5 · {{ $alto }}</td>
    </tr>
</table>
