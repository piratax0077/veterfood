/*
 * Mapa por direccion (uso global en formularios). Dentro del mismo formulario (o de un [data-mapa-grupo]):
 *   [data-mapa-direccion]  campo con la direccion (obligatorio)
 *   [data-mapa-comuna]     campo con la comuna (opcional)
 *   [data-mapa-url]        campo oculto donde se guarda el enlace de Google Maps
 *   [data-mapa-abrir]      enlace "Abrir mapa"
 *   [data-mapa-frame]      iframe para ver el mapa (opcional)
 *   [data-mapa-activar]    boton "Activar mapa por direccion"
 */
(function () {
    function grupoDe(elemento) {
        return elemento.closest('[data-mapa-grupo]') || elemento.closest('form') || document;
    }

    function actualizar(grupo) {
        var q = function (selector) { return grupo.querySelector(selector); };
        var direccion = q('[data-mapa-direccion]');
        var comuna = q('[data-mapa-comuna]');
        var texto = [direccion && direccion.value, comuna && comuna.value].filter(Boolean).join(' ').trim();
        var consulta = encodeURIComponent(texto);
        var url = texto ? 'https://www.google.com/maps/search/?api=1&query=' + consulta : '';
        var campoUrl = q('[data-mapa-url]');
        var enlace = q('[data-mapa-abrir]');
        var marco = q('[data-mapa-frame]');

        if (campoUrl) campoUrl.value = url;
        if (enlace) enlace.href = url || 'https://www.google.com/maps';
        if (marco) {
            if (texto) marco.src = 'https://maps.google.com/maps?q=' + consulta + '&output=embed';
            else marco.removeAttribute('src');
        }
        return {listo: !!texto, conMarco: !!marco};
    }

    document.addEventListener('click', function (evento) {
        var boton = evento.target.closest('[data-mapa-activar]');
        if (!boton) return;
        var resultado = actualizar(grupoDe(boton));
        if (typeof window.notificar !== 'function') return;
        if (!resultado.listo) window.notificar('Ingresa una dirección para ubicarla en el mapa.', 'aviso');
        else if (!resultado.conMarco) window.notificar('Mapa listo para la dirección ingresada.', 'exito');
    });

    // Al cargar (o al limpiar el formulario) muestra el mapa si ya hay direccion guardada
    function mostrarGuardados(raiz) {
        raiz.querySelectorAll('[data-mapa-frame]').forEach(function (marco) {
            var grupo = grupoDe(marco);
            var direccion = grupo.querySelector('[data-mapa-direccion]');
            if (direccion && direccion.value) actualizar(grupo);
            else marco.removeAttribute('src');
        });
    }

    document.addEventListener('reset', function (evento) {
        setTimeout(function () { mostrarGuardados(evento.target); });
    }, true);

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { mostrarGuardados(document); });
    else mostrarGuardados(document);
}());
