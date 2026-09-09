/* Catalogo */
(function () {
    'use strict';

    var formulario = document.querySelector('[data-orden-form]');

    if (!formulario) {
        return;
    }

    var selector = formulario.querySelector('select');
    var boton = formulario.querySelector('[data-orden-enviar]');

    if (!selector) {
        return;
    }

    if (boton) {
        boton.hidden = true;
    }

    selector.addEventListener('change', function () {
        formulario.submit();
    });
}());

/* Selector de cantidad */
(function () {
    'use strict';

    var contenedores = document.querySelectorAll('[data-qty]');

    if (!contenedores.length) {
        return;
    }

    Array.prototype.forEach.call(contenedores, function (contenedor) {
        var campo = contenedor.querySelector('[data-qty-campo]');

        if (!campo) {
            return;
        }

        var minimo = parseInt(campo.getAttribute('min'), 10) || 1;
        var maximo = parseInt(campo.getAttribute('max'), 10) || Infinity;

        function ajustar(paso) {
            var valor = parseInt(campo.value, 10);

            if (isNaN(valor)) {
                valor = minimo;
            }

            campo.value = Math.min(maximo, Math.max(minimo, valor + paso));
            revisarLimites();
        }

        function revisarLimites() {
            var valor = parseInt(campo.value, 10) || minimo;

            Array.prototype.forEach.call(contenedor.querySelectorAll('[data-qty-paso]'), function (boton) {
                var paso = parseInt(boton.getAttribute('data-qty-paso'), 10);
                boton.disabled = paso < 0 ? valor <= minimo : valor >= maximo;
            });
        }

        contenedor.addEventListener('click', function (evento) {
            var boton = evento.target.closest('[data-qty-paso]');

            if (boton && !boton.disabled) {
                ajustar(parseInt(boton.getAttribute('data-qty-paso'), 10));
            }
        });

        campo.addEventListener('change', revisarLimites);
        revisarLimites();
    });
}());

/* Panel de filtros */
(function () {
    'use strict';

    var panel = document.querySelector('[data-filtros-panel]');
    var disparador = document.querySelector('[data-filtros-abrir]');

    if (!panel || !disparador) {
        return;
    }

    var focoPrevio = null;

    function abrir() {
        focoPrevio = document.activeElement;
        panel.setAttribute('aria-hidden', 'false');
        disparador.setAttribute('aria-expanded', 'true');
        document.body.classList.add('filtros-abiertos');

        var cerrar = panel.querySelector('[data-filtros-cerrar]');
        if (cerrar) {
            cerrar.focus();
        }
    }

    function cerrar() {
        panel.setAttribute('aria-hidden', 'true');
        disparador.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('filtros-abiertos');

        if (focoPrevio && typeof focoPrevio.focus === 'function') {
            focoPrevio.focus();
        }
    }

    disparador.addEventListener('click', abrir);

    panel.addEventListener('click', function (evento) {
        if (evento.target.closest('[data-filtros-cerrar]')) {
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
