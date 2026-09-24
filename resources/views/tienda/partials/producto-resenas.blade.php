{{-- Reseñas de la ficha del producto. Son de ejemplo hasta conectarlas con las reales; el formulario todavía no guarda.
     Estilos: public/css/tienda-resenas.css · Filtro, orden y formulario: public/js/tienda-resenas.js --}}
@php
    // En los de exóticos el texto no nombra la especie: sirve para conejos, roedores y aves
    $ejemplos = [
        'perros' => [
            ['nombre' => 'Camila R.', 'nota' => 5, 'titulo' => 'Lo come sin dejar nada', 'texto' => 'Toby es súper mañoso con la comida y este se lo come completo. Llevamos dos meses y no ha tenido ni un problema de guata.', 'dias' => 3],
            ['nombre' => 'Felipe M.', 'nota' => 5, 'titulo' => 'Se le nota en el pelo', 'texto' => 'Lo cambiamos por recomendación de la veterinaria. Al mes Luna tenía el pelo más brillante y se rasca mucho menos.', 'dias' => 9],
            ['nombre' => 'Daniela S.', 'nota' => 4, 'titulo' => 'Bueno, pero la bolsa no cierra', 'texto' => 'A Rocco le encanta y lo digiere bien. Le bajo una estrella porque la bolsa no trae cierre y hay que pasarlo a un tarro.', 'dias' => 12],
            ['nombre' => 'Ignacio P.', 'nota' => 5, 'titulo' => 'Llegó al día siguiente', 'texto' => 'Pedí el lunes y el martes ya estaba en la casa. La croqueta tiene buen tamaño y Maya la come tranquila, sin atorarse.', 'dias' => 18],
            ['nombre' => 'Valentina D.', 'nota' => 3, 'titulo' => 'Le costó acostumbrarse', 'texto' => 'Los primeros días lo dejaba en el plato. Hicimos el cambio de a poco, mezclándolo con el anterior, y ahora sí lo come, aunque no con tantas ganas.', 'dias' => 23],
            ['nombre' => 'Matías G.', 'nota' => 5, 'titulo' => 'Mejoró su digestión', 'texto' => 'Bruno tenía las deposiciones blandas con otras marcas. Con este se le normalizaron en una semana.', 'dias' => 31],
            ['nombre' => 'Francisca M.', 'nota' => 4, 'titulo' => 'Buena relación precio y calidad', 'texto' => 'No es el más barato, pero rinde harto y Canela queda satisfecha con menos cantidad que con el anterior.', 'dias' => 38],
            ['nombre' => 'Sebastián C.', 'nota' => 2, 'titulo' => 'A mi perro no le gustó', 'texto' => 'Lo probamos un par de semanas y Simón lo dejaba casi entero. Puede que sea el sabor. Volvimos al que comía antes.', 'dias' => 44],
            ['nombre' => 'Catalina H.', 'nota' => 5, 'titulo' => 'La veterinaria lo aprobó', 'texto' => 'La llevé al control y la veterinaria la encontró con muy buen peso. Seguimos con este.', 'dias' => 52],
            ['nombre' => 'Tomás F.', 'nota' => 4, 'titulo' => 'Sin olor fuerte', 'texto' => 'No tiene ese olor fuerte que tienen otros alimentos y Max lo come con ganas. Me gustaría que viniera en un formato más chico para probar.', 'dias' => 60],
            ['nombre' => 'Josefa V.', 'nota' => 5, 'titulo' => 'Ya vamos en el tercer saco', 'texto' => 'Lo compro todos los meses y siempre llega bien sellado y con fecha de vencimiento larga.', 'dias' => 67],
            ['nombre' => 'Diego R.', 'nota' => 5, 'titulo' => 'Con energía todo el día', 'texto' => 'Pancho es muy activo y con este alimento anda con energía todo el día. Además, no le ha caído mal ni una vez.', 'dias' => 75],
        ],
        'gatos' => [
            ['nombre' => 'Constanza S.', 'nota' => 5, 'titulo' => 'Por fin uno que le gusta', 'texto' => 'Mía es muy regodeona y siempre deja comida en el plato. Este se lo come completo y hasta pide más.', 'dias' => 2],
            ['nombre' => 'Joaquín T.', 'nota' => 5, 'titulo' => 'Menos bolas de pelo', 'texto' => 'Desde que cambiamos a este alimento Simba casi no ha vomitado bolas de pelo. Lo notamos a las pocas semanas.', 'dias' => 8],
            ['nombre' => 'Antonia F.', 'nota' => 4, 'titulo' => 'Bueno, aunque algo caro', 'texto' => 'A Luna le encanta y tiene el pelo suavecito. Le doy cuatro estrellas por el precio, pero lo voy a seguir comprando.', 'dias' => 13],
            ['nombre' => 'Martín E.', 'nota' => 5, 'titulo' => 'Llegó rapidísimo', 'texto' => 'Lo pedí en la mañana y llegó al día siguiente, bien embalado. Garfield lo come feliz.', 'dias' => 17],
            ['nombre' => 'Isidora N.', 'nota' => 3, 'titulo' => 'Le costó el cambio', 'texto' => 'La primera semana lo olía y se iba. Lo mezclé con su alimento anterior y de a poco lo aceptó. Ahora lo come, pero sin mucho entusiasmo.', 'dias' => 24],
            ['nombre' => 'Benjamín R.', 'nota' => 5, 'titulo' => 'Recomendado por el veterinario', 'texto' => 'En el último control el veterinario nos sugirió este alimento y Tom lo aceptó altiro. Lo vemos con buen peso y muy activo.', 'dias' => 30],
            ['nombre' => 'Florencia G.', 'nota' => 4, 'titulo' => 'El arenero huele menos', 'texto' => 'Se nota que lo digiere mejor que el que le dábamos antes y el arenero huele bastante menos.', 'dias' => 37],
            ['nombre' => 'Nicolás C.', 'nota' => 2, 'titulo' => 'No le cayó bien', 'texto' => 'A los pocos días empezó a vomitar un rato después de comer. Lo suspendimos y se le pasó. Puede que sea algo propio de ella, pero a nosotros no nos resultó.', 'dias' => 43],
            ['nombre' => 'Javiera S.', 'nota' => 5, 'titulo' => 'Pelo mucho más brillante', 'texto' => 'En un mes se le nota el pelo mucho más brillante. Oliver lo come con ganas y ya no anda pidiendo comida a cada rato.', 'dias' => 50],
            ['nombre' => 'Vicente A.', 'nota' => 4, 'titulo' => 'Buena calidad', 'texto' => 'Se nota que tiene buenos ingredientes. Salem lo come bien, aunque al principio prefería el anterior.', 'dias' => 57],
            ['nombre' => 'Fernanda T.', 'nota' => 5, 'titulo' => 'Ya es parte de la compra del mes', 'texto' => 'Lo agrego siempre al pedido y llega junto con la arena. Nunca ha llegado roto ni vencido.', 'dias' => 64],
            ['nombre' => 'Cristóbal V.', 'nota' => 5, 'titulo' => 'Mis dos gatas lo comen', 'texto' => 'Pelusa y su hermana tienen gustos muy distintos y este es el único que les gusta a las dos. Un alivio.', 'dias' => 79],
        ],
        'exoticos' => [
            ['nombre' => 'Paula M.', 'nota' => 5, 'titulo' => 'Lo come con muchas ganas', 'texto' => 'Apenas escucha la bolsa ya está esperando. Se le ve activo y con buen peso.', 'dias' => 4],
            ['nombre' => 'Gonzalo R.', 'nota' => 4, 'titulo' => 'Buena calidad', 'texto' => 'Viene entero, sin polvo al fondo de la bolsa. Le bajo una estrella porque me gustaría un formato más grande.', 'dias' => 11],
            ['nombre' => 'Carolina P.', 'nota' => 5, 'titulo' => 'Nos lo recomendó la veterinaria', 'texto' => 'En pocas semanas se notó la diferencia: está más activo y come mejor.', 'dias' => 19],
            ['nombre' => 'Andrés S.', 'nota' => 5, 'titulo' => 'Difícil de encontrar', 'texto' => 'No lo encontraba en las tiendas cerca de mi casa. Lo pedí aquí y llegó al día siguiente.', 'dias' => 26],
            ['nombre' => 'Macarena O.', 'nota' => 3, 'titulo' => 'Hay que tenerle paciencia', 'texto' => 'Primero se come lo que más le gusta y deja el resto. Con el tiempo se lo termina, pero le costó acostumbrarse.', 'dias' => 34],
            ['nombre' => 'Rodrigo C.', 'nota' => 5, 'titulo' => 'Nunca le ha caído mal', 'texto' => 'Llevo meses dándoselo y nunca le ha caído mal. El envase cierra bien y el alimento no se humedece.', 'dias' => 41],
            ['nombre' => 'Bárbara F.', 'nota' => 4, 'titulo' => 'Buen precio para la calidad', 'texto' => 'Comparado con otras marcas importadas está bien de precio y rinde harto. Lo voy a seguir comprando.', 'dias' => 53],
            ['nombre' => 'Esteban P.', 'nota' => 5, 'titulo' => 'Más activo que antes', 'texto' => 'Desde que come esto está más inquieto y juguetón, y ya no deja comida en el plato.', 'dias' => 66],
        ],
    ];
    // Solo los alimentos traen reseñas de ejemplo; el resto muestra "Aún no hay reseñas"
    $subcategoria = Str::lower(Str::ascii((string) $producto->subcategoria));
    $especie = $producto->categoria !== 'alimento_mascota' ? null : match (true) {
        in_array($seccion, ['perros', 'gatos'], true) => $seccion,
        $subcategoria === 'conejos' || preg_match('/\bconejos?\b/', $texto) => 'conejos',
        $subcategoria === 'roedores' || preg_match('/\b(cuy(es)?|roedor(es)?|hamsters?|chinchillas?|degus?)\b/', $texto) => 'roedores',
        $subcategoria === 'aves' || preg_match('/\b(aves?|pajaros?|loros?|periquitos?|canarios?|catitas?)\b/', $texto) => 'aves',
        default => 'perros',
    };
    $lista = match ($especie) {
        null => [],
        'perros', 'gatos' => $ejemplos[$especie],
        default => $ejemplos['exoticos'],
    };

    // Cada producto muestra una parte distinta de los ejemplos
    $resenas = collect($lista)
        ->map(fn ($resena, $i) => $resena + ['i' => $i])
        ->sortBy(fn ($resena) => crc32($producto->id . '-' . $resena['i']))
        ->take(max(1, count($lista) - $producto->id % 4))
        ->map(function ($resena) use ($producto) {
            $resena['fecha'] = now()->subDays($resena['dias'] + $producto->id % 5);
            return $resena;
        })
        ->sortByDesc('fecha')
        ->values();

    $total = $resenas->count();
    $promedio = $total ? round($resenas->avg('nota'), 1) : 0;
    $porNota = collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($nota) => [$nota => $resenas->where('nota', $nota)->count()]);
    $porPagina = 4;
    $textosNota = [1 => 'Muy malo', 2 => 'Malo', 3 => 'Regular', 4 => 'Bueno', 5 => 'Excelente'];
