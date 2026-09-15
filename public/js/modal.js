/*
 * Modal global (componente <x-modal>).
 *   - Cualquier elemento con data-modal-abrir="id" abre ese modal.
 *   - Dentro del modal, data-modal-cerrar lo cierra; tambien Esc o un clic fuera de la caja.
 *   - Un modal con data-modal-abierto se abre solo al cargar la pagina (ej. tras un error de validacion).
 *   - data-modal-rellenar='{"campo":"valor"}' en el boton abre el formulario con esos datos ya puestos.
 *   - Al abrir se emite el evento "modal:abierto" en el dialog (lo usa el asistente por pasos).
 */
(function () {
    var inicioClicFuera = false;

    // Las notificaciones flotantes deben verse sobre el modal: se mueven dentro de el mientras esta abierto.
    function moverNotificaciones(destino) {
        var avisos = document.querySelector('.notificaciones');
        if (avisos && avisos.parentNode !== destino) destino.appendChild(avisos);
    }

    // data-modal-rellenar='{"campo":"valor"}' en el boton: limpia el formulario y lo completa con esos datos
    function rellenar(modal, datos) {
        var formulario = modal.querySelector('form');
        if (!formulario || !datos) return;
        formulario.reset();
        Object.keys(datos).forEach(function (nombre) {
            var campo = formulario.elements.namedItem(nombre);
            if (!campo || campo instanceof RadioNodeList) return;
            campo.value = datos[nombre];
            campo.dispatchEvent(new Event('change', {bubbles: true}));
        });
    }

    function abrir(modal, abridor) {
        if (!modal || modal.open) return;
        if (abridor && abridor.dataset.modalRellenar) {
            try { rellenar(modal, JSON.parse(abridor.dataset.modalRellenar)); } catch (e) {}
        }
        modal.showModal();
        moverNotificaciones(modal);
        document.documentElement.classList.add('modal-abierto');
        modal.dispatchEvent(new CustomEvent('modal:abierto'));
        var campo = modal.querySelector('[autofocus], .modal-cuerpo input:not([type=hidden]), .modal-cuerpo select, .modal-cuerpo textarea');
        if (campo && !modal.querySelector('[data-wizard]')) campo.focus();
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
                // Abierto desde otro modal (ej. "Crear cuenta" dentro de "Iniciar sesión"): se cambia uno por otro
                var actual = abridor.closest('dialog.modal[open]');
                if (actual && actual !== destino) cerrar(actual);
                abrir(destino, abridor);
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

    // Para abrir un modal desde otro script: window.abrirModal('id')
    window.abrirModal = function (id) {
        abrir(document.getElementById(id));
    };

    function iniciar() {
        document.querySelectorAll('dialog.modal[data-modal-abierto]').forEach(abrir);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
}());
