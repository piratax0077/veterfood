/*
 * Asistente por pasos (wizard) para formularios largos.
 *
 * Marcado:
 *   <div class="wizard" data-wizard>
 *       <section class="wizard-paso" data-titulo="Datos del bono">...campos...</section>
 *       <section class="wizard-paso" data-titulo="Cobertura">...</section>
 *   </div>
 *   Botones (en el mismo modal o formulario):
 *   <span data-wizard-contador></span>
 *   <button type="button" data-modal-cerrar data-wizard-solo-inicio>Cancelar</button>  (solo en el primer paso)
 *   <button type="button" data-wizard-anterior>Anterior</button>
 *   <button type="button" data-wizard-siguiente>Siguiente</button>
 *   <button type="submit" data-wizard-final>Crear</button>
 *
 * - Antes de avanzar valida los campos visibles del paso (requeridos, formatos).
 * - Enter en un campo avanza de paso en vez de enviar.
 * - Si el servidor devolvio errores, abre en el primer paso con un campo marcado (.has-error).
 * - Sin data-wizard (ej. en paginas de edicion) las secciones se ven todas seguidas.
 */
(function () {
    // Campos que el usuario ve en el paso (excluye ocultos por condiciones, ej. "Region" si es nacional).
    // Un paso que no esta a la vista se muestra un instante de forma invisible para poder medirlo.
    function camposVisibles(paso) {
        var oculto = !paso.classList.contains('is-actual');
        if (oculto) paso.classList.add('is-revisando');
        var campos = Array.prototype.filter.call(paso.querySelectorAll('input, select, textarea'), function (campo) {
            return !campo.disabled && campo.type !== 'hidden' && campo.offsetParent !== null;
        });
        if (oculto) paso.classList.remove('is-revisando');
        return campos;
    }

    function iniciar(wizard) {
        if (wizard.dataset.wizardListo) return;
        wizard.dataset.wizardListo = '1';

        var pasos = Array.prototype.slice.call(wizard.querySelectorAll(':scope > .wizard-paso'));
        if (pasos.length < 2) return;

        var ambito = wizard.closest('dialog') || wizard.closest('form') || document;
        var formulario = wizard.closest('form');
        var anterior = ambito.querySelector('[data-wizard-anterior]');
        var siguiente = ambito.querySelector('[data-wizard-siguiente]');
        var final = ambito.querySelector('[data-wizard-final]');
        var contador = ambito.querySelector('[data-wizard-contador]');
        var soloInicio = ambito.querySelectorAll('[data-wizard-solo-inicio]');
        var cuerpo = wizard.closest('.modal-cuerpo');
        var actual = 0;

        // Indicador de pasos
        var indicador = document.createElement('ol');
        indicador.className = 'wizard-indicador';
        pasos.forEach(function (paso, indice) {
            var item = document.createElement('li');
            item.innerHTML = '<button type="button" class="wizard-marca"><span class="wizard-numero">' + (indice + 1) + '</span><span class="wizard-nombre"></span></button>';
            item.querySelector('.wizard-nombre').textContent = paso.dataset.titulo || ('Paso ' + (indice + 1));
            item.querySelector('button').addEventListener('click', function () { irA(indice); });
            indicador.appendChild(item);
        });
        wizard.insertBefore(indicador, pasos[0]);
        wizard.classList.add('is-activo');

        function mostrar(indice, enfocar) {
            actual = Math.max(0, Math.min(indice, pasos.length - 1));
            pasos.forEach(function (paso, i) { paso.classList.toggle('is-actual', i === actual); });
            Array.prototype.forEach.call(indicador.children, function (item, i) {
                item.classList.toggle('is-actual', i === actual);
                item.classList.toggle('is-hecho', i < actual);
                var marca = item.querySelector('button');
                if (i === actual) marca.setAttribute('aria-current', 'step'); else marca.removeAttribute('aria-current');
            });
            var esUltimo = actual === pasos.length - 1;
            if (anterior) anterior.hidden = actual === 0;
            soloInicio.forEach(function (boton) { boton.hidden = actual !== 0; });
            if (siguiente) siguiente.hidden = esUltimo;
            if (final) final.hidden = !esUltimo;
            if (contador) contador.textContent = 'Paso ' + (actual + 1) + ' de ' + pasos.length;
            if (cuerpo) cuerpo.scrollTop = 0;
            if (enfocar) {
                var primero = camposVisibles(pasos[actual])[0];
                if (primero) primero.focus({preventScroll: true});
            }
        }

        // Si el paso tiene un campo invalido, lo muestra y el navegador indica que falta
        function pasoValido(indice) {
            var invalido = camposVisibles(pasos[indice]).find(function (campo) { return !campo.checkValidity(); });
            if (invalido) {
                if (indice !== actual) mostrar(indice, false);
                invalido.reportValidity();
                return false;
            }
            return true;
        }

        function irA(indice) {
            if (indice <= actual) { mostrar(indice, true); return; }
            for (var i = actual; i < indice; i++) {
                if (!pasoValido(i)) return;
            }
            mostrar(indice, true);
        }

        if (anterior) anterior.addEventListener('click', function () { mostrar(actual - 1, true); });
        if (siguiente) siguiente.addEventListener('click', function () { irA(actual + 1); });

        if (formulario) {
            // La validacion la hace el asistente paso a paso (el navegador no puede mostrar campos de pasos ocultos)
            formulario.noValidate = true;
            formulario.addEventListener('submit', function (evento) {
                if (actual < pasos.length - 1) {
                    evento.preventDefault();
                    irA(actual + 1);
                    return;
                }
                // Revisa todos los pasos: un campo invalido en un paso oculto no se podria mostrar al enviar
                for (var i = 0; i < pasos.length; i++) {
                    if (!pasoValido(i)) {
                        evento.preventDefault();
                        return;
                    }
                }
            });
            formulario.addEventListener('reset', function () { setTimeout(function () { mostrar(0, false); }); });
        }

        // Al abrir el modal: primer paso con error del servidor, o el primero
        function pasoInicial() {
            var conError = pasos.findIndex(function (paso) { return paso.querySelector('.has-error, .field-error'); });
            return conError >= 0 ? conError : 0;
        }
        var modal = wizard.closest('dialog');
        if (modal) modal.addEventListener('modal:abierto', function () { mostrar(pasoInicial(), true); });

        mostrar(pasoInicial(), false);
    }

    function iniciarTodos() {
        document.querySelectorAll('[data-wizard]').forEach(iniciar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarTodos);
    } else {
        iniciarTodos();
    }
}());
