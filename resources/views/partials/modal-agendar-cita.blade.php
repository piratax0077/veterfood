{{-- Agendar cita veterinaria: lo abre el botón flotante de la tienda (data-modal-abrir="modal-agendar-cita").
     Estilos: css/agendar-cita.css · Pasos, búsqueda y horas: js/agendar-cita.js --}}
@php
    // Datos de ejemplo hasta conectar la agenda oficial. Cada servicio: [nombre, valor, minutos]
    $tiposAtencion = [
        'consulta-general' => ['Consulta general', [
            ['Consulta general', 20000, 30],
            ['Control sano', 18000, 30],
            ['Control geriátrico', 22000, 30],
            ['Control post operatorio', 15000, 20],
            ['Segunda opinión', 25000, 40],
        ]],
        'consulta-especialidad' => ['Consulta especialidad', [
            ['Dermatología', 35000, 45],
            ['Cardiología', 38000, 45],
            ['Oftalmología', 35000, 40],
            ['Traumatología', 38000, 45],
            ['Oncología', 40000, 45],
            ['Neurología', 40000, 45],
            ['Nutrición', 30000, 40],
            ['Comportamiento (etología)', 35000, 60],
            ['Odontología', 32000, 40],
            ['Medicina felina', 30000, 40],
            ['Animales exóticos', 30000, 40],
        ]],
        'urgencia' => ['Urgencia', [
            ['Urgencia general', 45000, 60],
            ['Vómitos o diarrea', 40000, 45],
            ['Herida o accidente', 45000, 60],
            ['Posible intoxicación', 45000, 60],
            ['Dificultad para respirar', 45000, 60],
        ]],
        'procedimiento-examen' => ['Procedimiento / examen', [
            ['Esterilización (hembra)', 110000, 120],
            ['Castración (macho)', 80000, 90],
            ['Limpieza dental (destartraje)', 60000, 90],
            ['Curación de heridas', 15000, 30],
            ['Implante de microchip', 15000, 15],
            ['Ecografía abdominal', 35000, 40],
            ['Radiografía', 30000, 30],
            ['Electrocardiograma', 28000, 30],
        ]],
        'examenes' => ['Exámenes', [
            ['Hemograma', 18000, 15],
            ['Perfil bioquímico', 28000, 15],
            ['Hemograma y perfil bioquímico', 40000, 20],
            ['Examen de orina', 15000, 15],
            ['Examen de deposiciones', 15000, 15],
            ['Test VIF y ViLeF (gatos)', 25000, 20],
            ['Test de parvovirus', 22000, 20],
            ['Perfil tiroideo', 30000, 15],
        ]],
        'vacunacion' => ['Vacunación', [
            ['Vacuna óctuple (perros)', 18000, 20],
            ['Vacuna antirrábica', 12000, 15],
            ['Vacuna KC (tos de las perreras)', 20000, 15],
            ['Vacuna triple felina', 18000, 20],
            ['Vacuna leucemia felina', 22000, 15],
        ]],
        'desparasitacion' => ['Desparasitación', [
            ['Desparasitación interna', 10000, 15],
            ['Desparasitación externa', 12000, 15],
            ['Desparasitación completa', 18000, 20],
        ]],
    ];

    // Especies, razas y lugares de ejemplo: los comparte con la ventana de servicios (config/agenda_ejemplo.php)
    $especiesCita = config('agenda_ejemplo.especies');
    $lugaresCita = config('agenda_ejemplo.lugares');

    $profesionalesCita = [
        ['id' => 1, 'nombre' => 'Dra. Camila Fuentes', 'especialidad' => 'Medicina general y felina', 'tono' => '#03715b', 'fondo' => '#e7f5f0', 'lugares' => [1, 2, 3]],
        ['id' => 2, 'nombre' => 'Dr. Matías Rojas', 'especialidad' => 'Medicina interna', 'tono' => '#b45309', 'fondo' => '#fff4e5', 'lugares' => [1, 4]],
        ['id' => 3, 'nombre' => 'Dra. Valentina Soto', 'especialidad' => 'Cirugía y traumatología', 'tono' => '#0e7490', 'fondo' => '#e6f6f8', 'lugares' => [2]],
        ['id' => 4, 'nombre' => 'Dr. Tomás Herrera', 'especialidad' => 'Dermatología y alergias', 'tono' => '#12313b', 'fondo' => '#eef2f6', 'lugares' => [3, 4]],
    ];

    $mediosPago = [
        'debito' => ['Tarjeta de débito', 'Paga al instante con tu tarjeta de débito o prepago.', 'tarjeta'],
        'credito' => ['Tarjeta de crédito', 'Paga con tu tarjeta de crédito, en cuotas si tu banco lo permite.', 'tarjeta'],
        'transferencia' => ['Transferencia bancaria', 'Te enviamos los datos bancarios a tu email.', 'banco'],
    ];

    $pasosCita = ['Tipo de atención', 'Tutor/Mascota', 'Lugar', 'Agendar hora', 'Pago'];

    // Lo que usa el script: servicios por tipo y razas por especie
    $datosCita = [
        'tipos' => collect($tiposAtencion)->map(fn ($tipo, $clave) => [
            'nombre' => $tipo[0],
            'servicios' => collect($tipo[1])->map(fn ($s) => [
                'id' => $clave . ':' . Str::slug($s[0]),
                'nombre' => $s[0],
                'precio' => $s[1],
                'minutos' => $s[2],
            ])->values()->all(),
        ])->all(),
        'especies' => collect($especiesCita)->map(fn ($e) => ['nombre' => $e['nombre'], 'razas' => $e['razas']])->all(),
        'profesionales' => $profesionalesCita,
        'lugares' => $lugaresCita,
    ];

    // Con sesión iniciada se completan los datos del tutor y se ofrecen sus mascotas
    $tutor = auth()->user();
    $partesNombre = $tutor ? preg_split('/\s+/', trim((string) $tutor->name), 2) : [];
    $tutorNombre = $tutor ? ($tutor->nombres ?: ($partesNombre[0] ?? '')) : '';
    $tutorApellido = $tutor ? ($tutor->apellidos ?: ($partesNombre[1] ?? '')) : '';
    $tutorRut = $tutor?->perfilCliente?->rut ? \App\Rules\RutChileno::formatear($tutor->perfilCliente->rut) : '';
    $mascotasTutor = $tutor ? $tutor->mascotas()->orderBy('nombre')->get(['id', 'nombre', 'especie', 'raza']) : collect();

    $iconoMascota = function ($especie) {
        $texto = Str::lower(Str::ascii(trim((string) $especie)));
        return match (true) {
            in_array($texto, ['perro', 'perra', 'canino', 'canina', 'can'], true) => ['perro', 'Perro'],
            in_array($texto, ['gato', 'gata', 'felino', 'felina'], true) => ['gato', 'Gato'],
            default => ['mascota', $especie ? Str::ucfirst($especie) : 'Mascota'],
        };
    };

    $check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
