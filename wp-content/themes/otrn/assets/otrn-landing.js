/**
 * Portada inmersiva: scrollytelling con GSAP + ScrollTrigger (ETAPA B).
 *
 * Escenas:
 *   1. Hero editorial: panel fotográfico vertical que crece a sangre al hacer
 *      scroll (--expand) mientras el texto del hero se retira.
 *   2. Territorio «Mar, ríos y comunidades»: crossfades del estuario a la bahía.
 *   3. Costa viva: crossfade a las lanchas al atardecer (pin temporal + vello).
 *   4–5. Datos y exploración: reveals por IntersectionObserver tras el pin.
 *
 * Robustez:
 *   - Sin JS, con prefers-reduced-motion o en móvil NO se arma el pin: el CSS
 *     deja las escenas apiladas y legibles (modo estático).
 *   - Si GSAP/ScrollTrigger no cargaron, se usa el MISMO respaldo (IO) para que
 *     ningún contenido quede oculto.
 *
 * Todo se construye dentro de un gsap.context() anclado al contenedor, para
 * desmontarlo sin fugas globales (cambio de modo en resize).
 */
document.addEventListener( 'DOMContentLoaded', function () {
	var root = document.getElementById( 'otrn-landing' );
	if ( ! root ) {
		return;
	}

	// Riel de escenas 01–05 (decorativo; el estado lo marca la animación).
	var railItems = Array.prototype.slice.call( root.querySelectorAll( '.otrn-rail__item' ) );
	function setRail( n ) {
		railItems.forEach( function ( it, i ) {
			it.classList.toggle( 'is-active', ( i + 1 ) === n );
		} );
	}

	// Reveals para las tarjetas (datos / indicadores / noticias).
	var io = null;
	function revelarConIO() {
		var items = root.querySelectorAll( '.otrn-reveal' );
		if ( ! items.length ) {
			return;
		}
		io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					e.target.classList.add( 'is-visible' );
					io.unobserve( e.target );
				}
			} );
		}, { threshold: 0.16, rootMargin: '0px 0px -8% 0px' } );
		items.forEach( function ( el ) { io.observe( el ); } );
	}

	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var mq = window.matchMedia && window.matchMedia( '(min-width: 769px)' );
	var sinGSAP = typeof window.gsap === 'undefined' || typeof window.ScrollTrigger === 'undefined';

	var ctx = null;

	// Desecha cualquier estado inmersivo previo (se usa en el cambio de modo).
	function limpiar() {
		if ( ctx ) {
			ctx.revert();
			ctx = null;
		}
		if ( io ) {
			io.disconnect();
			io = null;
		}
		var st = root.querySelector( '.otrn-stage' );
		if ( st ) {
			st.classList.remove( 'is-immersiva' );
			st.style.removeProperty( '--expand' );
		}
	}

	// --- Escena inmersiva (solo escritorio + GSAP, sin reduced-motion) ---
	function iniciarImmersivo() {
		if ( reduce || sinGSAP || ! ( mq && mq.matches ) ) {
			return false;
		}
		window.gsap.registerPlugin( window.ScrollTrigger );
		var stage = root.querySelector( '.otrn-stage' );
		if ( ! stage ) {
			return false;
		}
		stage.classList.add( 'is-immersiva' );
		stage.style.setProperty( '--expand', 0 ); // panel inicial (derecho)

		ctx = window.gsap.context( function () {
			// --expand (0→1) controla el crecimiento del panel vía clip-path.
			var expand = { v: 0 };
			var tl = window.gsap.timeline( {
				defaults: { ease: 'none' },
				scrollTrigger: {
					trigger: stage,
					start: 'top top',
					end: '+=280%',
					pin: true,
					scrub: 1,
				},
			} );

			// 1→2: el panel crece a sangre mientras el hero se retira.
			tl.to( expand, {
				v: 1, duration: 2,
				onUpdate: function () { stage.style.setProperty( '--expand', expand.v ); },
			}, 1.4 );

			// Crossfades del nivel fotográfico compartido.
			tl.set( '.otrn-layer[data-layer="0"]', { opacity: 1 }, 0 );
			tl.to( '.otrn-layer[data-layer="0"]', { opacity: 0, duration: 1.4 }, 2.0 );
			tl.fromTo( '.otrn-layer[data-layer="1"]', { opacity: 0 }, { opacity: 1, duration: 1.4 }, 2.4 );
			tl.to( '.otrn-layer[data-layer="1"]', { opacity: 0, duration: 1.4 }, 5.4 );
			tl.fromTo( '.otrn-layer[data-layer="2"]', { opacity: 0 }, { opacity: 1, duration: 1.4 }, 5.6 );

			// Velo de legibilidad de las escenas 2 y 3 (texto claro sobre foto):
			// entra antes de que aparezca el texto de la escena 2 y se queda.
			tl.fromTo( '.otrn-stage__grad', { opacity: 0 }, { opacity: 1, duration: 1.0 }, 1.6 );

			// Aparición / retiro del texto de cada escena.
			tl.to( '.otrn-act--hero .otrn-act__inner', { opacity: 0, duration: 1.0 }, 1.4 );
			tl.to( '.otrn-stage__cue', { opacity: 0, duration: 0.6 }, 0.5 );
			tl.fromTo( '.otrn-act--territorio .otrn-act__inner', { opacity: 0, yPercent: 6 }, { opacity: 1, yPercent: 0, duration: 1.2 }, 3.0 );
			tl.to( '.otrn-act--territorio .otrn-act__inner', { opacity: 0, duration: 1.0 }, 5.4 );
			tl.fromTo( '.otrn-act--costa .otrn-act__inner', { opacity: 0, yPercent: 6 }, { opacity: 1, yPercent: 0, duration: 1.2 }, 6.0 );
			// Sostiene la escena costera hasta el fin del pin.
			tl.to( expand, { v: 1, duration: 1.8 }, 7.2 );

			// Riel 01–03 según el progreso del pin.
			window.ScrollTrigger.create( {
				trigger: stage, start: 'top top', end: '+=280%',
				onUpdate: function ( self ) {
					setRail( self.progress < 0.34 ? 1 : ( self.progress < 0.7 ? 2 : 3 ) );
				},
			} );

			// Riel 04–05 en las secciones reales que siguen al pin.
			var datos = root.querySelector( '#otrn-datos' );
			var explora = root.querySelector( '#otrn-explora' );
			if ( datos ) {
				window.ScrollTrigger.create( {
					trigger: datos, start: 'top 60%',
					onEnter: function () { setRail( 4 ); },
					onEnterBack: function () { setRail( 3 ); },
				} );
			}
			if ( explora ) {
				window.ScrollTrigger.create( {
					trigger: explora, start: 'top 60%',
					onEnter: function () { setRail( 5 ); },
					onEnterBack: function () { setRail( 4 ); },
				} );
			}
		}, root );

		return true;
	}

	// Arranca en el modo correcto. En estático, todo visible + reveals por IO.
	function iniciar() {
		limpiar();
		var inmersivo = iniciarImmersivo();
		revelarConIO();
		if ( ! inmersivo ) {
			setRail( 1 );
		}
	}

	iniciar();

	// Recálculo ante cambio de modo (resize entre escritorio y móvil).
	if ( mq ) {
		var onMq = function () {
			limpiar();
			iniciar();
		};
		if ( mq.addEventListener ) {
			mq.addEventListener( 'change', onMq );
		} else if ( mq.addListener ) {
			mq.addListener( onMq );
		}
	}

	// Las fuentes e imágenes desplazan el layout: refresca los disparos.
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( function () {
			if ( window.ScrollTrigger ) {
				window.ScrollTrigger.refresh();
			}
		} );
	}
	window.addEventListener( 'load', function () {
		if ( window.ScrollTrigger ) {
			window.ScrollTrigger.refresh();
		}
	} );
} );
