<?php
/**
 * Guía de usuario — detalle (título + contenido).
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<article class="wrap otrn-detail" style="padding-top:36px;">
		<div class="otrn-crumb"><a href="<?php echo esc_url( get_post_type_archive_link( 'guia' ) ); ?>">Guía de usuario</a> <span style="color:var(--text2);font-weight:400;">/</span> Detalle</div>

		<h1 style="font-size:30px;margin:12px 0;"><?php the_title(); ?></h1>

		<div class="otrn-prose"><?php the_content(); ?></div>
	</article>

	<?php
endwhile;

get_footer();
