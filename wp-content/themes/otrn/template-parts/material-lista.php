<?php
/**
 * Listado en filas de Material de apoyo (docs/05 Parte A §4).
 *
 * Compartido por las dos vistas en que se divide la sección — Documentación
 * técnica y Reportes — y por el archivo completo del CPT.
 *
 * @package otrn
 *
 * @var array<string,mixed> $args {
 *     @type string   $eyebrow Antetítulo de la cabecera.
 *     @type string   $titulo  Título de la página.
 *     @type string   $intro   Bajada.
 *     @type string   $base    URL a la que envía el buscador.
 *     @type WP_Query $query   Resultados ya consultados.
 *     @type string   $q       Término buscado.
 *     @type string   $vacio   Texto cuando no hay resultados.
 * }
 */

defined( 'ABSPATH' ) || exit;

$q_actual = isset( $args['q'] ) ? $args['q'] : '';
$listado  = $args['query'];
?>

<div class="wrap otrn-pagehead">
	<span class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></span>
	<h1><?php echo esc_html( $args['titulo'] ); ?></h1>
	<p><?php echo esc_html( $args['intro'] ); ?></p>
</div>

<div class="wrap" style="padding-bottom:60px;">
	<form class="otrn-filters" method="get" action="<?php echo esc_url( $args['base'] ); ?>">
		<div class="otrn-field">
			<label for="f-q">Buscar</label>
			<input id="f-q" type="search" name="q" value="<?php echo esc_attr( $q_actual ); ?>" placeholder="Nombre del material">
		</div>
		<button class="otrn-btn" type="submit">Buscar</button>
		<?php if ( $q_actual ) : ?><a class="otrn-btn ghost" href="<?php echo esc_url( $args['base'] ); ?>">Limpiar</a><?php endif; ?>
	</form>

	<?php if ( ! $listado->have_posts() ) : ?>
		<div class="otrn-empty" style="border:1px solid var(--grey);border-radius:14px;"><?php echo esc_html( $args['vacio'] ); ?></div>
	<?php else : ?>
		<div class="otrn-rowlist">
			<?php while ( $listado->have_posts() ) : $listado->the_post();
				$id     = get_the_ID();
				$alc    = get_the_terms( $id, 'alcance_material' );
				$alc    = ( $alc && ! is_wp_error( $alc ) ) ? $alc[0]->name : '';
				$doc    = otrn_material_documento( $id );
				$enlace = get_post_meta( $id, 'enlace_externo', true );
				?>
				<div class="otrn-rowitem">
					<span class="ttl"><?php the_title(); ?></span>
					<?php if ( $alc ) : ?><span class="otrn-tag"><?php echo esc_html( $alc ); ?></span><?php endif; ?>
					<span class="acts">
						<?php if ( $doc ) : ?>
							<a class="otrn-rowlink" href="<?php echo esc_url( $doc['url'] ); ?>" download>
								Descargar<?php echo $doc['ext'] ? ' ' . esc_html( $doc['ext'] ) : ''; ?> ↓
							</a>
						<?php endif; ?>
						<?php if ( $enlace ) : ?>
							<a class="otrn-rowlink ghost" href="<?php echo esc_url( $enlace ); ?>" target="_blank" rel="noopener">Ver sitio ↗</a>
						<?php endif; ?>
						<?php if ( ! $doc && ! $enlace ) : ?>
							<span class="docs">sin archivo</span>
						<?php endif; ?>
					</span>
				</div>
			<?php endwhile; ?>
		</div>
	<?php endif; wp_reset_postdata(); ?>
</div>
