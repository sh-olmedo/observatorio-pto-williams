<?php
/**
 * Autodiagnóstico — mapa + listado de autoevaluaciones (docs/01 §5.1, §5.2, §5.4).
 *
 * Vista única: primero el mapa de dónde se ha evaluado y debajo la tabla con
 * cada autoevaluación. La página `/mapa/` redirige aquí.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Los borradores solo se listan a quien puede gestionarlos (docs/01 §5.2): para
// el público siguen siendo privados, accesibles únicamente por su enlace con token.
$gestiona = current_user_can( 'edit_evaluaciones' );

$estados = $gestiona ? array( 'publish', 'draft' ) : 'publish';
$base    = array(
	'post_type'   => 'evaluacion',
	'post_status' => $estados,
	'numberposts' => -1,
	'orderby'     => 'date',
	'order'       => 'DESC',
	'fields'      => 'ids',
);

// Filtros de la tabla (solo afectan al listado, no al mapa de arriba).
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- filtros GET de solo lectura.
$f_sector = isset( $_GET['sector'] ) ? sanitize_text_field( wp_unslash( $_GET['sector'] ) ) : '';
$f_desde  = isset( $_GET['desde'] ) ? sanitize_text_field( wp_unslash( $_GET['desde'] ) ) : '';
$f_hasta  = isset( $_GET['hasta'] ) ? sanitize_text_field( wp_unslash( $_GET['hasta'] ) ) : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended
foreach ( array( 'f_desde', 'f_hasta' ) as $var ) {
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $$var ) ) {
		$$var = '';
	}
}

// Opciones del selector: los sectores realmente presentes en las evaluaciones.
// Se filtra por la meta `destino` (el nombre del sector) y no por `sector`,
// porque las evaluaciones sembradas antes del selector no tienen ese slug.
$todos_ids = get_posts( $base );
$sectores  = array();
foreach ( $todos_ids as $id ) {
	$d = get_post_meta( $id, 'destino', true );
	if ( '' !== $d ) {
		$sectores[ $d ] = true;
	}
}
$sectores = array_keys( $sectores );
sort( $sectores );

$args = $base;
if ( '' !== $f_sector ) {
	$args['meta_query'] = array( array( 'key' => 'destino', 'value' => $f_sector ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
}
if ( '' !== $f_desde || '' !== $f_hasta ) {
	$rango = array( 'inclusive' => true );
	if ( '' !== $f_desde ) {
		$rango['after'] = $f_desde;
	}
	if ( '' !== $f_hasta ) {
		$rango['before'] = $f_hasta;
	}
	$args['date_query'] = array( $rango );
}

$ids         = get_posts( $args );
$hay_filtros = ( '' !== $f_sector || '' !== $f_desde || '' !== $f_hasta );
$mapa        = otrn_mapa_codigo_ambito();
$total_crit  = count( otrn_codigos_criterios() );
?>

<?php if ( isset( $_GET['archivada'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
	<div class="wrap" style="padding-top:24px;">
		<div class="otrn-notice ok con-cierre" data-otrn-dismiss>
			<button type="button" class="otrn-notice-x" aria-label="Descartar aviso">&times;</button>
			<strong>Autoevaluación archivada.</strong> Queda en la papelera de WordPress; un administrador puede restaurarla desde <em>Evaluaciones → Papelera</em>.
		</div>
	</div>
	<script>
	// Cierra el aviso y limpia el parámetro de la URL, para que no reaparezca al recargar.
	document.querySelectorAll('.otrn-notice[data-otrn-dismiss] .otrn-notice-x').forEach(function(b){
		b.addEventListener('click', function(){
			b.closest('.otrn-notice').remove();
			var u = new URL(window.location.href);
			u.searchParams.delete('archivada');
			history.replaceState(null, '', u.pathname + u.search + u.hash);
		});
	});
	</script>
<?php endif; ?>

<div class="wrap otrn-pagehead">
	<span class="eyebrow">Evaluación de sostenibilidad</span>
	<h1>Autoevaluaciones de sostenibilidad</h1>
	<p>Cada autoevaluación califica los 74 criterios de sostenibilidad turística en 4 ámbitos, en una escala de 0 a 3.<?php echo $gestiona ? ' Las marcadas como <strong>borrador</strong> están incompletas: no tienen resultados y solo las ves tú, con permisos de gestión.' : ''; ?></p>
	<p style="margin-top:16px;display:flex;gap:12px;"><a class="otrn-btn" href="<?php echo esc_url( home_url( '/evaluar/' ) ); ?>">+ Nueva autoevaluación</a></p>
</div>

<div class="wrap" id="mapa">
	<h2 class="otrn-sechead">Dónde se ha evaluado</h2>
	<?php get_template_part( 'template-parts/mapa-evaluaciones' ); ?>
</div>

<div class="wrap" id="listado" style="padding-bottom:60px;">
	<h2 class="otrn-sechead">Autoevaluaciones registradas</h2>

	<form class="otrn-filters" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'evaluacion' ) ); ?>">
		<div class="otrn-field">
			<label for="f-sector">Sector evaluado</label>
			<select id="f-sector" name="sector">
				<option value="">Todos los sectores</option>
				<?php foreach ( $sectores as $s ) : ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $f_sector, $s ); ?>><?php echo esc_html( $s ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="otrn-field">
			<label for="f-desde">Desde</label>
			<input id="f-desde" type="date" name="desde" value="<?php echo esc_attr( $f_desde ); ?>">
		</div>
		<div class="otrn-field">
			<label for="f-hasta">Hasta</label>
			<input id="f-hasta" type="date" name="hasta" value="<?php echo esc_attr( $f_hasta ); ?>">
		</div>
		<button class="otrn-btn" type="submit">Filtrar</button>
		<?php if ( $hay_filtros ) : ?>
			<a class="otrn-btn ghost" href="<?php echo esc_url( get_post_type_archive_link( 'evaluacion' ) ); ?>">Limpiar</a>
		<?php endif; ?>
	</form>

	<?php if ( $hay_filtros ) : ?>
		<p class="otrn-filtro-resumen mono"><?php echo count( $ids ); ?> de <?php echo count( $todos_ids ); ?> autoevaluaciones</p>
		<script>
		// Al filtrar, el formulario GET recarga la página completa: llevar la vista
		// al listado para no aterrizar arriba del todo, sobre el mapa.
		if (!window.location.hash) { document.getElementById('listado').scrollIntoView(); }
		</script>
	<?php endif; ?>

	<?php if ( empty( $ids ) ) : ?>
		<div class="otrn-empty" style="border:1px solid var(--grey);border-radius:14px;">
			<?php echo $hay_filtros ? 'Ninguna autoevaluación coincide con los filtros.' : 'Aún no hay autoevaluaciones registradas.'; ?>
		</div>
	<?php else : ?>
		<?php // En móvil la tabla se reordena como tarjetas (`tarjetas-movil`): con scroll
		// horizontal, la columna de acciones quedaba fuera de pantalla. ?>
		<div class="otrn-tablewrap tarjetas-movil-wrap">
			<table class="otrn-table tarjetas-movil">
				<thead>
					<tr><th>Destino</th><th>Fecha</th><th class="num">Sociocult.</th><th class="num">Econ.</th><th class="num">Gestión</th><th class="num">M. Amb.</th><th class="num">Global</th><th></th></tr>
				</thead>
				<tbody>
				<?php foreach ( $ids as $id ) :
					$respuestas  = otrn_evaluacion_respuestas( $id );
					$calc        = otrn_calcular_desempeno( $respuestas, $mapa );
					$es_borrador = 'draft' === get_post_status( $id );
					$destino     = get_post_meta( $id, 'destino', true ) ?: ( $es_borrador ? 'Sin sector' : get_the_title( $id ) );
					$es_demo     = (bool) get_post_meta( $id, 'es_ejemplo', true );
					$g           = $calc['global'];
					$cell        = static function ( $amb ) use ( $calc, $es_borrador ) {
						$p = $calc['por_ambito'][ $amb ]['pct'] ?? null;
						return ( $es_borrador || null === $p ) ? '—' : otrn_mm( $p ) . '%';
					};
					?>
					<tr<?php echo $es_borrador ? ' class="es-borrador"' : ''; ?>>
						<td class="ini-title">
							<?php echo esc_html( $destino ); ?>
							<?php if ( $es_borrador ) : ?>
								<span class="otrn-tag borrador">borrador</span>
							<?php elseif ( $es_demo ) : ?>
								<span class="otrn-tag" style="background:var(--primary-soft);color:var(--primary-dark);">ejemplo</span>
							<?php endif; ?>
						</td>
						<td class="mono" data-label="Fecha"><?php echo esc_html( get_the_date( 'd/m/Y', $id ) ); ?></td>
						<td class="num" data-label="Sociocult."><?php echo esc_html( $cell( 'Sociocultural' ) ); ?></td>
						<td class="num" data-label="Econ."><?php echo esc_html( $cell( 'Económico' ) ); ?></td>
						<td class="num" data-label="Gestión"><?php echo esc_html( $cell( 'Gestión' ) ); ?></td>
						<td class="num" data-label="M. Amb."><?php echo esc_html( $cell( 'Medio Ambiente' ) ); ?></td>
						<td class="num" data-label="<?php echo $es_borrador ? 'Respondidas' : 'Global'; ?>" style="font-weight:800;">
							<?php if ( $es_borrador ) : ?>
								<span class="otrn-progreso"><?php echo count( $respuestas ); ?>/<?php echo (int) $total_crit; ?></span>
							<?php else : ?>
								<?php echo null === $g ? '—' : esc_html( otrn_mm( $g ) ) . '%'; ?>
							<?php endif; ?>
						</td>
						<td class="acciones">
							<?php if ( ! $es_borrador ) : ?>
								<a class="otrn-more" href="<?php echo esc_url( get_permalink( $id ) ); ?>">Ver resultados →</a>
							<?php else : ?>
								<div class="otrn-rowacts">
									<a class="otrn-more" href="<?php echo esc_url( otrn_borrador_url( $id ) ); ?>">Continuar →</a>
									<?php if ( current_user_can( 'delete_evaluacion', $id ) ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('¿Archivar esta autoevaluación en borrador? Queda en la papelera y se puede restaurar desde wp-admin.');">
											<input type="hidden" name="action" value="otrn_archivar_eval">
											<input type="hidden" name="evaluacion_id" value="<?php echo (int) $id; ?>">
											<?php wp_nonce_field( 'otrn_archivar_' . $id, 'otrn_archivar_nonce' ); ?>
											<button type="submit" class="otrn-linkbtn">Archivar</button>
										</form>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>

<?php
get_footer();
