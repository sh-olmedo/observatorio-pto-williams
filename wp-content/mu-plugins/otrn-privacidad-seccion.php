<?php
/**
 * Plugin Name: OTRN — Privacidad de la sección de autoevaluación
 * Description: Aplica el gate de la sección privada (CPTs `evaluacion`, `criterio_autodiag` y `sector`) a las superficies que no cubre el `template_redirect` del tema: rutas REST, búsqueda (REST y de portada), oEmbed y sitemaps. Corrige F11b sin apagar `show_in_rest` (Gutenberg/administración siguen funcionando) y sin tocar la taxonomía compartida `ambito`.
 * Author: Observatorio Turístico Rapa Nui
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tipos de contenido que forman la sección privada de autoevaluación.
 *
 * `ambito` no está en la lista a propósito: es una taxonomía compartida con
 * indicadores y buenas prácticas públicas ([docs/ISSUES.md](../../docs/ISSUES.md) D2).
 *
 * @return string[] Nombres de CPT.
 */
function otrn_privado_tipos() {
	/**
	 * Permite ajustar la composición de la sección privada sin editar este archivo.
	 *
	 * @param string[] $tipos Nombres de CPT.
	 */
	return (array) apply_filters( 'otrn_privado_tipos', array( 'evaluacion', 'criterio_autodiag', 'sector' ) );
}

/**
 * Capacidad primitiva de lectura de un CPT (p. ej. `read_private_evaluaciones`).
 *
 * No se usa `cap->read_post` (`read_evaluacion`): en estos CPT, registrados con
 * `map_meta_cap => true`, esa es una capacidad *meta* que `map_meta_cap()`
 * reenvía a `read_post` y, sin un ID de entrada, resuelve `do_not_allow` para
 * todo el mundo (administrador incluido). La capacidad primitiva con la que
 * WordPress decide la lectura de contenido no público de un tipo es
 * `read_private_{plural}` (`cap->read_private_posts`), que aquí solo posee el
 * rol administrador (`otrn_autodiag_grant_admin_caps()` en
 * `otrn-autodiagnostico.php`).
 *
 * @param string $tipo Nombre del CPT.
 * @return string Capacidad, o cadena vacía si el tipo no está registrado.
 */
function otrn_privado_cap_lectura( $tipo ) {
	$objeto = get_post_type_object( $tipo );
	if ( ! $objeto || empty( $objeto->cap->read_private_posts ) ) {
		return '';
	}
	return (string) $objeto->cap->read_private_posts;
}

/**
 * Tipos privados que el usuario actual no puede leer.
 *
 * @return string[] Nombres de CPT.
 */
function otrn_privado_tipos_ocultos() {
	$ocultos = array();
	foreach ( otrn_privado_tipos() as $tipo ) {
		$cap = otrn_privado_cap_lectura( $tipo );
		if ( '' === $cap || ! current_user_can( $cap ) ) {
			$ocultos[] = $tipo;
		}
	}
	return $ocultos;
}

/**
 * Devuelve el objeto del CPT privado si la ruta REST pertenece a la sección.
 *
 * Coincide con la ruta completa o cualquiera de sus subrutas
 * (`/wp/v2/evaluacion`, `/wp/v2/evaluacion/123`, `/wp/v2/evaluacion/123/revisions`…)
 * usando el namespace y el `rest_base` realmente registrados, sin tocar rutas
 * ajenas.
 *
 * @param WP_REST_Request $request Petición REST.
 * @return WP_Post_Type|null Objeto del CPT privado, o null si la ruta no es de la sección.
 */