@endphp

<x-modal id="modal-agendar-cita" titulo="Agendar cita veterinaria" class="modal-cita">
    <x-slot:barra>
        <ol class="cita-pasos" aria-label="Pasos para agendar">
            @foreach($pasosCita as $i => $nombrePaso)
                <li @class(['is-actual' => $i === 0]) data-cita-marca data-nombre="{{ $nombrePaso }}">
                    <button type="button" class="cita-limpio cita-marca" data-cita-ir="{{ $i }}" @if($i === 0) aria-current="step" @endif disabled>
                        <span class="cita-marca-numero" aria-hidden="true"><span>{{ $i + 1 }}</span>{!! $check !!}</span>
                        <span class="cita-marca-nombre"><span class="sr-only">Paso {{ $i + 1 }}: </span>{{ $nombrePaso }}</span>
                    </button>
                </li>
            @endforeach
        </ol>
        <p class="cita-pasos-movil" data-cita-paso-movil aria-hidden="true">Paso 1 de {{ count($pasosCita) }} · {{ $pasosCita[0] }}</p>
    </x-slot:barra>

    <form id="form-agendar-cita" class="cita" method="POST" action="#" novalidate data-validar data-cita-form
          data-iconos="{{ asset('iconos-sdi') }}"
          data-url-regiones="{{ route('tienda.regiones') }}"
          data-url-comunas="{{ route('tienda.ciudades', ['region' => '__REGION__']) }}">
        @csrf

        {{-- 1. Tipo de atención --}}
        <section class="cita-paso" data-cita-paso="atencion" aria-labelledby="cita-titulo-atencion">
            <header class="cita-paso-cabecera">
                <h3 id="cita-titulo-atencion" tabindex="-1">¿Qué tipo de atención necesitas?</h3>
                <p>Elige el tipo de atención y el servicio para mostrarte las horas disponibles.</p>
            </header>
            <div class="cita-campos">
                <div>
                    <label class="floating-label-activo-sm" for="cita-tipo">Tipo de atención</label>
                    <select class="form-control form-control-sm" id="cita-tipo" name="tipo_atencion" required data-msg="Elige el tipo de atención." data-cita-tipo>
                        <option value="">Selecciona una opción</option>
                        @foreach($datosCita['tipos'] as $claveTipo => $tipo)
                            <option value="{{ $claveTipo }}">{{ $tipo['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div data-cita-servicio-campo>
                    <label class="floating-label-activo-sm" for="cita-servicio">Servicio</label>
                    <select class="form-control form-control-sm" id="cita-servicio" name="servicio" required disabled data-msg="Elige el servicio." data-cita-servicio>
                        <option value="">Primero elige el tipo de atención</option>
                    </select>
                </div>
                <div data-cita-motivo-campo hidden>
                    <label class="floating-label-activo-sm" for="cita-motivo">Motivo de urgencia</label>
                    <input class="form-control form-control-sm" id="cita-motivo" name="motivo_urgencia" maxlength="150" placeholder="Ej: se cortó una pata y no para de sangrar" autocomplete="off" data-msg="Cuéntanos el motivo de la urgencia." data-cita-motivo>
                </div>
            </div>
            <p class="cita-nota cita-nota--alerta" data-cita-urgencia hidden>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 4.2L2.8 17.5A2 2 0 0 0 4.5 20.5h15a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0z"/><path d="M12 9.5v4.5"/><path d="M12 17.3v.1"/></svg>
                <span><strong>¿Es una emergencia grave?</strong> No esperes la hora: lleva a tu mascota de inmediato a la urgencia veterinaria más cercana.</span>
            </p>
            <div class="cita-servicio" data-cita-servicio-info aria-live="polite" hidden></div>
        </section>

        {{-- 2. Tutor y mascota --}}
        <section class="cita-paso" data-cita-paso="tutor" aria-labelledby="cita-titulo-tutor" hidden>
            <header class="cita-paso-cabecera">
                <h3 id="cita-titulo-tutor" tabindex="-1">¿Quién agenda y para quién es la cita?</h3>
            </header>

            <div class="cita-bloque">
                <p class="cita-etiqueta"><x-icono nombre="usuario" />Datos del tutor</p>
                <div class="cita-campos">
                    <div class="cita-tercio">
                        <label class="floating-label-activo-sm" for="cita-rut">RUT</label>
                        <input class="form-control form-control-sm" id="cita-rut" name="tutor_rut" value="{{ $tutorRut }}" placeholder="12.345.678-9" maxlength="12" autocomplete="off" data-rut required>
                    </div>
                    <div class="cita-tercio cita-mitad-movil">
                        <label class="floating-label-activo-sm" for="cita-nombre">Nombre</label>
                        <input class="form-control form-control-sm" id="cita-nombre" name="tutor_nombre" value="{{ $tutorNombre }}" autocomplete="given-name" maxlength="120" required data-cita-tutor-nombre>
                    </div>
                    <div class="cita-tercio cita-mitad-movil">
                        <label class="floating-label-activo-sm" for="cita-apellido">Apellido</label>
                        <input class="form-control form-control-sm" id="cita-apellido" name="tutor_apellido" value="{{ $tutorApellido }}" autocomplete="family-name" maxlength="120" required data-cita-tutor-apellido>
                    </div>
                    <div>
                        <label class="floating-label-activo-sm" for="cita-email">Email</label>
                        <input class="form-control form-control-sm" type="email" id="cita-email" name="tutor_email" value="{{ $tutor?->email }}" autocomplete="email" placeholder="nombre@correo.cl" required data-cita-tutor-email>
                    </div>
                    <div>
                        <x-campo-telefono name="tutor_telefono" :value="$tutor?->telefono" label="Teléfono" id="cita-telefono" required />
                    </div>
                </div>
            </div>

            <div class="cita-bloque">
                <p class="cita-etiqueta"><x-icono nombre="mascota" />¿Para quién es la cita?</p>
                @if($mascotasTutor->isNotEmpty())
                    <div class="cita-mascotas-caja">
                        <div class="cita-mascotas" role="radiogroup" aria-label="Elige la mascota">
                            @foreach($mascotasTutor as $mascota)
                                @php
                                    [$iconoEspecie, $nombreEspecie] = $iconoMascota($mascota->especie);
                                    $detalleMascota = $nombreEspecie . ($mascota->raza ? ' · ' . $mascota->raza : '');
                                @endphp
                                <label class="cita-mascota">
                                    <input type="radio" name="mascota_id" value="{{ $mascota->id }}" data-nombre="{{ $mascota->nombre }}" data-detalle="{{ $detalleMascota }}" required data-msg="Elige para quién es la cita." @checked($mascotasTutor->count() === 1)>
                                    <span class="cita-mascota-caja">
                                        <span class="cita-mascota-icono"><x-icono :nombre="$iconoEspecie" /></span>
                                        <span class="cita-mascota-texto"><strong>{{ $mascota->nombre }}</strong><small>{{ $detalleMascota }}</small></span>
                                    </span>
                                </label>
                            @endforeach
                            <label class="cita-mascota cita-mascota--otra">
                                <input type="radio" name="mascota_id" value="nueva" required data-msg="Elige para quién es la cita." data-cita-mascota-nueva>
                                <span class="cita-mascota-caja">
                                    <span class="cita-mascota-icono"><x-icono nombre="plus" /></span>
                                    <span class="cita-mascota-texto"><strong>Otra mascota</strong><small>Ingresa sus datos</small></span>
                                </span>
                            </label>
                        </div>
                    </div>
                @endif

                <div class="cita-campos cita-campos--mascota" data-cita-mascota-campos @if($mascotasTutor->isNotEmpty()) hidden @endif>
                    <div>
                        <label class="floating-label-activo-sm" for="cita-mascota-nombre">Nombre</label>
                        <input class="form-control form-control-sm" id="cita-mascota-nombre" name="mascota_nombre" maxlength="80" placeholder="Ej: Max, Luna, Pelusa" autocomplete="off" required data-msg="Escribe el nombre de tu mascota." data-cita-mascota-nombre>
                    </div>
                    <div class="cita-segmento">
                        <span class="floating-label-activo-sm" id="cita-especie-titulo">Tipo de mascota</span>
                        <div class="cita-opciones" role="radiogroup" aria-labelledby="cita-especie-titulo">
                            @foreach($especiesCita as $valorEspecie => $especieCita)
                                <label class="cita-opcion">
                                    <input type="radio" name="mascota_especie" value="{{ $valorEspecie }}" data-nombre="{{ $especieCita['nombre'] }}" required data-msg="Elige el tipo de mascota.">
                                    <span><x-icono :nombre="$especieCita['icono']" />{{ $especieCita['nombre'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="cita-tercio">
                        <label class="floating-label-activo-sm" for="cita-raza" data-cita-raza-titulo>Raza</label>
                        <select class="form-control form-control-sm" id="cita-raza" name="mascota_raza" disabled data-cita-raza>
                            <option value="">Primero elige el tipo de mascota</option>
                        </select>
                    </div>
                    {{-- Con "Otro" / "Otra raza" se escribe cuál es --}}
                    <div class="cita-tercio cita-otra" data-cita-otra-caja hidden>
                        <label class="floating-label-activo-sm" for="cita-raza-otra" data-cita-otra-titulo>¿Qué raza es?</label>
                        <input class="form-control form-control-sm" id="cita-raza-otra" name="mascota_raza_otra" maxlength="80" autocomplete="off" required data-cita-otra-campo>
                    </div>
                    <div class="cita-segmento cita-tercio">
                        <span class="floating-label-activo-sm" id="cita-sexo-titulo">Sexo</span>
                        <div class="cita-opciones" role="radiogroup" aria-labelledby="cita-sexo-titulo">
                            <label class="cita-opcion"><input type="radio" name="mascota_sexo" value="macho" required data-msg="Elige el sexo."><span>Macho</span></label>
                            <label class="cita-opcion"><input type="radio" name="mascota_sexo" value="hembra" required data-msg="Elige el sexo."><span>Hembra</span></label>
                        </div>
                    </div>
                    <div class="cita-segmento cita-tercio">
                        <span class="floating-label-activo-sm" id="cita-esterilizado-titulo">Esterilizado/a</span>
                        <div class="cita-opciones" role="radiogroup" aria-labelledby="cita-esterilizado-titulo">
                            <label class="cita-opcion"><input type="radio" name="mascota_esterilizado" value="si" required data-msg="Cuéntanos si está esterilizado/a."><span>Sí</span></label>
                            <label class="cita-opcion"><input type="radio" name="mascota_esterilizado" value="no" required data-msg="Cuéntanos si está esterilizado/a."><span>No</span></label>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. Lugar --}}
        <section class="cita-paso" data-cita-paso="lugar" data-cita-boton="Buscar horas" data-cita-boton-icono="lupa" aria-labelledby="cita-titulo-lugar" hidden>
            <header class="cita-paso-cabecera">
                <h3 id="cita-titulo-lugar" tabindex="-1">¿Dónde prefieres la atención?</h3>
                <p>Te mostraremos los profesionales y centros que atienden en tu comuna.</p>
            </header>
            <div class="cita-campos">
                <div>
                    <label class="floating-label-activo-sm" for="cita-region">Región</label>
                    <select class="form-control form-control-sm" id="cita-region" name="region_id" required data-msg="Elige la región." data-cita-region>
                        <option value="">Cargando regiones…</option>
                    </select>
                </div>
                <div>
                    <label class="floating-label-activo-sm" for="cita-comuna">Comuna</label>
                    <select class="form-control form-control-sm" id="cita-comuna" name="comuna_id" required disabled data-msg="Elige la comuna." data-cita-comuna>
                        <option value="">Primero elige la región</option>
                    </select>
                </div>
            </div>
            <p class="cita-nota" data-cita-ubicacion-nota hidden><x-icono nombre="locacion" /><span>Usamos la ubicación que elegiste en la tienda. Puedes cambiarla aquí.</span></p>
        </section>

        {{-- 4. Agendar hora: resultados y luego lugar + calendario del profesional elegido --}}
        <section class="cita-paso" data-cita-paso="hora" aria-labelledby="cita-titulo-resultados" hidden>
            <div class="cita-vista" data-cita-vista="resultados">
                <header class="cita-paso-cabecera cita-resultados-cabecera">
                    <div>
                        <h3 id="cita-titulo-resultados" tabindex="-1">Resultados de búsqueda</h3>
                        <p data-cita-conteo aria-live="polite">Buscando horas disponibles…</p>
                    </div>
                    <div class="cita-filtros" data-cita-filtros></div>
                </header>
                <div class="cita-profesionales" data-cita-profesionales></div>
            </div>

            <div class="cita-vista" data-cita-vista="detalle" hidden>
                <button type="button" class="cita-limpio cita-volver" data-cita-volver><x-icono nombre="volver" />Volver a resultados</button>
                <header class="cita-detalle-pro" data-cita-detalle-pro></header>
                <div class="cita-detalle">
                    <div class="cita-detalle-lugares">
                        <p class="cita-columna-titulo"><x-icono nombre="locacion" />Elige el lugar</p>
                        <div class="cita-lugares" data-cita-lugares></div>
                    </div>
                    <div class="cita-agenda" data-cita-agenda></div>
                </div>
            </div>

            <input type="hidden" name="profesional_id" data-cita-dato="profesional">
            <input type="hidden" name="lugar_id" data-cita-dato="lugar">
            <input type="hidden" name="fecha" data-cita-dato="fecha">
            <input type="hidden" name="hora" data-cita-dato="hora">
        </section>

        {{-- 5. Pago --}}
        <section class="cita-paso" data-cita-paso="pago" data-cita-boton="Ir al pago" data-cita-boton-icono="candado" aria-labelledby="cita-titulo-pago" hidden>
            <header class="cita-paso-cabecera">
                <h3 id="cita-titulo-pago" tabindex="-1">Elige el medio de pago</h3>
                <p>Elige cómo quieres pagar y revisa los datos de tu cita.</p>
            </header>
            <div class="cita-pago">
                <div class="cita-pago-medios">
                    <div class="cita-medios-caja">
                        <div class="cita-medios" role="radiogroup" aria-labelledby="cita-titulo-pago">
                            @foreach($mediosPago as $valorPago => [$tituloPago, $detallePago, $iconoPago])
                                <label class="cita-medio">
                                    <input type="radio" name="medio_pago" value="{{ $valorPago }}" data-nombre="{{ $tituloPago }}" required data-msg="Elige cómo quieres pagar.">
                                    <span class="cita-medio-caja">
                                        <span class="cita-medio-icono"><x-icono :nombre="$iconoPago" /></span>
                                        <span class="cita-medio-texto"><strong>{{ $tituloPago }}</strong><small>{{ $detallePago }}</small></span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <p class="cita-seguro"><x-icono nombre="candado" />Pago seguro · Transacciones protegidas</p>
                </div>
                <aside class="cita-resumen" data-cita-resumen aria-label="Resumen de tu cita"></aside>
            </div>
        </section>

        {{-- Cita lista --}}
        <section class="cita-listo" data-cita-listo tabindex="-1" aria-labelledby="cita-titulo-listo" hidden>
            <span class="cita-listo-icono" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
            <h3 id="cita-titulo-listo">Tu hora quedó agendada</h3>
            <p class="cita-listo-texto" data-cita-listo-texto></p>
            <div class="cita-ticket" data-cita-ticket></div>
        </section>
    </form>

    <script type="application/json" data-cita-datos>@json($datosCita)</script>

    <x-slot:pie>
        <p class="cita-pie-info" data-cita-info aria-live="polite"></p>
        <button type="button" class="btn btn-cancelar" data-cita-atras hidden><x-icono nombre="volver" class="isdi-izq" />Atrás</button>
        <button type="submit" class="btn btn-success" form="form-agendar-cita" data-cita-seguir>Continuar<x-icono nombre="siguiente" class="isdi-der" /></button>
        <button type="button" class="btn btn-cancelar" data-cita-otra hidden><x-icono nombre="plus" class="isdi-izq" /><span class="cita-solo-escritorio">Agendar otra cita</span><span class="cita-solo-movil">Otra cita</span></button>
        <button type="button" class="btn btn-success" data-modal-cerrar data-cita-cerrar hidden>Listo</button>
    </x-slot:pie>
</x-modal>
