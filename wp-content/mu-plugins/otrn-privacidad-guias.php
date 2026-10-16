<?php
/**
 * Plugin Name: OTRN — Privacidad de la guía de usuario
 * Description: El contenido de la guía de usuario (`/guias/`) es para usuarios con sesión iniciada. Este archivo cierra las superficies públicas que no cubre el gate HTML del tema (`otrn_guia_usuario_privada()` en `functions.php`): rutas REST, búsqueda REST y de portada, oEmbed, sitemap y feeds. Repite el patrón de `otrn-privacidad-seccion.php`, pero el requisito aquí es *sesión iniciada*, no una capacidad: la guía es para todo usuario autenticado, sin roles. No toca la sección privada de autoevaluación ni apaga `show_in_rest` (Gutenberg y la administración siguen funcionando).
 * Author: Observatorio Turístico Rapa Nui
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tipos de contenido cuya lectura exige sesión iniciada.
 *
 * Solo `guia` por ahora; se expone como filtro para poder añadir otro tipo sin
 * editar este archivo (mismo patrón que `otrn_privado_tipos()`).
 *
 * @return string[] Nombres de CPT.
 */
function otrn_guia_tipos_sesion() {
	/**
	 * Permite ajustar los tipos que exigen sesión iniciada sin editar este archivo.
	 *
	 * @param string[] $tipos Nombres de CPT.
	 */
	return (array) apply_filters( 'otrn_guia_tipos_sesion', array( 'guia' ) );
}

/**
 * Devuelve el objeto del CPT si la ruta REST pertenece a un tipo con sesión.
 *
 * Coincide con la ruta completa o cualquiera de sus subrutas
 * (`/wp/v2/guia`, `/wp/v2/guia/409`, `/wp/v2/guia/409/revisions`…) usando el
 * namespace y el `rest_base` realmente registrados, sin tocar rutas ajenas.
 *
 * @param WP_REST_Request $request Petición REST.
 * @return WP_Post_Type|null Objeto del CPT, o null si la ruta no es de la guía.
 */
function otrn_guia_ruta_rest( WP_REST_Request $request ) {
	$ruta = '/' . trim( (string) $request->get_route(), '/' );
	foreach ( otrn_guia_tipos_sesion() as $tipo ) {
		$objeto = get_post_type_object( $tipo );
		if ( ! $objeto || empty( $objeto->show_in_rest ) ) {
			continue;
		}
		$base    = ! empty( $objeto->rest_base ) ? $objeto->rest_base : $objeto->name;
		$ns      = ! empty( $objeto->rest_namespace ) ? $objeto->rest_namespace : 'wp/v2';
		$prefijo = '/' . $ns . '/' . $base;
		if ( $ruta === $prefijo || 0 === strpos( $ruta, $prefijo . '/' ) ) {
			return $objeto;
		}
	}
	return null;
}

/**
 * Exige sesión iniciada para la lectura REST de la guía.
 *
 * Corre en `rest_pre_dispatch` (prioridad 20, después de los filtros
 * habituales), donde la petición ya está resuelta: cubre rutas y subrutas,
 * peticiones por lotes y `?rest_route=`. Solo se gatea la **lectura**
 * (GET/HEAD): un anónimo recibe 401 y un usuario con sesión pasa (la guía no
 * exige ninguna capacidad). Las escrituras siguen delegadas a los
 * `permission_callback` de WordPress, que ya exigen `edit_*`/`create_*`.
 *
 * Igual que en `otrn-privacidad-seccion.php`, nunca se devuelve `WP_Error` en
 * contexto de lote: `/batch/v1` solo admite POST/PUT/PATCH/DELETE, así que el
 * camino de lote no entra en este bloque.
 *
 * @param mixed           $result  Resultado previo del filtro (`null` si no hay).
 * @param WP_REST_Server  $server  Servidor REST.
 * @param WP_REST_Request $request Petición.
 * @return mixed WP_Error de denegación, o el resultado original.
 */