@endphp

<section class="resenas" id="resenas" aria-labelledby="resenas-titulo" data-resenas data-por-pagina="{{ $porPagina }}">
    <h2 id="resenas-titulo">Reseñas de clientes</h2>

    @if($total)
        <div class="resenas-panel">
            <aside class="resenas-resumen" aria-label="Resumen de las calificaciones">
                <div class="resenas-promedio">
                    <strong>{{ number_format($promedio, 1, ',', '.') }}</strong>
                    <div>
                        <x-estrellas :nota="$promedio" class="estrellas--grande" />
                        <span>{{ $total }} {{ $total === 1 ? 'reseña' : 'reseñas' }}</span>
                    </div>
                </div>

                <div class="resenas-barras">
                    @foreach($porNota as $nota => $cuantas)
                        <button type="button" class="resenas-barra" data-filtro-nota="{{ $nota }}" aria-pressed="false" aria-label="Ver solo las de {{ $nota }} {{ $nota === 1 ? 'estrella' : 'estrellas' }} ({{ $cuantas }})" @disabled(! $cuantas)>
                            <span class="resenas-barra-nota">{{ $nota }}<x-icono nombre="estrella" /></span>
                            <span class="resenas-barra-pista"><span style="width:{{ round($cuantas * 100 / $total) }}%"></span></span>
                            <span class="resenas-barra-total">{{ $cuantas }}</span>
                        </button>
                    @endforeach
                </div>
            </aside>

            <div class="resenas-contenido">
                <div class="resenas-herramientas" data-resenas-herramientas>
                    <div class="resenas-estado">
                        <p aria-live="polite" data-resenas-estado>Mostrando {{ min($porPagina, $total) }} de {{ $total }} {{ $total === 1 ? 'reseña' : 'reseñas' }}</p>
                        <button type="button" class="resenas-quitar" data-quitar-filtro hidden><span class="sr-only">Quitar el filtro de </span><span data-filtro-texto></span><x-icono nombre="cerrar" /></button>
                    </div>
                    <div class="resenas-orden">
                        <label class="floating-label-activo-sm" for="resenas-orden">Ordenar por</label>
                        <select class="form-control form-control-sm" id="resenas-orden" data-resenas-orden>
                            <option value="recientes">Más recientes</option>
                            <option value="altas">Mejor calificadas</option>
                            <option value="bajas">Peor calificadas</option>
                        </select>
                    </div>
                </div>

                <div class="resenas-lista" data-resenas-lista>
                    @foreach($resenas as $i => $resena)
                        <article class="resena" data-resena data-nota="{{ $resena['nota'] }}" data-fecha="{{ $resena['fecha']->timestamp }}" tabindex="-1" @if($i >= $porPagina) hidden @endif>
                            <header class="resena-cabecera">
                                <h3>{{ $resena['titulo'] }}</h3>
                                <time class="resena-fecha" datetime="{{ $resena['fecha']->toDateString() }}" title="{{ $resena['fecha']->translatedFormat('j \d\e F \d\e Y') }}">{{ Str::ucfirst($resena['fecha']->diffForHumans()) }}</time>
                            </header>
                            <x-estrellas :nota="$resena['nota']" class="resena-estrellas" />
                            <p class="resena-texto">{{ $resena['texto'] }}</p>
                        </article>
                    @endforeach
                    <p class="resenas-vacio" data-resenas-vacio hidden>No hay reseñas con esa calificación.</p>
                </div>

                <button type="button" class="btn resenas-mas" data-resenas-mas @if($total <= $porPagina) hidden @endif>Ver más reseñas</button>
            </div>
        </div>
    @else
        <div class="resenas-panel resenas-panel--vacio">
            <x-estrellas :nota="0" class="estrellas--grande" />
            <h3>Aún no hay reseñas</h3>
            <p>Cuéntanos qué te pareció y ayuda a otros clientes a elegir.</p>
            <button type="button" class="btn resenas-boton" data-modal-abrir="modal-resena"><x-icono nombre="editar" class="isdi-izq" />Escribir una reseña</button>
        </div>
    @endif
