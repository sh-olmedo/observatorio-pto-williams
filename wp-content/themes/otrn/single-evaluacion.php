<?php
/**
 * Autodiagnóstico — resultados de una evaluación (docs/01 §5, mockup detalle).
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$post_id    = get_the_ID();
	$respuestas = otrn_evaluacion_respuestas( $post_id );
	$criterios  = otrn_cargar_criterios();
	$mapa       = otrn_mapa_codigo_ambito();
	$calc       = otrn_calcular_desempeno( $respuestas, $mapa );
	$es_demo    = (bool) get_post_meta( $post_id, 'es_ejemplo', true );
	$destino    = get_post_meta( $post_id, 'destino', true ) ?: get_the_title();

	$orden_ambitos = array( 'Sociocultural', 'Económico', 'Gestión', 'Medio Ambiente' );
	$global        = $calc['global'];
	$tier_g        = otrn_tier( $global );
	?>

	<div class="wrap otrn-detail">

		<div style="padding-top:36px;">
			<div class="otrn-crumb">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'evaluacion' ) ); ?>">Autoevaluaciones</a>
				<span style="color:var(--text3);font-weight:400;"> / </span> Resultados
			</div>

			<?php if ( $es_demo ) : ?>
				<div class="otrn-demo-banner">● Evaluación de ejemplo — datos de demostración, no reflejan una medición real de Rapa Nui.</div>
			<?php endif; ?>

			<div class="otrn-title-row">
				<div>
					<h1 style="font-size:30px;"><?php echo esc_html( $destino ); ?></h1>
					<div class="otrn-title-sub mono"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?> · Autodiagnóstico de sostenibilidad turística</div>
				</div>
				<?php if ( null !== $global ) : ?>
					<span class="otrn-status-pill tier-<?php echo esc_attr( $tier_g ); ?>">
						<span class="dot"></span> <?php echo esc_html( otrn_mm( $global ) ); ?>% · <?php echo esc_html( otrn_tier_label( $tier_g ) ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>

		<!-- % por ámbito -->
		<div class="otrn-score-grid">
			<?php foreach ( $orden_ambitos as $amb ) :
				$pct  = $calc['por_ambito'][ $amb ]['pct'] ?? null;
				$tier = otrn_tier( $pct );
				?>
				<div class="otrn-score-box tier-<?php echo esc_attr( $tier ); ?>">
					<div class="n"><?php echo null === $pct ? '—' : esc_html( otrn_mm( $pct ) ) . '%'; ?></div>
					<div class="l"><?php echo esc_html( $amb ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Pestañas por ámbito -->
		<div class="otrn-tabbar" role="tablist">
			<?php foreach ( $orden_ambitos as $i => $amb ) : ?>
				<button class="otrn-tab<?php echo 0 === $i ? ' active' : ''; ?>" data-panel="otrn-panel-<?php echo $i; ?>"><?php echo esc_html( $amb ); ?></button>
			<?php endforeach; ?>
		</div>

		<?php foreach ( $orden_ambitos as $i => $amb ) : ?>
			<div class="otrn-tab-content" id="otrn-panel-<?php echo $i; ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
				<?php
				$grupos = $criterios[ $amb ]['criterios'] ?? array();
				foreach ( $grupos as $nombre_criterio => $indicadores ) :
					?>
					<div class="otrn-criterio">
						<h3><?php echo esc_html( $nombre_criterio ); ?></h3>
						<?php if ( ! empty( $indicadores[0]['pregunta'] ) ) : ?>
							<p class="preg"><?php echo esc_html( $indicadores[0]['pregunta'] ); ?></p>
						<?php endif; ?>
						<?php foreach ( $indicadores as $ind ) :
							$codigo = $ind['codigo'];
							$val    = $respuestas[ $codigo ] ?? null;
							$is_na  = ( null === $val || 'NA' === $val || '' === $val );
							$clase  = $is_na ? 'sna' : 's' . (int) $val;
							?>
							<div class="otrn-qa">
								<div class="ind"><span class="code"><?php echo esc_html( $codigo ); ?></span><?php echo esc_html( $ind['indicador'] ); ?></div>
								<div class="otrn-score-tag <?php echo esc_attr( $clase ); ?>"><?php echo $is_na ? 'N/A' : (int) $val; ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>

		<?php
		// Buenas prácticas sugeridas por brecha (docs/01 §5.5): máximo 3, de los
		// ámbitos con las respuestas más bajas de esta evaluación.
		$sugeridas = otrn_buenas_practicas_sugeridas( $respuestas );
		if ( ! empty( $sugeridas ) ) :
			?>
			<section class="otrn-sugeridas">
				<h2 class="otrn-sechead">Buenas prácticas sugeridas</h2>
				<p class="otrn-secsub">Seleccionadas según los indicadores con las respuestas más bajas de esta autoevaluación.</p>
				<div class="otrn-cardgrid">
					<?php foreach ( $sugeridas as $s ) :
						$tier_s = otrn_tier( $s['pct'] );
						?>
						<a class="otrn-card" href="<?php echo esc_url( get_permalink( $s['id'] ) ); ?>">
							<span class="otrn-tag"><?php echo esc_html( $s['ambito'] ); ?></span>
							<h3><?php echo esc_html( get_the_title( $s['id'] ) ); ?></h3>
							<p class="meta"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( (string) get_post_meta( $s['id'], 'descripcion', true ) ), 20 ) ); ?></p>
							<?php if ( ! empty( $s['indicador'] ) ) : ?>
								<p class="meta">Responde a <span class="mono"><?php echo esc_html( $s['codigo'] ); ?></span> — <?php echo esc_html( wp_trim_words( $s['indicador'], 14 ) ); ?></p>
							<?php endif; ?>
							<span class="foot">
								<span class="otrn-pctbadge tier-<?php echo esc_attr( $tier_s ); ?>"><?php echo esc_html( otrn_mm( $s['pct'] ) ); ?>% en <?php echo esc_html( $s['ambito'] ); ?></span>
								<span class="more">Ver práctica →</span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

	</div>

	<script>
	(function(){
		var tabs = document.querySelectorAll('.otrn-tab');
		tabs.forEach(function(t){
			t.addEventListener('click', function(){
				tabs.forEach(function(x){ x.classList.remove('active'); });
				t.classList.add('active');
				document.querySelectorAll('.otrn-tab-content').forEach(function(p){ p.hidden = true; });
				document.getElementById(t.dataset.panel).hidden = false;
			});
		});
	})();
	</script>

	<?php
endwhile;

get_footer();
