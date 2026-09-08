/* ==========================================================================
   VeterFood - Menu de la tienda
   ========================================================================== */
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
