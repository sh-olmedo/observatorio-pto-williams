<?php
/**
 * Plugin Name: OTRN — Autodiagnóstico (CPT, taxonomía, cálculo)
 * Description: Registra el CPT `criterio_autodiagnostico`, la taxonomía compartida `ambito`, el CPT `evaluacion`, y la función pura de cálculo de % por ámbito. Ver docs/01-autoevaluacion-destinos-turisticos.md.
 * Author: Observatorio Turístico Rapa Nui
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ámbitos del autodiagnóstico (docs/01 §2). Vocabulario cerrado.
 *
 * @return array<string,string> slug => nombre.
 */
function otrn_get_ambitos() {
	return array(
		'sociocultural'  => 'Sociocultural',
		'economico'      => 'Económico',
		'gestion'        => 'Gestión',
		'medio-ambiente' => 'Medio Ambiente',
	);
}

/**
 * Registra el CPT `criterio_autodiagnostico` (los 74 indicadores calificables).
 */
function otrn_register_criterio_cpt() {
	$labels = array(
		'name'          => 'Criterios de autodiagnóstico',
		'singular_name' => 'Criterio',
		'menu_name'     => 'Autodiagnóstico',
		'add_new_item'  => 'Agregar criterio',
		'edit_item'     => 'Editar criterio',
		'all_items'     => 'Todos los criterios',
		'search_items'  => 'Buscar criterios',
		'not_found'     => 'No se encontraron criterios.',
	);

	// NOTA: la clave del CPT debe tener ≤20 caracteres (límite de WP). El slug
	// `criterio_autodiagnostico` de CLAUDE.md/docs06 tiene 24 y no se registra;
	// se usa `criterio_autodiag`. Ver docs/07 §13.
	register_post_type(
		'criterio_autodiag',
		array(
			'labels'          => $labels,
			'public'          => true,
			'show_in_rest'    => true,
			'has_archive'     => false,
			'supports'        => array( 'title' ),
			'taxonomies'      => array( 'ambito' ),
			'menu_icon'       => 'dashicons-forms',
			'rewrite'         => array( 'slug' => 'criterio' ),
			'capability_type' => array( 'criterio_autodiagnostico', 'criterios_autodiagnostico' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'otrn_register_criterio_cpt' );

/**
 * Registra el CPT `evaluacion` (resultados guardados de una autoevaluación).
 */
function otrn_register_evaluacion_cpt() {
	register_post_type(
		'evaluacion',
		array(
			'labels'          => array(
				'name'          => 'Evaluaciones',
				'singular_name' => 'Evaluación',
				'menu_name'     => 'Evaluaciones',
			),
			'public'          => true,
			'show_in_rest'    => true,
			'has_archive'     => true,
			'supports'        => array( 'title' ),
			'menu_icon'       => 'dashicons-chart-area',
			'rewrite'         => array( 'slug' => 'autoevaluacion' ),
			'capability_type' => array( 'evaluacion', 'evaluaciones' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'otrn_register_evaluacion_cpt' );

/**
 * Registra el CPT `sector` (sectores de Rapa Nui evaluables, con coordenadas).
 *
 * Editable en wp-admin: agregar = nuevo sector publicado; esconder = borrador.
 * No tiene frontend propio (`public => false`); solo alimenta el formulario y
 * el mapa del autodiagnóstico.
 */
function otrn_register_sector_cpt() {
	register_post_type(
		'sector',
		array(
			'labels'          => array(
				'name'          => 'Sectores',
				'singular_name' => 'Sector',
				'menu_name'     => 'Sectores',
				'add_new_item'  => 'Agregar sector',
				'edit_item'     => 'Editar sector',
				'all_items'     => 'Todos los sectores',
				'not_found'     => 'No hay sectores.',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => true,
			'supports'        => array( 'title', 'page-attributes' ),
			'menu_icon'       => 'dashicons-location',
			'capability_type' => array( 'sector', 'sectores' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'otrn_register_sector_cpt' );

/**
 * Registra la taxonomía compartida `ambito` (docs/01, docs/03).
 *
 * Compartida entre criterios, buenas prácticas e indicadores — no duplicar
 * (CLAUDE.md). En `indicador` es el eje de sostenibilidad, ortogonal a
 * `categoria_indicador` (Oferta/Demanda/Otros/Gobernanza); ver docs/02 §2.1.
 */
function otrn_register_ambito_taxonomy() {
	register_taxonomy(
		'ambito',
		array( 'criterio_autodiag', 'buena_practica', 'indicador' ),
		array(
			'labels'            => array(
				'name'          => 'Ámbitos',
				'singular_name' => 'Ámbito',
				'menu_name'     => 'Ámbitos',
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'ambito' ),
		)
	);
}
add_action( 'init', 'otrn_register_ambito_taxonomy' );

/**
 * Siembra los 4 ámbitos si faltan.
 */
function otrn_seed_ambitos() {
	foreach ( otrn_get_ambitos() as $slug => $nombre ) {
		if ( ! term_exists( $slug, 'ambito' ) ) {
			wp_insert_term( $nombre, 'ambito', array( 'slug' => $slug ) );
		}
	}
}
add_action( 'init', 'otrn_seed_ambitos', 20 );

/**
 * Calcula el % de desempeño por ámbito (docs/01 §4).
 *
 * Función pura: no toca la BD. Recibe respuestas [codigo => puntaje] donde el
 * puntaje es 0..3 o 'NA' (o null), y un mapa [codigo => ambito]. No duplicar
 * esta fórmula en las vistas — usar siempre esta función.
 *
 * % ámbito = (Σ puntajes calificables / (n_calificables × 3)) × 100.
 * Los 'NA'/null se excluyen de numerador y denominador.
 *
 * @param array<string,mixed>  $respuestas   [codigo => 0..3 | 'NA' | null].
 * @param array<string,string> $codigo_ambito [codigo => nombre de ámbito].
 * @return array{por_ambito:array<string,array{suma:float,n:int,pct:?float}>,global:?float}
 */
function otrn_calcular_desempeno( array $respuestas, array $codigo_ambito ) {
	$acc = array();

	foreach ( $codigo_ambito as $codigo => $ambito ) {
		if ( ! isset( $acc[ $ambito ] ) ) {
			$acc[ $ambito ] = array( 'suma' => 0.0, 'n' => 0, 'pct' => null );
		}
		if ( ! array_key_exists( $codigo, $respuestas ) ) {
			continue;
		}
		$val = $respuestas[ $codigo ];
		if ( null === $val || 'NA' === $val || '' === $val ) {
			continue; // excluido (docs/01 §4).
		}
		$acc[ $ambito ]['suma'] += (float) $val;
		$acc[ $ambito ]['n']++;
	}

	$suma_g = 0.0;
	$n_g    = 0;
	foreach ( $acc as $ambito => &$a ) {
		if ( $a['n'] > 0 ) {
			$a['pct'] = round( $a['suma'] / ( $a['n'] * 3 ) * 100, 1 );
			$suma_g  += $a['suma'];
			$n_g     += $a['n'];
		}
	}
	unset( $a );

	return array(
		'por_ambito' => $acc,
		'global'     => $n_g > 0 ? round( $suma_g / ( $n_g * 3 ) * 100, 1 ) : null,
	);
}

/**
 * Sectores por defecto de Rapa Nui (semilla del CPT `sector`).
 *
 * @return array<string,array{nombre:string,lat:float,lng:float}>
 */
function otrn_sectores_default() {
	return array(
		'isla-completa'  => array( 'nombre' => 'Isla completa (Rapa Nui)', 'lat' => -27.1127, 'lng' => -109.3497 ),
		'hanga-roa'      => array( 'nombre' => 'Hanga Roa',                 'lat' => -27.1500, 'lng' => -109.4333 ),
		'tahai'          => array( 'nombre' => 'Tahai',                     'lat' => -27.1400, 'lng' => -109.4290 ),
		'anakena'        => array( 'nombre' => 'Anakena',                   'lat' => -27.0730, 'lng' => -109.3230 ),
		'rano-raraku'    => array( 'nombre' => 'Rano Raraku',              'lat' => -27.1213, 'lng' => -109.2890 ),
		'ahu-tongariki'  => array( 'nombre' => 'Ahu Tongariki',           'lat' => -27.1256, 'lng' => -109.2769 ),
		'orongo-rano-kau'=> array( 'nombre' => 'Orongo · Rano Kau',       'lat' => -27.1889, 'lng' => -109.4419 ),
		'poike'          => array( 'nombre' => 'Poike',                    'lat' => -27.0940, 'lng' => -109.2540 ),
		'vaihu'          => array( 'nombre' => 'Vaihú · Hanga Teʻe',       'lat' => -27.1720, 'lng' => -109.3650 ),
		'terevaka'       => array( 'nombre' => 'Terevaka',                 'lat' => -27.0850, 'lng' => -109.3760 ),
	);
}

/**
 * Sectores seleccionables en el autodiagnóstico (fuente única para formulario
 * y manejador de envío).
 *
 * Lee del CPT `sector` (editable en wp-admin): solo los publicados; poner un
 * sector en borrador lo «esconde» del formulario. Si aún no se ha sembrado
 * ningún sector, cae a `otrn_sectores_default()` para no romper el formulario.
 * La lat/lng vive en el CPT, no la ingresa el usuario que evalúa.
 *
 * @return array<string,array{nombre:string,lat:float,lng:float}>
 */
function otrn_sectores_rapanui() {
	$posts = get_posts( array(
		'post_type'   => 'sector',
		'post_status' => 'publish',
		'numberposts' => -1,
		'orderby'     => 'menu_order title',
		'order'       => 'ASC',
	) );

	if ( empty( $posts ) ) {
		return otrn_sectores_default();
	}

	$out = array();
	foreach ( $posts as $p ) {
		$out[ $p->post_name ] = array(
			'nombre' => $p->post_title,
			'lat'    => (float) get_post_meta( $p->ID, 'lat', true ),
			'lng'    => (float) get_post_meta( $p->ID, 'lng', true ),
		);
	}
	return $out;
}

/**
 * Devuelve (creando si falta) el token de reanudación de un borrador.
 *
 * Quien evalúa es anónimo (no hay cuenta pública, docs/01 §6), así que la única
 * llave para volver a un borrador es este token en la URL. Se borra al publicar.
 *
 * @param int $post_id ID de la evaluación.
 * @return string Token.
 */
function otrn_borrador_token( $post_id ) {
	$token = (string) get_post_meta( $post_id, 'borrador_token', true );
	if ( '' === $token ) {
		$token = wp_generate_password( 24, false, false );
		update_post_meta( $post_id, 'borrador_token', $token );
	}
	return $token;
}

/**
 * URL para retomar un borrador de autoevaluación.
 *
 * @param int $post_id ID de la evaluación en borrador.
 * @return string URL absoluta a /evaluar/ con el borrador precargado.
 */
function otrn_borrador_url( $post_id ) {
	return add_query_arg(
		array(
			'borrador' => (int) $post_id,
			'token'    => otrn_borrador_token( $post_id ),
		),
		home_url( '/evaluar/' )
	);
}

/**
 * Valida un par (id, token) de borrador.
 *
 * Solo acepta evaluaciones en estado `draft`: una vez publicada, el enlace deja
 * de servir para editarla.
 *
 * @param mixed  $post_id ID candidato.
 * @param string $token   Token candidato.
 * @return WP_Post|null El borrador, o null si el par no es válido.
 */
function otrn_borrador_valido( $post_id, $token ) {
	$post_id = (int) $post_id;
	$token   = (string) $token;
	if ( $post_id <= 0 || '' === $token ) {
		return null;
	}
	$post = get_post( $post_id );
	if ( ! $post || 'evaluacion' !== $post->post_type || 'draft' !== $post->post_status ) {
		return null;
	}
	$guardado = (string) get_post_meta( $post_id, 'borrador_token', true );
	if ( '' === $guardado || ! hash_equals( $guardado, $token ) ) {
		return null;
	}
	return $post;
}

/**
 * Códigos de los criterios publicados (fuente de verdad de «evaluación completa»).
 *
 * @return string[] Ej. ['1.1','1.2',…].
 */
function otrn_codigos_criterios() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$ids   = get_posts( array(
		'post_type'   => 'criterio_autodiag',
		'post_status' => 'publish',
		'numberposts' => -1,
		'fields'      => 'ids',
	) );
	$cache = array();
	foreach ( $ids as $id ) {
		$codigo = (string) get_post_meta( $id, 'codigo', true );
		if ( '' !== $codigo ) {
			$cache[] = $codigo;
		}
	}
	return $cache;
}

/**
 * Códigos de criterios que quedan sin responder.
 *
 * `NA` cuenta como respuesta (es una decisión explícita de quien evalúa); solo
 * falta lo que no tiene ningún valor.
 *
 * @param array<string,mixed> $respuestas [codigo => 0..3 | 'NA'].
 * @return string[] Códigos faltantes.
 */
function otrn_criterios_faltantes( array $respuestas ) {
	$faltan = array();
	foreach ( otrn_codigos_criterios() as $codigo ) {
		if ( ! array_key_exists( $codigo, $respuestas ) || '' === $respuestas[ $codigo ] || null === $respuestas[ $codigo ] ) {
			$faltan[] = $codigo;
		}
	}
	return $faltan;
}

/**
 * Procesa el envío del formulario de autodiagnóstico y crea/actualiza una `evaluacion`.
 *
 * Dos modos según el botón usado (campo `modo`):
 * - `borrador`: guarda en estado `draft` aunque falte el sector o falten respuestas,
 *   y devuelve al formulario con el enlace para retomarlo.
 * - `final`: exige sector válido, publica y redirige a los resultados.
 *
 * Si vienen `borrador_id` + `borrador_token` válidos, se actualiza ese borrador en
 * vez de crear otra evaluación (así retomar y completar no deja duplicados).
 *
 * Seguridad: nonce obligatorio; el sector se valida contra la lista canónica y
 * su coordenada se toma del servidor (no del cliente); las respuestas se validan
 * por formato de código y rango de puntaje. NOTA: en producción esto debería
 * exigir usuario con rol Operador (docs/01 §6); aquí queda abierto (nopriv).
 */
function otrn_handle_eval_submit() {
	if ( ! isset( $_POST['otrn_eval_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['otrn_eval_nonce'] ), 'otrn_eval' ) ) {
		wp_die( 'Token de seguridad inválido.', 'Autodiagnóstico', array( 'response' => 403 ) );
	}

	$es_borrador = isset( $_POST['modo'] ) && 'borrador' === sanitize_key( wp_unslash( $_POST['modo'] ) );

	// Sector: se valida contra la lista canónica; la coordenada la pone el servidor.
	// En borrador puede quedar sin elegir (la evaluación está incompleta por definición).
	$sectores = otrn_sectores_rapanui();
	$sector   = isset( $_POST['sector'] ) ? sanitize_key( wp_unslash( $_POST['sector'] ) ) : '';
	if ( ! isset( $sectores[ $sector ] ) ) {
		if ( ! $es_borrador ) {
			wp_die( 'Selecciona un sector válido.', 'Autodiagnóstico', array( 'response' => 400 ) );
		}
		$sector = '';
	}
	$destino = '' !== $sector ? $sectores[ $sector ]['nombre'] : '';

	// N/A solo se admite al evaluar un sector; para la isla completa se ignora
	// (se responde 0-3). El sector '' (borrador sin sector) también permite N/A,
	// para no perder respuestas de un borrador que aún no fija el sector.
	$permite_na = ( 'isla-completa' !== $sector );

	$respuestas = array();
	$in         = isset( $_POST['resp'] ) && is_array( $_POST['resp'] ) ? wp_unslash( $_POST['resp'] ) : array();
	foreach ( $in as $codigo => $val ) {
		$codigo = sanitize_text_field( $codigo );
		if ( ! preg_match( '/^\d{1,2}\.\d{1,2}$/', $codigo ) ) {
			continue; // formato de código inválido.
		}
		if ( $permite_na && 'NA' === $val ) {
			$respuestas[ $codigo ] = 'NA';
		} elseif ( in_array( (string) $val, array( '0', '1', '2', '3' ), true ) ) {
			$respuestas[ $codigo ] = (int) $val;
		}
		// Sin responder o valor inválido → se omite.
	}

	// Publicar exige la evaluación completa (el botón del formulario ya lo impide;
	// esto cubre envíos con la página desactualizada o fuera del formulario).
	if ( ! $es_borrador ) {
		$faltan = otrn_criterios_faltantes( $respuestas );
		if ( ! empty( $faltan ) ) {
			wp_die(
				sprintf(
					'La autoevaluación está incompleta: faltan %d indicadores por responder (%s…). Vuelve atrás, complétalos o guarda un borrador.',
					count( $faltan ),
					esc_html( implode( ', ', array_slice( $faltan, 0, 5 ) ) )
				),
				'Autodiagnóstico',
				array( 'response' => 400, 'back_link' => true )
			);
		}
	}

	$borrador = otrn_borrador_valido(
		isset( $_POST['borrador_id'] ) ? wp_unslash( $_POST['borrador_id'] ) : 0,
		isset( $_POST['borrador_token'] ) ? sanitize_text_field( wp_unslash( $_POST['borrador_token'] ) ) : ''
	);

	$postarr = array(
		'post_type'   => 'evaluacion',
		'post_status' => $es_borrador ? 'draft' : 'publish',
		'post_title'  => ( '' !== $destino ? $destino : 'Sin sector' ) . ' — ' . ( $es_borrador ? 'borrador ' : '' ) . wp_date( 'd/m/Y' ),
	);

	if ( $borrador ) {
		$postarr['ID'] = $borrador->ID;
		if ( ! $es_borrador ) {
			// Al publicar, la fecha visible es la de término, no la de inicio del borrador.
			$postarr['post_date']     = current_time( 'mysql' );
			$postarr['post_date_gmt'] = current_time( 'mysql', true );
		}
		$post_id = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $post_id ) ) {
		wp_die( esc_html( $post_id->get_error_message() ) );
	}

	update_post_meta( $post_id, 'sector', $sector );
	update_post_meta( $post_id, 'destino', $destino );
	update_post_meta( $post_id, 'respuestas', wp_json_encode( $respuestas ) );
	if ( '' !== $sector ) {
		update_post_meta( $post_id, 'destino_lat', $sectores[ $sector ]['lat'] );
		update_post_meta( $post_id, 'destino_lng', $sectores[ $sector ]['lng'] );
	} else {
		delete_post_meta( $post_id, 'destino_lat' );
		delete_post_meta( $post_id, 'destino_lng' );
	}

	if ( $es_borrador ) {
		wp_safe_redirect( add_query_arg( 'guardado', '1', otrn_borrador_url( $post_id ) ) );
		exit;
	}

	// Publicada: el enlace de borrador deja de ser válido.
	delete_post_meta( $post_id, 'borrador_token' );

	wp_safe_redirect( get_permalink( $post_id ) );
	exit;
}
add_action( 'admin_post_otrn_submit_eval', 'otrn_handle_eval_submit' );
// La sección de autoevaluación es privada: no se aceptan envíos anónimos.
// (Gate en `otrn_seccion_autoevaluacion_privada()`, functions.php del tema.)

/**
 * Archiva (envía a la papelera) una autoevaluación en borrador.
 *
 * Reversible: un administrador puede restaurarla desde wp-admin. Solo usuarios
 * con permisos (no hay `nopriv`): quien evalúa de forma anónima abandona un
 * borrador simplemente no volviendo a su enlace.
 */
function otrn_handle_archivar_eval() {
	$id = isset( $_POST['evaluacion_id'] ) ? (int) $_POST['evaluacion_id'] : 0;

	if ( ! isset( $_POST['otrn_archivar_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['otrn_archivar_nonce'] ), 'otrn_archivar_' . $id ) ) {
		wp_die( 'Token de seguridad inválido.', 'Autodiagnóstico', array( 'response' => 403 ) );
	}

	$post = get_post( $id );
	if ( ! $post || 'evaluacion' !== $post->post_type ) {
		wp_die( 'Autoevaluación no encontrada.', 'Autodiagnóstico', array( 'response' => 404 ) );
	}
	if ( ! current_user_can( 'delete_evaluacion', $id ) ) {
		wp_die( 'No tienes permisos para archivar esta autoevaluación.', 'Autodiagnóstico', array( 'response' => 403 ) );
	}

	wp_trash_post( $id );

	wp_safe_redirect( add_query_arg( 'archivada', '1', get_post_type_archive_link( 'evaluacion' ) ) );
	exit;
}
add_action( 'admin_post_otrn_archivar_eval', 'otrn_handle_archivar_eval' );

/**
 * Registra `docs/acf-json/` como ruta de auto-carga (comparte con el otro mu-plugin,
 * idempotente si ya está añadida).
 */
function otrn_autodiag_acf_json_load_point( $paths ) {
	$dir = ABSPATH . 'docs/acf-json';
	if ( ! in_array( $dir, $paths, true ) ) {
		$paths[] = $dir;
	}
	return $paths;
}
add_filter( 'acf/settings/load_json', 'otrn_autodiag_acf_json_load_point' );

/**
 * Concede al administrador las capacidades de los CPTs de autodiagnóstico.
 */
function otrn_autodiag_grant_admin_caps() {
	$role = get_role( 'administrator' );
	if ( ! $role ) {
		return;
	}
	foreach ( array( 'criterio_autodiagnostico' => 'criterios_autodiagnostico', 'evaluacion' => 'evaluaciones', 'sector' => 'sectores' ) as $sing => $plur ) {
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
 * Se concede en `after_setup_theme` (antes de que el núcleo construya el usuario
 * actual) para que las capacidades estén disponibles en la primera petición; en
 * `admin_init` llegaban tarde (docs/ISSUES.md F29).
 */
add_action( 'after_setup_theme', 'otrn_autodiag_grant_admin_caps' );
