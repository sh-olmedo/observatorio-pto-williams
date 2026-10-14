<?php
/**
 * Portada del Observatorio Turístico de Arauco — experiencia inmersiva.
 *
 * Scrollytelling en 5 escenas (storyboard aprobado · segunda propuesta, ETAPA B):
 *   1. Hero editorial asimétrico (fondo claro + fotografía aérea vertical del
 *      estuario, 22.08.51) que se expande a sangre al hacer scroll.
 *   2. Territorio «Mar, ríos y comunidades» (bahía 22.09.17): de lo aéreo a lo
 *      costero; el texto forma parte de la escena.
 *   3. Costa viva «Una costa viva que proyecta su historia» (lanchas 22.17.07):
 *      escena panorámica con pin temporal.
 *   4. Datos «Un territorio con oportunidades» sobre superficie clara.
 *   5. Exploración: indicadores + noticias funcionales.
 *
 * Arquitectura (ver assets/otrn-landing.js y assets/css/otrn-landing.css):
 *   - MODO ESTÁTICO (defecto: sin JS, prefers-reduced-motion y móvil): las
 *     escenas se apilan a sangre, cada una con su foto y su texto legible.
 *   - MODO INMERSIVO (.is-immersiva, escritorio + GSAP): un nivel fotográfico
 *     compartido se pinnea y hace crossfade entre las 3 escenas con scrub
 *     reversible; el hero arranca como panel vertical que crece a sangre.
 *
 * Los datos funcionales (cifras, indicadores y noticias) salen del CMS. Las
 * cifras son herencia del Observatorio de Rapa Nui y se etiquetan como
 * provisionales mientras se migran a la comuna de Arauco.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

// --- Datos funcionales (no se inventan; salen del CMS) -------------------
$datos       = otrn_datos_portada();
$indicadores = get_posts( array( 'post_type' => 'indicador', 'post_status' => 'publish', 'numberposts' => 6 ) );
$noticias    = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 6 ) );

// --- Fotografías aprobadas (references/web → assets/img/arauco) ----------
$otrn_img     = get_stylesheet_directory_uri() . '/assets/img/arauco/';
$img_estuario = $otrn_img . 'estuario.webp';            // 22.08.51 · hero vertical
$img_bahia    = $otrn_img . 'hero-bahia.webp';         // 22.09.17 · territorio
$img_costa    = $otrn_img . 'lanchas-atardecer.webp';  // 22.17.07 · costa viva

// Enlaces funcionales (mismos que la cabecera / portada original).
$otrn_url_indicadores = get_post_type_archive_link( 'indicador' );
$otrn_url_noticias    = otrn_noticias_url();
$otrn_url_acerca      = otrn_acerca_url();

// Íconos decorativos de las tarjetas de datos (HTML estático, sin entrada de
// usuario: se inyectan tal cual con un phpcs:ignore documentado).
$otrn_ico = array(
	'people' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-5 6-5s6 1.7 6 5"/><circle cx="17.5" cy="9" r="2.1"/><path d="M15.5 20c0-2.1 1-3.5 3-3.5"/></svg>',
	'bed'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 7v11M3 12h18v6M21 12v-1a3 3 0 0 0-3-3h-8v4"/><circle cx="6.5" cy="10.5" r="1.4"/></svg>',
	'chart'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20V11M10 20V4M16 20v-7M22 20H2"/></svg>',
);
$otrn_icos = array( $otrn_ico['people'], $otrn_ico['bed'], $otrn_ico['chart'] );
?>

<div id="otrn-landing" class="otrn-landing" data-otrn-landing>
	<!-- Riel de escenas (indicador vertical 01–05). Decorativo; el estado lo
	     marca JS. Encaja con la composición del storyboard. -->
	<nav class="otrn-rail" aria-label="Escenas de la portada">
		<ol class="otrn-rail__lista">
			<li class="otrn-rail__item is-active"><a href="#otrn-hero"><span class="otrn-rail__n">01</span><span class="otrn-rail__d" aria-hidden="true"></span></a></li>
			<li class="otrn-rail__item"><a href="#otrn-territorio"><span class="otrn-rail__n">02</span><span class="otrn-rail__d" aria-hidden="true"></span></a></li>
			<li class="otrn-rail__item"><a href="#otrn-costa"><span class="otrn-rail__n">03</span><span class="otrn-rail__d" aria-hidden="true"></span></a></li>
			<li class="otrn-rail__item"><a href="#otrn-datos"><span class="otrn-rail__n">04</span><span class="otrn-rail__d" aria-hidden="true"></span></a></li>
			<li class="otrn-rail__item"><a href="#otrn-explora"><span class="otrn-rail__n">05</span><span class="otrn-rail__d" aria-hidden="true"></span></a></li>
		</ol>
	</nav>

	<!-- ESCENAS 1–3 · Escenario inmersivo (estuario → bahía → lanchas).
	     En modo estático las tres <article class="otrn-act"> se apilan a sangre;
	     en modo inmersivo se convierten en capas de texto sobre el nivel
	     fotográfico compartido que pinnea y crossfade. -->
	<section class="otrn-stage" data-otrn-stage aria-label="Narrativa territorial de Arauco">
		<div class="otrn-stage__viewport">

			<!-- Nivel fotográfico compartido (solo modo inmersivo). -->
			<div class="otrn-stage__media" aria-hidden="true">
				<figure class="otrn-layer is-active" data-layer="0">
					<img src="<?php echo esc_url( $img_estuario ); ?>" alt="Vista aérea del estuario y la boca de río de Arauco" fetchpriority="high" decoding="async">
				</figure>
				<figure class="otrn-layer" data-layer="1">
					<img src="<?php echo esc_url( $img_bahia ); ?>" alt="Bahía y pueblo costero de Arauco" loading="lazy" decoding="async">
				</figure>
				<figure class="otrn-layer" data-layer="2">
					<img src="<?php echo esc_url( $img_costa ); ?>" alt="Lanchas de pesca en el muelle al atardecer" loading="lazy" decoding="async">
				</figure>
				<div class="otrn-stage__grad"></div>
			</div>
			<!-- Acto 1 · Hero editorial asimétrico. -->
			<article class="otrn-act otrn-act--hero" id="otrn-hero" data-scene="1" style="--scene:url('<?php echo esc_url( $img_estuario ); ?>')">
				<div class="otrn-act__grad"></div>
				<div class="wrap otrn-act__inner">
					<p class="otrn-act__eyebrow">Observatorio Turístico</p>
					<h1 class="otrn-act__titulo">Arauco, territorio que inspira</h1>
					<p class="otrn-act__lead">Conectamos datos, territorio y personas para un turismo más sostenible en la costa y los valles de Arauco.</p>
					<a class="otrn-act__cta" href="<?php echo esc_url( $otrn_url_indicadores ); ?>">Explorar indicadores <span class="otrn-act__arrow" aria-hidden="true">→</span></a>
				</div>
				<p class="otrn-stage__cue"><span class="otrn-stage__cue-ico" aria-hidden="true"></span>Desliza para descubrir</p>
			</article>

			<!-- Acto 2 · Territorio. -->
			<article class="otrn-act otrn-act--territorio" id="otrn-territorio" data-scene="2" style="--scene:url('<?php echo esc_url( $img_bahia ); ?>')">
				<div class="otrn-act__grad"></div>
				<div class="wrap otrn-act__inner">
					<p class="otrn-act__eyebrow">Nuestro territorio</p>
					<h2 class="otrn-act__titulo">Mar, ríos y comunidades</h2>
					<p class="otrn-act__lead">Arauco es un territorio costero y rural, donde el mar, los ríos, los bosques y sus comunidades dan forma a una rica identidad cultural, gastronómica y natural.</p>
					<a class="otrn-act__cta otrn-act__cta--outline" href="<?php echo esc_url( $otrn_url_acerca ); ?>">Conoce más sobre Arauco <span class="otrn-act__arrow" aria-hidden="true">→</span></a>
				</div>
			</article>

			<!-- Acto 3 · Costa viva. -->
			<article class="otrn-act otrn-act--costa" id="otrn-costa" data-scene="3" style="--scene:url('<?php echo esc_url( $img_costa ); ?>')">
				<div class="otrn-act__grad"></div>
				<div class="wrap otrn-act__inner">
					<p class="otrn-act__eyebrow">Tradición y futuro</p>
					<h2 class="otrn-act__titulo">Una costa viva que proyecta su historia</h2>
					<p class="otrn-act__lead">La pesca artesanal, la gastronomía local y sus paisajes costeros son parte de un territorio que mira al futuro con sus raíces en la comunidad.</p>
					<a class="otrn-act__cta otrn-act__cta--link" href="<?php echo esc_url( $otrn_url_noticias ); ?>">Explorar historias <span class="otrn-act__arrow" aria-hidden="true">→</span></a>
				</div>
			</article>

		</div>
	</section>
	<?php if ( $datos ) : ?>
	<!-- 4 · DATOS «Un territorio con oportunidades» (superficie clara).
	     Cifras provisionales: herencia de Rapa Nui, migrando a Arauco. -->
	<section class="otrn-landing-seccion otrn-landing-datos" id="otrn-datos" data-scene="4" aria-labelledby="otrn-datos-titulo">
		<div class="wrap">
			<div class="otrn-datos-head">
				<div class="otrn-datos-head__izq">
					<p class="otrn-act__eyebrow">En cifras</p>
					<h2 class="otrn-act__titulo" id="otrn-datos-titulo">Un territorio con oportunidades</h2>
				</div>
				<p class="otrn-datos-head__der">El turismo es un motor de desarrollo para las comunidades de Arauco. Conoce las principales cifras del territorio y su evolución en los últimos años.</p>
			</div>
			<div class="otrn-provisional" role="note">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 11v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="7.5" r="1.1" fill="currentColor"/></svg>
				<p><strong>Datos provisionales.</strong> Las cifras actuales corresponden a la herencia del Observatorio de Rapa Nui y se están migrando a la comuna de Arauco. Se actualizarán con la base local.</p>
			</div>
			<div class="otrn-datos">
				<?php
				foreach ( $datos as $otn_i => $dato ) :
					$otn_anio = get_post_meta( $dato->ID, 'anio', true );
					$otn_ico  = isset( $otrn_icos[ $otn_i % count( $otrn_icos ) ] ) ? $otrn_icos[ $otn_i % count( $otrn_icos ) ] : $otrn_ico['chart'];
				?>
				<article class="otrn-dato-lite otrn-reveal">
					<span class="otrn-dato-lite__ico" aria-hidden="true"><?php echo $otn_ico; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML estático de tema ?>
					</span>
					<span class="otrn-dato-lite__n"><?php echo esc_html( get_post_meta( $dato->ID, 'cifra', true ) ); ?></span>
					<h3 class="otrn-dato-lite__label"><?php echo esc_html( get_the_title( $dato ) ); ?></h3>
					<?php if ( $otn_anio ) : ?>
						<span class="otrn-dato-lite__anio"><?php echo esc_html( $otn_anio ); ?></span>
					<?php endif; ?>
				</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $indicadores ) : ?>
	<!-- 5 · EXPLORACIÓN «Datos para un turismo más sostenible» (funcional). -->
	<section class="otrn-landing-seccion otrn-landing-explora" id="otrn-explora" data-scene="5" aria-labelledby="otrn-explora-titulo">
		<div class="wrap">
			<div class="otrn-explora-head">
				<div class="otrn-explora-head__izq">
					<p class="otrn-act__eyebrow">Explora el observatorio</p>
					<h2 class="otrn-act__titulo" id="otrn-explora-titulo">Datos para un turismo más sostenible</h2>
					<p class="otrn-act__lead">Accede a indicadores, análisis y recursos sobre el turismo en Arauco.</p>
					<a class="otrn-act__cta otrn-act__cta--outline" href="<?php echo esc_url( $otrn_url_indicadores ); ?>">Ver todos los indicadores <span class="otrn-act__arrow" aria-hidden="true">→</span></a>
				</div>
				<div class="otrn-indexpanel">
					<?php
					// La etiqueta a color es la categoría; si falta, el ámbito la
					// reemplaza para no dejar el pie de la tarjeta vacío.
					foreach ( $indicadores as $ind ) :
						$cat = otrn_primer_termino( $ind, 'categoria_indicador' );
						$amb = otrn_primer_termino( $ind, 'ambito' );
						$destacado  = $cat ? $cat : $amb;
						$secundario = get_post_meta( $ind->ID, 'nivel_territorial', true );
						if ( ! $secundario && $cat && $amb ) {
							$secundario = $amb->name;
						}
					?>
						<a class="otrn-indexcard otrn-reveal" href="<?php echo esc_url( get_permalink( $ind ) ); ?>">
							<h3><?php echo esc_html( get_the_title( $ind ) ); ?></h3>
							<div class="foot">
								<span class="dest <?php echo esc_attr( otrn_clase_secuencia( $destacado ) ); ?>"><?php echo $destacado ? esc_html( $destacado->name ) : ''; ?></span>
								<span class="sec"><?php echo esc_html( $secundario ); ?></span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $noticias ) : ?>
	<!-- 6 · NOTICIAS funcionales. -->
	<section class="otrn-landing-seccion" aria-labelledby="otrn-not-titulo">
		<div class="wrap">
			<div class="otrn-sectionhead grande teal">
				<div>
					<p class="otrn-act__eyebrow">Últimas noticias</p>
					<h2 class="otrn-act__titulo" id="otrn-not-titulo">Actualidad turística en Arauco</h2>
				</div>
				<a class="otrn-btn teal" href="<?php echo esc_url( $otrn_url_noticias ); ?>">Ver todas las noticias →</a>
			</div>
			<div class="otrn-newsgrid">
				<?php
				foreach ( $noticias as $p ) {
					get_template_part( 'template-parts/noticia-card', null, array( 'post' => $p ) );
				}
				?>
			</div>
		</div>
	</section>
	<?php endif; ?>

</div>

<?php
get_footer();



