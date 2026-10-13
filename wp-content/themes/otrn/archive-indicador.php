<?php
/**
 * Indicadores — listado (docs/02 §4).
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$f_cat = isset( $_GET['categoria'] ) ? sanitize_title( wp_unslash( $_GET['categoria'] ) ) : '';
$f_amb = isset( $_GET['ambito'] ) ? sanitize_title( wp_unslash( $_GET['ambito'] ) ) : '';
$f_q   = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

// Nivel territorial (docs/02 §3): campo ACF `nivel_territorial`, no taxonomía.
// "Local" es la etiqueta visible del valor real "Comunal" (no se toca el dato).
// Por defecto, si no viene el parámetro en la URL, se filtra por "Comunal".
$niveles_labels = array(
	'Comunal'       => 'Local',
	'Regional'      => 'Regional',
	'Nacional'      => 'Nacional',
	'Internacional' => 'Internacional',
);
$f_nivel = isset( $_GET['nivel'] ) ? sanitize_text_field( wp_unslash( $_GET['nivel'] ) ) : 'Comunal';

$args = array(
	'post_type'      => 'indicador',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
);
// Los dos ejes son ortogonales (docs/02 §2.1): categoría de indicador y ámbito
// de sostenibilidad. Se combinan con AND cuando vienen los dos.
$tax = array();
if ( $f_cat ) {
	$tax[] = array( 'taxonomy' => 'categoria_indicador', 'field' => 'slug', 'terms' => $f_cat );
}
if ( $f_amb ) {
	$tax[] = array( 'taxonomy' => 'ambito', 'field' => 'slug', 'terms' => $f_amb );
}
if ( $tax ) {
	$args['tax_query'] = count( $tax ) > 1 ? array_merge( array( 'relation' => 'AND' ), $tax ) : $tax;
}
if ( $f_nivel && isset( $niveles_labels[ $f_nivel ] ) ) {
	$args['meta_query'] = array(
		array( 'key' => 'nivel_territorial', 'value' => $f_nivel ),
	);
}
if ( $f_q ) {
	$args['s'] = $f_q;
}
$q      = new WP_Query( $args );
$cats   = get_terms( array( 'taxonomy' => 'categoria_indicador', 'hide_empty' => false ) );
$ambitos = get_terms( array( 'taxonomy' => 'ambito', 'hide_empty' => false ) );
?>

<div class="wrap otrn-pagehead">
	<span class="eyebrow">Monitoreo</span>
	<h1>Indicadores</h1>
	<p>Indicadores para monitorear el comportamiento del turismo en Rapa Nui a lo largo del tiempo, en cuatro categorías.</p>
</div>

<div class="wrap" style="padding-bottom:60px;">
	<form class="otrn-filters" method="get">
		<div class="otrn-field">
			<label for="f-cat">Categoría</label>
			<select id="f-cat" name="categoria">
				<option value="">Todas las categorías</option>
				<?php foreach ( $cats as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $f_cat, $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="otrn-field">
			<label for="f-amb">Ámbito</label>
			<select id="f-amb" name="ambito">
				<option value="">Todos los ámbitos</option>
				<?php foreach ( $ambitos as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $f_amb, $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="otrn-field">
			<label for="f-nivel">Nivel territorial</label>
			<select id="f-nivel" name="nivel">
				<option value="" <?php selected( $f_nivel, '' ); ?>>Todos los niveles</option>
				<?php foreach ( $niveles_labels as $valor => $etiqueta ) : ?>
					<option value="<?php echo esc_attr( $valor ); ?>" <?php selected( $f_nivel, $valor ); ?>><?php echo esc_html( $etiqueta ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="otrn-field">
			<label for="f-q">Buscar</label>
			<input id="f-q" type="search" name="q" value="<?php echo esc_attr( $f_q ); ?>" placeholder="Nombre del indicador">
		</div>
		<button class="otrn-btn" type="submit">Filtrar</button>
		<?php if ( $f_cat || $f_amb || $f_q || 'Comunal' !== $f_nivel ) : ?><a class="otrn-btn ghost" href="<?php echo esc_url( get_post_type_archive_link( 'indicador' ) ); ?>">Limpiar</a><?php endif; ?>
	</form>

	<?php if ( ! $q->have_posts() ) : ?>
		<div class="otrn-empty" style="border:1px solid var(--grey);border-radius:14px;">No hay indicadores que coincidan.</div>
	<?php else : ?>
		<div class="otrn-cardgrid">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$cat = get_the_terms( get_the_ID(), 'categoria_indicador' );
				$cat = ( $cat && ! is_wp_error( $cat ) ) ? $cat[0]->name : '';
				$amb = get_the_terms( get_the_ID(), 'ambito' );
				$amb = ( $amb && ! is_wp_error( $amb ) ) ? $amb[0]->name : '';
				?>
				<a class="otrn-card" href="<?php the_permalink(); ?>">
					<span class="otrn-tags">
						<?php if ( $cat ) : ?><span class="otrn-tag"><?php echo esc_html( $cat ); ?></span><?php endif; ?>
						<?php if ( $amb ) : ?><span class="otrn-tag"><?php echo esc_html( $amb ); ?></span><?php endif; ?>
					</span>
					<h3><?php the_title(); ?></h3>
					<div class="meta"><?php echo esc_html( wp_trim_words( get_post_meta( get_the_ID(), 'descripcion', true ), 22 ) ); ?></div>
					<div class="foot"><span class="more">Ver evolución →</span></div>
				</a>
			<?php endwhile; ?>
		</div>
	<?php endif; wp_reset_postdata(); ?>
</div>

<?php
get_footer();
