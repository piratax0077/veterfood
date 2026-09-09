/* Panel lateral del carro */
(function () {
    'use strict';

    var panel = document.getElementById('carro-panel');
    var disparador = document.querySelector('.shop-cart');

    if (!panel || !disparador) {
        return;
    }

    var cuerpo = panel.querySelector('[data-carro-cuerpo]');
    var pie = panel.querySelector('[data-carro-pie]');
    var urlCarro = disparador.getAttribute('href');
    var focoPrevio = null;
    var cargando = false;

    /* Apertura y cierre */
    function abrir(evento) {
        if (evento) {
            evento.preventDefault();
        }

        focoPrevio = document.activeElement;
        panel.setAttribute('aria-hidden', 'false');
        document.body.classList.add('carro-abierto');

        var cerrar = panel.querySelector('[data-carro-cerrar]');
        if (cerrar) {
            cerrar.focus();
        }

        cargarResumen();
    }

    function cerrar() {
        panel.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('carro-abierto');

        if (focoPrevio && typeof focoPrevio.focus === 'function') {
            focoPrevio.focus();
        }
    }

    /* Lectura del carro */
    function cargarResumen() {
        if (cargando) {
            return;
        }

        cargando = true;
        cuerpo.innerHTML = plantillaCargando();
        pie.hidden = true;

        fetch(urlCarro, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error('respuesta ' + respuesta.status);
                }
                return respuesta.text();
            })
            .then(function (html) {
                pintar(new DOMParser().parseFromString(html, 'text/html'));
            })
            .catch(function () {
                cuerpo.innerHTML = '<p class="carro-aviso">No pudimos cargar el resumen. ' +
                    '<a href="' + urlCarro + '">Abrir el carro completo</a>.</p>';
                pie.hidden = true;
            })
            .then(function () {
                cargando = false;
            });
    }

    function pintar(doc) {
        var filas = doc.querySelectorAll('main table tbody tr');

        if (!filas.length) {
            cuerpo.innerHTML = '<div class="carro-vacio">' +
                '<p class="carro-vacio-titulo">Tu carro está vacío</p>' +
                '<p class="carro-aviso">Agrega productos desde el catálogo para verlos aquí.</p>' +
                '</div>';
            pie.hidden = true;
            actualizarContador(0);
            return;
        }

        var lista = document.createElement('ul');
        lista.className = 'carro-lista';
        var unidades = 0;

        Array.prototype.forEach.call(filas, function (fila) {
            var celdas = fila.querySelectorAll('td');
            if (celdas.length < 5) {
                return;
            }

            var campoCantidad = celdas[3].querySelector('input');
            var cantidad = campoCantidad ? parseInt(campoCantidad.value, 10) || 0 : 0;
            unidades += cantidad;

            var item = document.createElement('li');
            item.className = 'carro-item';
            item.appendChild(bloqueProducto(celdas[0], celdas[2], cantidad));
            item.appendChild(elemento('span', 'carro-item-total', texto(celdas[4])));
            lista.appendChild(item);
        });

        cuerpo.innerHTML = '';
        cuerpo.appendChild(lista);

        pintarTotales(doc);
        pie.hidden = false;
        actualizarContador(unidades);
    }

    function bloqueProducto(celdaProducto, celdaPrecio, cantidad) {
        var envoltorio = document.createElement('div');
        envoltorio.className = 'carro-item-datos';

        var nombre = celdaProducto.querySelector('strong');
        envoltorio.appendChild(elemento('p', 'carro-item-nombre', nombre ? nombre.textContent.trim() : ''));

        var marca = celdaProducto.querySelector('.muted');
        if (marca && marca.textContent.trim()) {
            envoltorio.appendChild(elemento('p', 'carro-item-marca', marca.textContent.trim()));
        }

        envoltorio.appendChild(elemento('p', 'carro-item-detalle', cantidad + ' x ' + texto(celdaPrecio)));

        return envoltorio;
    }

    function pintarTotales(doc) {
        var bloque = doc.querySelector('main table');
        var contenedor = bloque ? bloque.closest('.card') : null;
        var totales = pie.querySelector('[data-carro-totales]');

        totales.innerHTML = '';

        if (!contenedor) {
            return;
        }

        Array.prototype.forEach.call(contenedor.querySelectorAll('p'), function (parrafo) {
            var partes = parrafo.textContent.split(':');
            if (partes.length < 2) {
                return;
            }
            totales.appendChild(lineaTotal('carro-total-linea', partes[0].trim(), partes[1].trim()));
        });

        var total = contenedor.querySelector('h2');
        if (total) {
            var partesTotal = total.textContent.split(':');
            totales.appendChild(lineaTotal(
                'carro-total-linea carro-total-linea--fuerte',
                partesTotal[0].trim(),
                (partesTotal[1] || '').trim()
            ));
        }
    }

    /* Utilidades */
    function lineaTotal(clase, etiqueta, valor) {
        var linea = elemento('div', clase, '');
        linea.appendChild(elemento('span', '', etiqueta));
        linea.appendChild(elemento('strong', '', valor));
        return linea;
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

    function texto(celda) {
        return celda ? celda.textContent.trim() : '';
    }

    function plantillaCargando() {
        return '<div class="carro-esqueleto" aria-hidden="true">' +
            '<span></span><span></span><span></span>' +
            '</div><p class="carro-aviso">Cargando tu carro…</p>';
    }

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

    /* Eventos */
    disparador.addEventListener('click', abrir);

    panel.addEventListener('click', function (evento) {
        if (evento.target.closest('[data-carro-cerrar]')) {
            evento.preventDefault();
            cerrar();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && panel.getAttribute('aria-hidden') === 'false') {
            cerrar();
        }
    });
}());
