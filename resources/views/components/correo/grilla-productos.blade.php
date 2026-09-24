@props(['productos'])
@php
    $f = "'Nunito','Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif";
    $pesos = fn ($m) => '$' . number_format($m, 0, ',', '.');
    $pares = array_chunk($productos, 2);
@endphp
{{-- Grilla de productos: siempre 2 columnas (también en el celular, para que las fotos no queden gigantes) --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 8px 0;">
    @foreach($pares as $par)
        <tr>
            @foreach($par as $i => $producto)
                <td width="50%" valign="top" style="padding:0 {{ $i === 0 ? '6px' : '0' }} 20px {{ $i === 1 ? '6px' : '0' }};">
                    <a href="{{ $producto['url'] }}" target="_blank" style="display:block;text-decoration:none;">
                        <img src="{{ $producto['foto'] }}" width="150" height="150" alt="{{ $producto['nombre'] }}" style="display:block;width:100%;max-width:150px;height:auto;margin:0 0 10px 0;border:1px solid #d7e4e7;border-radius:8px;">
                        <span class="dm-titulo" style="display:block;font-family:{{ $f }};font-size:14px;line-height:19px;font-weight:700;color:#12313b;">{{ $producto['nombre'] }}</span>
                        <span class="dm-suave" style="display:block;font-family:{{ $f }};font-size:12px;line-height:18px;color:#566d76;margin:0 0 4px 0;">{{ $producto['marca'] }}</span>
                        <span class="dm-titulo" style="display:block;font-family:{{ $f }};font-size:15px;line-height:22px;font-weight:800;color:#12313b;">{{ $pesos($producto['precio']) }}</span>
                    </a>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 0 0;">
                        <tr>
                            <td align="center" class="boton-celda dm-boton" bgcolor="#03715b" style="border-radius:7px;background-color:#03715b;mso-padding-alt:9px 0;">
                                <a href="{{ $producto['url'] }}" target="_blank" class="boton-enlace" style="display:block;padding:9px 0;border-radius:7px;font-family:{{ $f }};font-size:13px;line-height:16px;font-weight:700;color:#ffffff;text-decoration:none;">Agregar al carro</a>
                            </td>
                        </tr>
                    </table>
                </td>
            @endforeach
            @if(count($par) === 1)
                <td width="50%" style="padding:0;">&nbsp;</td>
            @endif
        </tr>
    @endforeach
</table>
