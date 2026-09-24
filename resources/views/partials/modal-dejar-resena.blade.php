{{-- Reseña desde "Mis compras": lo abre el botón "Dejar reseña" de cada producto (data-resena-datos con el id, nombre y foto).
     Misma estructura que la reseña de la ficha de producto: css/tienda-resenas.css · js/tienda-resenas.js --}}
@php
    $textosNotaCompra = [1 => 'Muy malo', 2 => 'Malo', 3 => 'Regular', 4 => 'Bueno', 5 => 'Excelente'];
@endphp

<x-modal id="modal-resena-compra" titulo="Escribir una reseña" ancho="chico" class="modal-resena">
    <form id="form-resena-compra" class="resena-form" data-validar data-form-resena>
        <div class="resena-form-producto">
            <span class="resena-form-foto" data-resena-foto>
                <x-icono nombre="mascota" />
            </span>
            <p><span>Tu opinión sobre</span><strong data-resena-nombre>este producto</strong></p>
        </div>

        <input type="hidden" name="producto_id">

        <div class="resena-form-nota">
            <span class="resena-form-rotulo" id="resena-compra-nota-rotulo">Calificación</span>
            <div class="resena-elegir" role="radiogroup" aria-labelledby="resena-compra-nota-rotulo" data-elegir-nota>
                @foreach($textosNotaCompra as $nota => $textoNota)
                    <label data-texto="{{ $textoNota }}">
                        <input type="radio" name="nota" value="{{ $nota }}" required data-msg="Elige de 1 a 5 estrellas.">
                        <x-icono nombre="estrella" />
                        <span class="sr-only">{{ $nota }} de 5, {{ Str::lower($textoNota) }}</span>
                    </label>
                @endforeach
                <span class="resena-elegir-texto" data-nota-texto aria-hidden="true">Elige de 1 a 5</span>
            </div>
        </div>

        <div>
            <label class="floating-label-activo-sm" for="resena-compra-titulo">Título</label>
            <input class="form-control form-control-sm" id="resena-compra-titulo" name="titulo" maxlength="80" placeholder="Ej: Lo come sin dejar nada" required>
        </div>

        <div>
            <label class="floating-label-activo-sm" for="resena-compra-texto">Tu reseña</label>
            <textarea class="form-control form-control-sm" id="resena-compra-texto" name="texto" rows="5" minlength="20" maxlength="500" placeholder="¿Cómo le fue a tu mascota? ¿Lo volverías a comprar?" required data-contar></textarea>
            <span class="resena-form-contador" data-contador aria-hidden="true">0 / 500</span>
        </div>
    </form>

    <x-slot:pie>
        <button type="button" class="btn btn-cancelar" data-modal-cerrar><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</button>
        <button type="submit" class="btn btn-success" form="form-resena-compra"><x-icono nombre="activar" class="isdi-izq" />Publicar reseña</button>
    </x-slot:pie>
</x-modal>
