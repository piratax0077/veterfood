/*
 * Reseñas de la ficha del producto (tienda/partials/producto-resenas).
 * Filtro por estrellas, orden, "Ver más reseñas" y el formulario para escribir una.
 * El formulario todavía no guarda nada: cierra la ventana y da las gracias.
 */
(function () {
    'use strict';

    function plural(cantidad, uno, varios) {
        return cantidad + ' ' + (cantidad === 1 ? uno : varios);
    }

    function iniciarLista(seccion) {
        var lista = seccion.querySelector('[data-resenas-lista]');
        if (!lista) return;

        var porPagina = Number(seccion.dataset.porPagina) || 5;
        var resenas = Array.prototype.slice.call(lista.querySelectorAll('[data-resena]'));
        var vacio = lista.querySelector('[data-resenas-vacio]');
        var estado = seccion.querySelector('[data-resenas-estado]');
        var mas = seccion.querySelector('[data-resenas-mas]');
        var orden = seccion.querySelector('[data-resenas-orden]');
        var quitar = seccion.querySelector('[data-quitar-filtro]');
        var textoFiltro = seccion.querySelector('[data-filtro-texto]');
        var herramientas = seccion.querySelector('[data-resenas-herramientas]');
        var barras = Array.prototype.slice.call(seccion.querySelectorAll('[data-filtro-nota]'));
        var filtro = 0;
        var mostrar = porPagina;

        function coinciden() {
            return resenas.filter(function (resena) {
                return !filtro || Number(resena.dataset.nota) === filtro;
            });
        }

        function pintar() {
            var visibles = coinciden();
            resenas.forEach(function (resena) { resena.hidden = true; });
            visibles.forEach(function (resena, i) { resena.hidden = i >= mostrar; });

            var vistas = Math.min(mostrar, visibles.length);
            estado.textContent = 'Mostrando ' + vistas + ' de ' + plural(visibles.length, 'reseña', 'reseñas');
            vacio.hidden = visibles.length > 0;
            mas.hidden = vistas >= visibles.length;

            quitar.hidden = !filtro;
            textoFiltro.textContent = filtro ? plural(filtro, 'estrella', 'estrellas') : '';
            barras.forEach(function (barra) {
                var activa = Number(barra.dataset.filtroNota) === filtro;
                barra.classList.toggle('is-activa', activa);
                barra.setAttribute('aria-pressed', activa ? 'true' : 'false');
            });
        }

        function ordenar() {
            var criterio = orden ? orden.value : 'recientes';
            var dato = function (resena, nombre) { return Number(resena.dataset[nombre]); };
            resenas.sort(function (a, b) {
                var porFecha = dato(b, 'fecha') - dato(a, 'fecha');
                if (criterio === 'altas') return dato(b, 'nota') - dato(a, 'nota') || porFecha;
                if (criterio === 'bajas') return dato(a, 'nota') - dato(b, 'nota') || porFecha;
                return porFecha;
            });
            resenas.forEach(function (resena) { lista.insertBefore(resena, vacio); });
        }

        // En el celular el resumen queda arriba: se baja a la lista para que se vea el cambio
        function llevarALista() {
            var arriba = herramientas.getBoundingClientRect().top;
            if (arriba < 0 || arriba > window.innerHeight - 160) {
                herramientas.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
        }

        function enfocarPrimera() {
            var primera = coinciden()[0];
            if (primera) primera.focus({preventScroll: true});
        }

        barras.forEach(function (barra) {
            barra.addEventListener('click', function () {
                var nota = Number(barra.dataset.filtroNota);
                filtro = filtro === nota ? 0 : nota;
                mostrar = porPagina;
                pintar();
                llevarALista();
            });
        });

        quitar.addEventListener('click', function () {
            filtro = 0;
            mostrar = porPagina;
            pintar();
            enfocarPrimera();
        });

        if (orden) {
            orden.addEventListener('change', function () {
                ordenar();
                mostrar = porPagina;
                pintar();
            });
        }

        mas.addEventListener('click', function () {
            var primeraNueva = mostrar;
            mostrar += porPagina;
            pintar();
            // Si ya no quedan más, el foco pasa a la primera reseña que apareció
            var nueva = coinciden()[primeraNueva];
            if (nueva && mas.hidden) nueva.focus({preventScroll: true});
        });

        pintar();
    }

    function iniciarFormulario(form) {
        var modal = form.closest('dialog');
        var grupo = form.querySelector('[data-elegir-nota]');
        var campoTexto = form.querySelector('[data-contar]');
        var contador = form.querySelector('[data-contador]');

        // Estrellas para calificar: se encienden al pasar el mouse y quedan en la elegida
        if (grupo) {
            var estrellas = Array.prototype.slice.call(grupo.querySelectorAll('label'));
            var leyenda = grupo.querySelector('[data-nota-texto]');
            var leyendaInicial = leyenda.textContent;

            var elegida = function () {
                var marcada = grupo.querySelector('input:checked');
                return marcada ? Number(marcada.value) : 0;
            };
            var encender = function (nota) {
                estrellas.forEach(function (estrella, i) { estrella.classList.toggle('is-llena', i < nota); });
                leyenda.textContent = nota ? estrellas[nota - 1].dataset.texto : leyendaInicial;
                leyenda.classList.toggle('is-elegida', nota > 0);
            };

            estrellas.forEach(function (estrella, i) {
                estrella.addEventListener('mouseenter', function () { encender(i + 1); });
            });
            grupo.addEventListener('mouseleave', function () { encender(elegida()); });
            grupo.addEventListener('change', function () { encender(elegida()); });
            form.addEventListener('reset', function () { setTimeout(function () { encender(0); }); });
        }

        if (campoTexto && contador) {
            var contar = function () { contador.textContent = campoTexto.value.length + ' / ' + campoTexto.maxLength; };
            campoTexto.addEventListener('input', contar);
            form.addEventListener('reset', function () { setTimeout(contar); });
            contar();
        }

        // validacion.js revisa antes; si llega aquí, todo está bien
        form.addEventListener('submit', function (evento) {
            evento.preventDefault();
            modal.addEventListener('close', function () {
                form.reset();
                // Los avisos de error de intentos anteriores ya no aplican
                document.querySelectorAll('.notificacion--error .notificacion-cerrar').forEach(function (boton) { boton.click(); });
                if (window.notificar) {
                    window.notificar({tipo: 'exito', titulo: 'Gracias por tu reseña', mensaje: 'La revisaremos y la publicaremos en 1 o 2 días hábiles.'});
                }
            }, {once: true});
            modal.querySelector('[data-modal-cerrar]').click();
        });
    }

    // Modal de reseña desde "Mis compras": el botón trae el producto en data-resena-datos
    function iniciarModalCompra() {
        var modal = document.getElementById('modal-resena-compra');
        if (!modal) return;
        var form = document.getElementById('form-resena-compra');
        var foto = modal.querySelector('[data-resena-foto]');
        var nombre = modal.querySelector('[data-resena-nombre]');
        var fotoInicial = foto.innerHTML;

        document.addEventListener('click', function (evento) {
            var abridor = evento.target.closest('[data-resena-datos]');
            if (!abridor || abridor.dataset.modalAbrir !== modal.id) return;
            var datos;
            try { datos = JSON.parse(abridor.dataset.resenaDatos); } catch (e) { return; }

            form.reset();
            form.elements.producto_id.value = datos.producto_id || '';
            nombre.textContent = datos.nombre || 'este producto';
            if (datos.foto) {
                var img = document.createElement('img');
                img.src = datos.foto;
                img.alt = '';
                foto.replaceChildren(img);
            } else {
                foto.innerHTML = fotoInicial;
            }
        }, true);
    }

    function iniciar() {
        document.querySelectorAll('[data-resenas]').forEach(iniciarLista);
        document.querySelectorAll('[data-form-resena]').forEach(iniciarFormulario);
        iniciarModalCompra();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
}());
