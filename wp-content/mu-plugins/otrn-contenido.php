<?php
/**
 * Plugin Name: OTRN — Contenido (Indicadores, Buenas prácticas, Material)
 * Description: Registra los CPT `indicador`, `buena_practica`, `material_apoyo` y sus taxonomías. Ver docs/02, docs/03, docs/05.
 * Author: Observatorio Turístico Rapa Nui
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Helper: arma un set estándar de labels para un CPT.
 *
 * @param string $singular Singular.
 * @param string $plural   Plural.
 * @return array<string,string>
 */
function otrn_cpt_labels( $singular, $plural ) {
	return array(
		'name'          => $plural,
		'singular_name' => $singular,
		'menu_name'     => $plural,
		'add_new_item'  => "Agregar {$singular}",
		'edit_item'     => "Editar {$singular}",
		'all_items'     => "Todos: {$plural}",
		'search_items'  => "Buscar {$plural}",
		'not_found'     => "No se encontraron {$plural}.",
	);
}

/**
 * Registra los CPTs de contenido.
 */
function otrn_register_contenido_cpts() {
	register_post_type( 'indicador', array(
		'labels'          => otrn_cpt_labels( 'Indicador', 'Indicadores' ),
		'public'          => true,
		'show_in_rest'    => true,
		'has_archive'     => true,
		'supports'        => array( 'title' ),
		'taxonomies'      => array( 'categoria_indicador' ),
		'menu_icon'       => 'dashicons-chart-bar',
		'rewrite'         => array( 'slug' => 'indicadores' ),
		'capability_type' => array( 'indicador', 'indicadores' ),
		'map_meta_cap'    => true,
	) );

	register_post_type( 'buena_practica', array(
		'labels'          => otrn_cpt_labels( 'Buena práctica', 'Buenas prácticas' ),
		'public'          => true,
		'show_in_rest'    => true,
		'has_archive'     => true,
		'supports'        => array( 'title', 'thumbnail' ),
		'taxonomies'      => array( 'ambito' ),
		'menu_icon'       => 'dashicons-awards',
		'rewrite'         => array( 'slug' => 'buenas-practicas' ),
		'capability_type' => array( 'buena_practica', 'buenas_practicas' ),
		'map_meta_cap'    => true,
	) );

	// Las tres cifras destacadas de la portada. Son datos editoriales, no
	// indicadores de serie: no tienen ficha propia ni archivo público, solo
	// alimentan la portada (docs/06 §2.1). `page-attributes` da el orden.
	register_post_type( 'dato_portada', array(
		'labels'          => otrn_cpt_labels( 'Dato de portada', 'Datos de portada' ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_rest'    => true,
		'has_archive'     => false,
		'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
		'menu_icon'       => 'dashicons-chart-pie',
		'capability_type' => array( 'dato_portada', 'datos_portada' ),
		'map_meta_cap'    => true,
	) );

	register_post_type( 'material_apoyo', array(
		'labels'          => otrn_cpt_labels( 'Material de apoyo', 'Material de apoyo' ),
		'public'          => true,
		'show_in_rest'    => true,
		'has_archive'     => true,
		'supports'        => array( 'title' ),
		'taxonomies'      => array( 'tipo_material', 'alcance_material' ),
		'menu_icon'       => 'dashicons-media-document',
		'rewrite'         => array( 'slug' => 'material-de-apoyo' ),
		'capability_type' => array( 'material_apoyo', 'materiales_apoyo' ),
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'otrn_register_contenido_cpts' );

/**
 * Taxonomías propias de contenido (`ambito` la registra otrn-autodiagnostico).
 */
function otrn_register_contenido_taxonomies() {
	register_taxonomy( 'categoria_indicador', array( 'indicador' ), array(
		'labels'            => array( 'name' => 'Categorías de indicador', 'singular_name' => 'Categoría' ),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'categoria-indicador' ),
	) );

	// Divide Material de apoyo en dos vistas públicas: /material/documentacion-tecnica/
	// y /material/reportes/ (docs/05 Parte A §2). Slug propio para no chocar con
	// el rewrite del CPT (`material-de-apoyo`).
	register_taxonomy( 'tipo_material', array( 'material_apoyo' ), array(
		'labels'            => array( 'name' => 'Tipos de material', 'singular_name' => 'Tipo' ),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'material' ),
		'default_term'      => array(
			'name' => 'Documentación técnica',
			'slug' => 'documentacion-tecnica',
		),
	) );

	register_taxonomy( 'alcance_material', array( 'material_apoyo' ), array(
		'labels'            => array( 'name' => 'Alcances', 'singular_name' => 'Alcance' ),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'alcance-material' ),
	) );
}
add_action( 'init', 'otrn_register_contenido_taxonomies' );

