<?php
/**
 * Guía de usuario — listado (título + contenido de cada guía).
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$q = new WP_Query( array(
	'post_type'      => 'guia',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
) );
?>

<div class="wrap otrn-pagehead">
	<span class="eyebrow">Ayuda</span>
	<h1>Guía de usuario</h1>
	<p>Instrucciones para usar el Observatorio Turístico Rapa Nui: autodiagnóstico, indicadores, buenas prácticas y seguimiento Pladetur.</p>
</div>

<div class="wrap" style="padding-bottom:60px;display:flex;flex-direction:column;gap:18px;">
	<?php if ( ! $q->have_posts() ) : ?>
		<div class="otrn-empty" style="border:1px solid var(--grey);border-radius:14px;">Todavía no hay guías publicadas.</div>
	<?php else : ?>
		<?php while ( $q->have_posts() ) : $q->the_post(); ?>
			<div class="otrn-panel">
				<h2 style="font-size:20px;"><?php the_title(); ?></h2>
				<div class="otrn-prose"><?php the_content(); ?></div>
			</div>
		<?php endwhile; ?>
	<?php endif; wp_reset_postdata(); ?>
</div>

<?php
get_footer();
