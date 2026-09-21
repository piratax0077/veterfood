@props([
    'asunto' => 'VeterFood',
    'preheader' => '',
    'contexto' => null,
    'email' => null,
    'logo' => null,
    'logoBlanco' => null,
])
@php
    // Los logos van en PNG porque Gmail y Outlook no muestran SVG
    $logo = $logo ?: asset('images/correos/logo-veterfood.png');
    $logoBlanco = $logoBlanco ?: asset('images/correos/logo-veterfood-blanco.png');
    $f = "'Nunito','Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif";
@endphp
<!doctype html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ $asunto }}</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <style>td, th, div, p, a, h1, h2 { font-family: 'Segoe UI', Arial, sans-serif !important; }</style>
    <![endif]-->
    <!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!--<![endif]-->
    <style>{!! file_get_contents(public_path('css/correos.css')) !!}</style>
</head>
<body class="dm-fondo" style="margin:0;padding:0;width:100%;background-color:#f2f7f8;">
    {{-- Texto de vista previa: lo que se lee junto al asunto en la bandeja --}}
    <div style="display:none;max-height:0;max-width:0;overflow:hidden;opacity:0;font-size:1px;line-height:1px;color:#f2f7f8;mso-hide:all;">{{ $preheader }}{!! str_repeat('&#847;&zwnj;&nbsp;', 80) !!}</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f2f7f8" class="dm-fondo" style="width:100%;background-color:#f2f7f8;">
        <tr>
            <td align="center" class="marco" style="padding:24px 16px 40px 16px;">
                <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">

                    {{-- Cabecera: logo a la izquierda y de qué trata el correo a la derecha --}}
                    <tr>
                        <td style="padding:0 0 16px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left" valign="middle">
                                        <a href="{{ route('tienda.catalogo') }}" target="_blank" style="text-decoration:none;">
                                            <img class="logo-claro" src="{{ $logo }}" width="144" height="38" alt="VeterFood" style="display:block;width:144px;height:auto;border:0;">
                                            <img class="logo-oscuro" src="{{ $logoBlanco }}" width="144" height="38" alt="VeterFood" style="display:none;max-height:0;overflow:hidden;mso-hide:all;width:144px;height:auto;border:0;">
                                        </a>
                                    </td>
                                    @if($contexto)
                                        <td align="right" valign="middle" class="dm-suave" style="font-family:{{ $f }};font-size:14px;line-height:24px;color:#566d76;">{{ $contexto }}</td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Tarjeta con el contenido --}}
                    <tr>
                        <td class="tarjeta dm-tarjeta" bgcolor="#ffffff" style="padding:40px 40px 24px 40px;background-color:#ffffff;border:1px solid #d7e4e7;border-radius:12px;font-family:{{ $f }};font-size:16px;line-height:24px;color:#35505a;">
                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- Pie --}}
                    <tr>
                        <td class="pie" style="padding:24px 8px 0 8px;font-family:{{ $f }};font-size:13px;line-height:20px;color:#566d76;">
                            <p class="dm-suave" style="margin:0 0 8px 0;color:#566d76;">¿Dudas? Escríbenos por <a href="https://wa.me/56984882443" target="_blank" class="dm-enlace" style="color:#03715b;text-decoration:underline;font-weight:700;">WhatsApp</a> al +56 9 8488 2443, de lunes a sábado de 09:00 a 19:00 hrs.</p>
                            <p class="dm-suave" style="margin:0 0 8px 0;color:#566d76;">VeterFood · Santiago, Chile</p>
                            @if($email)
                                <p class="dm-suave" style="margin:0;color:#566d76;">Enviamos este correo a {{ $email }} porque tienes una cuenta en VeterFood.</p>
                            @endif
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
