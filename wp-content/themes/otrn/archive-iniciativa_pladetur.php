<?php
/**
 * Seguimiento Pladetur — dashboard agregado (docs/04 §4) + listado (§5).
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

// --- Filtros (GET) ----------------------------------------------------------
$f_lineamiento = isset( $_GET['lineamiento'] ) ? sanitize_title( wp_unslash( $_GET['lineamiento'] ) ) : '';
$f_q           = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

// --- Datos ------------------------------------------------------------------
$ids = get_posts(
	array(
		'post_type'   => 'iniciativa_pladetur',
		'post_status' => 'publish',
		'numberposts' => -1,
		'orderby'     => 'meta_value',
		'meta_key'    => 'codigo_iniciativa',
		'order'       => 'ASC',
		'fields'      => 'ids',
	)
);

$filas = array_map( 'otrn_pladetur_iniciativa', $ids );

// Aplica filtros al conjunto (recalcula agregados sobre lo filtrado).
$filas = array_values(
	array_filter(
		$filas,
		static function ( $f ) use ( $f_lineamiento, $f_q ) {
			if ( $f_lineamiento && $f['lineamiento_slug'] !== $f_lineamiento ) {
				return false;
			}
			if ( $f_q && stripos( $f['titulo'] . ' ' . $f['codigo'], $f_q ) === false ) {
				return false;
			}
			return true;
		}
	)
);

$ag        = otrn_pladetur_agregados( $filas );
$anio_now  = (int) wp_date( 'Y' );
$asig_now  = $ag['por_anio'][ $anio_now ] ?? 0;
$prioritarias = $ag['por_prioridad'][1];

// Secuencia de color por lineamiento (docs/design-tokens §63).
$paleta = array( 'var(--primary)', 'var(--secondary)', 'var(--blue-dark)', 'var(--dark)', 'var(--primary-dark)' );

$lineamientos_terms = get_terms( array( 'taxonomy' => 'lineamiento', 'hide_empty' => false ) );
?>

<div class="wrap otrn-pagehead">
	<span class="eyebrow">Plan de Desarrollo Turístico</span>
	<h1>Seguimiento Pladetur</h1>
	<p>Estado de avance, financiamiento y prioridad de las iniciativas del Pladetur de Rapa Nui, agregado por lineamiento estratégico.</p>
</div>

<div class="wrap">

	<!-- Filtros -->
	<form class="otrn-filters" method="get">
		<div class="otrn-field">
			<label for="f-lin">Lineamiento</label>
			<select id="f-lin" name="lineamiento">
				<option value="">Todos los lineamientos</option>
				<?php foreach ( $lineamientos_terms as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $f_lineamiento, $t->slug ); ?>>
						<?php echo esc_html( $t->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="otrn-field">
			<label for="f-q">Buscar</label>
			<input id="f-q" type="search" name="q" value="<?php echo esc_attr( $f_q ); ?>" placeholder="Nombre o código de iniciativa">
		</div>
		<button class="otrn-btn" type="submit">Filtrar</button>
		<?php // El acceso a Estadísticas vive aquí, no en la nav (docs/04 §4). ?>
		<a class="otrn-btn ghost" href="<?php echo esc_url( otrn_estadisticas_url() ); ?>">Ver estadísticas</a>
		<?php if ( $f_lineamiento || $f_q ) : ?>
			<a class="otrn-btn ghost" href="<?php echo esc_url( get_post_type_archive_link( 'iniciativa_pladetur' ) ); ?>">Limpiar</a>
		<?php endif; ?>
	</form>

	<!-- Stat boxes -->
	<div class="otrn-stats">
		<div class="otrn-stat accent">
			<div class="n mono"><?php echo (int) $ag['total']; ?></div>
			<div class="l">Iniciativas</div>
		</div>
		<div class="otrn-stat">
			<div class="n mono"><?php echo esc_html( otrn_mm( $ag['monto_total'] ) ); ?></div>
			<div class="l">Inversión total ($MM)</div>
		</div>
		<div class="otrn-stat">
			<div class="n mono"><?php echo (int) $prioritarias; ?></div>
			<div class="l">Iniciativas prioritarias</div>
		</div>
		<div class="otrn-stat">
			<div class="n mono"><?php echo esc_html( otrn_mm( $asig_now ) ); ?></div>
			<div class="l">Inversión <?php echo (int) $anio_now; ?> ($MM)</div>
		</div>
	</div>

	<div class="otrn-grid2">
		<!-- Por lineamiento -->
		<div class="otrn-panel">
			<h3>Inversión por lineamiento <span class="sub">· $MM y nº de iniciativas</span></h3>
			<?php
			$max_monto = 0;
			foreach ( $ag['por_lineamiento'] as $l ) {
				$max_monto = max( $max_monto, $l['monto'] );
			}
			$i = 0;
			foreach ( $ag['por_lineamiento'] as $l ) :
				$pct   = $max_monto > 0 ? round( $l['monto'] / $max_monto * 100 ) : 0;
				$color = $paleta[ $i % count( $paleta ) ];
				$i++;
				?>
				<div class="otrn-bar-row">
					<div class="otrn-bar-top">
						<span class="lbl"><?php echo esc_html( $l['nombre'] ); ?></span>
						<span class="val"><?php echo esc_html( otrn_mm( $l['monto'] ) ); ?> · <?php echo (int) $l['count']; ?></span>
					</div>
					<div class="otrn-bar-track">
						<div class="otrn-bar-fill" style="width:<?php echo (int) $pct; ?>%;background:<?php echo esc_attr( $color ); ?>;"></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Inversión planificada por año -->
		<div class="otrn-panel">
			<h3>Inversión planificada por año <span class="sub">· asignado, $MM</span></h3>
			<?php get_template_part( 'template-parts/pladetur/chart-anual', null, array( 'por_anio' => $ag['por_anio'] ) ); ?>
		</div>
	</div>

	<!-- Listado (§5) -->
	<div class="otrn-tablewrap">
		<table class="otrn-table">
			<thead>
				<tr>
					<th>Iniciativa</th>
					<th>Lineamiento</th>
					<th class="num">Duración</th>
					<th class="num">Total $MM</th>
					<th class="num">Asig. <?php echo (int) $anio_now; ?> $MM</th>
					<th class="num">% Avance</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $filas ) ) : ?>
				<tr><td colspan="7" class="otrn-empty">No hay iniciativas que coincidan con el filtro.</td></tr>
			<?php else : ?>
				<?php foreach ( $filas as $f ) : ?>
					<tr>
						<td class="ini-title">
							<?php if ( $f['codigo'] ) : ?><span class="ini-code"><?php echo esc_html( $f['codigo'] ); ?></span><br><?php endif; ?>
							<?php echo esc_html( $f['titulo'] ); ?>
						</td>
						<td><?php if ( $f['lineamiento'] ) : ?><span class="otrn-tag"><?php echo esc_html( $f['lineamiento'] ); ?></span><?php endif; ?></td>
						<td class="num"><?php echo esc_html( otrn_pladetur_duracion( $f['periodo_inversion'] ) ); ?></td>
						<td class="num"><?php echo esc_html( otrn_mm( $f['monto_total'] ) ); ?></td>
						<td class="num"><?php echo isset( $f['asignado'][ $anio_now ] ) && null !== $f['asignado'][ $anio_now ] ? esc_html( otrn_mm( $f['asignado'][ $anio_now ] ) ) : '—'; ?></td>
						<td class="num">
							<?php if ( null === $f['porcentaje_avance'] ) : ?>
								<span class="na">s/d</span>
							<?php else : ?>
								<span class="otrn-avance">
									<span class="track"><span class="fill" style="width:<?php echo esc_attr( min( 100, max( 0, $f['porcentaje_avance'] ) ) ); ?>%;"></span></span>
									<?php echo esc_html( otrn_mm( $f['porcentaje_avance'] ) ); ?>%
								</span>
							<?php endif; ?>
						</td>
						<td><a class="otrn-more" href="<?php echo esc_url( $f['permalink'] ); ?>">Ver más →</a></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>

	<p style="color:var(--text3);font-size:12px;margin:16px 0 60px;">
		Mostrando <?php echo count( $filas ); ?> de <?php echo count( $ids ); ?> iniciativas · Fuente: Pladetur Rapa Nui.
	</p>

</div>

<?php
get_footer();
