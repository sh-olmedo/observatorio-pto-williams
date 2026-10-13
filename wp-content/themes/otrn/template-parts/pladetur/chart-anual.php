<?php
/**
 * Inversión planificada por año — barras SVG (docs/04 §4).
 *
 * Compartida por el archivo de Seguimiento Pladetur y la página de
 * Estadísticas para no duplicar el mismo gráfico dos veces.
 *
 * @package otrn
 *
 * @var array<string,mixed> $args {
 *     @type array<int,float> $por_anio  Monto asignado por año (salida de otrn_pladetur_agregados()).
 *     @type int|string       $resaltar  Año a destacar con el color primario (opcional, 'todos' = ninguno).
 * }
 */

defined( 'ABSPATH' ) || exit;

$por_anio = $args['por_anio'] ?? array();
$resaltar = $args['resaltar'] ?? 'todos';

$anios = array_filter( otrn_pladetur_anios(), static function ( $a ) { return $a <= 2033; } ); // 2034 reservado, sin datos.

$max_anio = 0;
foreach ( $anios as $a ) {
	$max_anio = max( $max_anio, $por_anio[ $a ] ?? 0 );
}
$bw = 100 / count( $anios );
?>
<svg viewBox="0 0 100 62" preserveAspectRatio="none" style="width:100%;height:170px;overflow:visible;font-family:'IBM Plex Mono',monospace;">
	<?php
	$x = 0;
	foreach ( $anios as $a ) :
		$val    = $por_anio[ $a ] ?? 0;
		$h      = $max_anio > 0 ? ( $val / $max_anio * 46 ) : 0;
		$bx     = $x + $bw * 0.18;
		$bwi    = $bw * 0.64;
		$activo = ( 'todos' !== $resaltar && (int) $resaltar === $a );
		?>
		<rect x="<?php echo esc_attr( $bx ); ?>" y="<?php echo esc_attr( 50 - $h ); ?>" width="<?php echo esc_attr( $bwi ); ?>" height="<?php echo esc_attr( max( $h, 0.4 ) ); ?>" rx="0.6" fill="<?php echo $activo ? 'var(--primary)' : 'var(--secondary)'; ?>"></rect>
		<text x="<?php echo esc_attr( $x + $bw / 2 ); ?>" y="57" font-size="3" fill="<?php echo $activo ? 'var(--primary-dark)' : 'var(--text3)'; ?>" font-weight="<?php echo $activo ? '700' : '400'; ?>" text-anchor="middle"><?php echo esc_html( substr( (string) $a, 2 ) ); ?></text>
		<?php $x += $bw; ?>
	<?php endforeach; ?>
</svg>
