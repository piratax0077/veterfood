{{--
    Campo de telefono de Chile: bandera + prefijo +56 fijo y 9 digitos.
    Guarda el valor completo (+56XXXXXXXXX) en un campo oculto con el name indicado.
    Uso: <x-campo-telefono name="telefono" :value="old('telefono', $usuario?->telefono)" />
    Props: name, value, label (texto del label; vacio = sin label), placeholder, required,
           flotante (true = label sobre el borde del campo; false = label normal arriba).
    Estilos: public/css/formularios.css · Comportamiento: public/js/telefono.js
--}}
@props(['name' => 'telefono', 'value' => null, 'label' => 'Teléfono', 'placeholder' => '912345678', 'required' => false, 'flotante' => true])

@php
    $digitos = substr(preg_replace('/\D/', '', (string) $value), -9);
    $idCampo = $attributes->get('id') ?? 'tel-' . \Illuminate\Support\Str::slug($name) . '-' . \Illuminate\Support\Str::random(5);
@endphp

@if($label)
    <label @class(['floating-label-activo-sm' => $flotante]) for="{{ $idCampo }}">{{ $label }}</label>
@endif
<div class="campo-telefono" data-telefono>
    <span class="campo-telefono-prefijo" title="Chile">
        <svg viewBox="0 0 30 20" aria-hidden="true"><rect width="30" height="20" fill="#fff"/><rect y="10" width="30" height="10" fill="#d52b1e"/><rect width="10" height="10" fill="#0039a6"/><path fill="#fff" d="M5 2.4l.59 1.79h1.88L5.95 5.31l.58 1.79L5 6l-1.53 1.1.58-1.79-1.52-1.12h1.88z"/></svg>
        +56
    </span>
    <input {{ $attributes->except('id')->class('form-control form-control-sm') }} type="tel" id="{{ $idCampo }}" value="{{ $digitos }}" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" placeholder="{{ $placeholder }}" autocomplete="tel-national" title="Ingresa 9 dígitos" data-telefono-digitos @required($required)>
    <input type="hidden" name="{{ $name }}" value="{{ $digitos !== '' ? '+56' . $digitos : '' }}" data-telefono-valor>
</div>
