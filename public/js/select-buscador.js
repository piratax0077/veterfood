/*
 * Select con buscador. Se aplica a los select de todos los paneles (body con data-selects-buscador,
 * que el layout pone fuera de la tienda) y a cualquier select con data-select-buscador.
 * Para dejar uno normal: data-sin-buscador.
 * El select original sigue en la página (oculto), así los formularios y los scripts existentes funcionan igual.
 */
(function () {
    'use strict';

    var SELECTOR = 'body[data-selects-buscador] select:not([multiple]):not([size]):not([data-sin-buscador]), .client-page select:not([multiple]):not([data-sin-buscador]), select[data-select-buscador]';
    var abierto = null;
    var contadorIds = 0;

    function normalizar(texto) {
        return String(texto || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    }

    function cerrarAbierto() {
        if (abierto) {
            abierto.cerrar();
        }
    }

    function mejorar(select) {
        if (select.dataset.buscadorListo) {
            return;
        }
        select.dataset.buscadorListo = '1';
        contadorIds++;

        var idLista = 'sb-lista-' + contadorIds;
        var envoltura = document.createElement('div');
        envoltura.className = 'sb';
        if (select.classList.contains('form-control-sm')) {
            envoltura.classList.add('sb--sm');
        }
        var anchoPropio = select.offsetWidth;
        var anchoPadre = select.parentNode.clientWidth;
        if (anchoPropio && anchoPadre && anchoPropio < anchoPadre - 24) {
            envoltura.classList.add('sb--angosto');
            envoltura.style.width = Math.max(anchoPropio, 150) + 'px';
        }
        select.parentNode.insertBefore(envoltura, select);
        envoltura.appendChild(select);
        select.classList.add('sb-nativo');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');
        select.addEventListener('focus', function () {
            boton.focus();
        });

        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'sb-boton';
        boton.setAttribute('aria-haspopup', 'listbox');
        boton.setAttribute('aria-expanded', 'false');
        boton.innerHTML = '<span class="sb-texto"></span><svg class="sb-flecha" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>';
        envoltura.appendChild(boton);

        // El label del select pasa a nombrar el botón
        var etiqueta = select.id ? document.querySelector('label[for="' + select.id + '"]') : null;
        if (!etiqueta && select.previousElementSibling === null && envoltura.previousElementSibling && envoltura.previousElementSibling.tagName === 'LABEL') {
            etiqueta = envoltura.previousElementSibling;
        }
        if (etiqueta) {
            contadorIds++;
            etiqueta.id = etiqueta.id || 'sb-etiqueta-' + contadorIds;
            boton.setAttribute('aria-labelledby', etiqueta.id + ' ' + (boton.id = 'sb-boton-' + contadorIds));
            etiqueta.addEventListener('click', function (evento) {
                evento.preventDefault();
                boton.focus();
            });
        }

        var panel = document.createElement('div');
        panel.className = 'sb-panel';
        panel.hidden = true;
        panel.innerHTML = '<div class="sb-buscar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.6-3.6"/></svg>'
            + '<input type="text" class="sb-campo" placeholder="Buscar…" autocomplete="off" aria-label="Buscar opción" aria-controls="' + idLista + '"></div>'
            + '<ul class="sb-lista" role="listbox" id="' + idLista + '"></ul>'
            + '<p class="sb-vacio" hidden>No hay coincidencias</p>';
        envoltura.appendChild(panel);

        var campo = panel.querySelector('.sb-campo');
        var lista = panel.querySelector('.sb-lista');
        var vacio = panel.querySelector('.sb-vacio');
        var textoBoton = boton.querySelector('.sb-texto');
        var activo = -1;

        function opcionesVisibles() {
            return Array.prototype.filter.call(lista.children, function (item) {
                return !item.hidden;
            });
        }

        function pintarBoton() {
            var elegida = select.options[select.selectedIndex];
            var texto = elegida ? elegida.textContent.trim() : '';
            textoBoton.textContent = texto || 'Seleccionar';
            boton.classList.toggle('is-vacio', !elegida || elegida.value === '');
            boton.disabled = select.disabled;
            envoltura.classList.toggle('is-deshabilitado', select.disabled);
        }

        function construirLista() {
            lista.innerHTML = '';
            Array.prototype.forEach.call(select.options, function (opcion, indice) {
                var item = document.createElement('li');
                item.className = 'sb-opcion';
                item.setAttribute('role', 'option');
                item.dataset.indice = indice;
                item.textContent = opcion.textContent.trim();
                item.setAttribute('aria-selected', opcion.selected ? 'true' : 'false');
                if (opcion.disabled) {
                    item.setAttribute('aria-disabled', 'true');
                }
                if (opcion.value === '') {
                    item.classList.add('is-vacia');
                }
                lista.appendChild(item);
            });
        }

        function marcarActivo(indice) {
            var visibles = opcionesVisibles();
            if (!visibles.length) {
                activo = -1;
                return;
            }
            activo = Math.max(0, Math.min(indice, visibles.length - 1));
            visibles.forEach(function (item, i) {
                item.classList.toggle('is-activa', i === activo);
            });
            visibles[activo].scrollIntoView({ block: 'nearest' });
        }

        function filtrar() {
            var busqueda = normalizar(campo.value);
            var hay = 0;
            Array.prototype.forEach.call(lista.children, function (item) {
                var coincide = !busqueda || normalizar(item.textContent).indexOf(busqueda) !== -1;
                item.hidden = !coincide;
                hay += coincide ? 1 : 0;
            });
            vacio.hidden = hay > 0;
            marcarActivo(0);
        }

        function elegir(item) {
            if (!item || item.getAttribute('aria-disabled') === 'true') {
                return;
            }
            var indice = Number(item.dataset.indice);
            var cambio = select.selectedIndex !== indice;
            select.selectedIndex = indice;
            pintarBoton();
            cerrar();
            boton.focus();
            if (cambio) {
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        function abrir() {
            if (select.disabled) {
                return;
            }
            cerrarAbierto();
            construirLista();
            campo.value = '';
            filtrar();
            panel.hidden = false;
            envoltura.classList.add('is-abierto');
            boton.setAttribute('aria-expanded', 'true');

            posicionar();

            var elegido = lista.querySelector('[aria-selected="true"]');
            if (elegido) {
                marcarActivo(opcionesVisibles().indexOf(elegido));
            }
            campo.focus();
            abierto = { cerrar: cerrar, posicionar: posicionar };
        }

        // Ubica la lista pegada al botón; si no cabe hacia abajo, se abre hacia arriba
        function posicionar() {
            var caja = boton.getBoundingClientRect();
            if (caja.bottom < 0 || caja.top > window.innerHeight) {
                cerrar();
                return;
            }
            var espacioAbajo = window.innerHeight - caja.bottom;
            var arriba = espacioAbajo < 300 && caja.top > espacioAbajo;
            var ancho = Math.max(caja.width, 220);
            panel.style.left = Math.max(8, Math.min(caja.left, window.innerWidth - ancho - 8)) + 'px';
            panel.style.width = ancho + 'px';
            panel.style.top = arriba ? 'auto' : (caja.bottom + 6) + 'px';
            panel.style.bottom = arriba ? (window.innerHeight - caja.top + 6) + 'px' : 'auto';
            envoltura.classList.toggle('abre-arriba', arriba);

            // Dentro de un modal con animación, 'fixed' se mide desde el modal: se corrige el desfase
            var real = panel.getBoundingClientRect();
            var desfaseX = real.left - parseFloat(panel.style.left);
            var desfaseY = arriba ? real.bottom - (caja.top - 6) : real.top - (caja.bottom + 6);
            if (Math.abs(desfaseX) > 1) panel.style.left = (parseFloat(panel.style.left) - desfaseX) + 'px';
            if (Math.abs(desfaseY) > 1) {
                if (arriba) {
                    panel.style.bottom = (parseFloat(panel.style.bottom) + desfaseY) + 'px';
                } else {
                    panel.style.top = (parseFloat(panel.style.top) - desfaseY) + 'px';
                }
            }
        }

        function cerrar() {
            panel.hidden = true;
            envoltura.classList.remove('is-abierto', 'abre-arriba');
            boton.setAttribute('aria-expanded', 'false');
            if (abierto && abierto.cerrar === cerrar) {
                abierto = null;
            }
        }

        boton.addEventListener('click', function () {
            if (panel.hidden) {
                abrir();
            } else {
                cerrar();
            }
        });

        boton.addEventListener('keydown', function (evento) {
            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].indexOf(evento.key) !== -1) {
                evento.preventDefault();
                abrir();
            } else if (evento.key.length === 1 && !evento.ctrlKey && !evento.metaKey && !evento.altKey) {
                // Escribir sobre el botón abre y empieza a buscar
                abrir();
                campo.value = evento.key;
                filtrar();
                evento.preventDefault();
            }
        });

        campo.addEventListener('input', filtrar);

        campo.addEventListener('keydown', function (evento) {
            if (evento.key === 'ArrowDown') {
                evento.preventDefault();
                marcarActivo(activo + 1);
            } else if (evento.key === 'ArrowUp') {
                evento.preventDefault();
                marcarActivo(activo - 1);
            } else if (evento.key === 'Enter') {
                evento.preventDefault();
                elegir(opcionesVisibles()[activo]);
            } else if (evento.key === 'Escape') {
                evento.preventDefault();
                cerrar();
                boton.focus();
            } else if (evento.key === 'Tab') {
                cerrar();
            }
        });

        lista.addEventListener('mousedown', function (evento) {
            evento.preventDefault();
        });

        lista.addEventListener('click', function (evento) {
            elegir(evento.target.closest('.sb-opcion'));
        });

        lista.addEventListener('mousemove', function (evento) {
            var item = evento.target.closest('.sb-opcion');
            if (item) {
                marcarActivo(opcionesVisibles().indexOf(item));
            }
        });

        // Cambios hechos por otros scripts: opciones nuevas, habilitar/deshabilitar, valor asignado
        new MutationObserver(function () {
            pintarBoton();
            if (!panel.hidden) {
                construirLista();
                filtrar();
            }
        }).observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled'] });

        ['value', 'selectedIndex'].forEach(function (propiedad) {
            var original = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, propiedad);
            Object.defineProperty(select, propiedad, {
                configurable: true,
                get: function () {
                    return original.get.call(this);
                },
                set: function (valor) {
                    original.set.call(this, valor);
                    pintarBoton();
                }
            });
        });

        select.addEventListener('change', pintarBoton);

        if (select.form) {
            select.form.addEventListener('reset', function () {
                window.setTimeout(pintarBoton, 0);
            });
        }

        // Si el formulario no pasa la validación, se marca el botón
        select.addEventListener('invalid', function () {
            envoltura.classList.add('is-invalido');
            boton.focus();
        });
        select.addEventListener('change', function () {
            envoltura.classList.remove('is-invalido');
        });

        pintarBoton();
    }

    function mejorarTodos(raiz) {
        Array.prototype.forEach.call((raiz || document).querySelectorAll(SELECTOR), mejorar);
    }

    document.addEventListener('click', function (evento) {
        if (abierto && !evento.target.closest('.sb.is-abierto')) {
            cerrarAbierto();
        }
    });

    function reubicarAbierto() {
        if (abierto && abierto.posicionar) {
            abierto.posicionar();
        }
    }

    window.addEventListener('resize', reubicarAbierto);
    window.addEventListener('scroll', function (evento) {
        // El scroll dentro de la propia lista no la mueve
        if (evento.target && evento.target.closest && evento.target.closest('.sb-panel')) return;
        reubicarAbierto();
    }, true);

    window.selectBuscador = { mejorar: mejorar, mejorarTodos: mejorarTodos };

    function vigilarNuevos() {
        new MutationObserver(function (cambios) {
            cambios.forEach(function (cambio) {
                Array.prototype.forEach.call(cambio.addedNodes, function (nodo) {
                    if (nodo.nodeType !== 1 || nodo.classList.contains('sb')) return;
                    if (nodo.matches(SELECTOR)) {
                        mejorar(nodo);
                    } else if (nodo.querySelector('select')) {
                        mejorarTodos(nodo);
                    }
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    function arrancar() {
        mejorarTodos();
        vigilarNuevos();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', arrancar);
    } else {
        arrancar();
    }
}());
