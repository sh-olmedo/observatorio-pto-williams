<?php
/**
 * Material de apoyo dividido por tipo: Documentación técnica y Reportes
 * (docs/05 Parte A §2). Cada término es una página propia.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$term      = get_queried_object();
$secciones = otrn_material_secciones();
$sec       = isset( $secciones[ $term->slug ] ) ? $secciones[ $term->slug ] : array();
$f_q       = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

$args = array(
	'post_type'      => 'material_apoyo',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
	'tax_query'      => array(
		array(
			'taxonomy' => 'tipo_material',
			'field'    => 'term_id',
			'terms'    => $term->term_id,
		),
	),
);
if ( $f_q ) {
	$args['s'] = $f_q;
}

get_template_part( 'template-parts/material-lista', null, array(
	'eyebrow' => 'Material de apoyo',
	'titulo'  => isset( $sec['titulo'] ) ? $sec['titulo'] : $term->name,
	'intro'   => isset( $sec['intro'] ) ? $sec['intro'] : wp_strip_all_tags( term_description( $term->term_id ) ),
	'base'    => get_term_link( $term ),
	'query'   => new WP_Query( $args ),
	'q'       => $f_q,
	'vacio'   => 'No hay material que coincida en esta sección.',
) );

get_footer();
