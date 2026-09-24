/*
 * Notificaciones globales del sistema.
 *
 * Desde PHP (controladores): ->with('ok'|'info'|'neutral'|'aviso'|'error', 'Mensaje')
 *   o ->with('notificacion', ['tipo' => 'info', 'titulo' => '...', 'mensaje' => '...', 'duracion' => 6000])
 * Desde JavaScript: notificar('Mensaje', 'exito')  o  notificar({tipo, titulo, mensaje, duracion, estilo})
 * Tipos: exito, info, neutral, advertencia, error. duracion: 0 = no se cierra sola.
 *
 * Los avisos del carro (estilo: 'carrito') salen abajo al centro, en negro; el resto abajo a la derecha.
 */
(function () {
    var ICONOS = {
        exito: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.6v.1"/></svg>',
        neutral: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15z"/><path d="M10 20.5a2.2 2.2 0 0 0 4 0"/></svg>',
        advertencia: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 4.2L2.8 17.5A2 2 0 0 0 4.5 20.5h15a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0z"/><path d="M12 9.5v4.5"/><path d="M12 17.3v.1"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg>',
        carrito: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9.5" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3.5h2.6l2.4 11.2h11l2-7.7H6.5"/></svg>'
    };
    var CERRAR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>';
    var TITULOS = {exito: 'Listo', info: 'Información', neutral: 'Aviso', advertencia: 'Atención', error: 'Hubo un problema'};
    var DURACIONES = {exito: 2600, info: 3000, neutral: 2800, advertencia: 3800, error: 4800};
    var DURACION_CARRITO = 2400;
    var ALIAS = {ok: 'exito', success: 'exito', status: 'exito', aviso: 'advertencia', warning: 'advertencia', danger: 'error'};
    var MAXIMO_VISIBLES = 4;
    var contenedores = {};

    // Dos listas: la del carro (abajo al centro) y la del resto (abajo a la derecha)
    function obtenerContenedor(esCarrito) {
        var llave = esCarrito ? 'carrito' : 'normal';

        if (!contenedores[llave]) {
            var nueva = document.createElement('div');
            nueva.className = 'notificaciones' + (esCarrito ? ' notificaciones--carrito' : '');
            nueva.setAttribute('aria-live', 'polite');
            contenedores[llave] = nueva;
        }

        // Con un modal abierto el aviso va dentro de el; si no, quedaria tapado por el modal.
        var modalesAbiertos = document.querySelectorAll('dialog.modal[open]');
        var destino = modalesAbiertos.length ? modalesAbiertos[modalesAbiertos.length - 1] : document.body;
        if (contenedores[llave].parentNode !== destino) destino.appendChild(contenedores[llave]);

        return contenedores[llave];
    }

    function cerrar(elemento) {
        if (!elemento || elemento.classList.contains('is-saliendo')) return;
        elemento.classList.add('is-saliendo');
        var quitar = function () { if (elemento.parentNode) elemento.parentNode.removeChild(elemento); };
        elemento.addEventListener('animationend', function (evento) {
            if (evento.target === elemento) quitar();
        });
        setTimeout(quitar, 450);
    }

    function crearElemento(etiqueta, clase, texto) {
        var nodo = document.createElement(etiqueta);
        nodo.className = clase;
        if (texto !== undefined) nodo.textContent = texto;
        return nodo;
    }

    // Avisos de productos que entran o salen del carro: van en negro abajo al centro
    function esDelCarrito(opciones, clase) {
        if (opciones.estilo === 'carrito') return true;
        if (clase === 'error') return false;
        return /carro|carrito/i.test((opciones.titulo || '') + ' ' + (opciones.mensaje || ''));
    }

    function notificar(opciones, tipo) {
        if (typeof opciones === 'string') opciones = {mensaje: opciones, tipo: tipo};
        opciones = opciones || {};
        var clase = ALIAS[opciones.tipo] || opciones.tipo;
        if (!TITULOS[clase]) clase = 'info';
        var esCarrito = esDelCarrito(opciones, clase);
        var duracion = typeof opciones.duracion === 'number'
            ? opciones.duracion
            : (esCarrito ? DURACION_CARRITO : DURACIONES[clase]);

        var notificacion = crearElemento('div', 'notificacion notificacion--' + clase + (esCarrito ? ' notificacion--carrito' : ''));
        notificacion.setAttribute('role', clase === 'error' ? 'alert' : 'status');

        var icono = crearElemento('span', 'notificacion-icono');
        icono.setAttribute('aria-hidden', 'true');
        icono.innerHTML = esCarrito ? ICONOS.carrito : ICONOS[clase];

        var texto = crearElemento('div', 'notificacion-texto');
        if (opciones.titulo || !esCarrito) {
            texto.appendChild(crearElemento('strong', 'notificacion-titulo', opciones.titulo || TITULOS[clase]));
        }
        if (opciones.mensaje) texto.appendChild(crearElemento('span', 'notificacion-mensaje', opciones.mensaje));

        var botonCerrar = crearElemento('button', 'notificacion-cerrar');
        botonCerrar.type = 'button';
        botonCerrar.setAttribute('aria-label', 'Cerrar notificación');
        botonCerrar.innerHTML = CERRAR;
        botonCerrar.addEventListener('click', function () { cerrar(notificacion); });

        notificacion.appendChild(icono);
        notificacion.appendChild(texto);
        notificacion.appendChild(botonCerrar);

        // Se cierra sola; mientras el mouse esta encima, el tiempo se detiene.
        if (duracion > 0) {
            var reloj = setTimeout(function () { cerrar(notificacion); }, duracion);
            notificacion.addEventListener('mouseenter', function () { clearTimeout(reloj); });
            notificacion.addEventListener('mouseleave', function () {
                reloj = setTimeout(function () { cerrar(notificacion); }, duracion);
            });
        }

        var lista = obtenerContenedor(esCarrito);
        lista.insertBefore(notificacion, lista.firstChild);

        var visibles = Array.prototype.filter.call(lista.children, function (nodo) {
            return !nodo.classList.contains('is-saliendo');
        });
        visibles.slice(MAXIMO_VISIBLES).forEach(cerrar);

        return notificacion;
    }

    window.notificar = notificar;

    document.addEventListener('keydown', function (evento) {
        if (evento.key !== 'Escape') return;
        Object.keys(contenedores).forEach(function (llave) {
            var lista = contenedores[llave];
            if (lista && lista.firstChild) cerrar(lista.firstChild);
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        var datos = document.getElementById('notificaciones-iniciales');
        if (!datos) return;
        var pendientes = [];
        try { pendientes = JSON.parse(datos.textContent) || []; } catch (e) { pendientes = []; }
        pendientes.forEach(function (aviso, indice) {
            setTimeout(function () { notificar(aviso); }, 150 + indice * 140);
        });
    });
}());
