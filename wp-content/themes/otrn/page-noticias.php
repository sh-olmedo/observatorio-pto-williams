<?php
/**
 * Noticias — listado (docs/05 Parte B §3).
 *
 * Va sobre una página `/noticias/` en vez del índice del blog: el sitio muestra
 * la portada del Observatorio en `/` (`show_on_front=posts` + `front-page.php`),
 * así que `home.php` nunca se alcanzaría.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$f_q     = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
$pagina  = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$por_pag = 9;

$args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => $por_pag,
	'paged'          => $pagina,
);
if ( $f_q ) {
	$args['s'] = $f_q;
}
$q    = new WP_Query( $args );
$base = get_permalink();
?>

<div class="wrap otrn-pagehead">
	<span class="eyebrow">Actualidad</span>
	<h1><?php the_title(); ?></h1>
	<p>Novedades del Observatorio: nuevos indicadores, talleres, avances del Pladetur y actividades del turismo en Rapa Nui.</p>
</div>

<div class="wrap" style="padding-bottom:60px;">
	<form class="otrn-filters" method="get" action="<?php echo esc_url( $base ); ?>">
		<div class="otrn-field">
			<label for="f-q">Buscar</label>
			<input id="f-q" type="search" name="q" value="<?php echo esc_attr( $f_q ); ?>" placeholder="Título o contenido de la noticia">
		</div>
		<button class="otrn-btn" type="submit">Buscar</button>
		<?php if ( $f_q ) : ?><a class="otrn-btn ghost" href="<?php echo esc_url( $base ); ?>">Limpiar</a><?php endif; ?>
	</form>

	<?php if ( ! $q->have_posts() ) : ?>
		<div class="otrn-empty" style="border:1px solid var(--grey);border-radius:14px;">No hay noticias que coincidan.</div>
	<?php else : ?>
		<div class="otrn-newsgrid">
			<?php
			while ( $q->have_posts() ) :
				$q->the_post();
				get_template_part( 'template-parts/noticia-card', null, array( 'post' => get_post() ) );
			endwhile;
			?>
		</div>

		<?php
		$enlaces = paginate_links( array(
			'base'      => add_query_arg( 'paged', '%#%', $base ),
			'format'    => '',
			'current'   => $pagina,
			'total'     => $q->max_num_pages,
			'add_args'  => $f_q ? array( 'q' => rawurlencode( $f_q ) ) : array(),
			'prev_text' => '←',
			'next_text' => '→',
			'type'      => 'array',
		) );
		if ( $enlaces ) :
			?>
			<nav class="otrn-pager" aria-label="Paginación de noticias">
				<?php echo implode( '', array_map( 'wp_kses_post', $enlaces ) ); ?>
			</nav>
			<?php
		endif;
		?>
	<?php endif; wp_reset_postdata(); ?>
</div>

<?php
get_footer();