</section>

<x-modal id="modal-resena" titulo="Escribir una reseña" ancho="chico" class="modal-resena">
    <form id="form-resena" class="resena-form" data-validar data-form-resena>
        <div class="resena-form-producto">
            <span class="resena-form-foto">
                @if($producto->foto_url)
                    <img src="{{ asset($producto->foto_url) }}" alt="">
                @else
                    <x-icono nombre="mascota" />
                @endif
            </span>
            <p><span>Tu opinión sobre</span>{{ $producto->nombre }}</p>
        </div>

        <div class="resena-form-nota">
            <span class="resena-form-rotulo" id="resena-nota-rotulo">Calificación</span>
            <div class="resena-elegir" role="radiogroup" aria-labelledby="resena-nota-rotulo" data-elegir-nota>
                @foreach($textosNota as $nota => $textoNota)
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
            <label class="floating-label-activo-sm" for="resena-titulo">Título</label>
            <input class="form-control form-control-sm" id="resena-titulo" name="titulo" maxlength="80" placeholder="Ej: Lo come sin dejar nada" required>
        </div>

        <div>
            <label class="floating-label-activo-sm" for="resena-texto">Tu reseña</label>
            <textarea class="form-control form-control-sm" id="resena-texto" name="texto" rows="5" minlength="20" maxlength="500" placeholder="¿Cómo le fue a tu mascota? ¿Lo volverías a comprar?" required data-contar></textarea>
            <span class="resena-form-contador" data-contador aria-hidden="true">0 / 500</span>
        </div>

        @guest
            <div>
                <label class="floating-label-activo-sm" for="resena-email">Email</label>
                <input class="form-control form-control-sm" type="email" id="resena-email" name="email" autocomplete="email" required>
                <small class="field-help">No lo publicaremos.</small>
            </div>
            <p class="resena-form-cuenta">¿Ya tienes cuenta? <a href="#login" data-modal-abrir="modal-iniciar-sesion">Inicia sesión</a> y completamos tus datos.</p>
        @endguest
    </form>

    <x-slot:pie>
        <button type="button" class="btn btn-cancelar" data-modal-cerrar><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</button>
        <button type="submit" class="btn btn-success" form="form-resena"><x-icono nombre="activar" class="isdi-izq" />Publicar reseña</button>
    </x-slot:pie>
</x-modal>

<script src="{{ asset('js/tienda-resenas.js') }}?v={{ @filemtime(public_path('js/tienda-resenas.js')) }}" defer></script>
