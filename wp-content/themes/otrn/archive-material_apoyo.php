<?php
/**
 * Material de apoyo — índice completo (docs/05 Parte A §4).
 *
 * La navegación pública entra por las dos páginas de `tipo_material`
 * (Documentación técnica y Reportes); esta vista reúne ambas y sirve de
 * destino a las búsquedas transversales.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$f_q  = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
$args = array( 'post_type' => 'material_apoyo', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' );
if ( $f_q ) {
	$args['s'] = $f_q;
}

get_template_part( 'template-parts/material-lista', null, array(
	'eyebrow' => 'Recursos',
	'titulo'  => 'Material de apoyo',
	'intro'   => 'Documentación técnica, antecedentes estadísticos y enlaces de interés para operadores, gestores y comunidad turística de Rapa Nui.',
	'base'    => get_post_type_archive_link( 'material_apoyo' ),
	'query'   => new WP_Query( $args ),
	'q'       => $f_q,
	'vacio'   => 'No hay material que coincida.',
) );

get_footer();
