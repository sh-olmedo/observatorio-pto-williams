<?php
/**
 * Tarjeta de noticia: imagen + fecha + título (docs/05 Parte B §3).
 *
 * Compartida por la portada, el listado `/noticias/` y las noticias
 * relacionadas del detalle, para que la grilla se vea igual en las tres.
 *
 * @package otrn
 *
 * @var array<string,mixed> $args {
 *     @type WP_Post|int $post Noticia a mostrar.
 * }
 */

defined( 'ABSPATH' ) || exit;

$noticia = get_post( $args['post'] );
if ( ! $noticia ) {
	return;
}
?>
<a class="otrn-news" href="<?php echo esc_url( get_permalink( $noticia ) ); ?>">
	<?php if ( has_post_thumbnail( $noticia ) ) : ?>
		<div class="thumb"><?php echo get_the_post_thumbnail( $noticia, 'otrn-noticia-card' ); ?></div>
	<?php else : ?>
		<div class="thumb sin-imagen" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="body">
		<div class="date"><?php echo esc_html( get_the_date( 'd/m/Y', $noticia ) ); ?></div>
		<h3><?php echo esc_html( get_the_title( $noticia ) ); ?></h3>
		<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( strip_shortcodes( $noticia->post_content ) ), 20 ) ); ?></p>
	</div>
</a>