/** Términos semilla de las taxonomías de contenido (docs/02 §2, docs/05 §2). */
function otrn_seed_contenido_terms() {
	$sets = array(
		'categoria_indicador' => array( 'Oferta', 'Demanda', 'Otros', 'Gobernanza' ),
		'tipo_material'       => array( 'Documentación técnica', 'Reportes' ),
		'alcance_material'    => array( 'Comunal', 'Regional', 'Nacional', 'Internacional', 'Alojamiento', 'Gestión' ),
	);
	foreach ( $sets as $tax => $nombres ) {
		foreach ( $nombres as $n ) {
			if ( ! term_exists( $n, $tax ) ) {
				wp_insert_term( $n, $tax );
			}
		}
	}

	// `default_term` de register_taxonomy solo graba la opción cuando el término
	// aún no existe; si ya estaba sembrado, WP se queda sin término por defecto y
	// un material creado sin clasificar no aparecería en ninguna de las dos
	// páginas (docs/05 Parte A §2). Se fija a mano.
	if ( ! get_option( 'default_term_tipo_material' ) ) {
		$doc = get_term_by( 'slug', 'documentacion-tecnica', 'tipo_material' );
		if ( $doc ) {
			update_option( 'default_term_tipo_material', $doc->term_id );
		}
	}
}
add_action( 'init', 'otrn_seed_contenido_terms', 20 );

/** Auto-carga de grupos ACF desde docs/acf-json/ (idempotente). */
function otrn_contenido_acf_json( $paths ) {
	$dir = ABSPATH . 'docs/acf-json';
	if ( ! in_array( $dir, $paths, true ) ) {
		$paths[] = $dir;
	}
	return $paths;
}
add_filter( 'acf/settings/load_json', 'otrn_contenido_acf_json' );

/** Concede al administrador las capacidades de los CPTs de contenido. */
function otrn_contenido_grant_caps() {
	$role = get_role( 'administrator' );
	if ( ! $role ) {
		return;
	}
	$mapa = array(
		'indicador'      => 'indicadores',
		'buena_practica' => 'buenas_practicas',
		'material_apoyo' => 'materiales_apoyo',
		'dato_portada'   => 'datos_portada',
	);
	foreach ( $mapa as $sing => $plur ) {
		$caps = array(
			"edit_{$sing}", "read_{$sing}", "delete_{$sing}",
			"edit_{$plur}", "edit_others_{$plur}", "publish_{$plur}",
			"read_private_{$plur}", "delete_{$plur}", "delete_private_{$plur}",
			"delete_published_{$plur}", "delete_others_{$plur}",
			"edit_private_{$plur}", "edit_published_{$plur}", "create_{$plur}",
		);
		foreach ( $caps as $cap ) {
			if ( ! $role->has_cap( $cap ) ) {
				$role->add_cap( $cap );
			}
		}
	}
}
/*
 * Se concede en `after_setup_theme`, que se dispara antes de que el núcleo
 * construya el usuario actual (`wp-settings.php`: `after_setup_theme` L757,
 * `$GLOBALS['wp']->init()` L766). En `admin_init` llegaba tarde: `WP_User::allcaps`
 * ya estaba calculado y la primera pantalla del listado respondía «no tienes
 * permisos para editar este tipo de entradas» (docs/ISSUES.md F29).
 */
add_action( 'after_setup_theme', 'otrn_contenido_grant_caps' );
