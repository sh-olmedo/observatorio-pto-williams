<?php
/**
 * Acerca de — página institucional editable desde wp-admin.
 *
 * Toda la bajada y el cuerpo son contenido del post (`the_excerpt()` /
 * `the_content()`): no se hardcodea texto editorial en la plantilla, según la
 * convención de CLAUDE.md. La plantilla se aplica por convención de slug
 * (`/acerca/`), creada por scripts/crear-pagina-acerca.php.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$otrn_bajada = has_excerpt() ? get_the_excerpt() : '';
	?>

	<div class="wrap otrn-pagehead">
		<span class="eyebrow">El Observatorio</span>
		<h1><?php the_title(); ?></h1>
		<?php if ( $otrn_bajada ) : ?>
			<p><?php echo esc_html( $otrn_bajada ); ?></p>
		<?php endif; ?>
	</div>

	<div class="wrap" style="padding-bottom:60px;">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'large', array( 'style' => 'width:100%;height:auto;border-radius:var(--radius);' ) ); ?>
		<?php endif; ?>

		<div class="otrn-prose"><?php the_content(); ?></div>
	</div>

	<?php
endwhile;

get_footer();
