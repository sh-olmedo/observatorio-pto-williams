<?php
/**
 * Autodiagnóstico — mapa de autoevaluaciones (docs/01 §5.1).
 *
 * El mapa se fusionó con el listado: hoy `/mapa/` redirige a `/autoevaluacion/#mapa`
 * (`otrn_redirigir_pagina_mapa()` en functions.php) y esta plantilla no se renderiza.
 * Se conserva para poder volver a tener el mapa como página propia: basta con
 * quitar ese hook — el marcado y el JS viven en el parcial compartido.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="wrap otrn-pagehead">
	<span class="eyebrow">Autodiagnóstico</span>
	<h1>Mapa de autoevaluaciones</h1>
	<p>Cada punto agrupa las autoevaluaciones registradas en una misma ubicación, con el % promedio. La lista lateral detalla cada evaluación individual.</p>
</div>

<div class="wrap">
	<?php get_template_part( 'template-parts/mapa-evaluaciones' ); ?>
</div>

<?php
get_footer();
