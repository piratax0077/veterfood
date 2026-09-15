/* Carrusel del inicio de la tienda */
(function () {
    'use strict';

    var carrusel = document.querySelector('[data-carrusel]');

    if (!carrusel) {
        return;
    }

    var slides = Array.prototype.slice.call(carrusel.querySelectorAll('[data-carrusel-slide]'));
    var puntos = Array.prototype.slice.call(carrusel.querySelectorAll('[data-carrusel-punto]'));
    var actual = 0;
    var reloj = null;
    var PAUSA = 6000;

    if (slides.length < 2) {
        return;
    }

    function mostrar(indice) {
        actual = (indice + slides.length) % slides.length;

        slides.forEach(function (slide, i) {
            slide.classList.toggle('is-activa', i === actual);
            slide.setAttribute('aria-hidden', i === actual ? 'false' : 'true');
        });

        puntos.forEach(function (punto, i) {
            punto.classList.toggle('is-activo', i === actual);
            punto.setAttribute('aria-selected', i === actual ? 'true' : 'false');
        });
    }

    function andar() {
        detener();
        reloj = window.setInterval(function () {
            mostrar(actual + 1);
        }, PAUSA);
    }

    function detener() {
        if (reloj) {
            window.clearInterval(reloj);
            reloj = null;
        }
    }

    function ir(indice) {
        mostrar(indice);
        andar();
    }

    carrusel.querySelector('[data-carrusel-antes]').addEventListener('click', function () {
        ir(actual - 1);
    });

    carrusel.querySelector('[data-carrusel-despues]').addEventListener('click', function () {
        ir(actual + 1);
    });

    puntos.forEach(function (punto, i) {
        punto.addEventListener('click', function () {
            ir(i);
        });
    });

    // Se detiene con el mouse encima, con el teclado y cuando la pestaña no se ve
    ['mouseenter', 'focusin'].forEach(function (tipo) {
        carrusel.addEventListener(tipo, detener);
    });
    ['mouseleave', 'focusout'].forEach(function (tipo) {
        carrusel.addEventListener(tipo, andar);
    });

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            detener();
        } else {
            andar();
        }
    });

    carrusel.addEventListener('keydown', function (evento) {
        if (evento.key === 'ArrowLeft') {
            ir(actual - 1);
        } else if (evento.key === 'ArrowRight') {
            ir(actual + 1);
        }
    });

    // Deslizar con el dedo
    var inicioX = null;

    carrusel.addEventListener('touchstart', function (evento) {
        inicioX = evento.touches[0].clientX;
        detener();
    }, { passive: true });

    carrusel.addEventListener('touchend', function (evento) {
        if (inicioX === null) {
            return;
        }

        var avance = evento.changedTouches[0].clientX - inicioX;
        inicioX = null;

        if (Math.abs(avance) > 45) {
            mostrar(avance < 0 ? actual + 1 : actual - 1);
        }

        andar();
    });

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        andar();
    }
}());

/* Suscripción a noticias: por ahora solo confirma en pantalla (aún no se guarda el correo) */
(function () {
    'use strict';

    var formulario = document.querySelector('[data-suscripcion]');
    if (!formulario) return;

    var correo = formulario.querySelector('input[type="email"]');
    var valido = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    correo.addEventListener('input', function () {
        correo.classList.remove('is-error');
    });

    formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();
        var valor = correo.value.trim();

        if (!valido.test(valor)) {
            correo.classList.add('is-error');
            correo.focus();
            if (window.notificar) window.notificar('Escribe un correo válido, por ejemplo nombre@correo.cl', 'error');
            return;
        }

        formulario.reset();
        if (window.notificar) {
            window.notificar({ tipo: 'exito', titulo: '¡Gracias por suscribirte!', mensaje: 'Te enviaremos nuestras novedades y promociones a ' + valor + '.' });
        }
    });
}());

/* Al bajar por el inicio, cada bloque sube y aparece cuando entra en pantalla.
   data-revelar: el bloque completo. data-revelar-grupo: cada elemento de adentro por separado. */
(function () {
    'use strict';

    var bloques = document.querySelectorAll('[data-revelar], [data-revelar-grupo] > *');

    if (!bloques.length || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    var observador = new IntersectionObserver(function (entradas) {
        var orden = 0;

        entradas.forEach(function (entrada) {
            if (!entrada.isIntersecting) {
                return;
            }

            // Si entran varios juntos (una fila de productos, por ejemplo) salen uno tras otro
            entrada.target.style.setProperty('--retraso', Math.min(orden++, 6) * 0.08 + 's');
            entrada.target.classList.add('is-visible');
            observador.unobserve(entrada.target);
        });
    }, { rootMargin: '0px 0px -8% 0px' });

    Array.prototype.forEach.call(bloques, function (bloque) {
        // Lo que ya se ve al abrir la página queda como está, así nada parpadea
        if (bloque.getBoundingClientRect().top < window.innerHeight) {
            return;
        }

        bloque.classList.add('revelar');
        observador.observe(bloque);
    });
}());