function otrn_privado_ruta_rest( WP_REST_Request $request ) {
	$ruta = '/' . trim( (string) $request->get_route(), '/' );
	foreach ( otrn_privado_tipos() as $tipo ) {
		$objeto = get_post_type_object( $tipo );
		if ( ! $objeto || empty( $objeto->show_in_rest ) ) {
			continue;
		}
		$base = ! empty( $objeto->rest_base ) ? $objeto->rest_base : $objeto->name;
		$ns   = ! empty( $objeto->rest_namespace ) ? $objeto->rest_namespace : 'wp/v2';
		$prefijo = '/' . $ns . '/' . $base;
		if ( $ruta === $prefijo || 0 === strpos( $ruta, $prefijo . '/' ) ) {
			return $objeto;
		}
	}
	return null;
}

/**
 * Exige sesión iniciada (y capacidad de lectura) para las rutas REST de la sección privada.
 *
 * Corre en `rest_pre_dispatch`, donde la petición ya está resuelta: cubre rutas
 * y subrutas, peticiones por lotes y `?rest_route=`. No pisa respuestas que
 * otros filtros hayan calculado antes (`$result` distinto de null).
 *
 * Solo se gatea la **lectura** (GET/HEAD): un anónimo recibe 401 y un usuario
 * autenticado sin la capacidad primitiva de lectura del tipo recibe 403. Las
 * escrituras se delegan a los `permission_callback` estándar de WordPress, que
 * ya exigen `create_*`/`edit_*`/`delete_*` (un anónimo tampoco las tiene). Como
 * los sub-requests de `/batch/v1` solo admiten POST/PUT/PATCH/DELETE (enum del
 * esquema), el filtro nunca devuelve `WP_Error` en contexto de lote: así no se
 * dispara el TypeError de WP 7.1 en el que `_wp_connectors_rest_settings_dispatch()`
 * (filtro de `rest_post_dispatch`) exige `WP_REST_Response` mientras el camino de
 * lote pasa un `WP_Error` de `rest_pre_dispatch` sin convertir.
 *
 * @param mixed           $result  Resultado previo del filtro (`null` si no hay).
 * @param WP_REST_Server  $server  Servidor REST.
 * @param WP_REST_Request $request Petición.
 * @return mixed WP_Error de denegación, o el resultado original.
 */
function otrn_privado_rest_pre_dispatch( $result, $server, WP_REST_Request $request ) {
	if ( null !== $result ) {
		return $result; // Otro filtro ya decidió: preservar su respuesta.
	}

	$objeto = otrn_privado_ruta_rest( $request );
	if ( ! $objeto ) {
		return $result;
	}

	$metodo = strtoupper( (string) $request->get_method() );
	if ( ! in_array( $metodo, array( 'GET', 'HEAD' ), true ) ) {
		return $result; // Escrituras: las validan los permission_callback de WordPress.
	}

	if ( ! is_user_logged_in() ) {
		return new WP_Error(
			'rest_cannot_read',
			'La sección de autoevaluación es privada: inicia sesión para consultarla.',
			array( 'status' => 401 )
		);
	}

	$cap = otrn_privado_cap_lectura( $objeto->name );
	if ( '' === $cap || ! current_user_can( $cap ) ) {
		return new WP_Error(
			'rest_forbidden',
			'No tienes permisos para consultar la sección de autoevaluación.',
			array( 'status' => rest_authorization_required_code() )
		);
	}

	return $result;
}
// Prioridad 20: después de los filtros habituales (10). ACF (prioridad 10) devuelve
// `null` sin reenviar `$result`, así que una denegación anterior se perdería; con 20
// nuestra respuesta es la última de la cadena actual y las respuestas no nulas de
// otros filtros siguen preservándose (ver primera guarda de la función).
add_filter( 'rest_pre_dispatch', 'otrn_privado_rest_pre_dispatch', 20, 3 );

/**
 * Quita los tipos privados de la búsqueda REST (`/wp/v2/search`) para quien no
 * puede leerlos; la búsqueda pública de indicadores y buenas prácticas no cambia.
 *
 * El handler REST de búsqueda consulta un array explícito de subtipos, así que
 * `exclude_from_search` no bastaría: hay que limitar los argumentos de WP_Query.
 *
 * @param array           $query_args Argumentos de WP_Query del handler de búsqueda.
 * @param WP_REST_Request $request    Petición.
 * @return array Argumentos ajustados.
 */
