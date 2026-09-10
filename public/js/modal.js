/*
 * Modal global (componente <x-modal>).
 *   - Cualquier elemento con data-modal-abrir="id" abre ese modal.
 *   - Dentro del modal, data-modal-cerrar lo cierra; tambien Esc o un clic fuera de la caja.
 *   - Un modal con data-modal-abierto se abre solo al cargar la pagina (ej. tras un error de validacion).
 */
(function () {
    var inicioClicFuera = false;

    // Las notificaciones flotantes deben verse sobre el modal: se mueven dentro de el mientras esta abierto.
    function moverNotificaciones(destino) {
        var avisos = document.querySelector('.notificaciones');
        if (avisos && avisos.parentNode !== destino) destino.appendChild(avisos);
    }

    function abrir(modal) {
        if (!modal || modal.open) return;
        modal.showModal();
        moverNotificaciones(modal);
        document.documentElement.classList.add('modal-abierto');
        var campo = modal.querySelector('[autofocus], .modal-cuerpo input:not([type=hidden]), .modal-cuerpo select, .modal-cuerpo textarea');
        if (campo) campo.focus();
    }

    // Devuelve los avisos a la pagina y desbloquea el scroll si no queda otro modal abierto
    function alCerrar() {
        var otroAbierto = document.querySelector('dialog.modal[open]');
        moverNotificaciones(otroAbierto || document.body);
        if (!otroAbierto) document.documentElement.classList.remove('modal-abierto');
    }

    function cerrar(modal) {
        if (!modal || !modal.open || modal.classList.contains('is-cerrando')) return;
        var reducido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        modal.classList.add('is-cerrando');
        setTimeout(function () {
            modal.classList.remove('is-cerrando');
            modal.close();
            alCerrar();
        }, reducido ? 0 : 160);
    }

    // Solo cierra por clic fuera si el clic empezo y termino fuera de la caja (evita cerrar al seleccionar texto)
    document.addEventListener('mousedown', function (evento) {
        inicioClicFuera = evento.target instanceof HTMLDialogElement && evento.target.classList.contains('modal');
    });

    document.addEventListener('click', function (evento) {
        var abridor = evento.target.closest('[data-modal-abrir]');
        if (abridor) {
            var destino = document.getElementById(abridor.dataset.modalAbrir);
            if (destino) {
                evento.preventDefault();
                abrir(destino);
            }
            return;
        }

        var cerrador = evento.target.closest('[data-modal-cerrar]');
        if (cerrador && cerrador.closest('dialog.modal')) {
            evento.preventDefault();
            cerrar(cerrador.closest('dialog.modal'));
            return;
        }

        if (inicioClicFuera && evento.target instanceof HTMLDialogElement && evento.target.classList.contains('modal')) {
            cerrar(evento.target);
        }
    });

    // Esc: usa la misma animacion de cierre
    document.addEventListener('cancel', function (evento) {
        if (evento.target.classList && evento.target.classList.contains('modal')) {
            evento.preventDefault();
            cerrar(evento.target);
        }
    }, true);

    // Respaldo: si el modal se cierra por otra via (ej. el navegador), igual se limpia
    document.addEventListener('close', function (evento) {
        if (evento.target.classList && evento.target.classList.contains('modal')) alCerrar();
    }, true);

    function iniciar() {
        document.querySelectorAll('dialog.modal[data-modal-abierto]').forEach(abrir);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
}());
