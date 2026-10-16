<?php
/**
 * Buenas prácticas — detalle (docs/03 §5).
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$id  = get_the_ID();
	$amb = get_the_terms( $id, 'ambito' );
	$amb = ( $amb && ! is_wp_error( $amb ) ) ? $amb[0]->name : '';
	$web = get_post_meta( $id, 'sitio_web', true );
	?>

	<div class="wrap otrn-detail" style="padding-top:36px;">
		<div class="otrn-crumb"><a href="<?php echo esc_url( get_post_type_archive_link( 'buena_practica' ) ); ?>">Buenas prácticas</a> <span style="color:var(--text2);font-weight:400;">/</span> Detalle</div>

		<?php if ( $amb ) : ?><span class="otrn-tag"><?php echo esc_html( $amb ); ?></span><?php endif; ?>
		<h1 style="font-size:30px;margin:12px 0;"><?php the_title(); ?></h1>

		<?php $desc = get_post_meta( $id, 'descripcion', true ); if ( $desc ) : ?>
			<div class="otrn-quote"><?php echo wp_kses_post( $desc ); ?></div>
		<?php endif; ?>

		<div class="otrn-meta3">
			<div class="box"><div class="k">Ámbito</div><div class="v"><?php echo esc_html( $amb ?: '—' ); ?></div></div>
			<div class="box"><div class="k">Fuente de la información</div><div class="v"><?php echo esc_html( get_post_meta( $id, 'fuente_de_la_informacion', true ) ?: '—' ); ?></div></div>
			<div class="box"><div class="k">Ubicación</div><div class="v"><?php echo esc_html( get_post_meta( $id, 'ubicacion', true ) ?: '—' ); ?></div></div>
		</div>

		<p style="color:var(--text2);font-size:12.5px;">Nivel territorial: <?php echo esc_html( get_post_meta( $id, 'nivel_territorial', true ) ?: '—' ); ?></p>

		<?php if ( $web ) : ?><p style="margin-top:20px;"><a class="otrn-btn ghost" href="<?php echo esc_url( $web ); ?>" target="_blank" rel="noopener">Sitio web ↗</a></p><?php endif; ?>

		<p style="color:var(--text2);font-size:12px;margin-top:26px;">Los documentos adjuntos (repetidor de archivos) requieren ACF PRO; diferido.</p>
	</div>

	<?php
endwhile;

get_footer();
