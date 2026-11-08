<?php
/**
 * Guía de usuario — centro de ayuda (`/guias/`).
 *
 * Reutiliza la URL del archivo del CPT `guia`, que ahora es esta página guiada:
 * índice de temas a la izquierda (fijo en pantalla de computador, desplegable en
 * el teléfono), artículos a la derecha y navegación anterior/siguiente. El texto
 * de los temas vive en `guias-contenido.php`, no aquí: este archivo solo arma la
 * página.
 *
 * Todo el contenido se sirve desde el servidor (no hay datos de la guía viajando
 * al navegador), y la página entera está detrás del gate de sesión
 * (`otrn_guia_usuario_privada()`, en `functions.php`): sin sesión iniciada, el
 * archivo no llega a pintarse.
 *
 * Sin JavaScript la página sigue siendo utilizable: todos los temas están en el
 * documento, el índice lleva a cada ancla y la navegación anterior/siguiente
 * funciona; solo el buscador y el resaltado del tema actual quedan inertes.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( 'guias-contenido.php' );

get_header();

$otrn_temas  = otrn_guia_temas();
$otrn_anexos = otrn_guia_anexos();
$otrn_total  = count( $otrn_temas );

/**
 * Pinta una vez la lista de temas (se usa en el índice de escritorio y en el
 * desplegable del teléfono; ver los comentarios de `otrn-guias.css`).
 *
 * @param array<int,array<string,mixed>> $temas  Temas en orden.
 * @param bool                           $anexos Si hay guías publicadas que añadir.
 */
$otrn_pinta_indice = static function ( $temas, $anexos ) {
	echo '<ol class="otrn-guia-lista">';
	foreach ( $temas as $i => $tema ) {
		printf(
			'<li><a href="#%1$s" data-indice="%1$s"><span class="n mono">%2$d</span><span class="t">%3$s</span>%4$s</a></li>',
			esc_attr( $tema['id'] ),
			(int) ( $i + 1 ),
			esc_html( $tema['titulo'] ),
			empty( $tema['privado'] ) ? '' : '<span class="candado" aria-hidden="true">•</span><span class="otrn-guia-sr">Requiere sesión iniciada</span>'
		);
	}
	if ( $anexos ) {
		echo '<li><a href="#guias-publicadas" data-indice="guias-publicadas"><span class="n mono">+</span><span class="t">Guías publicadas por el equipo</span></a></li>';
	}
	echo '</ol>';
};
?>

<div class="wrap otrn-pagehead" id="otrn-guia-inicio">
	<span class="eyebrow">Ayuda</span>
	<h1>Guía de usuario</h1>
	<p>Instrucciones para usar el Observatorio Turístico de Puerto Williams: navegación y cuenta, indicadores, geoportal, buenas prácticas, planificación, autoevaluación y material de apoyo. Es una página privada: solo se ve con la sesión iniciada.</p>
</div>

