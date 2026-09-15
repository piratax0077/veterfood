{{-- Zona para arrastrar o elegir una foto (public/js/zona-foto.js). Usa un input file normal, así el formulario se envía igual que siempre. --}}
@props(['name' => 'foto', 'id' => null, 'actual' => null, 'maximoMb' => 4, 'texto' => 'Arrastra una foto aquí'])
@php $id = $id ?: 'zona-foto-' . Str::random(6); @endphp

<div {{ $attributes->class('zona-foto') }} data-zona-foto data-maximo-mb="{{ $maximoMb }}" @if($actual) data-actual="{{ $actual }}" @endif>
    <input class="zona-foto-input" type="file" id="{{ $id }}" name="{{ $name }}" accept="image/jpeg,image/png,image/webp,image/gif" data-zona-foto-input>
    <label class="zona-foto-area" for="{{ $id }}" data-zona-foto-area>
        <span class="zona-foto-vacia">
            <span class="zona-foto-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8.5A2.5 2.5 0 0 1 6.5 6h1.8l1.4-2h4.6l1.4 2h1.8A2.5 2.5 0 0 1 20 8.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5z"/><circle cx="12" cy="13" r="3.6"/></svg>
            </span>
            <strong>{{ $texto }}</strong>
            <span>o <u>búscala en tu equipo</u></span>
            <small>JPG, PNG o WEBP · hasta {{ $maximoMb }} MB</small>
        </span>
        <img class="zona-foto-vista" alt="Vista previa de la foto" hidden data-zona-foto-vista>
        <span class="zona-foto-cambiar" aria-hidden="true">Cambiar foto</span>
    </label>
    <div class="zona-foto-pie" hidden data-zona-foto-pie>
        <span class="zona-foto-nombre" data-zona-foto-nombre></span>
        <button type="button" class="zona-foto-quitar" data-zona-foto-quitar>Quitar</button>
    </div>
</div>
