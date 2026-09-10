/*
 * Formulario de usuario (admin/partials/usuario-campos): arma el enlace de Google Maps desde la direccion.
 * Botones: [data-mapa-activar]; campos: [data-mapa-direccion], [data-mapa-url]; enlace: [data-mapa-abrir].
 */
document.addEventListener('click', function (evento) {
    var boton = evento.target.closest('[data-mapa-activar]');
    if (!boton) return;

    var formulario = boton.closest('form') || document;
    var direccion = formulario.querySelector('[data-mapa-direccion]');
    var campoUrl = formulario.querySelector('[data-mapa-url]');
    var enlace = formulario.querySelector('[data-mapa-abrir]');
    var texto = direccion ? direccion.value.trim() : '';
    var url = texto ? 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(texto) : 'https://www.google.com/maps';

    if (campoUrl) campoUrl.value = url;
    if (enlace) enlace.href = url;
    if (typeof window.notificar === 'function') {
        window.notificar(texto ? 'Mapa listo para la dirección ingresada.' : 'Ingresa una dirección para ubicarla en el mapa.', texto ? 'exito' : 'aviso');
    }
});
