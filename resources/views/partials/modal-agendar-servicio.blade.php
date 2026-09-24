{{-- Agendar un servicio ya comprado. Lo abre el botón "Agendar hora" de Mis compras (panel del cliente).
     Misma estructura que la cita veterinaria, sin pago: css/agendar-cita.css · js/agendar-cita.js --}}
@php
    // Especies, razas y lugares de ejemplo compartidos con la cita veterinaria (config/agenda_ejemplo.php)
    $especiesServicio = config('agenda_ejemplo.especies');
    $pasosServicio = ['Mascota', 'Lugar', 'Agendar hora'];

    $datosServicio = [
        'especies' => collect($especiesServicio)->map(fn ($e) => ['nombre' => $e['nombre'], 'razas' => $e['razas']])->all(),
        'lugares' => config('agenda_ejemplo.lugares'),
    ];

    $clienteAgenda = auth()->user();
    $partesCliente = $clienteAgenda ? preg_split('/\s+/', trim((string) $clienteAgenda->name), 2) : [];
    $nombreCliente = trim(($clienteAgenda?->nombres ?: ($partesCliente[0] ?? '')) . ' ' . ($clienteAgenda?->apellidos ?: ($partesCliente[1] ?? '')));
    $mascotasCliente = $clienteAgenda ? $clienteAgenda->mascotas()->orderBy('nombre')->get(['id', 'nombre', 'especie', 'raza']) : collect();

    $iconoMascotaServicio = function ($especie) {
        $texto = Str::lower(Str::ascii(trim((string) $especie)));
        return match (true) {
            in_array($texto, ['perro', 'perra', 'canino', 'canina', 'can'], true) => ['perro', 'Perro'],
            in_array($texto, ['gato', 'gata', 'felino', 'felina'], true) => ['gato', 'Gato'],
            default => ['mascota', $especie ? Str::ucfirst($especie) : 'Mascota'],
        };
    };

    $checkServicio = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
@endphp

