<?php
/**
 * Estadísticas Pladetur — dashboard agregado con filtro por año (docs/04 §4-5).
 *
 * Página `/estadisticas/` aparte del archivo de `iniciativa_pladetur`: mismo
 * modelo de datos y funciones puras (otrn_pladetur_iniciativa/agregados), pero
 * agregado por año de asignación en vez de por lineamiento/búsqueda, con
 * distribución por prioridad — ver docs/04 §4. Inspirada en el patrón
 * funcional de la página de estadísticas de planturismorapanui.cl, adaptada
 * al sistema de diseño propio (sin Bootstrap ni gráficos de terceros) y sin
 * la sección de "porcentaje acumulado obtenido", diferida por falta de datos
 * de ejecución (`invertido`) — ver docs/04 §0.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

// --- Filtros (GET) -----------------------------------------------------
$anios_disponibles = array_values( array_filter( otrn_pladetur_anios(), static function ( $a ) { return $a <= 2033; } ) ); // 2034 reservado, sin datos.

$f_anio_raw = isset( $_GET['anio'] ) ? sanitize_text_field( wp_unslash( $_GET['anio'] ) ) : 'todos';
$f_anio     = in_array( (int) $f_anio_raw, $anios_disponibles, true ) ? (int) $f_anio_raw : 'todos';

$pagina  = max( 1, (int) ( $_GET['pag'] ?? 1 ) );
$por_pag = 10;

// --- Datos ---------------------------------------------------------------
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

$todas = array_map( 'otrn_pladetur_iniciativa', $ids );

// El filtro de año limita a las iniciativas con asignación planificada ese año.
// Todas las filas traen `asignado_<año>` informado (aunque sea 0): el corte
// relevante es un monto mayor a cero, no solo que el campo exista.
$filas = ( 'todos' === $f_anio )
	? $todas
	: array_values(
		array_filter(
			$todas,
			static function ( $f ) use ( $f_anio ) {
				return ! empty( $f['asignado'][ $f_anio ] );
			}
		)
	);

$ag = otrn_pladetur_agregados( $filas );

$total_paginas = max( 1, (int) ceil( count( $filas ) / $por_pag ) );
$pagina        = min( $pagina, $total_paginas );
$filas_pagina  = array_slice( $filas, ( $pagina - 1 ) * $por_pag, $por_pag );

$base_url  = get_permalink();
$filtro_qs = 'todos' !== $f_anio ? array( 'anio' => $f_anio ) : array();

// Año usado para la columna "Asignado": el filtrado, o el año en curso si no hay filtro.
$anio_col = 'todos' !== $f_anio ? $f_anio : (int) wp_date( 'Y' );

// Secuencia de color por lineamiento (docs/design-tokens §63), mismo orden que el archivo del CPT.
$paleta = array( 'var(--primary)', 'var(--secondary)', 'var(--blue-dark)', 'var(--dark)', 'var(--primary-dark)' );

$lineamientos_terms = get_terms( array( 'taxonomy' => 'lineamiento', 'hide_empty' => false ) );

$prio_si  = $ag['por_prioridad'][1];
$prio_no  = $ag['por_prioridad'][0];
$prio_tot = max( 1, $prio_si + $prio_no );
?>

<div class="wrap otrn-pagehead">
	<span class="eyebrow">Plan de Desarrollo Turístico</span>
	<h1>Estadísticas Pladetur</h1>
	<p>Iniciativas, financiamiento y prioridad del Pladetur de Rapa Nui, agregados por lineamiento y filtrables por año de asignación.</p>
	<?php // Sin ítem de nav propio: se llega desde Planificación, y se vuelve por aquí. ?>
	<p><a href="<?php echo esc_url( get_post_type_archive_link( 'iniciativa_pladetur' ) ); ?>">← Volver a Planificación</a></p>
</div>

<div class="wrap" style="padding-bottom:60px;">

	<!-- Filtro por año -->
	<form class="otrn-filters" method="get" action="<?php echo esc_url( $base_url ); ?>">
		<div class="otrn-field">
			<label for="f-anio">Año</label>
			<select id="f-anio" name="anio">
				<option value="todos" <?php selected( 'todos', $f_anio ); ?>>Todos los años</option>
				<?php foreach ( $anios_disponibles as $a ) : ?>
					<option value="<?php echo esc_attr( $a ); ?>" <?php selected( $f_anio, $a ); ?>><?php echo esc_html( $a ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<button class="otrn-btn" type="submit">Filtrar</button>
		<?php if ( 'todos' !== $f_anio ) : ?>
			<a class="otrn-btn ghost" href="<?php echo esc_url( $base_url ); ?>">Limpiar</a>
		<?php endif; ?>
	</form>

	<p class="otrn-filtro-resumen">Total iniciativas encontradas en el período: <strong class="mono"><?php echo (int) $ag['total']; ?></strong></p>

	<!-- KPI por lineamiento -->
	<div class="otrn-lin-grid">
		<?php
		$i = 0;
		foreach ( $lineamientos_terms as $t ) :
			$datos = $ag['por_lineamiento'][ $t->slug ] ?? array(
				'count' => 0,
				'monto' => 0.0,
			);
			$color = $paleta[ $i % count( $paleta ) ];
			$i++;
			?>
			<div class="otrn-lin-card<?php echo 0 === $datos['count'] ? ' vacia' : ''; ?>">
				<span class="dot" style="background:<?php echo esc_attr( $color ); ?>;"></span>
				<h4><?php echo esc_html( $t->name ); ?></h4>
				<div class="otrn-lin-card__stat">
					<span class="n mono"><?php echo (int) $datos['count']; ?></span>
					<span class="l">Iniciativas</span>
				</div>
				<div class="otrn-lin-card__stat">
					<span class="n mono"><?php echo esc_html( otrn_mm( $datos['monto'] ) ); ?></span>
					<span class="l">$MM</span>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="otrn-grid2">
		<!-- Distribución por prioridad -->
		<div class="otrn-panel">
			<h3>Distribución por prioridad</h3>
			<div class="otrn-split-track">
				<div class="otrn-split-seg" style="width:<?php echo esc_attr( round( $prio_si / $prio_tot * 100 ) ); ?>%;background:var(--primary);"></div>
				<div class="otrn-split-seg" style="width:<?php echo esc_attr( round( $prio_no / $prio_tot * 100 ) ); ?>%;background:var(--lgrey);"></div>
			</div>
			<div class="otrn-split-legend">
				<span><i style="background:var(--primary);"></i>Prioritaria — <strong class="mono"><?php echo (int) $prio_si; ?></strong></span>
				<span><i style="background:var(--lgrey);"></i>No prioritaria — <strong class="mono"><?php echo (int) $prio_no; ?></strong></span>
			</div>
		</div>

		<!-- Inversión planificada por año -->
		<div class="otrn-panel">
			<h3>Inversión planificada por año <span class="sub">· asignado, $MM</span></h3>
			<?php
			get_template_part(
				'template-parts/pladetur/chart-anual',
				null,
				array(
					'por_anio' => $ag['por_anio'],
					'resaltar' => $f_anio,
				)
			);
			?>
		</div>
	</div>

	<!-- Listado (docs/04 §5) -->
	<div class="otrn-tablewrap tarjetas-movil-wrap">
		<table class="otrn-table tarjetas-movil">
			<thead>
				<tr>
					<th>Iniciativa</th>
					<th>Lineamiento</th>
					<th class="num">Duración</th>
					<th class="num">Total $MM</th>
					<th class="num">Asig. <?php echo (int) $anio_col; ?> $MM</th>
					<th class="num">% Avance</th>
					<th>Prioridad</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $filas_pagina ) ) : ?>
				<tr><td colspan="8" class="otrn-empty">No hay iniciativas que coincidan con el filtro.</td></tr>
			<?php else : ?>
				<?php foreach ( $filas_pagina as $f ) : ?>
					<tr>
						<td class="ini-title">
							<?php if ( $f['codigo'] ) : ?><span class="ini-code"><?php echo esc_html( $f['codigo'] ); ?></span><br><?php endif; ?>
							<?php echo esc_html( $f['titulo'] ); ?>
						</td>
						<td data-label="Lineamiento"><?php if ( $f['lineamiento'] ) : ?><span class="otrn-tag"><?php echo esc_html( $f['lineamiento'] ); ?></span><?php endif; ?></td>
						<td class="num" data-label="Duración"><?php echo esc_html( otrn_pladetur_duracion( $f['periodo_inversion'] ) ); ?></td>
						<td class="num" data-label="Total $MM"><?php echo esc_html( otrn_mm( $f['monto_total'] ) ); ?></td>
						<td class="num" data-label="Asig. <?php echo (int) $anio_col; ?> $MM"><?php echo isset( $f['asignado'][ $anio_col ] ) && null !== $f['asignado'][ $anio_col ] ? esc_html( otrn_mm( $f['asignado'][ $anio_col ] ) ) : '—'; ?></td>
						<td class="num" data-label="% Avance">
							<?php if ( null === $f['porcentaje_avance'] ) : ?>
								<span class="na">s/d</span>
							<?php else : ?>
								<span class="otrn-avance">
									<span class="track"><span class="fill" style="width:<?php echo esc_attr( min( 100, max( 0, $f['porcentaje_avance'] ) ) ); ?>%;"></span></span>
									<?php echo esc_html( otrn_mm( $f['porcentaje_avance'] ) ); ?>%
								</span>
							<?php endif; ?>
						</td>
						<td data-label="Prioridad"><span class="otrn-prio <?php echo 1 === $f['prioridad'] ? 'yes' : 'no'; ?>"><?php echo 1 === $f['prioridad'] ? 'Prioritaria' : 'No prioritaria'; ?></span></td>
						<td class="acciones"><a class="otrn-more" href="<?php echo esc_url( $f['permalink'] ); ?>">Ver más →</a></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php
	$enlaces = paginate_links(
		array(
			'base'      => add_query_arg( 'pag', '%#%', $base_url ),
			'format'    => '',
			'current'   => $pagina,
			'total'     => $total_paginas,
			'add_args'  => $filtro_qs,
			'prev_text' => '←',
			'next_text' => '→',
			'type'      => 'array',
		)
	);
	if ( $enlaces ) :
		?>
		<nav class="otrn-pager" aria-label="Paginación de iniciativas">
			<?php echo implode( '', array_map( 'wp_kses_post', $enlaces ) ); ?>
		</nav>
		<?php
	endif;
	?>

	<p style="color:var(--text2);font-size:12px;margin:4px 0 0;">
		Mostrando <?php echo count( $filas_pagina ); ?> de <?php echo count( $filas ); ?> iniciativas<?php echo 'todos' !== $f_anio ? ' en ' . (int) $f_anio : ''; ?> · Fuente: Pladetur Rapa Nui.
	</p>

</div>

<?php
get_footer();
