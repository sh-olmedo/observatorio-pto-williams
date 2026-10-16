/**
 * Navegación accesible (F1): desplegables de escritorio, acordeones del panel
 * móvil y menú de cuenta.
 *
 * La cabecera (`header.php`) pinta siempre la misma estructura —`#otrn-nav` con
 * sus grupos, el icono de cuenta y el botón de menú— y el CSS decide qué se ve
 * en cada ancho. Este script es el único que gobierna el estado, de modo que el
 * comportamiento es idéntico en la portada y en las páginas internas:
 *
 *   - Desplegables (escritorio y «Más» de tableta): el botón de cada grupo abre
 *     su panel; solo hay uno abierto a la vez; al pasar el ratón se abren solo
 *     con punteros finos (`hover: hover`) y solo en la fila.
 *   - Acordeones (panel móvil): el mismo botón despliega en línea; ahí cada
 *     grupo es independiente y el foco queda atrapado en el panel.
 *   - Menú de cuenta: el icono abre «Cerrar sesión» (solo con sesión iniciada).
 *     Sin sesión el icono es un enlace directo al login y no necesita JS.
 *   - Escape cierra (y devuelve el foco al botón), un clic fuera cierra, elegir
 *     un enlace cierra, y al cruzar el punto de corte se resincroniza el estado.
 *   - El JS no repite ningún ancho: consulta el CSS (`getComputedStyle`) para
 *     saber si está en modo fila o en modo panel.
 *
 * Ver header.php, `otrn_nav_estructura()` en functions.php y
 * assets/css/otrn-menu.css (fila, colapso progresivo y panel).
 */
