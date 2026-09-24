/*
 * Agendar hora, por pasos. Mueve dos ventanas con la misma estructura:
 *   - Cita veterinaria (partials/modal-agendar-cita): tipo de atención, tutor y mascota, lugar, hora y pago.
 *   - Servicio ya comprado (partials/modal-agendar-servicio): mascota, lugar y hora, sin pago.
 * Cada paso se marca con data-cita-paso="atencion|tutor|lugar|hora|pago" y el texto de su botón con data-cita-boton.
 * Profesionales, lugares y horas son de ejemplo: la búsqueda real se conecta en buscarProfesionales().
 */
(function () {
    'use strict';

    function lista(nodos) { return Array.prototype.slice.call(nodos); }

    function iniciar(form) {
    var modal = form.closest('dialog.modal');
    if (!modal) return;

    var datos = JSON.parse(modal.querySelector('[data-cita-datos]').textContent);
    var cuerpo = modal.querySelector('.modal-cuerpo');
    var pasos = lista(form.querySelectorAll('[data-cita-paso]'));
    var marcas = lista(modal.querySelectorAll('[data-cita-marca]'));
    var pasoMovil = modal.querySelector('[data-cita-paso-movil]');
    var info = modal.querySelector('[data-cita-info]');
    var botonAtras = modal.querySelector('[data-cita-atras]');
    var botonSeguir = modal.querySelector('[data-cita-seguir]');
    var botonOtra = modal.querySelector('[data-cita-otra]');
    var botonListo = modal.querySelector('[data-cita-cerrar]');
    var listo = form.querySelector('[data-cita-listo]');

    var campo = {
        tipo: form.querySelector('[data-cita-tipo]'),
        servicio: form.querySelector('[data-cita-servicio]'),
        nombre: form.querySelector('[data-cita-tutor-nombre]'),
        apellido: form.querySelector('[data-cita-tutor-apellido]'),
        email: form.querySelector('[data-cita-tutor-email]'),
        mascota: form.querySelector('[data-cita-mascota-nombre]'),
        raza: form.querySelector('[data-cita-raza]'),
        otra: form.querySelector('[data-cita-otra-campo]'),
        region: form.querySelector('[data-cita-region]'),
        comuna: form.querySelector('[data-cita-comuna]'),
        motivo: form.querySelector('[data-cita-motivo]')
    };
    var zona = {
        urgencia: form.querySelector('[data-cita-urgencia]'),
        servicio: form.querySelector('[data-cita-servicio-info]'),
        servicioCampo: form.querySelector('[data-cita-servicio-campo]'),
        motivoCampo: form.querySelector('[data-cita-motivo-campo]'),
        razaTitulo: form.querySelector('[data-cita-raza-titulo]'),
        otra: form.querySelector('[data-cita-otra-caja]'),
        otraTitulo: form.querySelector('[data-cita-otra-titulo]'),
        camposMascota: form.querySelector('[data-cita-mascota-campos]'),
        mascotaNueva: form.querySelector('[data-cita-mascota-nueva]'),
        notaUbicacion: form.querySelector('[data-cita-ubicacion-nota]'),
        conteo: form.querySelector('[data-cita-conteo]'),
        filtros: form.querySelector('[data-cita-filtros]'),
        profesionales: form.querySelector('[data-cita-profesionales]'),
        resultados: form.querySelector('[data-cita-vista="resultados"]'),
        detalle: form.querySelector('[data-cita-vista="detalle"]'),
        detallePro: form.querySelector('[data-cita-detalle-pro]'),
        lugares: form.querySelector('[data-cita-lugares]'),
        agenda: form.querySelector('[data-cita-agenda]'),
        resumen: form.querySelector('[data-cita-resumen]'),
        listoTexto: form.querySelector('[data-cita-listo-texto]'),
        ticket: form.querySelector('[data-cita-ticket]')
    };

    var DIAS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    var DIAS_CORTOS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    var MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    var MESES_CORTOS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    var SEMANAS_A_LA_VISTA = 8;
    var RELOJ = '<svg class="cita-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';
    var FLECHA_ABAJO = '<svg class="cita-svg cita-flechita" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>';
    var FLECHA_IZQ = '<svg class="cita-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>';
    var FLECHA_DER = '<svg class="cita-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>';

    // Cada ventana arma sus propios pasos; el motor los ubica por su clave
    var claves = pasos.map(function (paso) { return paso.dataset.citaPaso; });
    function indicePaso(clave) { return claves.indexOf(clave); }
    var iHora = indicePaso('hora');
    var iPago = indicePaso('pago');

    var actual = 0;
    var regionesPedidas = false;
    var estado = estadoNuevo();

    function estadoNuevo() {
        return {
            busqueda: '',      // servicio|comuna de la última búsqueda
            pedido: null,
            resultados: [],
            pro: null,         // profesional abierto en el detalle
            lugar: null,       // lugar elegido en el detalle
            lunes: null,       // semana a la vista
            dia: null,         // día a la vista (aaaa-mm-dd)
            cita: null,        // hora elegida: {pro, lugar, fecha, hora}
            enviando: false,
            terminado: false
        };
    }

    /* ---------- Utilidades ---------- */

    function esc(texto) {
        return String(texto == null ? '' : texto).replace(/[&<>"']/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
        });
    }

    function icono(nombre, clase) {
        return '<span class="isdi' + (clase ? ' ' + clase : '') + '" style="--isdi-src:url(\'' + form.dataset.iconos + '/' + nombre + '.svg\')" aria-hidden="true"></span>';
    }

    function pesos(valor) { return '$' + String(Math.round(valor)).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
    function capital(texto) { return texto.charAt(0).toUpperCase() + texto.slice(1); }
    function porId(listaItems, id) { return listaItems.filter(function (item) { return String(item.id) === String(id); })[0] || null; }
    function textoOpcion(select) { var o = select.options[select.selectedIndex]; return o && o.value ? o.textContent.trim() : ''; }

    function dos(n) { return (n < 10 ? '0' : '') + n; }
    function aIso(fecha) { return fecha.getFullYear() + '-' + dos(fecha.getMonth() + 1) + '-' + dos(fecha.getDate()); }
    function desdeIso(iso) { var p = iso.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
    function hoy() { var d = new Date(); return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
    function sumarDias(fecha, n) { return new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate() + n); }
    function lunesDe(fecha) { return sumarDias(fecha, -((fecha.getDay() + 6) % 7)); }
    function aHora(minutos) { return dos(Math.floor(minutos / 60)) + ':' + dos(minutos % 60); }

    // "Hoy", "Mañana" o "jue 24 sep"
    function diaCorto(iso) {
        var fecha = desdeIso(iso);
        var diferencia = Math.round((fecha - hoy()) / 864e5);
        if (diferencia === 0) return 'hoy';
        if (diferencia === 1) return 'mañana';
        return DIAS_CORTOS[fecha.getDay()].toLowerCase() + ' ' + fecha.getDate() + ' ' + MESES_CORTOS[fecha.getMonth()];
    }

    // "jueves 24 de septiembre"
    function diaLargo(iso) {
        var fecha = desdeIso(iso);
        return DIAS[fecha.getDay()] + ' ' + fecha.getDate() + ' de ' + MESES[fecha.getMonth()];
    }

    function rangoSemana(lunes) {
        var domingo = sumarDias(lunes, 6);
        if (lunes.getMonth() === domingo.getMonth()) return lunes.getDate() + ' al ' + domingo.getDate() + ' de ' + MESES[domingo.getMonth()];
        return lunes.getDate() + ' ' + MESES_CORTOS[lunes.getMonth()] + ' al ' + domingo.getDate() + ' ' + MESES_CORTOS[domingo.getMonth()];
    }

    function iniciales(nombre) {
        return nombre.replace(/^(Dra?\.)\s*/i, '').split(/\s+/).slice(0, 2).map(function (p) { return p.charAt(0); }).join('').toUpperCase();
    }

    function avatar(pro, clase) {
        return '<span class="cita-avatar' + (clase ? ' ' + clase : '') + '" style="--tono:' + pro.tono + ';--fondo:' + pro.fondo + '" aria-hidden="true">' + esc(iniciales(pro.nombre)) + '</span>';
    }

    // Quita el rojo/verde de un campo cuando cambian sus opciones
    function limpiarMarca(elemento) {
        var caja = elemento.closest('.cita-campos > *');
        if (!caja) return;
        caja.classList.remove('has-error', 'is-ok');
        var error = caja.querySelector(':scope > .field-error');
        if (error) error.remove();
        delete elemento.dataset.tocado;
        delete elemento.dataset.editado;
        elemento.removeAttribute('aria-invalid');
    }

    function llenar(select, items, primera, seleccion) {
        select.innerHTML = '';
        select.appendChild(new Option(primera, ''));
        items.forEach(function (item) {
            var valor = typeof item === 'object' ? item.id : item;
            var texto = typeof item === 'object' ? item.nombre : item;
            var opcion = new Option(texto, valor);
            opcion.selected = seleccion !== undefined && seleccion !== '' && String(valor) === String(seleccion);
            select.appendChild(opcion);
        });
    }

    /* ---------- Horas de ejemplo ---------- */

    // Número entre 0 y 1 que siempre sale igual para el mismo texto (así las horas no cambian al volver)
    function azar(texto) {
        var h = 2166136261;
        for (var i = 0; i < texto.length; i++) {
            h ^= texto.charCodeAt(i);
            h = Math.imul(h, 16777619);
        }
        h ^= h >>> 16;
        h = Math.imul(h, 2246822507);
        h ^= h >>> 13;
        h = Math.imul(h, 3266489909);
        h ^= h >>> 16;
        return (h >>> 0) / 4294967295;
    }

    function horasLibres(pro, lugar, iso) {
        var fecha = desdeIso(iso);
        var dia = fecha.getDay();
        var base = pro.id + '-' + lugar.id;
        if (fecha < hoy() || dia === 0 || azar(base + '-dia' + dia) < 0.3 || azar(base + iso) < 0.14) return [];

        var intervalo = Math.max(30, Math.ceil(lugar.minutos / 15) * 15);
        var fin = dia === 6 ? 13 * 60 : 19 * 60;
        var ahora = new Date();
        var desde = iso === aIso(ahora) ? ahora.getHours() * 60 + ahora.getMinutes() + 60 : 0;
        var horas = [];
        for (var m = 9 * 60; m + lugar.minutos <= fin; m += intervalo) {
            if (dia !== 6 && m >= 13 * 60 && m < 14 * 60 + 30) continue; // colación
            if (m < desde || azar(base + iso + m) < 0.4) continue;
            horas.push(aHora(m));
        }
        return horas;
    }

    function primeraHora(pro, lugar) {
        for (var i = 0; i < SEMANAS_A_LA_VISTA * 7; i++) {
            var iso = aIso(sumarDias(hoy(), i));
            var horas = horasLibres(pro, lugar, iso);
            if (horas.length) return {lugar: lugar, fecha: iso, hora: horas[0]};
        }
        return null;
    }

    // Búsqueda de ejemplo. Al conectar la agenda oficial, esta función debe devolver la misma forma de datos.
    function buscarProfesionales(filtros) {
        return new Promise(function (resolver) {
            setTimeout(function () {
                var lugares = datos.lugares.map(function (lugar) {
                    return {
                        id: lugar.id,
                        nombre: lugar.nombre,
                        direccion: lugar.direccion + ', ' + filtros.comuna,
                        precio: Math.round(filtros.servicio.precio * lugar.factor / 500) * 500,
                        minutos: filtros.servicio.minutos
                    };
                });
                var encontrados = datos.profesionales.map(function (pro) {
                    var suyos = lugares.filter(function (lugar) { return pro.lugares.indexOf(lugar.id) !== -1; });
                    var resultado = {
                        id: pro.id,
                        nombre: pro.nombre,
                        tono: pro.tono,
                        fondo: pro.fondo,
                        especialidad: filtros.tipo === 'consulta-especialidad' ? filtros.servicio.nombre : pro.especialidad,
                        lugares: suyos
                    };
                    resultado.proxima = suyos.reduce(function (mejor, lugar) {
                        var hora = primeraHora(resultado, lugar);
                        return hora && (!mejor || hora.fecha + hora.hora < mejor.fecha + mejor.hora) ? hora : mejor;
                    }, null);
                    return resultado;
                }).filter(function (pro) { return pro.proxima; });

                encontrados.sort(function (a, b) {
                    return (a.proxima.fecha + a.proxima.hora) < (b.proxima.fecha + b.proxima.hora) ? -1 : 1;
                });
                resolver(encontrados);
            }, 650);
        });
    }

    /* ---------- Paso 1: tipo de atención (o servicio ya comprado) ---------- */

    // Sin selects de atención el servicio viene de la compra (campos ocultos)
    function servicioElegido() {
        if (!campo.tipo) {
            var nombre = form.elements.servicio_nombre;
            return {
                id: 'servicio',
                nombre: nombre ? nombre.value : '',
                minutos: Number((form.elements.servicio_minutos || {}).value) || 30,
                precio: 0
            };
        }
        var tipo = datos.tipos[campo.tipo.value];
        var servicio = tipo ? porId(tipo.servicios, campo.servicio.value) : null;
        // En urgencia el motivo que escribe la persona reemplaza el nombre del servicio genérico
        if (servicio && campo.tipo.value === 'urgencia' && campo.motivo && campo.motivo.value.trim()) {
            servicio = Object.assign({}, servicio, {nombre: campo.motivo.value.trim()});
        }
        return servicio;
    }

    function llenarServicios() {
        if (!campo.tipo) return;
        var tipo = datos.tipos[campo.tipo.value];
        var esUrgencia = campo.tipo.value === 'urgencia';
        llenar(campo.servicio, tipo ? tipo.servicios : [], tipo ? 'Selecciona el servicio' : 'Primero elige el tipo de atención', esUrgencia && tipo ? tipo.servicios[0].id : undefined);
        campo.servicio.disabled = !tipo;
        limpiarMarca(campo.servicio);
        zona.urgencia.hidden = !esUrgencia;
        if (zona.servicioCampo) zona.servicioCampo.hidden = esUrgencia;
        if (zona.motivoCampo) zona.motivoCampo.hidden = !esUrgencia;
        campo.servicio.required = !esUrgencia;
        if (campo.motivo) {
            campo.motivo.required = esUrgencia;
            if (!esUrgencia) {
                campo.motivo.value = '';
                limpiarMarca(campo.motivo);
            }
        }
    }

    function mostrarServicio() {
        if (!zona.servicio) return;
        var servicio = servicioElegido();
        var compra = form.elements.compra ? form.elements.compra.value : '';
        var fecha = form.elements.servicio_fecha ? form.elements.servicio_fecha.value : '';
        zona.servicio.hidden = !servicio || !servicio.nombre;
        if (zona.servicio.hidden) return;
        zona.servicio.innerHTML = '<span class="cita-servicio-icono">' + icono(campo.tipo ? 'agenda-veterinaria' : 'servicios') + '</span>'
            + '<span class="cita-servicio-texto"><strong>' + esc(servicio.nombre) + '</strong>'
            + '<span class="cita-servicio-datos"><span>' + RELOJ + 'Duración aprox. ' + servicio.minutos + ' min</span>'
            + (compra ? '<span>' + icono('compras') + 'Pagado en tu compra ' + esc(compra) + (fecha ? ' del ' + esc(fecha) : '') + '</span>' : '')
            + '</span></span>';
    }

    /* ---------- Paso 2: tutor y mascota ---------- */

    function llenarRazas() {
        var elegida = form.querySelector('input[name="mascota_especie"]:checked');
        var especie = elegida ? datos.especies[elegida.value] : null;
        var exotico = !!elegida && elegida.value === 'exotico';
        llenar(campo.raza, especie ? especie.razas : [], especie ? (exotico ? 'Selecciona la especie' : 'Selecciona la raza') : 'Primero elige el tipo de mascota');
        campo.raza.disabled = !especie;
        zona.razaTitulo.textContent = exotico ? 'Especie' : 'Raza';
        limpiarMarca(campo.raza);
        mostrarOtra(false);
    }

    function esOtra(valor) { return /^otr[oa]\b/i.test(valor || ''); }

    // "Otro" / "Otra raza": aparece un campo para escribir cuál es
    function mostrarOtra(enfocar) {
        var especie = form.querySelector('input[name="mascota_especie"]:checked');
        var tipo = especie ? especie.value : '';
        var visible = esOtra(campo.raza.value);
        zona.otra.hidden = !visible;
        zona.otraTitulo.textContent = tipo === 'exotico' ? '¿Qué especie es?' : '¿Qué raza es?';
        campo.otra.placeholder = tipo === 'exotico' ? 'Ej: iguana, loro, axolote' : (tipo === 'gato' ? 'Ej: Azul ruso, Scottish Fold' : 'Ej: Samoyedo, Weimaraner');
        campo.otra.dataset.msg = tipo === 'exotico' ? 'Escribe qué especie es.' : 'Escribe qué raza es.';
        if (!visible) {
            campo.otra.value = '';
            limpiarMarca(campo.otra);
        } else if (enfocar) {
            campo.otra.focus();
        }
    }

    function mostrarCamposMascota(enfocar) {
        if (!zona.mascotaNueva) return;
        var nueva = zona.mascotaNueva.checked;
        zona.camposMascota.hidden = !nueva;
        if (nueva && enfocar) campo.mascota.focus();
    }

    function mascotaElegida() {
        var registrada = form.querySelector('input[name="mascota_id"]:checked');
        if (registrada && registrada.value !== 'nueva') {
            return {nombre: registrada.dataset.nombre, detalle: registrada.dataset.detalle};
        }
        var especie = form.querySelector('input[name="mascota_especie"]:checked');
        var raza = esOtra(campo.raza.value) && campo.otra.value.trim() ? campo.otra.value.trim() : campo.raza.value;
        var detalle = [especie ? especie.dataset.nombre : '', raza].filter(Boolean).join(' · ');
        return {nombre: campo.mascota.value.trim(), detalle: detalle};
    }

    // En el panel del cliente los datos del tutor vienen de su cuenta
    function nombreTutor() {
        if (!campo.nombre) return form.dataset.citaTutor || '';
        return (campo.nombre.value.trim() + ' ' + campo.apellido.value.trim()).trim();
    }

    function emailTutor() {
        return campo.email ? campo.email.value.trim() : (form.dataset.citaEmail || '');
    }

    /* ---------- Paso 3: región y comuna ---------- */

    function pedirJson(url) {
        return fetch(url, {headers: {Accept: 'application/json'}, credentials: 'same-origin'}).then(function (respuesta) {
            if (!respuesta.ok) throw new Error(respuesta.status);
            return respuesta.json();
        });
    }

    // La ubicación que la persona eligió en el menú de la tienda
    function ubicacionTienda() {
        var region = document.getElementById('ubicacion_region');
        var comuna = document.getElementById('ubicacion_ciudad');
        return {
            region: region ? region.dataset.seleccion || '' : '',
            comuna: comuna ? comuna.dataset.seleccion || '' : ''
        };
    }

    function cargarRegiones() {
        if (regionesPedidas) return;
        regionesPedidas = true;
        var guardada = ubicacionTienda();
        pedirJson(form.dataset.urlRegiones).then(function (regiones) {
            llenar(campo.region, regiones, 'Selecciona una región', campo.region.value || guardada.region);
            if (campo.region.value) {
                cargarComunas(campo.region.value, guardada.comuna);
                zona.notaUbicacion.hidden = !guardada.region;
            }
        }).catch(function () {
            regionesPedidas = false;
            llenar(campo.region, [], 'No pudimos cargar las regiones');
        });
    }

    function cargarComunas(region, seleccion) {
        campo.comuna.disabled = true;
        limpiarMarca(campo.comuna);
        if (!region) {
            llenar(campo.comuna, [], 'Primero elige la región');
            return;
        }
        llenar(campo.comuna, [], 'Cargando comunas…');
        pedirJson(form.dataset.urlComunas.replace('__REGION__', region)).then(function (comunas) {
            if (campo.region.value !== String(region)) return;
            llenar(campo.comuna, comunas, 'Selecciona una comuna', seleccion);
            campo.comuna.disabled = false;
        }).catch(function () {
            llenar(campo.comuna, [], 'No pudimos cargar las comunas');
        });
    }

    /* ---------- Paso 4: resultados ---------- */

    function filtros() {
        return {
            tipo: campo.tipo ? campo.tipo.value : '',
            servicio: servicioElegido(),
            comuna: textoOpcion(campo.comuna),
            comunaId: campo.comuna.value
        };
    }

    function mostrarVista(nombre) {
        if (zona.resultados) zona.resultados.hidden = nombre !== 'resultados';
        zona.detalle.hidden = nombre !== 'detalle';
    }

    function enDetalle() {
        return actual === iHora && !zona.detalle.hidden;
    }

    // La agenda del servicio comprado: sin profesionales, se elige lugar y hora
    function agendaServicio() {
        var servicio = servicioElegido();
        var comuna = textoOpcion(campo.comuna);
        return {
            id: 'srv-' + (form.elements.compra ? form.elements.compra.value : servicio.nombre),
            nombre: servicio.nombre,
            lugares: datos.lugares.map(function (lugar) {
                return {
                    id: lugar.id,
                    nombre: lugar.nombre,
                    direccion: lugar.direccion + ', ' + comuna,
                    precio: 0,
                    minutos: servicio.minutos
                };
            })
        };
    }

    function prepararBusqueda() {
        var clave = (campo.servicio ? campo.servicio.value : servicioElegido().nombre) + '|' + campo.comuna.value;
        if (clave === estado.busqueda) return;

        estado.busqueda = clave;
        estado.resultados = [];
        estado.pro = null;
        guardarCita(null);

        if (!zona.profesionales) {
            estado.pro = agendaServicio();
            mostrarVista('detalle');
            var conHoras = estado.pro.lugares.map(function (lugar) { return primeraHora(estado.pro, lugar); }).filter(Boolean);
            conHoras.sort(function (a, b) { return (a.fecha + a.hora) < (b.fecha + b.hora) ? -1 : 1; });
            elegirLugar(conHoras.length ? conHoras[0].lugar.id : estado.pro.lugares[0].id);
            return;
        }

        mostrarVista('resultados');
        pintarFiltros();

        zona.conteo.textContent = 'Buscando horas disponibles…';
        zona.profesionales.setAttribute('aria-busy', 'true');
        zona.profesionales.innerHTML = [1, 2, 3].map(function () {
            return '<div class="cita-esqueleto" aria-hidden="true"><i></i><i></i><i></i><i></i></div>';
        }).join('');

        var pedido = estado.pedido = {};
        buscarProfesionales(filtros()).then(function (encontrados) {
            if (pedido !== estado.pedido) return;
            estado.resultados = encontrados;
            pintarResultados();
        });
    }

    function pintarFiltros() {
        var f = filtros();
        zona.filtros.innerHTML = '<button type="button" class="cita-limpio cita-filtro" data-cita-ir="0" aria-label="Cambiar servicio: ' + esc(f.servicio.nombre) + '">' + icono('agenda-veterinaria') + esc(f.servicio.nombre) + icono('editar', 'cita-filtro-editar') + '</button>'
            + '<button type="button" class="cita-limpio cita-filtro" data-cita-ir="2" aria-label="Cambiar comuna: ' + esc(f.comuna) + '">' + icono('locacion') + esc(f.comuna) + icono('editar', 'cita-filtro-editar') + '</button>';
    }

    function pintarResultados() {
        var f = filtros();
        var encontrados = estado.resultados;
        zona.profesionales.removeAttribute('aria-busy');

        if (!encontrados.length) {
            zona.conteo.textContent = 'No hay horas disponibles en ' + f.comuna + '.';
            zona.profesionales.innerHTML = '<div class="cita-vacio">'
                + '<span class="cita-vacio-icono">' + icono('calendario') + '</span>'
                + '<strong>No encontramos horas disponibles</strong>'
                + '<p>Prueba con otra comuna o con otro servicio.</p>'
                + '<div class="cita-vacio-acciones">'
                + '<button type="button" class="btn btn-cancelar" data-cita-ir="2">Cambiar comuna</button>'
                + '<button type="button" class="btn btn-cancelar" data-cita-ir="0">Cambiar servicio</button>'
                + '</div></div>';
            return;
        }

        zona.conteo.innerHTML = '<strong>' + encontrados.length + '</strong> ' + (encontrados.length === 1 ? 'profesional disponible' : 'profesionales disponibles') + ' en ' + esc(f.comuna);
        zona.profesionales.innerHTML = encontrados.map(tarjetaProfesional).join('');
    }

    function tarjetaProfesional(pro) {
        var desde = Math.min.apply(null, pro.lugares.map(function (lugar) { return lugar.precio; }));
        var cantidad = pro.lugares.length;
        var idLista = 'cita-lugares-pro-' + pro.id;

        return '<article class="cita-pro">'
            + avatar(pro)
            + '<div class="cita-pro-cuerpo">'
            + '<h4 class="cita-pro-nombre">' + esc(pro.nombre) + '</h4>'
            + '<p class="cita-pro-especialidad">' + esc(pro.especialidad) + '</p>'
            + '<p class="cita-pro-proxima">' + icono('calendario') + '<span>Próxima hora: <strong>' + capital(diaCorto(pro.proxima.fecha)) + ', ' + pro.proxima.hora + '</strong> en ' + esc(pro.proxima.lugar.nombre) + '</span></p>'
            + '<button type="button" class="cita-limpio cita-pro-desplegar" aria-expanded="false" aria-controls="' + idLista + '" data-cita-desplegar>'
            + icono('locacion') + 'Atiende en ' + cantidad + (cantidad === 1 ? ' lugar' : ' lugares') + FLECHA_ABAJO + '</button>'
            + '</div>'
            + '<div class="cita-pro-accion">'
            + '<p class="cita-pro-precio">Desde <strong>' + pesos(desde) + '</strong></p>'
            + '<button type="button" class="btn btn-success cita-pro-ver" data-cita-ver="' + pro.id + '" aria-label="Ver agenda de ' + esc(pro.nombre) + '">' + icono('agenda-veterinaria', 'isdi-izq') + 'Ver agenda</button>'
            + '</div>'
            + '<ul class="cita-pro-lugares" id="' + idLista + '" hidden>'
            + pro.lugares.map(function (lugar) {
                return '<li><button type="button" class="cita-limpio cita-pro-lugar" data-cita-ver="' + pro.id + '" data-cita-lugar="' + lugar.id + '">'
                    + '<span class="cita-pro-lugar-texto"><strong>' + esc(lugar.nombre) + '</strong><small>' + esc(lugar.direccion) + '</small></span>'
                    + '<span class="cita-pro-lugar-precio">' + pesos(lugar.precio) + '</span>' + FLECHA_DER
                    + '</button></li>';
            }).join('')
            + '</ul>'
            + '</article>';
    }

    function desplegarLugares(boton) {
        var abierto = boton.getAttribute('aria-expanded') === 'true';
        boton.setAttribute('aria-expanded', abierto ? 'false' : 'true');
        document.getElementById(boton.getAttribute('aria-controls')).hidden = abierto;
    }

    /* ---------- Paso 4: lugar, semana y hora ---------- */

    function abrirDetalle(proId, lugarId) {
        var pro = porId(estado.resultados, proId);
        if (!pro) return;
        estado.pro = pro;

        var lugar = lugarId ? porId(pro.lugares, lugarId) : null;
        if (!lugar && estado.cita && estado.cita.pro.id === pro.id) lugar = estado.cita.lugar;
        if (!lugar) lugar = pro.proxima.lugar;

        if (zona.detallePro) {
            zona.detallePro.innerHTML = avatar(pro, 'cita-avatar--mediano')
                + '<div><h3 tabindex="-1">' + esc(pro.nombre) + '</h3><p>' + esc(pro.especialidad) + '<span class="cita-solo-escritorio"> · Elige el lugar, el día y la hora</span></p></div>';
        }
        mostrarVista('detalle');
        elegirLugar(lugar.id);
        cuerpo.scrollTop = 0;
        enfocarTitulo();
        actualizarPie();
    }

    function volverResultados() {
        mostrarVista('resultados');
        cuerpo.scrollTop = 0;
        enfocarTitulo();
        actualizarPie();
    }

    function elegirLugar(lugarId, desdeClic) {
        var lugar = porId(estado.pro.lugares, lugarId);
        if (!lugar) return;
        estado.lugar = lugar;

        // Se abre en la hora ya elegida (si es de este lugar) o en el primer día con horas
        var cita = estado.cita;
        var fecha = cita && cita.pro.id === estado.pro.id && cita.lugar.id === lugar.id ? cita.fecha : null;
        if (!fecha) {
            var primera = primeraHora(estado.pro, lugar);
            fecha = primera ? primera.fecha : null;
        }
        estado.lunes = lunesDe(fecha ? desdeIso(fecha) : hoy());
        estado.dia = fecha;

        pintarLugares();
        pintarAgenda();
        if (desdeClic && window.matchMedia('(max-width: 760px)').matches) {
            zona.agenda.scrollIntoView({behavior: 'smooth', block: 'start'});
        }
    }

    function pintarLugares() {
        var servicio = servicioElegido();
        zona.lugares.innerHTML = estado.pro.lugares.map(function (lugar) {
            var proxima = primeraHora(estado.pro, lugar);
            var elegido = lugar.id === estado.lugar.id;
            return '<button type="button" class="cita-limpio cita-lugar" data-cita-lugar="' + lugar.id + '" aria-pressed="' + elegido + '">'
                + '<span class="cita-lugar-nombre">' + esc(lugar.nombre) + '</span>'
                + '<span class="cita-lugar-direccion">' + esc(lugar.direccion) + '</span>'
                + '<span class="cita-lugar-servicio"><span>' + esc(servicio.nombre) + '</span>' + (lugar.precio ? '<strong>' + pesos(lugar.precio) + '</strong>' : '') + '</span>'
                + '<span class="cita-lugar-pie"><span>' + RELOJ + lugar.minutos + ' min</span>'
                + (proxima ? '<span class="cita-lugar-proxima">Próxima: ' + diaCorto(proxima.fecha) + ', ' + proxima.hora + '</span>' : '<span>Sin horas próximas</span>')
                + '</span></button>';
        }).join('');
    }

    function pintarAgenda(enfocar) {
        var pro = estado.pro;
        var lugar = estado.lugar;
        var lunes = estado.lunes;
        var primeraSemana = lunesDe(hoy());
        var ultimaSemana = sumarDias(primeraSemana, 7 * (SEMANAS_A_LA_VISTA - 1));
        var hoyIso = aIso(hoy());

        var html = '<div class="cita-agenda-cabecera">'
            + '<p class="cita-columna-titulo">' + icono('calendario') + 'Calendario semanal</p>'
            + '<div class="cita-semana">'
            + '<button type="button" class="cita-limpio cita-flecha" data-cita-semana="-1" aria-label="Semana anterior"' + (lunes <= primeraSemana ? ' disabled' : '') + '>' + FLECHA_IZQ + '</button>'
            + '<span class="cita-semana-rango" aria-live="polite">' + rangoSemana(lunes) + '</span>'
            + '<button type="button" class="cita-limpio cita-flecha" data-cita-semana="1" aria-label="Semana siguiente"' + (lunes >= ultimaSemana ? ' disabled' : '') + '>' + FLECHA_DER + '</button>'
            + '</div></div>';

        html += '<div class="cita-dias" role="group" aria-label="Días de la semana">';
        for (var i = 0; i < 7; i++) {
            var fecha = sumarDias(lunes, i);
            var iso = aIso(fecha);
            var cantidad = horasLibres(pro, lugar, iso).length;
            html += '<button type="button" class="cita-limpio cita-dia' + (iso === hoyIso ? ' es-hoy' : '') + '" data-cita-dia="' + iso + '" aria-pressed="' + (iso === estado.dia) + '"'
                + ' aria-label="' + capital(diaLargo(iso)) + (cantidad ? ', ' + cantidad + (cantidad === 1 ? ' hora disponible' : ' horas disponibles') : ', sin horas') + '"' + (cantidad ? '' : ' disabled') + '>'
                + '<span class="cita-dia-nombre">' + (iso === hoyIso ? 'Hoy' : DIAS_CORTOS[fecha.getDay()]) + '</span>'
                + '<strong>' + fecha.getDate() + '</strong><i></i></button>';
        }
        html += '</div><div class="cita-horas">';

        if (!estado.dia) {
            html += '<div class="cita-horas-vacio"><p>No quedan horas esta semana en este lugar.</p>'
                + (lunes < ultimaSemana ? '<button type="button" class="cita-limpio cita-enlace" data-cita-semana="1">Ver la semana siguiente' + FLECHA_DER + '</button>' : '')
                + '</div>';
        } else {
            var horas = horasLibres(pro, lugar, estado.dia);
            var cita = estado.cita;
            var mismaAgenda = cita && cita.pro.id === pro.id && cita.lugar.id === lugar.id && cita.fecha === estado.dia;

            html += '<p class="cita-horas-titulo">Horas disponibles <span>' + capital(diaLargo(estado.dia)) + '</span></p>'
                + '<div class="cita-horas-lista" role="group" aria-label="Horas disponibles">'
                + horas.map(function (hora) {
                    return '<button type="button" class="cita-limpio cita-hora" data-cita-hora="' + hora + '" aria-pressed="' + !!(mismaAgenda && cita.hora === hora) + '">' + hora + '</button>';
                }).join('')
                + '</div>';
        }
        html += '</div>';
        zona.agenda.innerHTML = html;

        if (enfocar) {
            var destino = zona.agenda.querySelector(enfocar);
            if (destino && !destino.disabled) destino.focus();
        }
    }

    function moverSemana(salto) {
        var lunes = sumarDias(estado.lunes, 7 * salto);
        var primeraSemana = lunesDe(hoy());
        if (lunes < primeraSemana || lunes > sumarDias(primeraSemana, 7 * (SEMANAS_A_LA_VISTA - 1))) return;
        estado.lunes = lunes;
        estado.dia = null;
        for (var i = 0; i < 7; i++) {
            var iso = aIso(sumarDias(lunes, i));
            if (horasLibres(estado.pro, estado.lugar, iso).length) {
                estado.dia = iso;
                break;
            }
        }
        pintarAgenda('[data-cita-semana="' + salto + '"]');
    }

    function elegirDia(iso) {
        estado.dia = iso;
        pintarAgenda('[data-cita-dia="' + iso + '"]');
    }

    function elegirHora(hora) {
        guardarCita({pro: estado.pro, lugar: estado.lugar, fecha: estado.dia, hora: hora});
        lista(zona.agenda.querySelectorAll('[data-cita-hora]')).forEach(function (boton) {
            boton.setAttribute('aria-pressed', boton.dataset.citaHora === hora ? 'true' : 'false');
        });
        actualizarPie();
        // Un latido suave en "Continuar" para mostrar el siguiente paso
        botonSeguir.classList.remove('is-latido');
        void botonSeguir.offsetWidth;
        botonSeguir.classList.add('is-latido');
    }

    function guardarCita(cita) {
        estado.cita = cita;
        var valores = {
            profesional: cita ? cita.pro.id : '',
            lugar: cita ? cita.lugar.id : '',
            fecha: cita ? cita.fecha : '',
            hora: cita ? cita.hora : ''
        };
        lista(form.querySelectorAll('[data-cita-dato]')).forEach(function (oculto) {
            oculto.value = valores[oculto.dataset.citaDato];
        });
    }

    /* ---------- Paso 5: resumen y pago ---------- */

    function dato(nombreIcono, titulo, valor, detalle) {
        return '<div class="cita-dato"><dt>' + icono(nombreIcono) + titulo + '</dt><dd>' + esc(valor) + (detalle ? '<small>' + esc(detalle) + '</small>' : '') + '</dd></div>';
    }

    function pintarResumen() {
        var cita = estado.cita;
        if (!cita || !zona.resumen) return;
        var servicio = servicioElegido();
        var mascota = mascotaElegida();

        zona.resumen.innerHTML = '<div class="cita-resumen-cabecera"><p class="cita-resumen-titulo">Resumen de tu cita</p>'
            + '<button type="button" class="cita-limpio cita-enlace" data-cita-ir="3">Cambiar hora</button></div>'
            + '<div class="cita-resumen-pro">' + avatar(cita.pro, 'cita-avatar--chico') + '<div><strong>' + esc(cita.pro.nombre) + '</strong><small>' + esc(cita.pro.especialidad) + '</small></div></div>'
            + '<dl class="cita-datos">'
            + dato('calendario', 'Fecha y hora', capital(diaLargo(cita.fecha)) + ', ' + cita.hora + ' hrs')
            + dato('locacion', 'Lugar', cita.lugar.nombre, cita.lugar.direccion)
            + dato('agenda-veterinaria', 'Servicio', servicio.nombre, 'Duración aprox. ' + cita.lugar.minutos + ' min')
            + dato('mascota', 'Paciente', mascota.nombre, mascota.detalle)
            + dato('usuario', 'Tutor', nombreTutor(), emailTutor())
            + '</dl>'
            + '<div class="cita-resumen-total"><span>Total a pagar</span><strong>' + pesos(cita.lugar.precio) + '</strong></div>';
    }

    function finalizar() {
        estado.enviando = true;
        botonAtras.disabled = true;
        botonSeguir.disabled = true;
        botonSeguir.classList.add('is-cargando');
        botonSeguir.innerHTML = '<span class="cita-girando" aria-hidden="true"></span>' + (iPago >= 0 ? 'Conectando con el pago…' : 'Agendando tu hora…');
        // Aquí se envía la reserva (y el pago, si corresponde); por ahora se muestra la confirmación
        setTimeout(mostrarListo, 1400);
    }

    function mostrarListo() {
        var cita = estado.cita;
        var medio = form.querySelector('input[name="medio_pago"]:checked');
        var mascota = mascotaElegida();
        var servicio = servicioElegido();
        var compra = form.elements.compra ? form.elements.compra.value : '';
        var email = emailTutor();
        var codigo = 'VF-' + Math.random().toString(36).slice(2, 7).toUpperCase();

        estado.enviando = false;
        estado.terminado = true;
        pasos.forEach(function (paso) { paso.hidden = true; });
        listo.hidden = false;
        pintarMarcas(true);

        zona.listoTexto.innerHTML = medio && medio.value === 'transferencia'
            ? 'Te enviamos los datos para transferir a <strong>' + esc(email) + '</strong>.'
            : 'Te enviamos el detalle a <strong>' + esc(email) + '</strong>.';
        zona.ticket.innerHTML = '<div class="cita-ticket-codigo"><span>Código de reserva</span><strong>' + codigo + '</strong></div>'
            + '<dl class="cita-datos">'
            + dato('calendario', 'Fecha y hora', capital(diaLargo(cita.fecha)) + ', ' + cita.hora + ' hrs')
            + dato('locacion', 'Lugar', cita.lugar.nombre, cita.lugar.direccion)
            + (cita.pro.especialidad
                ? dato('usuario', 'Profesional', cita.pro.nombre, servicio.nombre)
                : dato('servicios', 'Servicio', servicio.nombre, 'Duración aprox. ' + cita.lugar.minutos + ' min'))
            + dato('mascota', 'Paciente', mascota.nombre, mascota.detalle)
            + (medio
                ? dato(medio.value === 'transferencia' ? 'banco' : 'tarjeta', 'Pago', medio.dataset.nombre, pesos(cita.lugar.precio))
                : (compra ? dato('compras', 'Pago', 'Pagado en tu compra', compra) : ''))
            + '</dl>';

        info.hidden = true;
        botonAtras.hidden = true;
        botonSeguir.hidden = true;
        botonSeguir.disabled = false;
        botonSeguir.classList.remove('is-cargando');
        botonAtras.disabled = false;
        if (botonOtra) botonOtra.hidden = false;
        botonListo.hidden = false;
        cuerpo.scrollTop = 0;
        if (modal.open) listo.focus({preventScroll: true});
    }

    /* ---------- Pasos ---------- */

    function pintarMarcas(todoListo) {
        marcas.forEach(function (marca, i) {
            var boton = marca.querySelector('[data-cita-ir]');
            marca.classList.toggle('is-actual', !todoListo && i === actual);
            marca.classList.toggle('is-hecho', !!todoListo || i < actual);
            boton.disabled = !!todoListo || i >= actual;
            if (!todoListo && i === actual) boton.setAttribute('aria-current', 'step'); else boton.removeAttribute('aria-current');
        });
        pasoMovil.textContent = todoListo ? 'Cita agendada' : 'Paso ' + (actual + 1) + ' de ' + marcas.length + ' · ' + marcas[actual].dataset.nombre;
    }

    function enfocarTitulo() {
        if (!modal.open) return;
        var titulo = pasos[actual].querySelector('.cita-vista:not([hidden]) h3') || pasos[actual].querySelector('h3');
        if (titulo) titulo.focus({preventScroll: true});
    }

    function ir(indice, haciaAtras) {
        if (indice < 0 || indice >= pasos.length || estado.enviando || estado.terminado) return;
        actual = indice;
        pasos.forEach(function (paso, i) {
            paso.hidden = i !== indice;
            paso.classList.toggle('va-atras', i === indice && !!haciaAtras);
        });
        pintarMarcas(false);
        botonAtras.hidden = indice === 0;

        if (claves[indice] === 'lugar') cargarRegiones();
        if (claves[indice] === 'hora') prepararBusqueda();
        if (claves[indice] === 'pago') pintarResumen();

        actualizarPie();
        cuerpo.scrollTop = 0;
        enfocarTitulo();
    }

    function actualizarPie() {
        var cita = estado.cita;
        var texto = '';

        // Solo en hora y pago: la hora elegida y el total
        if (actual === iPago && cita) {
            texto = 'Total a pagar <strong>' + pesos(cita.lugar.precio) + '</strong>';
        } else if (actual === iHora) {
            texto = cita
                ? icono('calendario') + '<strong>' + capital(diaCorto(cita.fecha)) + ', ' + cita.hora + '</strong><span class="cita-pie-lugar"> · ' + esc(cita.lugar.nombre) + '</span>'
                : (enDetalle() ? 'Elige un día y una hora' : 'Elige un profesional para ver su agenda');
        }
        info.innerHTML = texto;

        // El texto del botón lo define cada paso
        var boton = pasos[actual].dataset;
        botonSeguir.innerHTML = boton.citaBotonIcono
            ? icono(boton.citaBotonIcono, 'isdi-izq') + (boton.citaBoton || 'Continuar')
            : (boton.citaBoton || 'Continuar') + icono('siguiente', 'isdi-der');
    }

    // Los avisos de "revisa el formulario" ya no aplican cuando el paso quedó bien
    function cerrarAvisos() {
        lista(modal.querySelectorAll('.notificacion--error .notificacion-cerrar, .notificacion--advertencia .notificacion-cerrar')).forEach(function (boton) {
            boton.click();
        });
    }

    function faltaHora() {
        if (window.notificar) {
            window.notificar({
                tipo: 'advertencia',
                titulo: 'Falta elegir la hora',
                mensaje: enDetalle() ? 'Elige un día y una hora disponible para continuar.' : 'Elige un profesional y luego una de sus horas disponibles.'
            });
        }
        if (enDetalle()) {
            zona.agenda.classList.remove('is-aviso');
            void zona.agenda.offsetWidth;
            zona.agenda.classList.add('is-aviso');
            zona.agenda.scrollIntoView({behavior: 'smooth', block: 'nearest'});
        }
    }

    function reiniciar() {
        var compra = form.elements.compra ? form.elements.compra.value : '';
        var servicioNombre = form.elements.servicio_nombre ? form.elements.servicio_nombre.value : '';
        var servicioMinutos = form.elements.servicio_minutos ? form.elements.servicio_minutos.value : '';
        var servicioFecha = form.elements.servicio_fecha ? form.elements.servicio_fecha.value : '';

        form.reset();
        estado = estadoNuevo();
        guardarCita(null);
        listo.hidden = true;
        info.hidden = false;
        botonSeguir.hidden = false;
        if (botonOtra) botonOtra.hidden = true;
        botonListo.hidden = true;
        if (zona.profesionales) zona.profesionales.innerHTML = '';
        if (zona.filtros) zona.filtros.innerHTML = '';
        mostrarVista(zona.profesionales ? 'resultados' : 'detalle');

        // El reset deja los select sin sus opciones dependientes: se rearman
        setTimeout(function () {
            guardarServicio({compra: compra, nombre: servicioNombre, minutos: servicioMinutos, fecha: servicioFecha});
            llenarServicios();
            mostrarServicio();
            llenarRazas();
            mostrarCamposMascota(false);
            var guardada = ubicacionTienda();
            if (regionesPedidas && guardada.region) {
                campo.region.value = guardada.region;
                cargarComunas(guardada.region, guardada.comuna);
            } else {
                cargarComunas(campo.region.value, '');
            }
            if (zona.notaUbicacion) zona.notaUbicacion.hidden = !(regionesPedidas && guardada.region && campo.region.value);
            ir(0);
        });
    }

    // Servicio que se viene a agendar (lo pasa el botón de la compra)
    function guardarServicio(servicio) {
        if (!form.elements.servicio_nombre || !servicio) return;
        form.elements.compra.value = servicio.compra || '';
        form.elements.servicio_nombre.value = servicio.nombre || '';
        form.elements.servicio_minutos.value = servicio.minutos || '';
        form.elements.servicio_fecha.value = servicio.fecha || '';
    }

    /* ---------- Eventos ---------- */

    if (campo.tipo) {
        campo.tipo.addEventListener('change', function () {
            llenarServicios();
            mostrarServicio();
            actualizarPie();
        });

        campo.servicio.addEventListener('change', function () {
            mostrarServicio();
            actualizarPie();
        });

        if (campo.motivo) {
            campo.motivo.addEventListener('input', function () {
                mostrarServicio();
                actualizarPie();
            });
        }
    }

    campo.region.addEventListener('change', function () {
        if (zona.notaUbicacion) zona.notaUbicacion.hidden = true;
        cargarComunas(campo.region.value, '');
    });

    form.addEventListener('change', function (evento) {
        if (evento.target.name === 'mascota_especie') llenarRazas();
        if (evento.target === campo.raza) mostrarOtra(true);
        if (evento.target.name === 'mascota_id') mostrarCamposMascota(true);
    });

    // validacion.js revisa los campos del paso antes de que llegue aquí
    form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        if (estado.enviando || estado.terminado) return;
        if (actual === iHora && !estado.cita) {
            faltaHora();
            return;
        }
        cerrarAvisos();
        if (actual < pasos.length - 1) {
            ir(actual + 1);
            return;
        }
        finalizar();
    });

    botonAtras.addEventListener('click', function () {
        if (enDetalle() && zona.resultados) {
            volverResultados();
            return;
        }
        ir(actual - 1, true);
    });

    if (botonOtra) botonOtra.addEventListener('click', reiniciar);

    modal.addEventListener('click', function (evento) {
        var el = evento.target.closest('button');
        if (!el || !modal.contains(el)) return;

        if (el.hasAttribute('data-cita-ir')) {
            ir(Number(el.dataset.citaIr), true);
        } else if (el.hasAttribute('data-cita-ver')) {
            abrirDetalle(el.dataset.citaVer, el.dataset.citaLugar);
        } else if (el.hasAttribute('data-cita-desplegar')) {
            desplegarLugares(el);
        } else if (el.hasAttribute('data-cita-volver')) {
            volverResultados();
        } else if (el.hasAttribute('data-cita-lugar')) {
            elegirLugar(el.dataset.citaLugar, true);
        } else if (el.hasAttribute('data-cita-semana')) {
            moverSemana(Number(el.dataset.citaSemana));
        } else if (el.hasAttribute('data-cita-dia')) {
            elegirDia(el.dataset.citaDia);
        } else if (el.hasAttribute('data-cita-hora')) {
            elegirHora(el.dataset.citaHora);
        }
    });

    botonSeguir.addEventListener('animationend', function () {
        botonSeguir.classList.remove('is-latido');
    });

    zona.agenda.addEventListener('animationend', function () {
        zona.agenda.classList.remove('is-aviso');
    });

    // Si una lista se abrió mientras el paso entraba, se reubica al terminar la animación
    form.addEventListener('animationend', function (evento) {
        if (evento.target.matches('.cita-paso, .cita-vista') && modal.querySelector('.sb.is-abierto')) {
            window.dispatchEvent(new Event('resize'));
        }
    });

    // El botón de la compra dice qué servicio se viene a agendar; si cambia, se parte de nuevo
    if (form.elements.servicio_nombre) {
        document.addEventListener('click', function (evento) {
            var abridor = evento.target.closest('[data-cita-servicio-datos]');
            if (!abridor || abridor.dataset.modalAbrir !== modal.id) return;
            var servicio;
            try { servicio = JSON.parse(abridor.dataset.citaServicioDatos); } catch (e) { return; }
            if (servicio.compra === form.elements.compra.value && !estado.terminado) return;
            guardarServicio(servicio);
            reiniciar();
        }, true);
    }

    // Al abrir se piden las regiones y, si venía a medias, se retoma donde quedó
    modal.addEventListener('modal:abierto', function () {
        cargarRegiones();
        mostrarServicio();
        if (actual > 0 && !estado.terminado) enfocarTitulo();
        if (estado.terminado) listo.focus({preventScroll: true});
    });

    // Terminada la reserva, al cerrar queda lista para una nueva
    modal.addEventListener('close', function () {
        if (estado.terminado) reiniciar();
    });

    // Enlaces con #agendar-cita abren la ventana directo
    if (modal.id === 'modal-agendar-cita' && window.location.hash === '#agendar-cita' && window.abrirModal) {
        history.replaceState(null, '', window.location.pathname + window.location.search);
        window.abrirModal(modal.id);
    }

    llenarRazas();
    ir(0);
    }

    lista(document.querySelectorAll('[data-cita-form]')).forEach(iniciar);
}());
