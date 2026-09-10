/*
 * Muestra unas patitas caminando mientras se carga la tienda.
 * Se activa al presionar cualquier enlace hacia la tienda (desde fuera de ella)
 * o un formulario/boton marcado con data-cargando-tienda.
 * La direccion base de la tienda viene en <body data-url-tienda="...">.
 */
(function () {
    // Tiempo que se ven las patitas antes de pasar a la tienda (alcanzan a aparecer las 5 huellas).
    var TIEMPO_PATITAS = 1600;
    var capa = null;
    var respaldo = null;
    var saliendo = false;

    function crearCapa() {
        capa = document.createElement('div');
        capa.className = 'cargando-tienda';
        capa.setAttribute('role', 'status');
        capa.setAttribute('aria-live', 'polite');
        capa.innerHTML = '<div class="cargando-tienda-caja">'
            + '<div class="patitas" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>'
            + '<p class="cargando-tienda-texto">Cargando la tienda…</p>'
            + '</div>';
        document.body.appendChild(capa);
    }

    function mostrar() {
        if (!capa) crearCapa();
        // Leer el tamano fuerza al navegador a aplicar el estado inicial, asi la entrada se anima de inmediato.
        void capa.offsetWidth;
        capa.classList.add('is-visible');
        clearTimeout(respaldo);
        // Si la pagina no cambia (sin conexion, descarga, etc.) se oculta sola.
        respaldo = setTimeout(ocultar, 10000);
    }

    function ocultar() {
        clearTimeout(respaldo);
        saliendo = false;
        if (capa) capa.classList.remove('is-visible');
    }

    function esTienda(url) {
        var base = document.body.dataset.urlTienda;
        if (!base || !url) return false;
        return url === base || url.indexOf(base + '/') === 0 || url.indexOf(base + '?') === 0 || url.indexOf(base + '#') === 0;
    }

    function estoyEnTienda() {
        return (document.body.dataset.route || '').indexOf('tienda.') === 0;
    }

    document.addEventListener('click', function (evento) {
        if (evento.defaultPrevented || evento.button !== 0 || evento.metaKey || evento.ctrlKey || evento.shiftKey || evento.altKey) return;
        var enlace = evento.target.closest('a[href]');
        if (!enlace || enlace.target === '_blank' || enlace.hasAttribute('download')) return;
        if (estoyEnTienda() && !enlace.hasAttribute('data-cargando-tienda')) return;
        if (!esTienda(enlace.href) && !enlace.hasAttribute('data-cargando-tienda')) return;

        evento.preventDefault();
        var destino = enlace.href;
        irConPatitas(function () { window.location.href = destino; });
    });

    document.addEventListener('submit', function (evento) {
        var formulario = evento.target;
        if (evento.defaultPrevented || !formulario.hasAttribute('data-cargando-tienda')) return;

        evento.preventDefault();
        irConPatitas(function () { HTMLFormElement.prototype.submit.call(formulario); });
    });

    // Muestra las patitas un momento y luego continua hacia la tienda (evita dobles clics).
    function irConPatitas(continuar) {
        if (saliendo) return;
        saliendo = true;
        mostrar();
        setTimeout(continuar, TIEMPO_PATITAS);
    }

    // Al volver con el boton "atras" el navegador puede restaurar la pagina con la capa visible.
    window.addEventListener('pageshow', function () {
        saliendo = false;
        ocultar();
    });

    window.cargandoTienda = {mostrar: mostrar, ocultar: ocultar};
}());
