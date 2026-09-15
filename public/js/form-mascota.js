/*
 * Formulario de mascota (admin/partials/mascota-campos): muestra la ficha del tutor elegido.
 * Select: [data-tutor-select] · datos: [data-tutor="nombre|rut|email|telefono|sincronizado"]
 */
(function () {
    var vacios = {nombre: '-', email: '-', rut: 'No informado', telefono: 'No informado', sincronizado: '-'};

    function mostrarTutor(select) {
        var formulario = select.closest('[data-form-mascota]');
        var opcion = select.selectedOptions[0];
        formulario.querySelectorAll('[data-tutor]').forEach(function (campo) {
            var clave = campo.dataset.tutor;
            campo.textContent = (opcion && opcion.value && opcion.dataset[clave]) || vacios[clave];
        });
    }

    document.addEventListener('change', function (evento) {
        if (evento.target.matches('[data-tutor-select]')) mostrarTutor(evento.target);
    });
    document.addEventListener('reset', function (evento) {
        setTimeout(function () { evento.target.querySelectorAll('[data-tutor-select]').forEach(mostrarTutor); });
    }, true);

    function iniciar() { document.querySelectorAll('[data-tutor-select]').forEach(mostrarTutor); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar); else iniciar();
}());
