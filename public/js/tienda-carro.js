/*
 * Carro de compras: panel lateral (icono del carro) y pagina del carro (tienda/carro).
 * Cada producto tiene su contador (- cantidad +) y boton eliminar; los cambios se guardan
 * al instante sin recargar y se reflejan en el panel, la pagina y el numero del icono.
 *
 * Una linea de producto es cualquier elemento [data-carro-linea] con:
 *   data-url (POST cantidad), data-id, data-precio, [data-qty-campo], [data-qty-paso], [data-carro-quitar], [data-carro-linea-total]
 */
(function () {
    'use strict';

    var disparador = document.querySelector('.shop-cart');

    if (!disparador) {
        return;
    }

    var panel = document.getElementById('carro-panel');
    var cuerpo = panel ? panel.querySelector('[data-carro-cuerpo]') : null;
    var pie = panel ? panel.querySelector('[data-carro-pie]') : null;
    var plantilla = panel ? panel.querySelector('[data-carro-plantilla]') : null;
    var pagina = document.querySelector('[data-carro-pagina]');
    var urlCarro = disparador.getAttribute('href');
    var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var ESPERA_GUARDADO = 380; // mientras la persona sigue presionando + o - no se guarda cada clic
    var secuencia = 0;
    var ultimaAplicada = 0;
    var pendientes = {};
    var focoPrevio = null;

    /* ---------- Utilidades ---------- */
    function pesos(monto) {
        return '$' + String(Math.round(monto || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function elemento(etiqueta, clase, contenido) {
        var nodo = document.createElement(etiqueta);
        if (clase) {
            nodo.className = clase;
        }
        if (contenido) {
            nodo.textContent = contenido;
        }
        return nodo;
    }

    function leerJson(respuesta) {
        if (!respuesta.ok) {
            throw new Error('respuesta ' + respuesta.status);
        }
        return respuesta.json();
    }

    function pedirCarro() {
        return fetch(urlCarro, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(leerJson);
    }

    function guardarCantidad(url, cantidad) {
        var datos = new FormData();
        datos.append('_token', token);
        datos.append('cantidad', cantidad);

        return fetch(url, {
            method: 'POST',
            body: datos,
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(leerJson);
    }

    /* ---------- Contador del icono del carro ---------- */
    function actualizarContador(unidades) {
        var contador = disparador.querySelector('.shop-cart-count');

        if (unidades > 0) {
            if (!contador) {
                contador = elemento('span', 'shop-cart-count', '');
                disparador.appendChild(contador);
            }
            contador.textContent = unidades;
            return;
        }

        if (contador) {
            contador.remove();
        }
    }

    /* ---------- Lineas de producto (panel y pagina) ---------- */
    function campoDe(linea) {
        return linea.querySelector('[data-qty-campo]');
    }

    function limitar(campo, valor) {
        var maximo = parseInt(campo.getAttribute('max'), 10) || Infinity;
        return Math.min(maximo, Math.max(1, isNaN(valor) ? 1 : valor));
    }

    function revisarBotones(linea) {
        var campo = campoDe(linea);
        var valor = parseInt(campo.value, 10) || 1;
        var maximo = parseInt(campo.getAttribute('max'), 10) || Infinity;

        Array.prototype.forEach.call(linea.querySelectorAll('[data-qty-paso]'), function (boton) {
            boton.disabled = parseInt(boton.getAttribute('data-qty-paso'), 10) < 0 ? valor <= 1 : valor >= maximo;
        });
    }

    // Actualiza el total de la linea al momento; luego el servidor confirma.
    function totalLocal(linea, cantidad) {
        var total = linea.querySelector('[data-carro-linea-total]');
        if (total) {
            total.textContent = pesos((parseInt(linea.dataset.precio, 10) || 0) * cantidad);
        }
    }

    function programarGuardado(linea, cantidad, inmediato) {
        var url = linea.dataset.url;

        clearTimeout(pendientes[url]);
        linea.classList.add('is-actualizando');

        function enviar() {
            pendientes[url] = null;
            var numero = ++secuencia;

            guardarCantidad(url, cantidad)
                .then(function (datos) {
                    aplicar(datos, numero);
                    if (cantidad === 0 && datos.mensaje && window.notificar) {
                        window.notificar({ estilo: 'carrito', mensaje: datos.mensaje });
                    }
                })
                .catch(function () {
                    if (window.notificar) {
                        window.notificar('No pudimos actualizar tu carro. Inténtalo nuevamente.', 'error');
                    }
                    pedirCarro().then(function (datos) {
                        aplicar(datos, ++secuencia);
                    });
                })
                .then(function () {
                    linea.classList.remove('is-actualizando');
                });
        }

        if (inmediato) {
            enviar();
        } else {
            pendientes[url] = setTimeout(enviar, ESPERA_GUARDADO);
        }
    }

    function quitarLinea(linea) {
        linea.classList.add('is-saliendo');
        programarGuardado(linea, 0, true);
    }

    // Deja cada linea igual a lo guardado; quita las que ya no estan en el carro.
    function sincronizarLineas(contenedor, datos) {
        var porId = {};
        datos.items.forEach(function (item) {
            porId[item.id] = item;
        });

        Array.prototype.forEach.call(contenedor.querySelectorAll('[data-carro-linea]'), function (linea) {
            var item = porId[linea.dataset.id];

            if (!item) {
                linea.classList.add('is-saliendo');
                setTimeout(function () {
                    linea.remove();
                }, 230);
                return;
            }

            linea.classList.remove('is-saliendo');
            var campo = campoDe(linea);
            campo.setAttribute('max', item.maximo);
            if (!pendientes[linea.dataset.url] && document.activeElement !== campo) {
                campo.value = item.cantidad;
            }
            revisarBotones(linea);

            var total = linea.querySelector('[data-carro-linea-total]');
            if (total && !pendientes[linea.dataset.url]) {
                total.textContent = pesos(item.total);
            }
        });
    }

    /* ---------- Aplicar la respuesta del servidor ---------- */
    function aplicar(datos, numero) {
        // Si llegan respuestas desordenadas, solo vale la mas reciente.
        if (numero < ultimaAplicada) {
            return;
        }
        ultimaAplicada = numero;

        actualizarContador(datos.unidades);

        if (panelAbierto()) {
            if (!datos.items.length) {
                pintarPanelVacio();
            } else {
                sincronizarLineas(cuerpo, datos);
                pintarTotalesPanel(datos);
            }
        }

        if (pagina) {
            sincronizarPagina(datos);
        }
    }

    /* ---------- Pagina del carro ---------- */
    function sincronizarPagina(datos) {
        sincronizarLineas(pagina, datos);

        var unidades = pagina.querySelector('[data-carro-texto-unidades]');
        if (unidades) {
            unidades.textContent = datos.unidades
                ? 'Tienes ' + datos.unidades + (datos.unidades === 1 ? ' producto listo' : ' productos listos') + ' para pagar.'
                : 'Aún no agregas productos.';
        }

        if (!datos.items.length) {
            var contenido = pagina.querySelector('[data-carro-contenido]');
            if (contenido) {
                contenido.remove();
            }
            // Sin productos ya no hay pasos que mostrar
            var pasos = pagina.querySelector('.pasos-compra');
            if (pasos) {
                pasos.remove();
            }
            pagina.querySelector('[data-carro-vacio]').hidden = false;
            return;
        }

        escribir(pagina, '[data-carro-subtotal]', pesos(datos.subtotal));
        escribir(pagina, '[data-carro-total]', pesos(datos.total));

        var envio = pagina.querySelector('[data-carro-envio]');
        if (envio) {
            envio.textContent = datos.costoEnvio ? pesos(datos.costoEnvio) : 'Gratis';
            envio.classList.toggle('es-gratis', !datos.costoEnvio);
        }

        var bloque = pagina.querySelector('[data-carro-envio-gratis]');
        if (bloque) {
            pintarEnvioGratis(bloque, datos);
        }
    }

    function escribir(contenedor, selector, texto) {
        var nodo = contenedor.querySelector(selector);
        if (nodo) {
            nodo.textContent = texto;
        }
    }

    // "Te faltan $X para el envio gratis" con su barra de avance.
    function pintarEnvioGratis(bloque, datos) {
        var falta = datos.faltaEnvioGratis || 0;
        var meta = datos.subtotal + falta;
        var texto = bloque.querySelector('[data-carro-envio-texto]');
        var barra = bloque.querySelector('[data-carro-envio-barra]');

        bloque.classList.toggle('is-logrado', falta === 0);
        texto.innerHTML = '';
        if (falta > 0) {
            texto.appendChild(document.createTextNode('Te faltan '));
            texto.appendChild(elemento('strong', '', pesos(falta)));
            texto.appendChild(document.createTextNode(' para el envío gratis.'));
        } else {
            texto.textContent = '¡Tienes envío gratis!';
        }
        barra.style.setProperty('--avance', (meta > 0 ? Math.min(100, Math.round(datos.subtotal / meta * 100)) : 100) + '%');
    }

    if (pagina) {
        Array.prototype.forEach.call(pagina.querySelectorAll('[data-carro-linea]'), revisarBotones);
    }

    /* ---------- Panel lateral ---------- */
    function panelAbierto() {
        return panel && panel.getAttribute('aria-hidden') === 'false';
    }

    function abrir(evento) {
        if (evento) {
            evento.preventDefault();
        }

        focoPrevio = document.activeElement;
        panel.setAttribute('aria-hidden', 'false');
        document.body.classList.add('carro-abierto');

        var cerrarBoton = panel.querySelector('[data-carro-cerrar]');
        if (cerrarBoton) {
            cerrarBoton.focus();
        }

        cuerpo.innerHTML = '<div class="carro-esqueleto" aria-hidden="true"><span></span><span></span><span></span></div>' +
            '<p class="carro-aviso">Cargando tu carro…</p>';
        pie.hidden = true;

        var numero = ++secuencia;
        pedirCarro()
            .then(function (datos) {
                ultimaAplicada = numero;
                actualizarContador(datos.unidades);
                pintarPanel(datos);
            })
            .catch(function () {
                cuerpo.innerHTML = '<p class="carro-aviso">No pudimos cargar tu carro. <a href="' + urlCarro + '">Abrir el carro completo</a>.</p>';
            });
    }

    function cerrar() {
        panel.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('carro-abierto');

        if (focoPrevio && typeof focoPrevio.focus === 'function') {
            focoPrevio.focus();
        }
    }

    function pintarPanelVacio() {
        cuerpo.innerHTML = '<div class="carro-vacio">' +
            '<p class="carro-vacio-titulo">Tu carro está vacío</p>' +
            '<p class="carro-aviso">Agrega productos desde el catálogo para verlos aquí.</p>' +
            '</div>';
        pie.hidden = true;
    }

    function pintarPanel(datos) {
        if (!datos.items.length) {
            pintarPanelVacio();
            return;
        }

        var lista = elemento('ul', 'carro-lista', '');
        datos.items.forEach(function (item) {
            lista.appendChild(lineaPanel(item));
        });

        cuerpo.innerHTML = '';
        cuerpo.appendChild(lista);
        pintarTotalesPanel(datos);
        pie.hidden = false;
    }

    function lineaPanel(item) {
        var linea = plantilla.content.firstElementChild.cloneNode(true);
        linea.dataset.url = item.url;
        linea.dataset.id = item.id;
        linea.dataset.precio = item.precio;

        var foto = linea.querySelector('.carro-item-foto');
        var imagen = foto.querySelector('img');
        if (item.foto) {
            imagen.src = item.foto;
            imagen.alt = item.nombre;
            var icono = foto.querySelector('.isdi');
            if (icono) {
                icono.remove();
            }
        } else {
            imagen.remove();
        }

        linea.querySelector('[data-campo="nombre"]').textContent = item.nombre;
        var detalle = linea.querySelector('[data-campo="detalle"]');
        if (item.detalle) {
            detalle.textContent = item.detalle;
        } else {
            detalle.remove();
        }
        var precio = linea.querySelector('[data-campo="precio"]');
        precio.textContent = pesos(item.precio) + ' c/u';
        // Producto del Outlet: muestra tambien el precio normal tachado.
        if (item.precioNormal) {
            precio.appendChild(document.createTextNode(' '));
            precio.appendChild(elemento('s', 'carro-item-precio-normal', pesos(item.precioNormal)));
        }

        var campo = campoDe(linea);
        campo.value = item.cantidad;
        campo.setAttribute('max', item.maximo);
        campo.setAttribute('aria-label', 'Cantidad de ' + item.nombre);
        linea.querySelector('[data-qty-paso="-1"]').setAttribute('aria-label', 'Quitar una unidad de ' + item.nombre);
        linea.querySelector('[data-qty-paso="1"]').setAttribute('aria-label', 'Agregar una unidad de ' + item.nombre);
        linea.querySelector('[data-carro-quitar]').setAttribute('aria-label', 'Eliminar ' + item.nombre + ' del carro');
        linea.querySelector('[data-carro-linea-total]').textContent = pesos(item.total);
        revisarBotones(linea);

        return linea;
    }

    function pintarTotalesPanel(datos) {
        var totales = pie.querySelector('[data-carro-totales]');
        totales.innerHTML = '';

        var envioGratis = elemento('div', 'carro-envio-gratis', '');
        envioGratis.appendChild(elemento('span', '', ''));
        envioGratis.firstChild.setAttribute('data-carro-envio-texto', '');
        var barra = elemento('span', 'barra', '');
        var avance = elemento('span', '', '');
        avance.setAttribute('data-carro-envio-barra', '');
        barra.appendChild(avance);
        envioGratis.appendChild(barra);
        pintarEnvioGratis(envioGratis, datos);
        totales.appendChild(envioGratis);

        totales.appendChild(lineaTotal('carro-total-linea', 'Subtotal', pesos(datos.subtotal)));
        totales.appendChild(lineaTotal('carro-total-linea', 'Envío', datos.costoEnvio ? pesos(datos.costoEnvio) : 'Gratis'));
        totales.appendChild(lineaTotal('carro-total-linea carro-total-linea--fuerte', 'Total', pesos(datos.total)));
    }

    function lineaTotal(clase, etiqueta, valor) {
        var linea = elemento('div', clase, '');
        linea.appendChild(elemento('span', '', etiqueta));
        linea.appendChild(elemento('strong', '', valor));
        return linea;
    }

    /* ---------- Eventos ---------- */
    if (panel && cuerpo && pie && plantilla) {
        disparador.addEventListener('click', abrir);

        panel.addEventListener('click', function (evento) {
            if (evento.target.closest('[data-carro-cerrar]')) {
                evento.preventDefault();
                cerrar();
            }
        });

        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape' && panelAbierto()) {
                cerrar();
            }
        });
    }

    // Contador (- +) y eliminar, tanto en el panel como en la pagina.
    document.addEventListener('click', function (evento) {
        var linea = evento.target.closest('[data-carro-linea]');

        if (!linea) {
            return;
        }

        var paso = evento.target.closest('[data-qty-paso]');
        if (paso && !paso.disabled) {
            var campo = campoDe(linea);
            var nuevo = limitar(campo, (parseInt(campo.value, 10) || 1) + parseInt(paso.getAttribute('data-qty-paso'), 10));
            campo.value = nuevo;
            revisarBotones(linea);
            totalLocal(linea, nuevo);
            programarGuardado(linea, nuevo, false);
            return;
        }

        if (evento.target.closest('[data-carro-quitar]')) {
            quitarLinea(linea);
        }
    });

    // Cantidad escrita a mano: se guarda al salir del campo o con Enter.
    document.addEventListener('change', function (evento) {
        var campo = evento.target.closest('[data-carro-linea] [data-qty-campo]');

        if (!campo) {
            return;
        }

        var linea = campo.closest('[data-carro-linea]');
        var valor = limitar(campo, parseInt(campo.value, 10));
        campo.value = valor;
        revisarBotones(linea);
        totalLocal(linea, valor);
        programarGuardado(linea, valor, true);
    });

    // En la pagina del carro, Enter en una cantidad guarda sin enviar el formulario completo.
    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Enter' && evento.target.closest('[data-carro-linea] [data-qty-campo]')) {
            evento.preventDefault();
            evento.target.blur();
        }
    });
}());
