/**
 * Menú de navegación accesible: botón tipo «hamburguesa» con captura de foco.
 *
 * La cabecera (header.php) dibuja siempre un <button class="otrn-burger"> y un
 * <nav id="otrn-nav">. En escritorio el nav está a la vista; en móvil el nav se
 * oculta y el botón lo abre como panel. Este script es el único que maneja el
 * estado para que el comportamiento sea idéntico con y sin JavaScript:
 *
 *   - aria-expanded / aria-controls mantienen el estado expuesto a los lectores
 *     de pantalla; el CSS (no el JS) decide si el nav es visible.
 *   - Escape cierra; un clic fuera del panel cierra.
 *   - Mientras está abierto, Tab queda atrapado dentro del nav (focus trap) y el
 *     foco regresa al botón al cerrar.
 *
 * Ver header.php y assets/css/otrn-landing.css (media queries de móvil).
 */
document.addEventListener( 'DOMContentLoaded', function () {
	var burger = document.querySelector( '.otrn-burger' );
	var nav    = document.getElementById( 'otrn-nav' );

	// Si la cabecera no trae botón y nav (páginas que no la usan), no hacer nada.
	if ( ! burger || ! nav ) {
		return;
	}

	var ultimoFoco = null;

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
		nav.setAttribute( 'aria-hidden', 'true' );
		burger.setAttribute( 'aria-expanded', 'false' );
		burger.classList.remove( 'is-active' );
		document.body.classList.remove( 'otrn-menu-abierto' );
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
} );
