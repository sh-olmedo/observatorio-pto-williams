<?php
/**
 * Buenas prácticas — listado (docs/03 §4).
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$f_amb = isset( $_GET['ambito'] ) ? sanitize_title( wp_unslash( $_GET['ambito'] ) ) : '';
$f_q   = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

$args = array( 'post_type' => 'buena_practica', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' );
if ( $f_amb ) {
	$args['tax_query'] = array( array( 'taxonomy' => 'ambito', 'field' => 'slug', 'terms' => $f_amb ) );
}
if ( $f_q ) {
	$args['s'] = $f_q;
}
$q    = new WP_Query( $args );
$ambs = get_terms( array( 'taxonomy' => 'ambito', 'hide_empty' => false ) );
?>

<div class="wrap otrn-pagehead">
	<span class="eyebrow">Aprendizajes</span>
	<h1>Buenas prácticas</h1>
	<p>Iniciativas de la municipalidad, instituciones, comunidad y operadores para avanzar hacia un turismo más sostenible y respetuoso del patrimonio de Puerto Williams.</p>
</div>

<div class="wrap" style="padding-bottom:60px;">
	<form class="otrn-filters" method="get">
		<div class="otrn-field">
			<label for="f-amb">Ámbito</label>
			<select id="f-amb" name="ambito">
				<option value="">Todos los ámbitos</option>
				<?php foreach ( $ambs as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $f_amb, $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="otrn-field">
			<label for="f-q">Buscar</label>
			<input id="f-q" type="search" name="q" value="<?php echo esc_attr( $f_q ); ?>" placeholder="Nombre de la práctica">
		</div>
		<button class="otrn-btn" type="submit">Filtrar</button>
		<?php if ( $f_amb || $f_q ) : ?><a class="otrn-btn ghost" href="<?php echo esc_url( get_post_type_archive_link( 'buena_practica' ) ); ?>">Limpiar</a><?php endif; ?>
	</form>

	<?php if ( ! $q->have_posts() ) : ?>
		<div class="otrn-empty" style="border:1px solid var(--grey);border-radius:var(--radius);">No hay buenas prácticas que coincidan.</div>
	<?php else : ?>
		<div class="otrn-cardgrid">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$amb = get_the_terms( get_the_ID(), 'ambito' );
				$amb = ( $amb && ! is_wp_error( $amb ) ) ? $amb[0]->name : '';
				?>
				<a class="otrn-card" href="<?php the_permalink(); ?>">
					<?php if ( $amb ) : ?><span class="otrn-tag"><?php echo esc_html( $amb ); ?></span><?php endif; ?>
					<h3><?php the_title(); ?></h3>
					<div class="meta"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_post_meta( get_the_ID(), 'descripcion', true ) ), 22 ) ); ?></div>
					<div class="foot"><span class="more">Ver práctica →</span></div>
				</a>
			<?php endwhile; ?>
		</div>
	<?php endif; wp_reset_postdata(); ?>
</div>

<?php
get_footer();
