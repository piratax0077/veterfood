/*
 * Desplegables del menu superior (.shop-drop): Ubicacion, Mi cuenta, Ingresa o registrate.
 * Con mouse se abren al pasar por encima (CSS). Este archivo agrega el toque en pantallas tactiles,
 * el cierre al tocar fuera y la tecla Escape.
 */
(function () {
    var conMouse = window.matchMedia('(hover: hover)');

    function disparadorDe(desplegable) {
        return desplegable.querySelector('[data-shop-drop-trigger]');
    }

    function cerrarTodos(excepto) {
        document.querySelectorAll('[data-shop-drop].is-open').forEach(function (desplegable) {
            if (desplegable === excepto) return;
            desplegable.classList.remove('is-open');
            var disparador = disparadorDe(desplegable);
            if (disparador) disparador.setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('click', function (evento) {
        var disparador = evento.target.closest('[data-shop-drop-trigger]');

        if (disparador) {
            var desplegable = disparador.closest('[data-shop-drop]');
            desplegable.classList.remove('is-silenciado');
            // Los enlaces (ej. "Mi cuenta") navegan con el mouse. En pantallas tactiles el primer toque abre el menu,
            // salvo en el menu del sitio en movil, donde el panel quedaria recortado y conviene navegar directo.
            if (disparador.tagName === 'A' && (conMouse.matches || !disparador.closest('.shop-actions'))) return;

            evento.preventDefault();
            var abrir = !desplegable.classList.contains('is-open');
            cerrarTodos(desplegable);
            desplegable.classList.toggle('is-open', abrir);
            disparador.setAttribute('aria-expanded', abrir ? 'true' : 'false');
            return;
        }

        if (!evento.target.closest('[data-shop-drop]')) cerrarTodos(null);
    });

    // Escape cierra el desplegable y devuelve el foco a su boton; queda "silenciado" hasta que el foco o el mouse salgan.
    document.addEventListener('keydown', function (evento) {
        if (evento.key !== 'Escape') return;
        var activo = document.activeElement;
        var desplegable = activo && activo.closest ? activo.closest('[data-shop-drop]') : null;
        cerrarTodos(null);
        if (!desplegable) return;
        desplegable.classList.add('is-silenciado');
        var disparador = disparadorDe(desplegable);
        if (disparador) disparador.focus();
    });

    function quitarSilencio(evento) {
        var desplegable = evento.currentTarget;
        if (evento.type === 'focusout' && desplegable.contains(evento.relatedTarget)) return;
        desplegable.classList.remove('is-silenciado');
    }

    document.querySelectorAll('[data-shop-drop]').forEach(function (desplegable) {
        desplegable.addEventListener('focusout', quitarSilencio);
        desplegable.addEventListener('mouseleave', quitarSilencio);
    });
}());
