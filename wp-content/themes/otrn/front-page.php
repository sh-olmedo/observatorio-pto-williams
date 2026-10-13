<?php
/**
 * Portada del Observatorio — reúne y enlaza todas las secciones.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mision      = otrn_mision_portada();
$datos       = otrn_datos_portada();
$indicadores = get_posts( array( 'post_type' => 'indicador', 'post_status' => 'publish', 'numberposts' => 6 ) );
$noticias    = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 6 ) );
?>

<section class="otrn-hero" style="--hero-img:url('<?php echo esc_url( otrn_hero_portada_url() ); ?>');">
	<div class="wrap">
		<h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
		<p>Plataforma pública de indicadores, autodiagnóstico de sostenibilidad, buenas prácticas y seguimiento del Plan de Desarrollo Turístico.</p>
	</div>
</section>

<div class="wrap">
	<?php if ( $mision ) : ?>
		<div class="otrn-mision">
			<h2><?php echo esc_html( get_the_title( $mision ) ); ?></h2>
			<?php echo wp_kses_post( apply_filters( 'the_content', $mision->post_content ) ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $datos ) : ?>
		<div class="otrn-datos">
			<?php
			foreach ( $datos as $dato ) :
				$img  = get_the_post_thumbnail_url( $dato, 'large' );
				$anio = get_post_meta( $dato->ID, 'anio', true );
				?>
				<article class="otrn-dato"<?php echo $img ? ' style="--dato-img:url(\'' . esc_url( $img ) . '\');"' : ''; ?>>
					<span class="cifra"><?php echo esc_html( get_post_meta( $dato->ID, 'cifra', true ) ); ?></span>
					<h3><?php echo esc_html( get_the_title( $dato ) ); ?></h3>
					<?php if ( $anio ) : ?><span class="anio"><?php echo esc_html( $anio ); ?></span><?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $indicadores ) : ?>
		<div class="otrn-sectionhead grande coral">
			<h2>Últimos indicadores</h2>
			<a class="otrn-btn" href="<?php echo esc_url( get_post_type_archive_link( 'indicador' ) ); ?>">Ver todos</a>
		</div>
		<div class="otrn-indexpanel">
			<?php
			foreach ( $indicadores as $ind ) :
				$cat = otrn_primer_termino( $ind, 'categoria_indicador' );
				$amb = otrn_primer_termino( $ind, 'ambito' );
				// La etiqueta a color es la categoría del indicador; cuando el
				// indicador solo está clasificado por ámbito, ese ocupa su lugar
				// para no dejar el pie de la tarjeta vacío (docs/02 §2.1).
				$destacado = $cat ? $cat : $amb;
				$secundario = get_post_meta( $ind->ID, 'nivel_territorial', true );
				if ( ! $secundario && $cat && $amb ) {
					$secundario = $amb->name;
				}
				?>
				<a class="otrn-indexcard" href="<?php echo esc_url( get_permalink( $ind ) ); ?>">
					<h3><?php echo esc_html( get_the_title( $ind ) ); ?></h3>
					<div class="foot">
						<span class="dest <?php echo esc_attr( otrn_clase_secuencia( $destacado ) ); ?>"><?php echo $destacado ? esc_html( $destacado->name ) : ''; ?></span>
						<span class="sec"><?php echo esc_html( $secundario ); ?></span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $noticias ) : ?>
		<div class="otrn-sectionhead grande teal">
			<h2>Últimas noticias</h2>
			<a class="otrn-btn teal" href="<?php echo esc_url( otrn_noticias_url() ); ?>">Ver todas</a>
		</div>
		<div class="otrn-newsgrid">
			<?php
			foreach ( $noticias as $p ) {
				get_template_part( 'template-parts/noticia-card', null, array( 'post' => $p ) );
			}
			?>
		</div>
	<?php endif; ?>
</div>

<?php
get_footer();
