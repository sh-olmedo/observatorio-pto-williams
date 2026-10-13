<?php
/**
 * Portada del Observatorio Turístico de Arauco — experiencia inmersiva.
 *
 * Estructura de scrollytelling en 5 secciones semánticas:
 *   1. Hero fotográfico a sangre (fotografía aprobada de la bahía).
 *   2. Narrativa territorial: columna visual fija + pasos de texto (crossfade).
 *   3. Cifras destacadas (con aviso de datos provisionales).
 *   4. Últimos indicadores.
 *   5. Últimas noticias.
 *
 * Cada bloque se omite si no trae contenido, para no pintar secciones vacías
 * (misma lógica de la portada original). Los datos de `dato_portada` son la
 * herencia del Observatorio de Rapa Nui y se etiquetan como provisionales
 * mientras se migran a la comuna de Arauco.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Datos editoriales. Cada bloque se omite si no hay contenido.
$mision      = otrn_mision_portada();
$datos       = otrn_datos_portada();
$indicadores = get_posts( array( 'post_type' => 'indicador', 'post_status' => 'publish', 'numberposts' => 6 ) );
$noticias    = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 6 ) );

// Fotos aprobadas: references/web → assets/img/arauco/ (nombres semánticos).
$otrn_img = get_stylesheet_directory_uri() . '/assets/img/arauco/';
?>

<div id="otrn-landing" class="otrn-landing" data-otrn-landing>

	<!-- 1 · HERO: fotografía aérea de la bahía, a sangre completa. -->
	<section class="otrn-landing-hero" aria-labelledby="otrn-hero-titulo">
		<div class="otrn-landing-hero__media" aria-hidden="true">
			<picture>
				<source type="image/webp" srcset="<?php echo esc_url( $otrn_img . 'hero-bahia.webp' ); ?>">
				<img
					src="<?php echo esc_url( $otrn_img . 'hero-bahia.jpg' ); ?>"
					alt="Vista aérea de la bahía y el pueblo costero de Arauco al atardecer"
					fetchpriority="high"
					decoding="async"
				>
			</picture>
			<div class="otrn-landing-hero__velo"></div>
		</div>
		<div class="otrn-landing-hero__contenido">
			<p class="otrn-landing-hero__eyebrow">Región del Bío-Bío · Costa de Arauco</p>
			<h1 id="otrn-hero-titulo"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
			<p class="otrn-landing-hero__lead">
				Plataforma pública de indicadores, autodiagnóstico de sostenibilidad,
				buenas prácticas y seguimiento del Plan de Desarrollo Turístico de la comuna de Arauco.
			</p>
			<a class="otrn-landing-hero__cue" href="#otrn-narrativa">
				<span class="otrn-landing-hero__cue-line" aria-hidden="true"></span>
				<span>Explora el territorio</span>
			</a>
		</div>
	</section>

	<!-- 2 · NARRATIVA TERRITORIAL: columna visual fija + pasos de texto. -->
	<section id="otrn-narrativa" class="otrn-landing-narrativa" aria-labelledby="otrn-narrativa-titulo">
		<div class="otrn-landing-narrativa__grid">
			<div class="otrn-landing-narrativa__visual" aria-hidden="true">
				<figure class="otrn-landing-foto is-activa" data-idx="0">
					<img src="<?php echo esc_url( $otrn_img . 'estuario.webp' ); ?>" alt="" loading="lazy" decoding="async">
					<figcaption>Estuario y boca de río</figcaption>
				</figure>
				<figure class="otrn-landing-foto" data-idx="1">
					<img src="<?php echo esc_url( $otrn_img . 'pesca-artesanal.webp' ); ?>" alt="" loading="lazy" decoding="async">
					<figcaption>Pesca artesanal en la costa</figcaption>
				</figure>
			</div>
			<div class="otrn-landing-pasos">
				<?php if ( $mision ) : ?>
					<div class="otrn-landing-paso" data-foto="0">
						<p class="otrn-landing-eyebrow"><?php echo esc_html( get_the_title( $mision ) ); ?></p>
						<?php echo wp_kses_post( apply_filters( 'the_content', $mision->post_content ) ); ?>
					</div>
				<?php else : ?>
					<div class="otrn-landing-paso" data-foto="0">
						<p class="otrn-landing-eyebrow">Un territorio que se mira</p>
						<p>Arauco se entiende desde su costa: estuarios, muelles y una tradición pesquera que define su identidad. Este observatorio reúne las señales que la comunidad y las instituciones miden para decidir.</p>
					</div>
				<?php endif; ?>
				<div class="otrn-landing-paso otrn-reveal" data-foto="1">
					<p class="otrn-landing-eyebrow">Medir para cuidar</p>
					<p>Cada indicador es una pregunta con respuesta: cuánta gente llega, cómo llega, qué consume y qué deja. Los datos permiten ver el turismo de Arauco a lo largo del tiempo y compararlo con el plan.</p>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $datos ) : ?>
	<!-- 3 · CIFRAS DESTACADAS (provisionales: herencia de Rapa Nui). -->
	<section class="otrn-landing-seccion" aria-labelledby="otrn-datos-titulo">
		<div class="wrap">
			<div class="otrn-provisional" role="note">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
					<path d="M12 11v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
					<circle cx="12" cy="7.5" r="1.1" fill="currentColor"/>
				</svg>
				<p><strong>Datos provisionales.</strong> Las cifras actuales corresponden a la herencia del Observatorio de Rapa Nui y se están migrando a la comuna de Arauco. Se actualizarán con la base local.</p>
			</div>
			<p class="otrn-landing-eyebrow">Panel</p>
			<h2 class="otrn-landing-titulo" id="otrn-datos-titulo">Cifras que importan</h2>
			<div class="otrn-datos">
				<?php
				foreach ( $datos as $dato ) :
					$img  = get_the_post_thumbnail_url( $dato, 'large' );
					$anio = get_post_meta( $dato->ID, 'anio', true );
					?>
					<article class="otrn-dato otrn-reveal"<?php echo $img ? ' style="--dato-img:url(\'' . esc_url( $img ) . '\');"' : ''; ?>>
						<span class="cifra"><?php echo esc_html( get_post_meta( $dato->ID, 'cifra', true ) ); ?></span>
						<h3><?php echo esc_html( get_the_title( $dato ) ); ?></h3>
						<?php if ( $anio ) : ?><span class="anio"><?php echo esc_html( $anio ); ?></span><?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $indicadores ) : ?>
	<!-- 4 · ÚLTIMOS INDICADORES. -->
	<section class="otrn-landing-seccion" aria-labelledby="otrn-ind-titulo">
		<div class="wrap">
			<div class="otrn-sectionhead grande coral">
				<h2 id="otrn-ind-titulo">Últimos indicadores</h2>
				<a class="otrn-btn" href="<?php echo esc_url( get_post_type_archive_link( 'indicador' ) ); ?>">Ver todos</a>
			</div>
			<div class="otrn-indexpanel">
				<?php
				foreach ( $indicadores as $ind ) :
					$cat = otrn_primer_termino( $ind, 'categoria_indicador' );
					$amb = otrn_primer_termino( $ind, 'ambito' );
					// La etiqueta a color es la categoría; si falta, el ámbito la
					// reemplaza para no dejar el pie de la tarjeta vacío (docs/02 §2.1).
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
	</section>
	<?php endif; ?>

	<?php if ( $noticias ) : ?>
	<!-- 5 · ÚLTIMAS NOTICIAS. -->
	<section class="otrn-landing-seccion" aria-labelledby="otrn-not-titulo">
		<div class="wrap">
			<div class="otrn-sectionhead grande teal">
				<h2 id="otrn-not-titulo">Últimas noticias</h2>
				<a class="otrn-btn teal" href="<?php echo esc_url( otrn_noticias_url() ); ?>">Ver todas</a>
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

