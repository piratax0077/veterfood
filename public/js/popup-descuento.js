/*
 * Aviso "Obtén 10% en tu primera compra" para quien navega la tienda sin cuenta.
 * Aparece al minuto de la visita (se cuenta desde la primera página) y se quita con la X.
 * Una vez quitado, no vuelve a aparecer por 7 días.
 */
(function () {
    'use strict';

    var aviso = document.querySelector('[data-popup-descuento]');
    if (!aviso) return;

    var ESPERA = 60 * 1000;
    var SIETE_DIAS = 7 * 24 * 60 * 60 * 1000;
    var CLAVE_INICIO = 'veterfood-visita-inicio';
    var CLAVE_CERRADO = 'veterfood-popup10-cerrado';

    function leer(almacen, clave) {
        try { return window[almacen].getItem(clave); } catch (error) { return null; }
    }

    function guardar(almacen, clave, valor) {
        try { window[almacen].setItem(clave, valor); } catch (error) { /* sin almacenamiento: se muestra igual */ }
    }

    // Para revisarlo sin esperar: agregar ?ver-popup a la dirección
    var forzar = /[?&]ver-popup/.test(window.location.search);

    var cerrado = parseInt(leer('localStorage', CLAVE_CERRADO), 10);
    if (!forzar && cerrado && Date.now() - cerrado < SIETE_DIAS) return;

    // El minuto se cuenta durante toda la visita, aunque cambie de página
    var inicio = parseInt(leer('sessionStorage', CLAVE_INICIO), 10);
    if (!inicio) {
        inicio = Date.now();
        guardar('sessionStorage', CLAVE_INICIO, String(inicio));
    }

    function mostrar() {
        aviso.hidden = false;
        // Lee el tamaño para que se vea la animación de entrada
        void aviso.offsetWidth;
        aviso.classList.add('is-visible');
    }

    function cerrar() {
        aviso.classList.remove('is-visible');
        guardar('localStorage', CLAVE_CERRADO, String(Date.now()));
        window.setTimeout(function () { aviso.hidden = true; }, 250);
    }

    // La X lo quita (también "Registrarme", que abre el formulario de cuenta)
    aviso.querySelectorAll('[data-popup-cerrar]').forEach(function (boton) {
        boton.addEventListener('click', cerrar);
    });

    window.setTimeout(mostrar, forzar ? 300 : Math.max(0, ESPERA - (Date.now() - inicio)));
}());
