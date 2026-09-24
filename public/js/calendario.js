/*
 * Calendario propio para los campos de fecha (input type="date").
 * El campo original queda oculto y sigue enviando la fecha como aaaa-mm-dd.
 * Encima se muestra un campo "dd-mm-aaaa" donde se puede escribir o elegir en el calendario,
 * que se abre justo debajo. Respeta min y max del campo original.
 * Para dejar un campo con el selector del navegador: data-calendario="nativo".
 */
(function () {
    'use strict';

    var MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    var MESES_CORTOS = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    var DIAS = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'];
    var FLECHA_IZQ = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>';
    var FLECHA_DER = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>';
    var ICONO = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="3"/><path d="M3.5 10h17M8 3v4M16 3v4"/><circle cx="8.5" cy="14.5" r=".9" fill="currentColor" stroke="none"/><circle cx="12" cy="14.5" r=".9" fill="currentColor" stroke="none"/><circle cx="15.5" cy="14.5" r=".9" fill="currentColor" stroke="none"/></svg>';
    var valorNativo = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
    var abierto = null;

    function dos(n) { return (n < 10 ? '0' : '') + n; }
    function aIso(fecha) { return fecha.getFullYear() + '-' + dos(fecha.getMonth() + 1) + '-' + dos(fecha.getDate()); }
    function aTexto(fecha) { return dos(fecha.getDate()) + '-' + dos(fecha.getMonth() + 1) + '-' + fecha.getFullYear(); }
    function hoy() { var d = new Date(); return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
    function mismoDia(a, b) { return !!a && !!b && a.getTime() === b.getTime(); }

    function desdeIso(valor) {
        var p = /^(\d{4})-(\d{2})-(\d{2})$/.exec(valor || '');
        if (!p) return null;
        var fecha = new Date(Number(p[1]), Number(p[2]) - 1, Number(p[3]));
        return fecha.getDate() === Number(p[3]) ? fecha : null;
    }

    function desdeTexto(valor) {
        var p = /^(\d{2})-(\d{2})-(\d{4})$/.exec(valor || '');
        if (!p) return null;
        var fecha = new Date(Number(p[3]), Number(p[2]) - 1, Number(p[1]));
        return fecha.getMonth() === Number(p[2]) - 1 && fecha.getDate() === Number(p[1]) ? fecha : null;
    }

    // Deja solo números y pone los guiones: 12032024 -> 12-03-2024
    function mascara(valor) {
        var d = valor.replace(/\D/g, '').slice(0, 8);
        if (d.length > 4) return d.slice(0, 2) + '-' + d.slice(2, 4) + '-' + d.slice(4);
        if (d.length > 2) return d.slice(0, 2) + '-' + d.slice(2);
        return d;
    }

    function preparar(original) {
        if (original.dataset.calendarioListo || original.dataset.calendario === 'nativo') return;
        original.dataset.calendarioListo = '1';

        // Campo visible donde se escribe o se muestra la fecha elegida
        var envoltura = document.createElement('div');
        envoltura.className = 'calendario-campo';
        var texto = document.createElement('input');
        texto.type = 'text';
        texto.className = (original.className + ' calendario-texto').trim();
        texto.placeholder = 'dd-mm-aaaa';
        texto.inputMode = 'numeric';
        texto.autocomplete = 'off';
        texto.maxLength = 10;
        texto.id = (original.id || 'fecha-' + Math.random().toString(36).slice(2, 8)) + '_texto';
        texto.setAttribute('aria-haspopup', 'dialog');
        texto.setAttribute('aria-expanded', 'false');
        texto.setAttribute('data-fecha', '');
        if (original.required) texto.required = true;
        if (original.disabled) texto.disabled = true;
        if (original.readOnly) texto.readOnly = true;
        if (original.getAttribute('aria-describedby')) texto.setAttribute('aria-describedby', original.getAttribute('aria-describedby'));
        if (original.dataset.msg) texto.dataset.msg = original.dataset.msg;

        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'calendario-boton';
        boton.tabIndex = -1;
        boton.setAttribute('aria-label', 'Abrir calendario');
        boton.innerHTML = ICONO;

        envoltura.appendChild(texto);
        envoltura.appendChild(boton);
        original.insertAdjacentElement('afterend', envoltura);

        // El original sigue en el formulario (con su nombre), pero no se ve ni se valida aparte
        original.style.setProperty('display', 'none', 'important');
        original.setAttribute('data-sin-validar', '');
        original.tabIndex = -1;

        // El label ahora apunta al campo visible
        if (original.id) {
            document.querySelectorAll('label[for="' + original.id + '"]').forEach(function (label) { label.htmlFor = texto.id; });
        }

        function limites() {
            texto.dataset.min = original.min || '';
            texto.dataset.max = original.max || '';
            return {min: desdeIso(original.min), max: desdeIso(original.max)};
        }

        function pintarTexto() {
            var fecha = desdeIso(valorNativo.get.call(original));
            texto.value = fecha ? aTexto(fecha) : '';
            limites();
        }

        // Si otro script cambia la fecha (ej. al editar), el campo visible se actualiza solo
        Object.defineProperty(original, 'value', {
            configurable: true,
            get: function () { return valorNativo.get.call(this); },
            set: function (valor) { valorNativo.set.call(this, valor); pintarTexto(); }
        });

        function avisar(campo) {
            campo.dispatchEvent(new Event('input', {bubbles: true}));
            campo.dispatchEvent(new Event('change', {bubbles: true}));
        }

        function guardar(fecha) {
            var nuevo = fecha ? aIso(fecha) : '';
            if (valorNativo.get.call(original) === nuevo) return;
            valorNativo.set.call(original, nuevo);
            avisar(original);
        }

        texto.addEventListener('input', function () {
            var limpio = mascara(texto.value);
            if (texto.value !== limpio) texto.value = limpio;
            var fecha = desdeTexto(limpio);
            var rango = limites();
            var permitida = fecha && (!rango.min || fecha >= rango.min) && (!rango.max || fecha <= rango.max);
            guardar(permitida ? fecha : null);
            if (fecha && abierto && abierto.texto === texto) abierto.ir(fecha, fecha);
        });

        texto.addEventListener('click', function () { abrir(control); });
        boton.addEventListener('click', function () {
            if (abierto && abierto.texto === texto) { cerrar(); return; }
            abrir(control);
            texto.focus();
        });
        texto.addEventListener('keydown', function (evento) {
            if (evento.key === 'ArrowDown' || (evento.key === 'Enter' && !abierto && evento.altKey)) {
                evento.preventDefault();
                abrir(control, true);
            } else if (evento.key === 'Escape' && abierto) {
                cerrar();
            }
        });

        // Al limpiar el formulario vuelve a la fecha de inicio
        if (original.form) {
            original.form.addEventListener('reset', function () { setTimeout(pintarTexto); });
        }

        var control = {
            original: original,
            texto: texto,
            envoltura: envoltura,
            limites: limites,
            elegida: function () { return desdeIso(valorNativo.get.call(original)); },
            elegir: function (fecha) {
                guardar(fecha);
                pintarTexto();
                avisar(texto);
            }
        };

        pintarTexto();
    }

    // ---------- Ventana del calendario ----------

    function crearVentana() {
        var caja = document.createElement('div');
        caja.className = 'calendario';
        caja.setAttribute('role', 'dialog');
        caja.setAttribute('aria-label', 'Elegir fecha');
        caja.innerHTML =
            '<div class="calendario-cabecera">' +
                '<button type="button" class="calendario-flecha" data-accion="anterior" aria-label="Anterior">' + FLECHA_IZQ + '</button>' +
                '<button type="button" class="calendario-titulo" data-accion="vista" aria-live="polite"></button>' +
                '<button type="button" class="calendario-flecha" data-accion="siguiente" aria-label="Siguiente">' + FLECHA_DER + '</button>' +
            '</div>' +
            '<div class="calendario-cuerpo"></div>' +
            '<div class="calendario-pie">' +
                '<button type="button" class="calendario-enlace" data-accion="hoy">Hoy</button>' +
                '<button type="button" class="calendario-enlace calendario-enlace--suave" data-accion="borrar">Borrar</button>' +
            '</div>';
        return caja;
    }

    function abrir(control, enfocarDia) {
        if (control.texto.disabled || control.texto.readOnly) return;
        if (abierto && abierto.texto === control.texto) return;
        cerrar();

        var caja = crearVentana();
        var anfitrion = control.texto.closest('dialog') || document.body;
        anfitrion.appendChild(caja);

        var estado = {
            texto: control.texto,
            caja: caja,
            anfitrion: anfitrion,
            vista: 'dias',
            mes: null,
            foco: null
        };

        var rango = control.limites();
        var base = control.elegida() || hoy();
        if (rango.max && base > rango.max) base = rango.max;
        if (rango.min && base < rango.min) base = rango.min;
        estado.mes = new Date(base.getFullYear(), base.getMonth(), 1);
        estado.foco = base;

        function fuera(fecha) {
            return (rango.min && fecha < rango.min) || (rango.max && fecha > rango.max);
        }

        function boton(clase, textoBoton, datos) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = clase;
            b.textContent = textoBoton;
            Object.keys(datos || {}).forEach(function (k) { b.dataset[k] = datos[k]; });
            return b;
        }

        function pintar() {
            rango = control.limites();
            var cuerpo = caja.querySelector('.calendario-cuerpo');
            var titulo = caja.querySelector('.calendario-titulo');
            cuerpo.innerHTML = '';
            cuerpo.className = 'calendario-cuerpo calendario-cuerpo--' + estado.vista;
            var elegida = control.elegida();
            var anio = estado.mes.getFullYear();

            if (estado.vista === 'dias') {
                titulo.innerHTML = MESES[estado.mes.getMonth()] + ' <span>' + anio + '</span>';
                titulo.setAttribute('aria-label', MESES[estado.mes.getMonth()] + ' ' + anio + '. Cambiar mes o año');
                DIAS.forEach(function (dia) {
                    var s = document.createElement('span');
                    s.className = 'calendario-semana';
                    s.textContent = dia;
                    cuerpo.appendChild(s);
                });
                // La semana parte el lunes
                var inicio = new Date(anio, estado.mes.getMonth(), 1);
                inicio.setDate(1 - ((inicio.getDay() + 6) % 7));
                for (var i = 0; i < 42; i++) {
                    var fecha = new Date(inicio.getFullYear(), inicio.getMonth(), inicio.getDate() + i);
                    var b = boton('calendario-dia', String(fecha.getDate()), {fecha: aIso(fecha)});
                    b.setAttribute('aria-label', fecha.getDate() + ' de ' + MESES[fecha.getMonth()].toLowerCase() + ' de ' + fecha.getFullYear());
                    if (fecha.getMonth() !== estado.mes.getMonth()) b.classList.add('is-otro-mes');
                    if (mismoDia(fecha, hoy())) b.classList.add('is-hoy');
                    if (mismoDia(fecha, elegida)) { b.classList.add('is-elegido'); b.setAttribute('aria-pressed', 'true'); }
                    if (fuera(fecha)) b.disabled = true;
                    b.tabIndex = mismoDia(fecha, estado.foco) ? 0 : -1;
                    cuerpo.appendChild(b);
                }
            } else if (estado.vista === 'meses') {
                titulo.innerHTML = '<span>' + anio + '</span>';
                titulo.setAttribute('aria-label', anio + '. Cambiar año');
                MESES_CORTOS.forEach(function (nombre, m) {
                    var b = boton('calendario-opcion', nombre, {mes: m});
                    var primero = new Date(anio, m, 1);
                    var ultimo = new Date(anio, m + 1, 0);
                    if ((rango.min && ultimo < rango.min) || (rango.max && primero > rango.max)) b.disabled = true;
                    if (elegida && elegida.getFullYear() === anio && elegida.getMonth() === m) b.classList.add('is-elegido');
                    if (hoy().getFullYear() === anio && hoy().getMonth() === m) b.classList.add('is-hoy');
                    cuerpo.appendChild(b);
                });
            } else {
                var desde = anio - (anio % 12);
                titulo.innerHTML = '<span>' + desde + ' – ' + (desde + 11) + '</span>';
                titulo.setAttribute('aria-label', 'Años ' + desde + ' a ' + (desde + 11));
                for (var a = desde; a < desde + 12; a++) {
                    var ba = boton('calendario-opcion', String(a), {anio: a});
                    if ((rango.min && a < rango.min.getFullYear()) || (rango.max && a > rango.max.getFullYear())) ba.disabled = true;
                    if (elegida && elegida.getFullYear() === a) ba.classList.add('is-elegido');
                    if (hoy().getFullYear() === a) ba.classList.add('is-hoy');
                    cuerpo.appendChild(ba);
                }
            }

            var hoyBoton = caja.querySelector('[data-accion="hoy"]');
            hoyBoton.disabled = !!fuera(hoy());
            caja.querySelector('[data-accion="borrar"]').hidden = !elegida;
            ubicar();
        }

        function enfocarDiaActual() {
            var actual = caja.querySelector('.calendario-dia[tabindex="0"]') || caja.querySelector('.calendario-dia:not([disabled])');
            if (actual) actual.focus();
        }

        function mover(dias) {
            var destino = new Date(estado.foco.getFullYear(), estado.foco.getMonth(), estado.foco.getDate() + dias);
            if (fuera(destino)) return;
            estado.foco = destino;
            estado.mes = new Date(destino.getFullYear(), destino.getMonth(), 1);
            pintar();
            enfocarDiaActual();
        }

        // Pone la ventana bajo el campo; si no cabe, arriba
        function ubicar() {
            var campo = control.envoltura.getBoundingClientRect();
            var base = anfitrion === document.body ? {top: -window.scrollY, left: -window.scrollX} : anfitrion.getBoundingClientRect();
            var alto = caja.offsetHeight;
            var ancho = caja.offsetWidth;
            var abajo = window.innerHeight - campo.bottom;
            var arriba = abajo < alto + 12 && campo.top > alto + 12;
            var top = arriba ? campo.top - alto - 6 : campo.bottom + 6;
            var left = Math.min(Math.max(8, campo.left), window.innerWidth - ancho - 8);
            caja.style.top = (top - base.top) + 'px';
            caja.style.left = (left - base.left) + 'px';
            caja.classList.toggle('is-arriba', arriba);
        }

        caja.addEventListener('mousedown', function (evento) { evento.preventDefault(); });

        caja.addEventListener('click', function (evento) {
            var objetivo = evento.target.closest('button');
            if (!objetivo || objetivo.disabled) return;
            var accion = objetivo.dataset.accion;

            if (objetivo.dataset.fecha) {
                control.elegir(desdeIso(objetivo.dataset.fecha));
                cerrar();
                control.texto.focus();
            } else if (objetivo.dataset.mes !== undefined) {
                estado.mes = new Date(estado.mes.getFullYear(), Number(objetivo.dataset.mes), 1);
                estado.vista = 'dias';
                pintar();
            } else if (objetivo.dataset.anio !== undefined) {
                estado.mes = new Date(Number(objetivo.dataset.anio), estado.mes.getMonth(), 1);
                estado.vista = 'meses';
                pintar();
            } else if (accion === 'vista') {
                estado.vista = estado.vista === 'dias' ? 'meses' : 'anios';
                pintar();
            } else if (accion === 'anterior' || accion === 'siguiente') {
                var paso = accion === 'anterior' ? -1 : 1;
                if (estado.vista === 'dias') estado.mes = new Date(estado.mes.getFullYear(), estado.mes.getMonth() + paso, 1);
                else estado.mes = new Date(estado.mes.getFullYear() + paso * (estado.vista === 'anios' ? 12 : 1), estado.mes.getMonth(), 1);
                pintar();
            } else if (accion === 'hoy') {
                control.elegir(hoy());
                cerrar();
                control.texto.focus();
            } else if (accion === 'borrar') {
                control.elegir(null);
                cerrar();
                control.texto.focus();
            }
        });

        // Teclado dentro de los días
        caja.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape') {
                evento.preventDefault();
                cerrar();
                control.texto.focus();
                return;
            }
            if (!evento.target.classList.contains('calendario-dia')) return;
            var movimientos = {ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7};
            if (movimientos[evento.key]) {
                evento.preventDefault();
                mover(movimientos[evento.key]);
            } else if (evento.key === 'PageUp' || evento.key === 'PageDown') {
                evento.preventDefault();
                var salto = evento.key === 'PageUp' ? -1 : 1;
                var destino = new Date(estado.foco.getFullYear(), estado.foco.getMonth() + salto, estado.foco.getDate());
                if (!fuera(destino)) mover(Math.round((destino - estado.foco) / 86400000));
            }
        });

        estado.ir = function (mesVisible, foco) {
            estado.vista = 'dias';
            estado.mes = new Date(mesVisible.getFullYear(), mesVisible.getMonth(), 1);
            estado.foco = foco;
            pintar();
        };
        estado.ubicar = ubicar;
        abierto = estado;
        control.texto.setAttribute('aria-expanded', 'true');
        control.envoltura.classList.add('is-abierto');
        pintar();
        requestAnimationFrame(function () { caja.classList.add('is-visible'); });
        if (enfocarDia) enfocarDiaActual();
    }

    function cerrar() {
        if (!abierto) return;
        var estado = abierto;
        abierto = null;
        estado.texto.setAttribute('aria-expanded', 'false');
        var envoltura = estado.texto.closest('.calendario-campo');
        if (envoltura) envoltura.classList.remove('is-abierto');
        estado.caja.remove();
    }

    // Cierra al hacer clic fuera, al salir con Tab o al cerrar un modal
    document.addEventListener('mousedown', function (evento) {
        if (!abierto) return;
        if (abierto.caja.contains(evento.target) || evento.target.closest('.calendario-campo') === abierto.texto.closest('.calendario-campo')) return;
        cerrar();
    });
    document.addEventListener('focusin', function (evento) {
        if (!abierto) return;
        if (abierto.caja.contains(evento.target) || evento.target === abierto.texto) return;
        cerrar();
    });
    document.addEventListener('close', cerrar, true);
    window.addEventListener('resize', function () { if (abierto) abierto.ubicar(); });
    document.addEventListener('scroll', function () { if (abierto) abierto.ubicar(); }, true);

    function prepararTodo(raiz) {
        (raiz || document).querySelectorAll('input[type="date"]').forEach(preparar);
    }

    window.calendarioFechas = {preparar: prepararTodo};
    prepararTodo();
}());