<x-modal id="modal-agendar-servicio" titulo="Agendar tu servicio" class="modal-cita">
    <x-slot:barra>
        <ol class="cita-pasos" aria-label="Pasos para agendar">
            @foreach($pasosServicio as $i => $nombrePaso)
                <li @class(['is-actual' => $i === 0]) data-cita-marca data-nombre="{{ $nombrePaso }}">
                    <button type="button" class="cita-limpio cita-marca" data-cita-ir="{{ $i }}" @if($i === 0) aria-current="step" @endif disabled>
                        <span class="cita-marca-numero" aria-hidden="true"><span>{{ $i + 1 }}</span>{!! $checkServicio !!}</span>
                        <span class="cita-marca-nombre"><span class="sr-only">Paso {{ $i + 1 }}: </span>{{ $nombrePaso }}</span>
                    </button>
                </li>
            @endforeach
        </ol>
        <p class="cita-pasos-movil" data-cita-paso-movil aria-hidden="true">Paso 1 de {{ count($pasosServicio) }} · {{ $pasosServicio[0] }}</p>
    </x-slot:barra>

    <form id="form-agendar-servicio" class="cita" method="POST" action="#" novalidate data-validar data-cita-form
          data-iconos="{{ asset('iconos-sdi') }}"
          data-url-regiones="{{ route('tienda.regiones') }}"
          data-url-comunas="{{ route('tienda.ciudades', ['region' => '__REGION__']) }}"
          data-cita-tutor="{{ $nombreCliente }}"
          data-cita-email="{{ $clienteAgenda?->email }}">
        @csrf
        {{-- Los completa el botón de la compra --}}
        <input type="hidden" name="compra">
        <input type="hidden" name="servicio_nombre">
        <input type="hidden" name="servicio_minutos">
        <input type="hidden" name="servicio_fecha">

        {{-- 1. Mascota --}}
        <section class="cita-paso" data-cita-paso="tutor" aria-labelledby="servicio-titulo-mascota">
            <header class="cita-paso-cabecera">
                <h3 id="servicio-titulo-mascota" tabindex="-1">¿Para quién es el servicio?</h3>
                <p>Elige la mascota que recibirá la atención.</p>
            </header>

            <div class="cita-servicio" data-cita-servicio-info aria-live="polite" hidden></div>

            <div class="cita-bloque">
                @if($mascotasCliente->isNotEmpty())
                    <div class="cita-mascotas-caja">
                        <div class="cita-mascotas" role="radiogroup" aria-label="Elige la mascota">
                            @foreach($mascotasCliente as $mascotaServicio)
                                @php
                                    [$iconoEspecieServicio, $nombreEspecieServicio] = $iconoMascotaServicio($mascotaServicio->especie);
                                    $detalleMascotaServicio = $nombreEspecieServicio . ($mascotaServicio->raza ? ' · ' . $mascotaServicio->raza : '');
                                @endphp
                                <label class="cita-mascota">
                                    <input type="radio" name="mascota_id" value="{{ $mascotaServicio->id }}" data-nombre="{{ $mascotaServicio->nombre }}" data-detalle="{{ $detalleMascotaServicio }}" required data-msg="Elige para quién es el servicio." @checked($mascotasCliente->count() === 1)>
                                    <span class="cita-mascota-caja">
                                        <span class="cita-mascota-icono"><x-icono :nombre="$iconoEspecieServicio" /></span>
                                        <span class="cita-mascota-texto"><strong>{{ $mascotaServicio->nombre }}</strong><small>{{ $detalleMascotaServicio }}</small></span>
                                    </span>
                                </label>
                            @endforeach
                            <label class="cita-mascota cita-mascota--otra">
                                <input type="radio" name="mascota_id" value="nueva" required data-msg="Elige para quién es el servicio." data-cita-mascota-nueva>
                                <span class="cita-mascota-caja">
                                    <span class="cita-mascota-icono"><x-icono nombre="plus" /></span>
                                    <span class="cita-mascota-texto"><strong>Otra mascota</strong><small>Ingresa sus datos</small></span>
                                </span>
                            </label>
                        </div>
                    </div>
                @endif

                <div class="cita-campos cita-campos--mascota" data-cita-mascota-campos @if($mascotasCliente->isNotEmpty()) hidden @endif>
                    <div>
                        <label class="floating-label-activo-sm" for="servicio-mascota-nombre">Nombre</label>
                        <input class="form-control form-control-sm" id="servicio-mascota-nombre" name="mascota_nombre" maxlength="80" placeholder="Ej: Max, Luna, Pelusa" autocomplete="off" required data-msg="Escribe el nombre de tu mascota." data-cita-mascota-nombre>
                    </div>
                    <div class="cita-segmento">
                        <span class="floating-label-activo-sm" id="servicio-especie-titulo">Tipo de mascota</span>
                        <div class="cita-opciones" role="radiogroup" aria-labelledby="servicio-especie-titulo">
                            @foreach($especiesServicio as $valorEspecieServicio => $especieServicio)
                                <label class="cita-opcion">
                                    <input type="radio" name="mascota_especie" value="{{ $valorEspecieServicio }}" data-nombre="{{ $especieServicio['nombre'] }}" required data-msg="Elige el tipo de mascota.">
                                    <span><x-icono :nombre="$especieServicio['icono']" />{{ $especieServicio['nombre'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="cita-tercio">
                        <label class="floating-label-activo-sm" for="servicio-raza" data-cita-raza-titulo>Raza</label>
                        <select class="form-control form-control-sm" id="servicio-raza" name="mascota_raza" disabled data-cita-raza>
                            <option value="">Primero elige el tipo de mascota</option>
                        </select>
                    </div>
                    {{-- Con "Otro" / "Otra raza" se escribe cuál es --}}
                    <div class="cita-tercio cita-otra" data-cita-otra-caja hidden>
                        <label class="floating-label-activo-sm" for="servicio-raza-otra" data-cita-otra-titulo>¿Qué raza es?</label>
                        <input class="form-control form-control-sm" id="servicio-raza-otra" name="mascota_raza_otra" maxlength="80" autocomplete="off" required data-cita-otra-campo>
                    </div>
                    <div class="cita-segmento cita-tercio">
                        <span class="floating-label-activo-sm" id="servicio-sexo-titulo">Sexo</span>
                        <div class="cita-opciones" role="radiogroup" aria-labelledby="servicio-sexo-titulo">
                            <label class="cita-opcion"><input type="radio" name="mascota_sexo" value="macho" required data-msg="Elige el sexo."><span>Macho</span></label>
                            <label class="cita-opcion"><input type="radio" name="mascota_sexo" value="hembra" required data-msg="Elige el sexo."><span>Hembra</span></label>
                        </div>
                    </div>
                    <div class="cita-segmento cita-tercio">
                        <span class="floating-label-activo-sm" id="servicio-esterilizado-titulo">Esterilizado/a</span>
                        <div class="cita-opciones" role="radiogroup" aria-labelledby="servicio-esterilizado-titulo">
                            <label class="cita-opcion"><input type="radio" name="mascota_esterilizado" value="si" required data-msg="Cuéntanos si está esterilizado/a."><span>Sí</span></label>
                            <label class="cita-opcion"><input type="radio" name="mascota_esterilizado" value="no" required data-msg="Cuéntanos si está esterilizado/a."><span>No</span></label>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. Lugar --}}
        <section class="cita-paso" data-cita-paso="lugar" data-cita-boton="Buscar horas" data-cita-boton-icono="lupa" aria-labelledby="servicio-titulo-lugar" hidden>
            <header class="cita-paso-cabecera">
                <h3 id="servicio-titulo-lugar" tabindex="-1">¿Dónde quieres el servicio?</h3>
                <p>Te mostraremos los centros que lo realizan en tu comuna.</p>
            </header>
            <div class="cita-campos">
                <div>
                    <label class="floating-label-activo-sm" for="servicio-region">Región</label>
                    <select class="form-control form-control-sm" id="servicio-region" name="region_id" required data-msg="Elige la región." data-cita-region>
                        <option value="">Cargando regiones…</option>
                    </select>
                </div>
                <div>
                    <label class="floating-label-activo-sm" for="servicio-comuna">Comuna</label>
                    <select class="form-control form-control-sm" id="servicio-comuna" name="comuna_id" required disabled data-msg="Elige la comuna." data-cita-comuna>
                        <option value="">Primero elige la región</option>
                    </select>
                </div>
            </div>
        </section>

        {{-- 3. Lugar y hora --}}
        <section class="cita-paso" data-cita-paso="hora" data-cita-boton="Confirmar hora" data-cita-boton-icono="activar" aria-labelledby="servicio-titulo-hora" hidden>
            <div class="cita-vista" data-cita-vista="detalle">
                <header class="cita-paso-cabecera">
                    <h3 id="servicio-titulo-hora" tabindex="-1">Elige el lugar y la hora</h3>
                    <p>Estas son las horas disponibles para tu servicio.</p>
                </header>
                <div class="cita-detalle">
                    <div class="cita-detalle-lugares">
                        <p class="cita-columna-titulo"><x-icono nombre="locacion" />Elige el lugar</p>
                        <div class="cita-lugares" data-cita-lugares></div>
                    </div>
                    <div class="cita-agenda" data-cita-agenda></div>
                </div>
            </div>

            <input type="hidden" name="lugar_id" data-cita-dato="lugar">
            <input type="hidden" name="fecha" data-cita-dato="fecha">
            <input type="hidden" name="hora" data-cita-dato="hora">
        </section>

        {{-- Hora lista --}}
        <section class="cita-listo" data-cita-listo tabindex="-1" aria-labelledby="servicio-titulo-listo" hidden>
            <span class="cita-listo-icono" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
            <h3 id="servicio-titulo-listo">Tu hora quedó agendada</h3>
            <p class="cita-listo-texto" data-cita-listo-texto></p>
            <div class="cita-ticket" data-cita-ticket></div>
        </section>
    </form>

    <script type="application/json" data-cita-datos>@json($datosServicio)</script>

    <x-slot:pie>
        <p class="cita-pie-info" data-cita-info aria-live="polite"></p>
        <button type="button" class="btn btn-cancelar" data-cita-atras hidden><x-icono nombre="volver" class="isdi-izq" />Atrás</button>
        <button type="submit" class="btn btn-success" form="form-agendar-servicio" data-cita-seguir>Continuar<x-icono nombre="siguiente" class="isdi-der" /></button>
        <button type="button" class="btn btn-success" data-modal-cerrar data-cita-cerrar hidden>Listo</button>
    </x-slot:pie>
</x-modal>
