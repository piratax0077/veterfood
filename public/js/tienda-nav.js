/* Menu de la tienda */
(function () {
    'use strict';

    var bloque = document.querySelector('[data-shop-sticky]');

    if (!bloque) {
        return;
    }

    /* Efecto de scroll */
    var ticking = false;

    function revisarScroll() {
        bloque.classList.toggle('is-scrolled', window.scrollY > 12);
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            ticking = true;
            window.requestAnimationFrame(revisarScroll);
        }
    }, { passive: true });

    revisarScroll();

    /* Menu movil */
    var menu = bloque.querySelector('.shop-menu');
    var disparador = bloque.querySelector('[data-menu-toggle]');

    if (!menu || !disparador) {
        return;
    }

    var esMovil = window.matchMedia('(max-width: 960px)');

    function cerrarTodo() {
        menu.classList.remove('is-open');
        disparador.setAttribute('aria-expanded', 'false');

        Array.prototype.forEach.call(menu.querySelectorAll('li.is-open'), function (item) {
            item.classList.remove('is-open');
        });
    }

    disparador.addEventListener('click', function () {
        var abierto = menu.classList.toggle('is-open');
        disparador.setAttribute('aria-expanded', abierto ? 'true' : 'false');

        if (!abierto) {
            cerrarTodo();
        }
    });

    menu.addEventListener('click', function (evento) {
        if (!esMovil.matches) {
            return;
        }

        var enlace = evento.target.closest('a[aria-haspopup]');

        if (!enlace) {
            return;
        }

        var item = enlace.parentElement;

        if (!item.querySelector('.shop-sub')) {
            return;
        }

        evento.preventDefault();

        var abierto = item.classList.contains('is-open');

        Array.prototype.forEach.call(menu.querySelectorAll('li.is-open'), function (otro) {
            otro.classList.remove('is-open');
        });

        if (!abierto) {
            item.classList.add('is-open');
        }
    });

    document.addEventListener('click', function (evento) {
        if (esMovil.matches && !evento.target.closest('.shop-menu')) {
            cerrarTodo();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrarTodo();
        }
    });

    esMovil.addEventListener('change', cerrarTodo);
}());

/* Ubicacion de despacho: region y comuna elegidas en el menu superior */
(function () {
    'use strict';

    var formulario = document.querySelector('[data-ubicacion-form]');

    if (!formulario) {
        return;
    }

    var region = formulario.querySelector('[data-ubicacion-region]');
    var ciudad = formulario.querySelector('[data-ubicacion-ciudad]');
    var texto = document.querySelector('[data-ubicacion-texto]');
    var desplegable = formulario.closest('[data-shop-drop]');
    var regionesCargadas = false;

    function llenar(select, lista, primeraOpcion, seleccion) {
        select.innerHTML = '';
        select.appendChild(new Option(primeraOpcion, ''));
        lista.forEach(function (item) {
            var opcion = new Option(item.nombre, item.id);
            opcion.selected = String(item.id) === String(seleccion || '');
            select.appendChild(opcion);
        });
    }

    function pedirJson(url) {
        return fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error(respuesta.status);
                }
                return respuesta.json();
            });
    }

    function cargarCiudades(regionId, seleccion) {
        ciudad.disabled = true;

        if (!regionId) {
            llenar(ciudad, [], 'Selecciona una región', '');
            return;
        }

        ciudad.innerHTML = '<option value="">Cargando comunas…</option>';
        pedirJson(formulario.dataset.urlCiudades.replace('__REGION__', regionId))
            .then(function (lista) {
                llenar(ciudad, lista, 'Selecciona una comuna', seleccion);
                ciudad.disabled = false;
            })
            .catch(function () {
                ciudad.innerHTML = '<option value="">No pudimos cargar las comunas</option>';
            });
    }

    function cargarRegiones() {
        if (regionesCargadas) {
            return;
        }
        regionesCargadas = true;

        pedirJson(formulario.dataset.urlRegiones)
            .then(function (lista) {
                llenar(region, lista, 'Selecciona una región', region.dataset.seleccion);
                if (region.value) {
                    cargarCiudades(region.value, ciudad.dataset.seleccion);
                }
            })
            .catch(function () {
                regionesCargadas = false;
                region.innerHTML = '<option value="">No pudimos cargar las regiones</option>';
            });
    }

    // Las regiones se piden solo cuando la persona abre el desplegable.
    ['mouseenter', 'focusin', 'click'].forEach(function (tipo) {
        desplegable.addEventListener(tipo, cargarRegiones);
    });

    region.addEventListener('change', function () {
        cargarCiudades(region.value, '');
    });

    formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();
        var boton = formulario.querySelector('button[type="submit"]');
        boton.disabled = true;

        fetch(formulario.action, {
            method: 'POST',
            body: new FormData(formulario),
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (respuesta) {
                return respuesta.json().then(function (datos) {
                    if (!respuesta.ok) {
                        throw new Error(datos.message || 'No pudimos guardar tu ubicación.');
                    }
                    return datos;
                });
            })
            .then(function (datos) {
                texto.textContent = datos.ubicacion.ciudad;
                region.dataset.seleccion = datos.ubicacion.region_id;
                ciudad.dataset.seleccion = datos.ubicacion.ciudad_id;
                desplegable.classList.remove('is-open');
                desplegable.classList.add('is-silenciado');
                if (document.activeElement) {
                    document.activeElement.blur();
                }
                if (window.notificar) {
                    window.notificar({ tipo: 'exito', titulo: 'Ubicación guardada', mensaje: datos.message });
                }
            })
            .catch(function (error) {
                if (window.notificar) {
                    window.notificar(error.message || 'No pudimos guardar tu ubicación.', 'error');
                }
            })
            .then(function () {
                boton.disabled = false;
            });
    });
}());