function otrn_privado_rest_busqueda( $query_args, $request ) {
	$ocultos = otrn_privado_tipos_ocultos();
	if ( empty( $ocultos ) || empty( $query_args['post_type'] ) ) {
		return $query_args;
	}
	$tipos = array_values( array_diff( (array) $query_args['post_type'], $ocultos ) );
	if ( empty( $tipos ) ) {
		// Nada visible: fuerza un resultado vacío sin caer al post_type por defecto.
		$query_args['post__in'] = array( 0 );
	} else {
		$query_args['post_type'] = $tipos;
	}
	return $query_args;
}
add_filter( 'rest_post_search_query', 'otrn_privado_rest_busqueda', 10, 2 );

/**
 * Excluye los tipos privados de las consultas públicas de búsqueda y de los
 * archivos de la taxonomía compartida `ambito`.
 *
 * La búsqueda de portada (`?s=`) y los listados por ámbito ampliaban la consulta
 * a todos los tipos públicos, incluidos `evaluacion` y `criterio_autodiag`.
 * La taxonomía `ambito` sigue pública y compartida (D2): solo se omite el CPT
 * privado de su archivo cuando el usuario no puede leerlo, y los indicadores y
 * buenas prácticas siguen apareciendo.
 *
 * @param WP_Query $query Consulta.
 */
function otrn_privado_excluir_de_archivos( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$ocultos = otrn_privado_tipos_ocultos();
	if ( empty( $ocultos ) ) {
		return; // Usuario con acceso: nada que ocultar.
	}

	if ( $query->is_search() ) {
		$tipos = $query->get( 'post_type' );
		if ( empty( $tipos ) || 'any' === $tipos ) {
			$tipos = get_post_types( array( 'exclude_from_search' => false ) );
		}
		$query->set( 'post_type', array_values( array_diff( (array) $tipos, $ocultos ) ) );
		return;
	}

	if ( $query->is_tax( 'ambito' ) ) {
		$tax   = get_taxonomy( 'ambito' );
		$tipos = $tax ? (array) $tax->object_type : array();
		// Réplica de la expansión por defecto del archivo de taxonomía…
		$tipos = array_intersect( $tipos, get_post_types( array( 'exclude_from_search' => false ) ) );
		// …menos los tipos privados que el usuario no puede leer.
		$query->set( 'post_type', array_values( array_diff( $tipos, $ocultos ) ) );
	}
}
add_action( 'pre_get_posts', 'otrn_privado_excluir_de_archivos' );

/**
 * Bloquea el oEmbed de la sección privada.
 *
 * La ruta pública `/wp-json/oembed/1.0/embed` no pasa por el gate HTML y
 * exponía título y autor. Devolver `false` hace que `get_oembed_response_data()`
 * la trate como no encontrada (404) sin revelar datos.
 *
 * @param array   $data   Datos del oEmbed.
 * @param WP_Post $post   Post consultado.
 * @param int     $width  Ancho.
 * @param int     $height Alto.
 * @return array|false
 */
function otrn_privado_oembed( $data, $post, $width, $height ) {
	if ( $post instanceof WP_Post && in_array( $post->post_type, otrn_privado_tipos(), true ) ) {
		return false;
	}
	return $data;
}
add_filter( 'oembed_response_data', 'otrn_privado_oembed', 20, 4 );

/**
 * Excluye los tipos privados del sitemap de WordPress.
 *
 * @param WP_Post_Type[] $post_types Tipos indexados por nombre.
 * @return WP_Post_Type[] Tipos ajustados.
 */
function otrn_privado_sitemap( $post_types ) {
	foreach ( otrn_privado_tipos() as $tipo ) {
		unset( $post_types[ $tipo ] );
	}
	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'otrn_privado_sitemap' );
