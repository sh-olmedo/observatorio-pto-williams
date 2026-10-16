/**
 * Menú de navegación accesible: botón tipo «hamburguesa» con captura de foco.
 *
 * La cabecera (header.php) dibuja siempre un <button class="otrn-burger"> y un
 * <nav id="otrn-nav">. En escritorio el nav está a la vista; cuando la fila no
 * cabe (assets/css/otrn-menu.css: 1360 px sin sesión, 1520 px con sesión) se
 * abre como panel lateral. Este script es el único que maneja el estado, de modo
 * que el comportamiento es idéntico en la portada y en las páginas internas:
 *
 *   - aria-expanded / aria-controls / aria-label mantienen el estado expuesto a
 *     los lectores de pantalla; el CSS (no el JS) decide si el nav es visible.
 *   - Escape cierra; un clic fuera del panel cierra; un enlace cierra.
 *   - Mientras está abierto, Tab queda atrapado dentro del nav (focus trap) y el
 *     foco regresa al botón al cerrar.
 *   - Al cruzar el punto de corte (redimensionar la ventana) se resincroniza el
 *     estado para no dejar el nav oculto ni inaccesible.
 *
 * Ver header.php y assets/css/otrn-menu.css (fila compacta y panel lateral).
 */
document.addEventListener( 'DOMContentLoaded', function () {
	var burger = document.querySelector( '.otrn-burger' );
	var nav    = document.getElementById( 'otrn-nav' );

	// Si la cabecera no trae botón y nav (páginas que no la usan), no hacer nada.
	if ( ! burger || ! nav ) {
		return;
	}

	// Etiquetas del botón: header.php trae la de «abrir»; el estado cerrado
	// también debe restaurarla si el usuario redimensiona.
	var etiquetaAbrir  = burger.getAttribute( 'aria-label' ) || 'Abrir menú de navegación';
	var etiquetaCerrar = 'Cerrar menú de navegación';

	var ultimoFoco = null;
	var ultimoPanel = null;

	/**
	 * ¿El CSS está mostrando el panel lateral? El punto de corte vive en el CSS
	 * (y depende de la sesión: con sesión la fila necesita más ancho), así que el
	 * JS lo consulta en vez de repetir un ancho aquí y arriesgarse a
	 * desincronizarse. El botón solo se ve en modo panel.
	 */
	function enPanel() {
		return 'none' !== window.getComputedStyle( burger ).display;
	}

	function esFocable( el ) {
		return (
			el &&
			el.offsetParent !== null && // visible (no display:none)
			! el.hasAttribute( 'disabled' ) &&
			-1 !== [ 'a', 'button', 'input', 'select', 'textarea' ].indexOf( el.tagName.toLowerCase() )
		);
	}

	function focables() {
		return Array.prototype.slice
			.call( nav.querySelectorAll( 'a, button, input, select, textarea' ) )
			.filter( esFocable );
	}

	function abrir() {
		ultimoFoco = document.activeElement;
		nav.setAttribute( 'aria-hidden', 'false' );
		burger.setAttribute( 'aria-expanded', 'true' );
		burger.setAttribute( 'aria-label', etiquetaCerrar );
		burger.classList.add( 'is-active' );
		document.body.classList.add( 'otrn-menu-abierto' );
		var lista = focables();
		if ( lista.length ) {
			lista[0].focus();
		}
		document.addEventListener( 'keydown', tecla );
		document.addEventListener( 'click', fuera, true );
	}

	function cerrar() {
		burger.setAttribute( 'aria-expanded', 'false' );
		burger.setAttribute( 'aria-label', etiquetaAbrir );
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
		document.removeEventListener( 'keydown', tecla );
		document.removeEventListener( 'click', fuera, true );
		// Devolver el foco al botón para que el lector de pantalla no quede
		// «perdido» en el panel que acaba de ocultarse.
		if ( ultimoFoco && typeof ultimoFoco.focus === 'function' ) {
			ultimoFoco.focus();
		}
	}

	function estaAbierto() {
		return burger.getAttribute( 'aria-expanded' ) === 'true';
	}

	/**
	 * Resincroniza el estado al cargar o al cruzar el punto de corte (que puede
	 * cambiar al redimensionar): en escritorio limpia cualquier resto del panel;
	 * en modo panel cerrado deja el nav oculto (CSS `visibility`) y `aria-hidden`.
	 * Solo actúa cuando el modo cambió, para no tocar atributos en cada `resize`.
	 */
	function sincronizar() {
		var panel = enPanel();
		if ( panel === ultimoPanel ) {
			return;
		}
		ultimoPanel = panel;

		if ( ! panel ) {
			burger.setAttribute( 'aria-expanded', 'false' );
			burger.setAttribute( 'aria-label', etiquetaAbrir );
			burger.classList.remove( 'is-active' );
			document.body.classList.remove( 'otrn-menu-abierto' );
			nav.removeAttribute( 'aria-hidden' );
			document.removeEventListener( 'keydown', tecla );
			document.removeEventListener( 'click', fuera, true );
		} else if ( ! estaAbierto() ) {
			nav.setAttribute( 'aria-hidden', 'true' );
		}
	}

	function tecla( e ) {
		if ( 'Escape' === e.key ) {
			e.preventDefault();
			cerrar();
			return;
		}
		if ( 'Tab' === e.key ) {
			// Trampa de foco: Tab y Shift+Tab ciclan dentro del nav.
			var lista = focables();
			if ( ! lista.length ) {
				return;
			}
			var primero = lista[0];
			var ultimo  = lista[ lista.length - 1 ];
			var activo  = document.activeElement;
			var fueraDelNav = nav.contains( activo ) ? false : true;

			if ( e.shiftKey && ( activo === primero || fueraDelNav ) ) {
				e.preventDefault();
				ultimo.focus();
			} else if ( ! e.shiftKey && activo === ultimo ) {
				e.preventDefault();
				primero.focus();
			}
		}
	}

	// Un clic fuera del nav y del botón cierra el panel.
	function fuera( e ) {
		if ( estaAbierto() && ! nav.contains( e.target ) && ! burger.contains( e.target ) ) {
			cerrar();
		}
	}

	burger.addEventListener( 'click', function () {
		estaAbierto() ? cerrar() : abrir();
	} );

	// Un enlace del menú lo cierra al pulsarse (el usuario ya «llegó»).
	nav.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( 'a' ) ) {
			cerrar();
		}
	} );

	// Estado inicial (panel cerrado / fila visible) y resincronización al cruzar
	// el punto de corte con un redimensionado (F1).
	window.addEventListener( 'resize', sincronizar );
	sincronizar();
} );
