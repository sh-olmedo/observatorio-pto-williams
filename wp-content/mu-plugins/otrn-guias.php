<?php
/**
 * Plugin Name: OTRN — Guías de usuario
 * Description: Registra el CPT `guia` (título + editor WYSIWYG, sin taxonomía ni campos ACF). Página pública en /guias/.
 * Author: Observatorio Turístico Rapa Nui
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra el CPT de guías de usuario.
 */
function otrn_register_guia_cpt() {
	register_post_type( 'guia', array(
		'labels'          => otrn_cpt_labels( 'Guía', 'Guías' ),
		'public'          => true,
		'show_in_rest'    => true,
		'has_archive'     => true,
		'supports'        => array( 'title', 'editor' ),
		'menu_icon'       => 'dashicons-welcome-learn-more',
		'rewrite'         => array( 'slug' => 'guias' ),
		'capability_type' => array( 'guia', 'guias' ),
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'otrn_register_guia_cpt' );

/** Concede al administrador las capacidades del CPT `guia`. */
function otrn_guias_grant_caps() {
	$role = get_role( 'administrator' );
	if ( ! $role ) {
		return;
	}
	$caps = array(
		'edit_guia', 'read_guia', 'delete_guia',
		'edit_guias', 'edit_others_guias', 'publish_guias',
		'read_private_guias', 'delete_guias', 'delete_private_guias',
		'delete_published_guias', 'delete_others_guias',
		'edit_private_guias', 'edit_published_guias', 'create_guias',
	);
	foreach ( $caps as $cap ) {
		if ( ! $role->has_cap( $cap ) ) {
			$role->add_cap( $cap );
		}
	}
}
/*
 * Se concede en `after_setup_theme` (antes de que el núcleo construya el usuario
 * actual) para que las capacidades estén disponibles en la primera petición; en
 * `admin_init` llegaban tarde (docs/ISSUES.md F29).
 */
add_action( 'after_setup_theme', 'otrn_guias_grant_caps' );
