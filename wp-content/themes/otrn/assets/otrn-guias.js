/* ======================================================================
   Guía de usuario — buscador e índice (assets/js exclusivo de `/guias/`)
   ----------------------------------------------------------------------
   Dos tareas, ambas progresivas: sin este archivo la guía sigue completa en
   la página (todos los temas están en el documento, el índice lleva a cada
   ancla y la navegación anterior/siguiente funciona).

   1. Buscador: filtra los temas por título y texto (sin distinguir
      mayúsculas ni tildes) y cuenta los resultados en un aviso `role=status`.
   2. Tema actual: marca el enlace del índice que corresponde al tema que se
      está leyendo (`aria-current="true"`, que es lo que pinta la hoja).
      Se busca la última sección cuya cabecera ya pasó el borde superior, así
      que funciona igual con secciones largas o cortas, y nunca se queda sin
      ninguno activo.

   El índice se pinta dos veces a propósito (columna fija y desplegable de
   pantalla pequeña): se resaltan los dos y el desplegable se cierra al elegir
   un tema.
   ====================================================================== */
(function () {
  'use strict';

  var raiz = document.querySelector('.otrn-guia');
  if (!raiz) {
    return;
  }

  var buscador = document.getElementById('otrn-guia-q');
  var aviso    = document.getElementById('otrn-guia-resultado');
  var vacio    = document.getElementById('otrn-guia-vacio');
  var temas    = Array.prototype.slice.call(raiz.querySelectorAll('.otrn-guia-tema'));
  var enlaces  = Array.prototype.slice.call(raiz.querySelectorAll('.otrn-guia-lista a[data-indice]'));
  var desplegable = raiz.querySelector('.otrn-guia__temas-movil');

  /* ---- Utilidades ---------------------------------------------------- */
  function normaliza(texto) {
    var t = (texto || '').toString();
    if (t.normalize) {
      t = t.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }
    return t.toLowerCase();
  }

  function temaDe(id) {
    var encontrado = null;
    temas.forEach(function (tema) {
      if (tema.id === id) {
        encontrado = tema;
      }
    });
    return encontrado;
  }

  /* ---- 1 · Buscador -------------------------------------------------- */
  function filtrar() {
    var termino = buscador ? normaliza(buscador.value.trim()) : '';
    var visibles = 0;

    temas.forEach(function (tema) {
      var coincide = !termino || normaliza(tema.textContent).indexOf(termino) !== -1;
      tema.hidden = !coincide;
      if (coincide) {
        visibles++;
      }
    });

    enlaces.forEach(function (enlace) {
      var tema = temaDe(enlace.getAttribute('data-indice'));
      var visible = !!tema && !tema.hidden;
      enlace.parentNode.hidden = !visible;
      if (!visible) {
        enlace.removeAttribute('aria-current');
      }
    });

    if (vacio) {
      vacio.hidden = visibles > 0;
    }
    if (aviso) {
      if (!termino) {
        aviso.textContent = '';
      } else if (visibles === 0) {
        aviso.textContent = 'Sin resultados.';
      } else {
        aviso.textContent = visibles + (visibles === 1 ? ' tema coincide.' : ' temas coinciden.');
      }
    }

    marcarTemaActual();
  }

  if (buscador) {
    buscador.addEventListener('input', filtrar);
    buscador.addEventListener('search', filtrar);
    buscador.addEventListener('keydown', function (evento) {
      if ('Escape' === evento.key && buscador.value) {
        buscador.value = '';
        filtrar();
      }
    });
  }

  /* ---- 2 · Tema actual en el índice ---------------------------------- */
  var activo = null;

  function temaEnLectura() {
    var elegido = null;
    temas.forEach(function (tema) {
      if (tema.hidden) {
        return;
      }
      if (tema.getBoundingClientRect().top <= 130) {
        elegido = tema;
      }
    });
    // Antes del primer tema, se marca el primero visible.
    if (!elegido) {
      temas.some(function (tema) {
        if (!tema.hidden) {
          elegido = tema;
          return true;
        }
        return false;
      });
    }
    return elegido;
  }

  function marcarTemaActual() {
    var tema = temaEnLectura();
    var id = tema ? tema.id : null;
    if (id === activo) {
      return;
    }
    activo = id;
    enlaces.forEach(function (enlace) {
      if (enlace.getAttribute('data-indice') === id && !enlace.parentNode.hidden) {
        enlace.setAttribute('aria-current', 'true');
      } else {
        enlace.removeAttribute('aria-current');
      }
    });
  }

  var pendiente = false;
  function alDesplazar() {
    if (pendiente) {
      return;
    }
    pendiente = true;
    window.requestAnimationFrame(function () {
      pendiente = false;
      marcarTemaActual();
    });
  }

  window.addEventListener('scroll', alDesplazar, { passive: true });
  window.addEventListener('resize', alDesplazar);

  /* ---- 3 · Desplegable de pantalla pequeña ---------------------------- */
  if (desplegable) {
    desplegable.addEventListener('click', function (evento) {
      var enlace = evento.target.closest ? evento.target.closest('a[data-indice]') : null;
      if (enlace) {
        desplegable.open = false;
      }
    });
  }

  marcarTemaActual();
})();
