@props(['modo' => 'texto'])
{{-- Lista de datos con líneas finas. Cada fila es un <x-correo.fila>.
     modo="texto" (datos con valores largos, como una dirección) o modo="monto" (productos y totales) --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dm-borde" style="margin:0 0 24px 0;border-top:1px solid #e4edee;">
    {{ $slot }}
</table>
