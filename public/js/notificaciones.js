/*
 * Notificaciones globales del sistema.
 *
 * Desde PHP (controladores): ->with('ok'|'info'|'neutral'|'aviso'|'error', 'Mensaje')
 *   o ->with('notificacion', ['tipo' => 'info', 'titulo' => '...', 'mensaje' => '...', 'duracion' => 6000])
 * Desde JavaScript: notificar('Mensaje', 'exito')  o  notificar({tipo, titulo, mensaje, duracion})
 * Tipos: exito, info, neutral, advertencia, error. duracion: 0 = no se cierra sola.
 */
(function () {
    var ICONOS = {
        exito: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.6v.1"/></svg>',
        neutral: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15z"/><path d="M10 20.5a2.2 2.2 0 0 0 4 0"/></svg>',
        advertencia: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 4.2L2.8 17.5A2 2 0 0 0 4.5 20.5h15a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0z"/><path d="M12 9.5v4.5"/><path d="M12 17.3v.1"/></svg>',
        error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg>'
    };
    var CERRAR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>';
    var TITULOS = {exito: 'Listo', info: 'Información', neutral: 'Aviso', advertencia: 'Atención', error: 'Hubo un problema'};
    var DURACIONES = {exito: 4500, info: 5500, neutral: 5000, advertencia: 7000, error: 8000};
    var ALIAS = {ok: 'exito', success: 'exito', status: 'exito', aviso: 'advertencia', warning: 'advertencia', danger: 'error'};
    var MAXIMO_VISIBLES = 4;
    var contenedor = null;

    function obtenerContenedor() {
        if (!contenedor) {
            contenedor = document.createElement('div');
            contenedor.className = 'notificaciones';
            contenedor.setAttribute('aria-live', 'polite');
        }
        // Con un modal abierto el aviso va dentro de el; si no, quedaria tapado por el modal.
        var modalesAbiertos = document.querySelectorAll('dialog.modal[open]');
        var destino = modalesAbiertos.length ? modalesAbiertos[modalesAbiertos.length - 1] : document.body;
        if (contenedor.parentNode !== destino) destino.appendChild(contenedor);
        // Se ubica justo bajo el menu superior (que queda fijo al hacer scroll).
        var cabecera = document.querySelector('.shop-sticky, .nav');
        var borde = cabecera ? cabecera.getBoundingClientRect().bottom : 0;
        contenedor.style.top = Math.max(12, Math.round(borde) + 12) + 'px';
        return contenedor;
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

    function notificar(opciones, tipo) {
        if (typeof opciones === 'string') opciones = {mensaje: opciones, tipo: tipo};
        opciones = opciones || {};
        var clase = ALIAS[opciones.tipo] || opciones.tipo;
        if (!TITULOS[clase]) clase = 'info';
        var duracion = typeof opciones.duracion === 'number' ? opciones.duracion : DURACIONES[clase];

        var notificacion = crearElemento('div', 'notificacion notificacion--' + clase);
        notificacion.setAttribute('role', clase === 'error' ? 'alert' : 'status');

        var icono = crearElemento('span', 'notificacion-icono');
        icono.setAttribute('aria-hidden', 'true');
        icono.innerHTML = ICONOS[clase];

        var texto = crearElemento('div', 'notificacion-texto');
        texto.appendChild(crearElemento('strong', 'notificacion-titulo', opciones.titulo || TITULOS[clase]));
        if (opciones.mensaje) texto.appendChild(crearElemento('span', 'notificacion-mensaje', opciones.mensaje));

        var botonCerrar = crearElemento('button', 'notificacion-cerrar');
        botonCerrar.type = 'button';
        botonCerrar.setAttribute('aria-label', 'Cerrar notificación');
        botonCerrar.innerHTML = CERRAR;
        botonCerrar.addEventListener('click', function () { cerrar(notificacion); });

        notificacion.appendChild(icono);
        notificacion.appendChild(texto);
        notificacion.appendChild(botonCerrar);

        // La barra marca el tiempo restante; se pausa al pasar el mouse y al terminar cierra el aviso.
        if (duracion > 0) {
            var progreso = crearElemento('span', 'notificacion-progreso');
            progreso.style.setProperty('--duracion', duracion + 'ms');
            progreso.addEventListener('animationend', function () { cerrar(notificacion); });
            notificacion.appendChild(progreso);
        }

        var lista = obtenerContenedor();
        lista.insertBefore(notificacion, lista.firstChild);

        var visibles = Array.prototype.filter.call(lista.children, function (nodo) {
            return !nodo.classList.contains('is-saliendo');
        });
        visibles.slice(MAXIMO_VISIBLES).forEach(cerrar);

        return notificacion;
    }

    window.notificar = notificar;

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && contenedor && contenedor.firstChild) cerrar(contenedor.firstChild);
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
