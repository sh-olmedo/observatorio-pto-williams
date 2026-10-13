<?php
/**
 * Plugin Name: OTRN — Seguimiento Pladetur (CPT y taxonomía)
 * Description: Registra el CPT `iniciativa_pladetur` y la taxonomía `lineamiento` según docs/04-seguimiento-pladetur.md.
 * Author: Observatorio Turístico Rapa Nui
 *
 * Los campos personalizados viven en ACF (docs/acf-json/), no aquí.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Términos de la taxonomía `lineamiento` (docs/04 §2).
 *
 * El slug es la clave estable: coincide con el de la fuente Pladetur, lo que
 * permite re-sincronizar sin remapear. Ver docs/07 §2.
 *
 * @return array<string,string> slug => nombre.
 */
function otrn_get_lineamientos() {
	return array(
		'infraestructura-y-conectividad'      => 'Infraestructura y conectividad',
		'sostenibilidad-y-responsabilidad'    => 'Sostenibilidad y responsabilidad',
		'desarrollo-de-oferta-y-experiencias' => 'Desarrollo de oferta y experiencias',
		'marketing-y-promocion'               => 'Marketing y promoción',
		'articulacion-y-gestion'              => 'Articulación y gestión',
	);
}

/**
 * Registra el CPT `iniciativa_pladetur`.
 *
 * `capability_type` propio para que el rol "Editor de Seguimiento Pladetur"
 * pueda aislarse a este CPT (docs/06 §3, opción B).
 */
function otrn_register_iniciativa_pladetur_cpt() {
	$labels = array(
		'name'               => 'Iniciativas Pladetur',
		'singular_name'      => 'Iniciativa Pladetur',
		'menu_name'          => 'Seguimiento Pladetur',
		'name_admin_bar'     => 'Iniciativa Pladetur',
		'add_new'            => 'Agregar nueva',
		'add_new_item'       => 'Agregar nueva iniciativa',
		'new_item'           => 'Nueva iniciativa',
		'edit_item'          => 'Editar iniciativa',
		'view_item'          => 'Ver iniciativa',
		'all_items'          => 'Todas las iniciativas',
		'search_items'       => 'Buscar iniciativas',
		'not_found'          => 'No se encontraron iniciativas.',
		'not_found_in_trash' => 'No hay iniciativas en la papelera.',
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_rest'       => true,
		'has_archive'        => true,
		'supports'           => array( 'title' ),
		'taxonomies'         => array( 'lineamiento' ),
		'menu_icon'          => 'dashicons-chart-line',
		'rewrite'            => array( 'slug' => 'seguimiento-pladetur' ),
		'capability_type'    => array( 'iniciativa_pladetur', 'iniciativas_pladetur' ),
		'map_meta_cap'       => true,
	);

	register_post_type( 'iniciativa_pladetur', $args );
}
add_action( 'init', 'otrn_register_iniciativa_pladetur_cpt' );

/**
 * Registra la taxonomía `lineamiento`.
 *
 * Exclusiva de `iniciativa_pladetur`. La taxonomía compartida entre CPTs es
 * `ambito`, no esta (CLAUDE.md).
 */
function otrn_register_lineamiento_taxonomy() {
	$labels = array(
		'name'          => 'Lineamientos',
		'singular_name' => 'Lineamiento',
		'search_items'  => 'Buscar lineamientos',
		'all_items'     => 'Todos los lineamientos',
		'edit_item'     => 'Editar lineamiento',
		'update_item'   => 'Actualizar lineamiento',
		'add_new_item'  => 'Agregar lineamiento',
		'new_item_name' => 'Nuevo lineamiento',
		'menu_name'     => 'Lineamientos',
	);

	register_taxonomy(
		'lineamiento',
		array( 'iniciativa_pladetur' ),
		array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'lineamiento' ),
		)
	);
}
add_action( 'init', 'otrn_register_lineamiento_taxonomy' );

/**
 * Siembra los 5 lineamientos si faltan.
 *
 * Los términos son un vocabulario cerrado definido por el Pladetur, no
 * contenido editorial: por eso se crean en código y no a mano.
 */
function otrn_seed_lineamientos() {
	foreach ( otrn_get_lineamientos() as $slug => $nombre ) {
		if ( ! term_exists( $slug, 'lineamiento' ) ) {
			wp_insert_term( $nombre, 'lineamiento', array( 'slug' => $slug ) );
		}
	}
}
add_action( 'init', 'otrn_seed_lineamientos', 20 );

/**
 * Registra `docs/acf-json/` como ruta de auto-carga de grupos de campos ACF.
 *
 * Los grupos viven versionados en el repo, no en la base de datos. ACF free y
 * Secure Custom Fields respetan este filtro por igual.
 */
function otrn_acf_json_load_point( $paths ) {
	$paths[] = ABSPATH . 'docs/acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'otrn_acf_json_load_point' );

/**
 * Concede al administrador las capacidades del CPT.
 *
 * Sin esto el menú no aparece, porque `capability_type` propio genera
 * capacidades que ningún rol tiene todavía. Los demás roles de docs/06 §3 se
 * crean con Members.
 */
function otrn_grant_admin_caps() {
	$role = get_role( 'administrator' );
	if ( ! $role ) {
		return;
	}

	$caps = array(
		'edit_iniciativa_pladetur',
		'read_iniciativa_pladetur',
		'delete_iniciativa_pladetur',
		'edit_iniciativas_pladetur',
		'edit_others_iniciativas_pladetur',
		'publish_iniciativas_pladetur',
		'read_private_iniciativas_pladetur',
		'delete_iniciativas_pladetur',
		'delete_private_iniciativas_pladetur',
		'delete_published_iniciativas_pladetur',
		'delete_others_iniciativas_pladetur',
		'edit_private_iniciativas_pladetur',
		'edit_published_iniciativas_pladetur',
		'create_iniciativas_pladetur',
	);

	foreach ( $caps as $cap ) {
		if ( ! $role->has_cap( $cap ) ) {
			$role->add_cap( $cap );
		}
	}
}
add_action( 'admin_init', 'otrn_grant_admin_caps' );
