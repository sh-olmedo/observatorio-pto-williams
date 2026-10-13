<?php
/**
 * Noticias — detalle (docs/05 Parte B §4).
 *
 * Solo `post`: los CPT con ficha propia tienen su `single-{tipo}.php`.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$otrn_id     = get_the_ID();
	$otrn_fuente = otrn_noticia_fuente( $otrn_id );

	// La categoría por defecto («Sin categoría») no aporta: se muestra solo
	// cuando el editor clasificó la noticia a propósito.
	$otrn_defecto = (int) get_option( 'default_category' );
	$otrn_cats    = array_filter(
		get_the_category(),
		static function ( $cat ) use ( $otrn_defecto ) {
			return (int) $cat->term_id !== $otrn_defecto;
		}
	);
	?>

	<article class="wrap otrn-detail otrn-noticia">
		<div class="otrn-crumb"><a href="<?php echo esc_url( otrn_noticias_url() ); ?>">Noticias</a> <span style="color:var(--text3);font-weight:400;">/</span> Detalle</div>

		<div class="otrn-noticia-head">
			<?php foreach ( $otrn_cats as $otrn_cat ) : ?>
				<a class="otrn-tag" href="<?php echo esc_url( get_category_link( $otrn_cat ) ); ?>"><?php echo esc_html( $otrn_cat->name ); ?></a>
			<?php endforeach; ?>
			<time class="mono" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
		</div>

		<h1><?php the_title(); ?></h1>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="otrn-noticia-img">
				<?php the_post_thumbnail( 'otrn-noticia' ); ?>
				<?php $otrn_pie = wp_get_attachment_caption( get_post_thumbnail_id( $otrn_id ) ); ?>
				<?php if ( $otrn_pie ) : ?>
					<figcaption><?php echo esc_html( $otrn_pie ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>

		<div class="otrn-prose"><?php the_content(); ?></div>

		<?php if ( $otrn_fuente ) : ?>
			<div class="otrn-noticia-fuente">
				<?php if ( $otrn_fuente['url'] ) : ?>
					<a class="otrn-btn ghost" href="<?php echo esc_url( $otrn_fuente['url'] ); ?>" target="_blank" rel="noopener nofollow">Fuente de la información ↗</a>
				<?php else : ?>
					<p>Fuente de la información: <strong><?php echo esc_html( $otrn_fuente['texto'] ); ?></strong></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</article>

	<?php
	$otrn_otras = get_posts( array(
		'post_type'    => 'post',
		'post_status'  => 'publish',
		'numberposts'  => 3,
		'post__not_in' => array( $otrn_id ),
	) );

	if ( $otrn_otras ) :
		?>
		<div class="wrap">
			<div class="otrn-sectionhead">
				<h2>Otras noticias</h2>
				<a href="<?php echo esc_url( otrn_noticias_url() ); ?>">Ver todas →</a>
			</div>
			<div class="otrn-newsgrid">
				<?php
				foreach ( $otrn_otras as $otrn_p ) {
					get_template_part( 'template-parts/noticia-card', null, array( 'post' => $otrn_p ) );
				}
				?>
			</div>
		</div>
		<?php
	endif;

endwhile;

get_footer();
