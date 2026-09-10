/*
 * Menu lateral global: muestra una seccion a la vez con fundido suave.
 *
 * Marcado:
 *   <div class="menu-lateral-layout" data-menu-memoria="cliente">
 *       <x-menu-lateral :grupos="$menu" />
 *       <div class="menu-lateral-contenido">
 *           <section class="menu-lateral-seccion is-activa" data-menu-panel="resumen">...</section>
 *       </div>
 *   </div>
 * Cualquier boton con data-menu-ir="nombre" dentro del layout abre esa seccion.
 * La seccion activa se refleja en la URL (#nombre) y se recuerda al volver a la pagina.
 */
(function () {
    function iniciar(layout) {
        var paneles = Array.prototype.slice.call(layout.querySelectorAll('[data-menu-panel]'));
        if (!paneles.length) return;

        var nombres = paneles.map(function (panel) { return panel.dataset.menuPanel; });
        var claveMemoria = 'menuLateral:' + (layout.dataset.menuMemoria || location.pathname);
        var contenido = layout.querySelector('.menu-lateral-contenido');
        var franja = layout.querySelector('.menu-lateral-lista');

        // En tablet/movil el menu es una franja horizontal: la desliza para dejar visible la pestana activa.
        function mostrarEnFranja(item, suave) {
            if (!franja || !window.matchMedia('(max-width: 950px)').matches) return;
            var caja = franja.getBoundingClientRect();
            var pos = item.getBoundingClientRect();
            if (pos.left < caja.left || pos.right > caja.right - 24) {
                franja.scrollTo({left: franja.scrollLeft + pos.left - caja.left - 12, behavior: suave ? 'smooth' : 'auto'});
            }
        }

        function mostrar(nombre, desdeClic) {
            if (nombres.indexOf(nombre) < 0) return false;

            paneles.forEach(function (panel) {
                panel.classList.toggle('is-activa', panel.dataset.menuPanel === nombre);
            });

            layout.querySelectorAll('.menu-lateral-item[data-menu-ir]').forEach(function (item) {
                var activo = item.dataset.menuIr === nombre;
                item.classList.toggle('is-activo', activo);
                if (activo) {
                    item.setAttribute('aria-current', 'page');
                    mostrarEnFranja(item, desdeClic);
                } else {
                    item.removeAttribute('aria-current');
                }
            });

            if (location.hash !== '#' + nombre) {
                history.replaceState(null, '', '#' + nombre);
            }
            try { sessionStorage.setItem(claveMemoria, nombre); } catch (e) {}

            if (desdeClic && contenido && contenido.getBoundingClientRect().top < 0) {
                contenido.scrollIntoView({behavior: 'smooth', block: 'start'});
            }
            return true;
        }

        layout.addEventListener('click', function (evento) {
            var disparador = evento.target.closest('[data-menu-ir]');
            if (!disparador || !layout.contains(disparador)) return;
            if (mostrar(disparador.dataset.menuIr, true)) evento.preventDefault();
        });

        window.addEventListener('hashchange', function () {
            mostrar(location.hash.replace('#', ''), false);
        });

        var inicial = location.hash.replace('#', '');
        if (nombres.indexOf(inicial) < 0) {
            try { inicial = sessionStorage.getItem(claveMemoria) || ''; } catch (e) { inicial = ''; }
        }
        if (nombres.indexOf(inicial) >= 0) mostrar(inicial, false);
    }

    function iniciarTodos() {
        document.querySelectorAll('.menu-lateral-layout').forEach(iniciar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarTodos);
    } else {
        iniciarTodos();
    }
}());