<div class="wrap otrn-guia">

	<aside class="otrn-guia__lado" aria-label="Índice de la guía">
		<div class="otrn-guia__buscador">
			<label for="otrn-guia-q">Buscar en la guía</label>
			<input id="otrn-guia-q" type="search" placeholder="Palabra clave" autocomplete="off" spellcheck="false">
			<p class="otrn-guia__resultado" id="otrn-guia-resultado" role="status" aria-live="polite"></p>
			<noscript><p class="otrn-guia__resultado">La búsqueda necesita JavaScript; sin él, recorre los temas con el índice.</p></noscript>
		</div>

		<nav class="otrn-guia__indice" aria-labelledby="otrn-guia-indice-tit">
			<p class="otrn-guia__indice-tit" id="otrn-guia-indice-tit">Temas</p>
			<?php $otrn_pinta_indice( $otrn_temas, $otrn_anexos ); ?>
		</nav>

		<p class="otrn-guia__nota-lado">Los temas marcados con <span aria-hidden="true">•</span> tratan una parte del sitio que exige sesión iniciada.</p>
	</aside>

	<div class="otrn-guia__cuerpo">

		<details class="otrn-guia__temas-movil">
			<summary>Temas de la guía <?php echo esc_html( '(' . (int) $otrn_total . ')' ); ?></summary>
			<?php $otrn_pinta_indice( $otrn_temas, $otrn_anexos ); ?>
		</details>

		<p class="otrn-guia__vacio" id="otrn-guia-vacio" hidden>No hay temas que coincidan con lo que buscas. Prueba con otra palabra o revisa el índice completo.</p>

		<?php
		foreach ( $otrn_temas as $otrn_i => $otrn_tema ) :
			$otrn_prev = $otrn_i > 0 ? $otrn_temas[ $otrn_i - 1 ] : null;
			$otrn_next = isset( $otrn_temas[ $otrn_i + 1 ] ) ? $otrn_temas[ $otrn_i + 1 ] : null;
			?>
			<article class="otrn-guia-tema" id="<?php echo esc_attr( $otrn_tema['id'] ); ?>" data-tema="<?php echo esc_attr( $otrn_tema['id'] ); ?>">
				<header class="otrn-guia-tema__head">
					<p class="otrn-guia-tema__eyebrow"><span class="mono">Tema <?php echo (int) ( $otrn_i + 1 ); ?> de <?php echo (int) $otrn_total; ?></span></p>
					<h2><?php echo esc_html( $otrn_tema['titulo'] ); ?></h2>
					<p class="otrn-guia-tema__resumen"><?php echo esc_html( $otrn_tema['resumen'] ); ?></p>
					<?php if ( ! empty( $otrn_tema['privado'] ) ) : ?>
						<p class="otrn-guia-sello">Requiere sesión iniciada</p>
					<?php endif; ?>
				</header>

				<?php otrn_guia_bloques( $otrn_tema['bloques'] ); ?>

				<nav class="otrn-guia-nav" aria-label="Temas anterior y siguiente">
					<?php if ( $otrn_prev ) : ?>
						<a class="otrn-guia-nav__prev" href="#<?php echo esc_attr( $otrn_prev['id'] ); ?>">
							<span class="k">← Anterior</span>
							<span class="t"><?php echo esc_html( $otrn_prev['titulo'] ); ?></span>
						</a>
					<?php else : ?>
						<span class="otrn-guia-nav__hueco"></span>
					<?php endif; ?>
					<?php if ( $otrn_next ) : ?>
						<a class="otrn-guia-nav__next" href="#<?php echo esc_attr( $otrn_next['id'] ); ?>">
							<span class="k">Siguiente →</span>
							<span class="t"><?php echo esc_html( $otrn_next['titulo'] ); ?></span>
						</a>
					<?php endif; ?>
				</nav>

				<p class="otrn-guia-arriba"><a href="#otrn-guia-inicio"><span aria-hidden="true">↑</span> Volver arriba</a></p>
			</article>
		<?php endforeach; ?>

		<?php if ( $otrn_anexos ) : ?>
			<article class="otrn-guia-tema" id="guias-publicadas" data-tema="guias-publicadas">
				<header class="otrn-guia-tema__head">
					<p class="otrn-guia-tema__eyebrow"><span class="mono">Anexo</span></p>
					<h2>Guías publicadas por el equipo</h2>
					<p class="otrn-guia-tema__resumen">Instrucciones sueltas publicadas desde el panel de WordPress, además de los temas de arriba.</p>
				</header>
				<div class="otrn-guia-anexos">
					<?php foreach ( $otrn_anexos as $otrn_anexo ) : ?>
						<a class="otrn-guia-anexo" href="<?php echo esc_url( $otrn_anexo['url'] ); ?>">
							<span class="t"><?php echo esc_html( $otrn_anexo['titulo'] ); ?></span>
							<?php if ( $otrn_anexo['resumen'] ) : ?>
								<span class="r"><?php echo esc_html( $otrn_anexo['resumen'] ); ?></span>
							<?php endif; ?>
							<span class="m">Abrir guía →</span>
						</a>
					<?php endforeach; ?>
				</div>
				<p class="otrn-guia-arriba"><a href="#otrn-guia-inicio"><span aria-hidden="true">↑</span> Volver arriba</a></p>
			</article>
		<?php endif; ?>

	</div>
</div>

<?php
get_footer();
