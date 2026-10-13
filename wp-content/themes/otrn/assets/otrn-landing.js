/**
 * Portada inmersiva: scrollytelling con GSAP + ScrollTrigger.
 *
 * Escenas:
 *   1. Hero: Ken Burns (escala lenta) + parallax del velo al hacer scroll.
 *   2. Narrativa: columna visual fija; el paso de texto que ocupa el centro
 *      de pantalla activa su fotografía (crossfade).
 *   3. Reveal: los bloques .otrn-reveal aparecen al entrar en viewport.
 *
 * Robustez (lo más importante):
 *   - Si el sistema pide `prefers-reduced-motion: reduce` NO se arma GSAP:
 *     un IntersectionObserver simple hace los reveals y el crossfade sin
 *     animación, y el CSS deja todo visible.
 *   - Si GSAP/ScrollTrigger no cargaron, se usa el MISMO respaldo para que
 *     ningún contenido quede oculto (los .otrn-reveal arrancan con opacity:0).
 *
 * Toda la creación de animaciones va dentro de un `gsap.context()` anclado al
 * contenedor, para que se pueda desmontar de una y sin fugas globales.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	var root = document.getElementById( 'otrn-landing' );
	if ( ! root ) {
		return;
	}

	// --- Respaldo sin animación (reduced-motion o GSAP ausente) ----------
	function revelarConIO() {
		var items = root.querySelectorAll( '.otrn-reveal' );
		if ( ! items.length ) {
			return;
		}
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					e.target.classList.add( 'is-visible' );
					io.unobserve( e.target );
				}
			} );
		}, { threshold: 0.18, rootMargin: '0px 0px -8% 0px' } );
		items.forEach( function ( el ) { io.observe( el ); } );
	}

	function crossfadeConIO() {
		var pasos  = root.querySelectorAll( '.otrn-landing-paso' );
		var fotos  = root.querySelectorAll( '.otrn-landing-foto' );
		if ( ! pasos.length || ! fotos.length ) {
			return;
		}
		// Marca la primera foto activa al arrancar.
		var primera = fotos[0];
		if ( primera ) {
			primera.classList.add( 'is-activa' );
		}
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					var idx = e.target.getAttribute( 'data-foto' ) || '0';
					fotos.forEach( function ( f ) {
						f.classList.toggle( 'is-activa', f.getAttribute( 'data-idx' ) === idx );
					} );
				}
			} );
		}, { rootMargin: '-45% 0px -45% 0px', threshold: 0 } );
		pasos.forEach( function ( p ) { io.observe( p ); } );
	}

	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var sinGSAP = typeof window.gsap === 'undefined' || typeof window.ScrollTrigger === 'undefined';

	if ( reduce || sinGSAP ) {
		// Sin GSAP: el contenido tiene que verse igual, solo sin movimiento.
		revelarConIO();
		crossfadeConIO();
		return;
	}

	// --- Escena completa con GSAP ----------------------------------------
	window.gsap.registerPlugin( window.ScrollTrigger );

	var ctx = window.gsap.context( function () {
		// 1 · Hero: Ken Burns (escala) + parallax del velo, scrub sobre el alto
		//    de la propia sección.
		window.gsap.to( '.otrn-landing-hero__media img', {
			scale: 1.22,
			ease: 'none',
			scrollTrigger: {
				trigger: '.otrn-landing-hero',
				start: 'top top',
				end: 'bottom top',
				scrub: true,
			},
		} );
		window.gsap.to( '.otrn-landing-hero__velo', {
			opacity: 0.7,
			ease: 'none',
			scrollTrigger: {
				trigger: '.otrn-landing-hero',
				start: 'top top',
				end: 'center top',
				scrub: true,
			},
		} );

		// 2 · Narrativa: cada paso activa su foto cuando el paso toca el centro.
		var pasos = root.querySelectorAll( '.otrn-landing-paso' );
		var fotos = root.querySelectorAll( '.otrn-landing-foto' );
		pasos.forEach( function ( paso ) {
			window.ScrollTrigger.create( {
				trigger: paso,
				start: 'top center',
				end: 'bottom center',
				onToggle: function ( self ) {
					if ( ! self.isActive ) {
						return;
					}
					var idx = paso.getAttribute( 'data-foto' ) || '0';
					fotos.forEach( function ( f ) {
						f.classList.toggle( 'is-activa', f.getAttribute( 'data-idx' ) === idx );
					} );
				},
			} );
		} );
		// La primera foto arranca activa por si el primer paso no dispara.
		if ( fotos.length ) {
			fotos[0].classList.add( 'is-activa' );
		}

		// 3 · Reveal: aparece cada bloque al entrar (CSS transiciona .is-visible).
		window.gsap.utils
			.toArray( '.otrn-reveal' )
			.forEach( function ( el ) {
				window.ScrollTrigger.create( {
					trigger: el,
					start: 'top 85%',
					onEnter: function () { el.classList.add( 'is-visible' ); },
				} );
			} );
	}, root );

	// Al cargar las fuentes el layout (h1 con clamp) se desplaza: recalcula los
	// puntos de disparo. Igual de robusto ante el fin de carga de las imágenes.
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( function () {
			ctx.get( 'ScrollTrigger' ) && window.ScrollTrigger.refresh();
		} );
	}
	window.addEventListener( 'load', function () {
		window.ScrollTrigger.refresh();
	} );
} );