document.addEventListener( 'DOMContentLoaded', function () {
	var burger = document.querySelector( '.otrn-burger' );
	var nav    = document.getElementById( 'otrn-nav' );

	// Si la cabecera no trae botón y nav (páginas que no la usan), no hacer nada.
	if ( ! burger || ! nav ) {
		return;
	}

	// Marca de «JS disponible»: el CSS solo deja los desplegables abiertos por
	// `:hover`/`:focus-within` cuando este script no ha corrido (respaldo sin JS).
	var header = nav.closest( '.otrn-header' );
	if ( header ) {
		header.classList.add( 'otrn-js' );
	}

	var ETIQUETA_ABRIR  = burger.getAttribute( 'aria-label' ) || 'Abrir menú de navegación';
	var ETIQUETA_CERRAR = 'Cerrar menú de navegación';

	var ultimoFoco  = null; // Foco previo a abrir el panel móvil.
	var ultimoPanel = null; // Último modo conocido (fila / panel).

	var cuenta      = document.querySelector( '.otrn-cuenta' );
	var cuentaBoton = cuenta ? cuenta.querySelector( '.otrn-cuenta__boton' ) : null;
	var cuentaPanel = cuenta ? cuenta.querySelector( '.otrn-cuenta__panel' ) : null;

	/* ---------- Utilidades ------------------------------------------------ */

	/**
	 * ¿El CSS está mostrando el panel lateral? El punto de corte vive en el CSS
	 * (y depende del ancho disponible), así que el JS lo consulta en vez de
	 * repetir un ancho aquí y arriesgarse a desincronizarse.
	 */
	function enPanel() {
		return 'none' !== window.getComputedStyle( burger ).display;
	}

	/** Solo los punteros finos abren al pasar el ratón (en táctil el hover se queda «pegado»). */
	function punteroFino() {
		return !!( window.matchMedia && window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches );
	}

	function esFocable( el ) {
		if ( ! el || el.hasAttribute( 'disabled' ) ) {
			return false;
		}
		if ( -1 === [ 'a', 'button', 'input', 'select', 'textarea' ].indexOf( el.tagName.toLowerCase() ) ) {
			return false;
		}
		// Visible de verdad: `display:none` (offsetParent nulo) y `visibility:hidden`
		// (que sí conserva offsetParent) quedan fuera del recorrido de foco.
		return el.offsetParent !== null && 'hidden' !== window.getComputedStyle( el ).visibility;
	}

	function focables( raiz ) {
		return Array.prototype.slice
			.call( ( raiz || nav ).querySelectorAll( 'a, button, input, select, textarea' ) )
			.filter( esFocable );
	}

	/* ---------- Desplegables del nav (fila y «Más») ----------------------- */

	var items = Array.prototype.slice.call(
		nav.querySelectorAll( '.otrn-nav__item--grupo, .otrn-nav__item--mas' )
	);

	function boton( li ) {
		return li.querySelector( '.otrn-nav__boton' );
	}

	function estaAbierto( li ) {
		return li.classList.contains( 'is-abierto' );
	}

	function grupoAbierto() {
		for ( var i = 0; i < items.length; i++ ) {
			if ( estaAbierto( items[i] ) ) {
				return items[i];
			}
		}
		return null;
	}

	function abrirGrupo( li, foco ) {
		// En la fila solo hay un desplegable abierto; en el panel móvil cada
		// acordeón es independiente.
		if ( ! enPanel() ) {
			items.forEach( function ( otro ) {
				if ( otro !== li ) {
					cerrarGrupo( otro, false );
				}
			} );
		}
		li.classList.add( 'is-abierto' );
		boton( li ).setAttribute( 'aria-expanded', 'true' );
		if ( foco ) {
			var lista = focables( li.querySelector( '.otrn-subnav' ) );
			if ( lista.length ) {
				lista[0].focus();
			}
		}
	}

	function cerrarGrupo( li, foco ) {
		if ( ! estaAbierto( li ) ) {
			return;
		}
		li.classList.remove( 'is-abierto' );
		boton( li ).setAttribute( 'aria-expanded', 'false' );
		if ( foco ) {
			boton( li ).focus();
		}
	}

	function cerrarGrupos() {
		items.forEach( function ( li ) {
			cerrarGrupo( li, false );
		} );
	}

	items.forEach( function ( li ) {
		var b = boton( li );
		if ( ! b ) {
			return;
		}
		b.addEventListener( 'click', function () {
			if ( estaAbierto( li ) ) {
				cerrarGrupo( li, false );
			} else {
				abrirGrupo( li, false );
			}
		} );
		// Ratón: abrir al entrar y cerrar al salir, solo en la fila.
		li.addEventListener( 'mouseenter', function () {
			if ( punteroFino() && ! enPanel() ) {
				abrirGrupo( li, false );
			}
		} );
		li.addEventListener( 'mouseleave', function () {
			if ( punteroFino() && ! enPanel() ) {
				cerrarGrupo( li, false );
			}
		} );
		// Teclado: si el foco sale del grupo, el desplegable se cierra.
		li.addEventListener( 'focusout', function ( e ) {
			if ( enPanel() ) {
				return;
			}
			if ( ! li.contains( e.relatedTarget ) ) {
				cerrarGrupo( li, false );
			}
		} );
	} );

	/* ---------- Menú de cuenta -------------------------------------------- */

	function cuentaAbierta() {
		return !! cuenta && cuenta.classList.contains( 'is-abierto' );
	}

	function abrirCuenta() {
		if ( ! cuenta ) {
			return;
		}
		cerrarGrupos();
		cerrar();
		cuenta.classList.add( 'is-abierto' );
		if ( cuentaBoton ) {
			cuentaBoton.setAttribute( 'aria-expanded', 'true' );
		}
	}

	function cerrarCuenta( foco ) {
		if ( ! cuentaAbierta() ) {
			return;
		}
		cuenta.classList.remove( 'is-abierto' );
		if ( cuentaBoton ) {
			cuentaBoton.setAttribute( 'aria-expanded', 'false' );
			if ( foco ) {
				cuentaBoton.focus();
			}
		}
	}

	if ( cuentaBoton && cuentaPanel ) {
		cuentaBoton.addEventListener( 'click', function () {
			if ( cuentaAbierta() ) {
				cerrarCuenta( true );
			} else {
				abrirCuenta();
			}
		} );
	}

	/* ---------- Panel móvil ----------------------------------------------- */

	function panelAbierto() {
		return burger.getAttribute( 'aria-expanded' ) === 'true';
	}

	function abrir() {
		cerrarGrupos();
		cerrarCuenta( false );
		ultimoFoco = document.activeElement;
		nav.setAttribute( 'aria-hidden', 'false' );
		burger.setAttribute( 'aria-expanded', 'true' );
		burger.setAttribute( 'aria-label', ETIQUETA_CERRAR );
		burger.classList.add( 'is-active' );
		document.body.classList.add( 'otrn-menu-abierto' );
		var lista = focables( nav );
		if ( lista.length ) {
			lista[0].focus();
		}
	}

	function cerrar() {
		if ( ! panelAbierto() ) {
			return;
		}
		burger.setAttribute( 'aria-expanded', 'false' );
		burger.setAttribute( 'aria-label', ETIQUETA_ABRIR );
		burger.classList.remove( 'is-active' );
		document.body.classList.remove( 'otrn-menu-abierto' );
		// En modo panel el nav queda fuera de pantalla: se oculta a lectores de
		// pantalla. En escritorio la fila vuelve a estar a la vista, así que nunca
		// debe quedar `aria-hidden`.
		if ( enPanel() ) {
			nav.setAttribute( 'aria-hidden', 'true' );
		} else {
			nav.removeAttribute( 'aria-hidden' );
		}
		// Devolver el foco a quien abrió el panel (o al propio botón, si el
		// navegador no le dio el foco al pulsarlo) para que el lector de pantalla
		// no quede «perdido» en el panel que acaba de ocultarse.
		if ( ultimoFoco && ultimoFoco !== document.body && typeof ultimoFoco.focus === 'function' ) {
			ultimoFoco.focus();
		} else {
			burger.focus();
		}
	}

	/**
	 * Resincroniza el estado al cargar o al cruzar el punto de corte (que puede
	 * cambiar al redimensionar): cierra desplegables, acordeones y cuenta para no
	 * dejar estados a medias, y en fila limpia cualquier resto del panel. Solo
	 * actúa cuando el modo cambió, para no tocar atributos en cada `resize`.
	 */
	function sincronizar() {
		var panel = enPanel();
		if ( panel === ultimoPanel ) {
			return;
		}
		ultimoPanel = panel;

		cerrarGrupos();
		cerrarCuenta( false );

		if ( ! panel ) {
			burger.setAttribute( 'aria-expanded', 'false' );
			burger.setAttribute( 'aria-label', ETIQUETA_ABRIR );
			burger.classList.remove( 'is-active' );
			document.body.classList.remove( 'otrn-menu-abierto' );
			nav.removeAttribute( 'aria-hidden' );
		} else if ( ! panelAbierto() ) {
			nav.setAttribute( 'aria-hidden', 'true' );
		}
	}

	/* ---------- Eventos globales ------------------------------------------ */

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key ) {
			var grupo = grupoAbierto();
			if ( grupo ) {
				e.preventDefault();
				cerrarGrupo( grupo, true );
				return;
			}
			if ( cuentaAbierta() ) {
				e.preventDefault();
				cerrarCuenta( true );
				return;
			}
			if ( panelAbierto() ) {
				e.preventDefault();
				cerrar();
			}
			return;
		}
		if ( 'Tab' === e.key && panelAbierto() ) {
			// Trampa de foco: Tab y Shift+Tab ciclan dentro del panel.
			var lista = focables( nav );
			if ( ! lista.length ) {
				return;
			}
			var primero = lista[0];
			var ultimo  = lista[ lista.length - 1 ];
			var activo  = document.activeElement;
			var fuera   = ! nav.contains( activo );

			if ( e.shiftKey && ( activo === primero || fuera ) ) {
				e.preventDefault();
				ultimo.focus();
			} else if ( ! e.shiftKey && activo === ultimo ) {
				e.preventDefault();
				primero.focus();
			}
		}
	} );

	document.addEventListener( 'click', function ( e ) {
		// Un clic fuera del nav cierra los desplegables.
		if ( ! nav.contains( e.target ) ) {
			cerrarGrupos();
		} else if ( e.target.closest( '.otrn-subnav a' ) ) {
			// Elegir un destino cierra el desplegable que lo contenía.
			cerrarGrupos();
		}
		// Un clic fuera del bloque de cuenta cierra su menú.
		if ( cuenta && ! cuenta.contains( e.target ) ) {
			cerrarCuenta( false );
		}
		// Un clic fuera del panel móvil (y del botón) lo cierra.
		if ( panelAbierto() && ! nav.contains( e.target ) && ! burger.contains( e.target ) ) {
			cerrar();
		}
	} );

	burger.addEventListener( 'click', function () {
		if ( panelAbierto() ) {
			cerrar();
		} else {
			abrir();
		}
	} );

	// Un enlace del panel lo cierra al pulsarse (el usuario ya «llegó»).
	nav.addEventListener( 'click', function ( e ) {
		if ( panelAbierto() && e.target.closest( 'a' ) ) {
			cerrar();
		}
	} );

	// Estado inicial (panel cerrado / fila visible) y resincronización al cruzar
	// el punto de corte con un redimensionado (F1).
	window.addEventListener( 'resize', sincronizar );
	sincronizar();
} );
