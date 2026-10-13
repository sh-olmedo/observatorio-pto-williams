<?php
/**
 * Seguimiento Pladetur — detalle de iniciativa (docs/04 §6).
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$f = otrn_pladetur_iniciativa( get_the_ID() );
	?>

	<div class="wrap otrn-detail">

		<div class="otrn-pagehead" style="padding-bottom:0;">
			<a class="otrn-back" href="<?php echo esc_url( get_post_type_archive_link( 'iniciativa_pladetur' ) ); ?>">← Volver al seguimiento</a>
			<?php if ( $f['lineamiento'] ) : ?>
				<div style="margin-top:16px;"><span class="otrn-tag"><?php echo esc_html( $f['lineamiento'] ); ?></span></div>
			<?php endif; ?>
			<h1><?php echo esc_html( $f['titulo'] ); ?></h1>
		</div>

		<div class="otrn-detail-grid">
			<div class="otrn-panel">
				<h3>Detalle de la iniciativa</h3>
				<dl class="otrn-dl">
					<?php if ( $f['codigo'] ) : ?>
						<dt>Código</dt><dd class="mono"><?php echo esc_html( $f['codigo'] ); ?></dd>
					<?php endif; ?>
					<dt>Año de implementación</dt><dd class="mono"><?php echo esc_html( $f['anio_implementacion'] ?: '—' ); ?></dd>
					<dt>Duración</dt><dd class="mono"><?php echo esc_html( otrn_pladetur_duracion( $f['periodo_inversion'] ) ); ?></dd>
					<dt>Monto total</dt><dd class="mono"><?php echo esc_html( otrn_mm( $f['monto_total'] ) ); ?> $MM</dd>
					<dt>Prioridad</dt><dd><span class="otrn-prio <?php echo 1 === $f['prioridad'] ? 'yes' : 'no'; ?>"><?php echo 1 === $f['prioridad'] ? 'Prioritaria' : 'No prioritaria'; ?></span></dd>
					<dt>% de avance</dt><dd class="mono"><?php echo null === $f['porcentaje_avance'] ? 'Sin dato' : esc_html( otrn_mm( $f['porcentaje_avance'] ) ) . '%'; ?></dd>
				</dl>
				<?php if ( $f['descripcion'] ) : ?>
					<p style="margin-top:20px;color:var(--text2);font-size:14px;line-height:1.6;"><?php echo esc_html( $f['descripcion'] ); ?></p>
				<?php endif; ?>
			</div>

			<div class="otrn-panel">
				<h3>Gestión y financiamiento</h3>
				<dl class="otrn-dl">
					<dt>Organismos responsables</dt><dd><?php echo esc_html( $f['organismos'] ?: '—' ); ?></dd>
					<dt>Otros organismos vinculados</dt><dd><?php echo esc_html( $f['otros_organismos'] ?: '—' ); ?></dd>
					<dt>Fuente de financiamiento</dt><dd><?php echo esc_html( $f['fuente'] ?: '—' ); ?></dd>
				</dl>
			</div>
		</div>

		<?php
		// Distribución anual — solo años con dato (docs/04 §6: omitir columnas vacías).
		$anios_con_dato = array_filter( $f['asignado'], static function ( $v ) { return null !== $v; } );
		if ( ! empty( $anios_con_dato ) ) :
			?>
			<div class="otrn-panel" style="margin-top:18px;">
				<h3>Distribución anual <span class="sub">· asignado, $MM</span></h3>
				<div class="otrn-tablewrap" style="border:none;">
					<table class="otrn-table" style="min-width:0;">
						<thead>
							<tr>
								<th>Año</th>
								<?php foreach ( $anios_con_dato as $anio => $v ) : ?>
									<th class="num"><?php echo esc_html( $anio ); ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="font-weight:700;">Asignado</td>
								<?php foreach ( $anios_con_dato as $anio => $v ) : ?>
									<td class="num"><?php echo esc_html( otrn_mm( $v ) ); ?></td>
								<?php endforeach; ?>
							</tr>
						</tbody>
					</table>
				</div>
				<p style="color:var(--text3);font-size:12px;margin-top:12px;">Los ejes «previsto» e «invertido» quedan diferidos hasta contar con datos de ejecución (docs/04 §0).</p>
			</div>
		<?php endif; ?>

	</div>

	<?php
endwhile;

get_footer();
