<?php
/**
 * Indicadores — detalle (docs/02 §5).
 *
 * Patrón de dos zonas tomado del observatorio de referencia: drawer lateral
 * colapsable con la ficha (descripción, fórmula, institución, acciones) y un
 * visor ancho a la derecha. El visor renderiza el embed externo cuando
 * `fuente_datos = Embed`, o la serie manual (`serie_datos`, meta JSON porque
 * no hay repetidor PRO) como línea SVG inline.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$id      = get_the_ID();
	$cat     = get_the_terms( $id, 'categoria_indicador' );
	$cat     = ( $cat && ! is_wp_error( $cat ) ) ? $cat[0]->name : '';
	$amb     = get_the_terms( $id, 'ambito' );
	$amb     = ( $amb && ! is_wp_error( $amb ) ) ? $amb[0]->name : '';
	$fuente  = get_post_meta( $id, 'fuente_datos', true );
	$embed   = get_post_meta( $id, 'embed_url', true );
	$pdf       = function_exists( 'get_field' ) ? get_field( 'documento_pdf', $id ) : null;
	$pdf_url   = is_array( $pdf ) ? ( $pdf['url'] ?? '' ) : '';
	$excel     = function_exists( 'get_field' ) ? get_field( 'documento_excel', $id ) : null;
	$excel_url = is_array( $excel ) ? ( $excel['url'] ?? '' ) : '';
	$ficha_meto     = function_exists( 'get_field' ) ? get_field( 'ficha_metodologica', $id ) : null;
	$ficha_meto_url = is_array( $ficha_meto ) ? ( $ficha_meto['url'] ?? '' ) : '';
	$serie   = json_decode( (string) get_post_meta( $id, 'serie_datos', true ), true );
	$serie   = is_array( $serie ) ? $serie : array();
	$web     = get_post_meta( $id, 'sitio_web', true );
	$desc    = get_post_meta( $id, 'descripcion', true );
	$formula = get_post_meta( $id, 'formula', true );
	$inst    = get_post_meta( $id, 'institucion_responsable', true );
	$nivel   = get_post_meta( $id, 'nivel_territorial', true );

	// Sitios externos que responden con X-Frame-Options/CSP frame-ancestors
	// impidiendo el <iframe>; el navegador no expone esa cabecera al visor
	// para detectarlo en tiempo real, así que se listan a mano.
	$otrn_embed_sin_iframe = array( 'climagro.cl', 'www.climagro.cl' );
	$embed_host            = $embed ? wp_parse_url( $embed, PHP_URL_HOST ) : '';
	$embed_permite_iframe  = ! in_array( $embed_host, $otrn_embed_sin_iframe, true );
	?>

	<section class="otrn-ind-detalle">
		<button type="button" class="otrn-ind-handler" aria-expanded="true" aria-controls="otrn-ind-ficha">
			Detalle indicador <span aria-hidden="true">→</span>
		</button>

		<aside class="otrn-ind-ficha" id="otrn-ind-ficha">
			<div class="otrn-ind-ficha-head">
				<strong>Detalle indicador</strong>
				<button type="button" class="otrn-ind-close" aria-label="Cerrar detalle">&times;</button>
			</div>

			<?php if ( $desc ) : ?>
				<div class="otrn-ind-bloque">
					<small>Descripción</small>
					<p><?php echo esc_html( $desc ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $formula ) : ?>
				<div class="otrn-ind-bloque">
					<small>Fórmula</small>
					<p class="mono"><?php echo esc_html( $formula ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $nivel ) : ?>
				<div class="otrn-ind-bloque">
					<small>Nivel territorial</small>
					<p><?php echo esc_html( $nivel ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $inst ) : ?>
				<div class="otrn-ind-bloque">
					<small>Institución responsable</small>
					<p><?php echo esc_html( $inst ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $web ) : ?>
				<p class="otrn-ind-acciones">
					<a class="otrn-btn ghost" href="<?php echo esc_url( $web ); ?>" target="_blank" rel="noopener">Sitio web ↗</a>
				</p>
			<?php endif; ?>
		</aside>

		<article class="otrn-ind-visor">
			<div class="otrn-crumb"><a href="<?php echo esc_url( get_post_type_archive_link( 'indicador' ) ); ?>">Indicadores</a> <span style="color:var(--text2);font-weight:400;">/</span> Detalle</div>

			<div class="otrn-ind-tags">
				<?php if ( $cat ) : ?><span class="otrn-tag"><?php echo esc_html( $cat ); ?></span><?php endif; ?>
				<?php if ( $amb ) : ?><span class="otrn-tag"><?php echo esc_html( $amb ); ?></span><?php endif; ?>
			</div>

			<h1><?php the_title(); ?></h1>

			<?php if ( 'Embed' === $fuente && $embed && $embed_permite_iframe ) : ?>
				<div class="otrn-ind-embed">
					<iframe src="<?php echo esc_url( $embed ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" allowfullscreen></iframe>
				</div>
				<p class="otrn-ind-nota">Visor externo. Si no carga, ábrelo en <a href="<?php echo esc_url( $embed ); ?>" target="_blank" rel="noopener">una pestaña nueva ↗</a>.</p>
			<?php elseif ( 'Embed' === $fuente && $embed ) : ?>
				<div class="otrn-panel" style="margin-top:24px;text-align:center;">
					<p>Este visor externo no permite mostrarse dentro de otras páginas (restricción del sitio de origen).</p>
					<p class="otrn-ind-acciones" style="margin-top:14px;">
						<a class="otrn-btn" href="<?php echo esc_url( $embed ); ?>" target="_blank" rel="noopener">Abrir visor en una pestaña nueva ↗</a>
					</p>
				</div>
			<?php elseif ( 'PDF' === $fuente && $pdf_url ) : ?>
				<div class="otrn-ind-embed">
					<iframe src="<?php echo esc_url( $pdf_url ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></iframe>
				</div>
				<p class="otrn-ind-nota">Documento PDF. Si no carga, ábrelo en <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener">una pestaña nueva ↗</a>.</p>
			<?php elseif ( 'Excel' === $fuente && $excel_url ) : ?>
				<div class="otrn-panel otrn-ind-excel" id="otrn-ind-excel" data-excel-url="<?php echo esc_url( $excel_url ); ?>" style="margin-top:24px;overflow-x:auto;">
					<p class="otrn-ind-nota">Cargando documento…</p>
				</div>
				<p class="otrn-ind-nota">Documento Excel (solo valores, sin fórmulas ni formato). Si no carga, descárgalo <a href="<?php echo esc_url( $excel_url ); ?>" target="_blank" rel="noopener">aquí ↗</a>.</p>
			<?php elseif ( ! empty( $serie ) ) : ?>
				<div class="otrn-panel" style="margin-top:24px;">
					<h3>Evolución temporal</h3>
					<?php
					$vals = array_map( static function ( $r ) { return (float) $r['destino']; }, $serie );
					$max  = max( $vals );
					$min  = min( $vals );
					$span = ( $max - $min ) ?: 1;
					$n    = count( $serie );
					$pts  = array();
					foreach ( $vals as $i => $v ) {
						$x = $n > 1 ? ( $i / ( $n - 1 ) * 96 + 2 ) : 50;
						$y = 46 - ( ( $v - $min ) / $span * 42 );
						$pts[] = round( $x, 2 ) . ',' . round( $y, 2 );
					}
					?>
					<svg viewBox="0 0 100 54" preserveAspectRatio="none" style="width:100%;height:260px;">
						<polyline points="<?php echo esc_attr( implode( ' ', $pts ) ); ?>" fill="none" stroke="var(--primary)" stroke-width="0.8" stroke-linejoin="round"></polyline>
						<?php foreach ( $pts as $p ) : list($px,$py)=explode(',',$p); ?>
							<circle cx="<?php echo esc_attr( $px ); ?>" cy="<?php echo esc_attr( $py ); ?>" r="0.9" fill="var(--primary)"></circle>
						<?php endforeach; ?>
					</svg>
					<div style="display:flex;justify-content:space-between;font-family:var(--font-mono);font-size:11px;color:var(--text2);margin-top:6px;">
						<?php foreach ( $serie as $r ) : ?><span><?php echo esc_html( $r['fecha'] ); ?></span><?php endforeach; ?>
					</div>
					<p style="font-size:12px;color:var(--text2);margin-top:10px;">Último valor (<?php echo esc_html( end( $serie )['fecha'] ); ?>): <b class="mono"><?php echo esc_html( number_format( end( $vals ), 0, ',', '.' ) ); ?></b></p>
				</div>
			<?php else : ?>
				<p class="otrn-ind-nota">Sin serie de datos ni visor cargado.</p>
			<?php endif; ?>

			<?php if ( $ficha_meto_url ) : ?>
				<div class="otrn-panel" style="margin-top:24px;">
					<h3>Ficha metodológica</h3>
					<div class="otrn-ind-embed">
						<iframe src="<?php echo esc_url( $ficha_meto_url ); ?>" title="Ficha metodológica — <?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></iframe>
					</div>
					<p class="otrn-ind-nota">Si no carga, ábrela en <a href="<?php echo esc_url( $ficha_meto_url ); ?>" target="_blank" rel="noopener">una pestaña nueva ↗</a>.</p>
				</div>
			<?php endif; ?>
		</article>
	</section>

	<?php
endwhile;

get_footer();
