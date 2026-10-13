<?php
/**
 * Geoportal — visor externo del Geoportal de Turismo (ArcGIS StoryMaps).
 *
 * Sigue el patrón de embed de `single-indicador.php` (`.otrn-ind-embed` +
 * nota de respaldo con enlace directo).
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$geoportal_url = 'https://storymaps.arcgis.com/stories/d414dbcd4d3743de835b15745f5bd060';

while ( have_posts() ) :
	the_post();
	?>

	<div class="wrap otrn-pagehead">
		<span class="eyebrow">Geoportal</span>
		<h1><?php the_title(); ?></h1>
		<?php the_content(); ?>
	</div>

	<div class="wrap" style="padding-bottom:60px;">
		<div class="otrn-ind-embed">
			<iframe src="<?php echo esc_url( $geoportal_url ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" allowfullscreen></iframe>
		</div>
		<p class="otrn-ind-nota">Visor externo. Si no carga, ábrelo en <a href="<?php echo esc_url( $geoportal_url ); ?>" target="_blank" rel="noopener">una pestaña nueva ↗</a>.</p>
	</div>

	<?php
endwhile;

get_footer();