function otrn_guia_rest_pre_dispatch( $result, $server, WP_REST_Request $request ) {
	if ( null !== $result ) {
		return $result; // Otro filtro ya decidió: preservar su respuesta.
	}

	if ( ! otrn_guia_ruta_rest( $request ) ) {
		return $result;
	}

	$metodo = strtoupper( (string) $request->get_method() );
	if ( ! in_array( $metodo, array( 'GET', 'HEAD' ), true ) ) {
		return $result; // Escrituras: las validan los permission_callback de WordPress.
	}

	if ( ! is_user_logged_in() ) {
		return new WP_Error(
			'rest_cannot_read',
			'La guía de usuario es para usuarios con sesión iniciada: inicia sesión para consultarla.',
			array( 'status' => 401 )
		);
	}

	return $result;
}
add_filter( 'rest_pre_dispatch', 'otrn_guia_rest_pre_dispatch', 20, 3 );

/**
 * Quita la guía de la búsqueda REST (`/wp/v2/search`) para los anónimos.
 *
 * El handler de búsqueda consulta un array explícito de subtipos, así que
 * `exclude_from_search` no bastaría: hay que limitar los argumentos de
 * WP_Query. Con sesión iniciada la búsqueda pública no cambia.
 *
 * @param array           $query_args Argumentos de WP_Query del handler de búsqueda.
 * @param WP_REST_Request $request    Petición.
 * @return array Argumentos ajustados.
 */
function otrn_guia_rest_busqueda( $query_args, $request ) {
	if ( is_user_logged_in() || empty( $query_args['post_type'] ) ) {
		return $query_args;
	}
	$tipos = array_values( array_diff( (array) $query_args['post_type'], otrn_guia_tipos_sesion() ) );
	if ( empty( $tipos ) ) {
		$query_args['post__in'] = array( 0 ); // Nada visible: resultado vacío, sin caer al tipo por defecto.
	} else {
		$query_args['post_type'] = $tipos;
	}
	return $query_args;
}
add_filter( 'rest_post_search_query', 'otrn_guia_rest_busqueda', 10, 2 );

/**
 * Excluye la guía de las búsquedas públicas del sitio (`?s=…`) para los anónimos.
 *
 * Cualquier consulta con `post_type` explícito o ampliado a todos los tipos
 * públicos (`any`) dejaría entrar el CPT `guia`, que es público.
 *
 * @param WP_Query $query Consulta.
 */
function otrn_guia_excluir_de_busqueda( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}
	if ( is_user_logged_in() ) {
		return; // Con sesión: la guía puede aparecer.
	}
	$tipos = $query->get( 'post_type' );
	if ( empty( $tipos ) || 'any' === $tipos ) {
		$tipos = get_post_types( array( 'exclude_from_search' => false ) );
	}
	$query->set( 'post_type', array_values( array_diff( (array) $tipos, otrn_guia_tipos_sesion() ) ) );
}
add_action( 'pre_get_posts', 'otrn_guia_excluir_de_busqueda' );

/**
 * Bloquea el oEmbed de la guía para los anónimos.
 *
 * La ruta pública `/wp-json/oembed/1.0/embed` no pasa por el gate HTML y
 * devolvía título, autor y un bloque `<blockquote>` con el enlace al contenido.
 * Devolver `false` hace que `get_oembed_response_data()` la trate como no
 * encontrada (404) sin revelar datos.
 *
 * @param array   $data   Datos del oEmbed.
 * @param WP_Post $post   Post consultado.
 * @param int     $width  Ancho.
 * @param int     $height Alto.
 * @return array|false
 */
function otrn_guia_oembed( $data, $post, $width, $height ) {
	if ( is_user_logged_in() ) {
		return $data;
	}
	if ( $post instanceof WP_Post && in_array( $post->post_type, otrn_guia_tipos_sesion(), true ) ) {
		return false;
	}
	return $data;
}
add_filter( 'oembed_response_data', 'otrn_guia_oembed', 20, 4 );

/**
 * Excluye la guía del sitemap.
 *
 * El sitemap es una superficie de rastreo: se excluye siempre (no solo para
 * anónimos), igual que la sección privada de autoevaluación
 * (`otrn-privado_sitemap()`). Una URL que solo responde con sesión iniciada no
 * debe anunciarse a los buscadores.
 *
 * @param WP_Post_Type[] $post_types Tipos indexados por nombre.
 * @return WP_Post_Type[] Tipos ajustados.
 */
function otrn_guia_sitemap( $post_types ) {
	foreach ( otrn_guia_tipos_sesion() as $tipo ) {
		unset( $post_types[ $tipo ] );
	}
	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'otrn_guia_sitemap' );
