<?php
/**
 * Tema hijo del Observatorio — funciones de Seguimiento Pladetur.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

/** Rango de años del plan (docs/04 §3). */
function otrn_pladetur_anios() {
	return range( 2024, 2034 );
}

/**
 * Lee una iniciativa a un array plano y tipado.
 *
 * @param int $post_id ID del post `iniciativa_pladetur`.
 * @return array<string,mixed>
 */
function otrn_pladetur_iniciativa( $post_id ) {
	$avance_raw = get_post_meta( $post_id, 'porcentaje_avance', true );
	$asignado   = array();
	foreach ( otrn_pladetur_anios() as $a ) {
		$v = get_post_meta( $post_id, 'asignado_' . $a, true );
		$asignado[ $a ] = ( '' === $v || null === $v ) ? null : (float) $v;
	}
	$terms = get_the_terms( $post_id, 'lineamiento' );
	$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;

	return array(
		'id'                 => $post_id,
		'titulo'             => get_the_title( $post_id ),
		'codigo'             => get_post_meta( $post_id, 'codigo_iniciativa', true ),
		'descripcion'        => get_post_meta( $post_id, 'descripcion', true ),
		'lineamiento'        => $term ? $term->name : '',
		'lineamiento_slug'   => $term ? $term->slug : '',
		'anio_implementacion'=> get_post_meta( $post_id, 'anio_implementacion', true ),
		'periodo_inversion'  => get_post_meta( $post_id, 'periodo_inversion', true ),
		'monto_total'        => (float) get_post_meta( $post_id, 'monto_total', true ),
		'prioridad'          => (int) get_post_meta( $post_id, 'prioridad', true ),
		'porcentaje_avance'  => ( '' === $avance_raw || null === $avance_raw ) ? null : (float) $avance_raw,
		'organismos'         => get_post_meta( $post_id, 'organismos_responsables', true ),
		'otros_organismos'   => get_post_meta( $post_id, 'otros_organismos_vinculados', true ),
		'fuente'             => get_post_meta( $post_id, 'fuente_financiamiento', true ),
		'asignado'           => $asignado,
		'permalink'          => get_permalink( $post_id ),
	);
}

/**
 * Agrega estadísticas sobre un conjunto de iniciativas (docs/04 §4).
 *
 * Función pura: no consulta la BD, opera sobre las filas que recibe. Se usa
 * igual en el dashboard y en cualquier vista agregada — no duplicar la fórmula.
 *
 * @param array<int,array> $filas Salida de otrn_pladetur_iniciativa().
 * @return array<string,mixed>
 */
function otrn_pladetur_agregados( array $filas ) {
	$por_lineamiento = array();
	$por_anio        = array_fill_keys( otrn_pladetur_anios(), 0.0 );
	$por_prioridad   = array( 0 => 0, 1 => 0 );
	$monto_total     = 0.0;

	foreach ( $filas as $f ) {
		$monto_total += $f['monto_total'];

		$slug = $f['lineamiento_slug'] ?: '_sin';
		if ( ! isset( $por_lineamiento[ $slug ] ) ) {
			$por_lineamiento[ $slug ] = array(
				'nombre' => $f['lineamiento'] ?: 'Sin lineamiento',
				'count'  => 0,
				'monto'  => 0.0,
			);
		}
		$por_lineamiento[ $slug ]['count']++;
		$por_lineamiento[ $slug ]['monto'] += $f['monto_total'];

		foreach ( $f['asignado'] as $anio => $val ) {
			if ( null !== $val ) {
				$por_anio[ $anio ] += $val;
			}
		}

		$por_prioridad[ $f['prioridad'] === 1 ? 1 : 0 ]++;
	}

	// Ordena lineamientos por monto desc para presentación estable.
	uasort(
		$por_lineamiento,
		static function ( $a, $b ) {
			return $b['monto'] <=> $a['monto'];
		}
	);

	return array(
		'total'           => count( $filas ),
		'monto_total'     => $monto_total,
		'por_lineamiento' => $por_lineamiento,
		'por_anio'        => $por_anio,
		'por_prioridad'   => $por_prioridad,
	);
}

/**
 * Formatea un monto en $MM con separador de miles, sin decimales innecesarios.
 */
function otrn_mm( $valor ) {
	$valor = (float) $valor;
	$dec   = ( fmod( $valor, 1.0 ) === 0.0 ) ? 0 : 1;
	return number_format( $valor, $dec, ',', '.' );
}

/**
 * Formatea `periodo_inversion` ("2024-2033", docs/04 §3) como "2024/2033"
 * para mostrar la duración (año de inicio / año de término) en tablas.
 */
function otrn_pladetur_duracion( $periodo ) {
	$periodo = trim( (string) $periodo );
	if ( '' === $periodo ) {
		return '—';
	}
	return str_replace( '-', '/', $periodo );
}

/**
 * Devuelve la clase de tramo (tier) según un % (docs/01 §5.1).
 */
function otrn_tier( $pct ) {
	if ( null === $pct ) {
		return 'na';
	}
	if ( $pct >= 67 ) {
		return 'high';
	}
	if ( $pct >= 34 ) {
		return 'mid';
	}
	return 'low';
}

/** Etiqueta legible del tramo. */
function otrn_tier_label( $tier ) {
	return array( 'high' => 'Bueno', 'mid' => 'Medio', 'low' => 'Bajo', 'na' => 'Sin datos' )[ $tier ] ?? '';
}

/**
 * Carga los 74 criterios agrupados por ámbito → criterio, con sus campos.
 *
 * @return array<string,array> ambito_nombre => [ 'criterios' => [nombre => [indicadores...]] ]
 */
function otrn_cargar_criterios() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$ids = get_posts( array(
		'post_type'   => 'criterio_autodiag',
		'post_status' => 'publish',
		'numberposts' => -1,
		'fields'      => 'ids',
	) );

	// `codigo` es "criterio.indicador" (1.1, 10.4, 43.2). Ordenar por `meta_value`
	// lo compara como texto y deja 10.1 entre 1.2 y 2.1; `meta_value_num` depende
	// del casting del motor, y este sitio corre SQLite en local y MySQL en
	// producción. Se ordena en PHP para que la secuencia sea idéntica en ambos.
	$orden = array();
	foreach ( $ids as $id ) {
		$partes       = explode( '.', (string) get_post_meta( $id, 'codigo', true ) );
		$orden[ $id ] = array( (int) ( $partes[0] ?? 0 ), (int) ( $partes[1] ?? 0 ) );
	}
	usort( $ids, static function ( $a, $b ) use ( $orden ) {
		return $orden[ $a ] <=> $orden[ $b ];
	} );

	$out = array();
	foreach ( $ids as $id ) {
		$terms  = get_the_terms( $id, 'ambito' );
		$ambito = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'Sin ámbito';
		$crit   = get_post_meta( $id, 'criterio', true );
		$out[ $ambito ]['criterios'][ $crit ][] = array(
			'codigo'    => get_post_meta( $id, 'codigo', true ),
			'indicador' => get_post_meta( $id, 'indicador', true ),
			'pregunta'  => get_post_meta( $id, 'pregunta_criterio', true ),
			'escala'    => array(
				0 => get_post_meta( $id, 'escala_0', true ),
				1 => get_post_meta( $id, 'escala_1', true ),
				2 => get_post_meta( $id, 'escala_2', true ),
				3 => get_post_meta( $id, 'escala_3', true ),
			),
			'na'        => get_post_meta( $id, 'na', true ),
		);
	}
	$cache = $out;
	return $out;
}

/** Mapa codigo → nombre de ámbito, para la función de cálculo. */
function otrn_mapa_codigo_ambito() {
	$map = array();
	foreach ( otrn_cargar_criterios() as $ambito => $data ) {
		foreach ( $data['criterios'] as $indicadores ) {
			foreach ( $indicadores as $ind ) {
				$map[ $ind['codigo'] ] = $ambito;
			}
		}
	}
	return $map;
}

/** Lee las respuestas guardadas de una evaluación (meta JSON `respuestas`). */
function otrn_evaluacion_respuestas( $post_id ) {
	$raw = get_post_meta( $post_id, 'respuestas', true );
	$dec = is_string( $raw ) ? json_decode( $raw, true ) : $raw;
	return is_array( $dec ) ? $dec : array();
}

/**
 * `/mapa/` se fusionó con el listado de autoevaluaciones: mapa arriba, tabla abajo.
 *
 * Se mantiene la redirección (y la plantilla `page-mapa.php`) para no romper
 * enlaces antiguos; quitar este hook devuelve la página del mapa por separado.
 */
function otrn_redirigir_pagina_mapa() {
	if ( is_page( 'mapa' ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'evaluacion' ) . '#mapa', 302 );
		exit;
	}
}
add_action( 'template_redirect', 'otrn_redirigir_pagina_mapa' );

/**
 * La sección de autoevaluación es privada: exige sesión iniciada.
 *
 * Cubre la sección completa — listado `/autoevaluacion/` (con su mapa), ficha
 * de cada evaluación, formulario `/evaluar/`, `/mapa/` suelta y las fichas de
 * criterio `/criterio/`. Al público se le manda al login y se le devuelve a la
 * misma URL tras entrar. El enlace de nav también se oculta (`header.php`) y el
 * envío del formulario deja de aceptar peticiones anónimas
 * (`otrn-autodiagnostico.php`), para que la sección no sea alcanzable de lado.
 */
function otrn_seccion_autoevaluacion_privada() {
	if ( is_user_logged_in() ) {
		return;
	}
	$privada = is_post_type_archive( 'evaluacion' )
		|| is_singular( 'evaluacion' )
		|| is_singular( 'criterio_autodiag' )
		|| is_page( array( 'evaluar', 'mapa' ) );
	if ( ! $privada ) {
		return;
	}
	$destino = home_url( add_query_arg( array() ) );
	wp_safe_redirect( wp_login_url( $destino ), 302 );
	exit;
}
add_action( 'template_redirect', 'otrn_seccion_autoevaluacion_privada', 1 );

/**
 * Una evaluación en borrador no tiene vista de resultados: lleva a continuarla.
 *
 * Solo la alcanzan usuarios con permisos (WordPress ya devuelve 404 a los demás
 * en un `draft`); el borrador se retoma con su enlace + token (docs/01 §5.2).
 */
function otrn_redirigir_borrador_evaluacion() {
	if ( ! is_singular( 'evaluacion' ) ) {
		return;
	}
	$id = get_queried_object_id();
	if ( 'draft' === get_post_status( $id ) && function_exists( 'otrn_borrador_url' ) ) {
		wp_safe_redirect( otrn_borrador_url( $id ) );
		exit;
	}
}
add_action( 'template_redirect', 'otrn_redirigir_borrador_evaluacion' );


/**
 * Soporte del tema: imagen destacada y recortes de Noticias (docs/05 Parte B).
 *
 * El soporte de `post-thumbnails` lo agrega WordPress por ser tema de bloques,
 * pero se declara explícito para que no dependa del tema padre: sin él, el
 * panel «Imagen destacada» desaparece del editor de noticias.
 */
function otrn_setup_theme() {
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'otrn-noticia', 1120, 560, true );      // Cabecera del detalle.
	add_image_size( 'otrn-noticia-card', 640, 400, true );  // Tarjeta del listado.
}
add_action( 'after_setup_theme', 'otrn_setup_theme' );

/**
 * URL del listado de Noticias.
 *
 * La sección vive en la página `/noticias/` (`page-noticias.php`) porque `/`
 * ya la ocupa la portada del Observatorio. Si esa página no existe todavía se
 * cae al índice de entradas para no dejar enlaces rotos.
 */
function otrn_noticias_url() {
	$pagina = get_page_by_path( 'noticias' );
	if ( $pagina && 'publish' === $pagina->post_status ) {
		return get_permalink( $pagina );
	}

	return home_url( '/?post_type=post' );
}

/** URL de la página de Estadísticas Pladetur (docs/04 §4), creada por scripts/crear-pagina-estadisticas.php. */
function otrn_estadisticas_url() {
	$pagina = get_page_by_path( 'estadisticas' );
	if ( $pagina && 'publish' === $pagina->post_status ) {
		return get_permalink( $pagina );
	}

	return home_url( '/estadisticas/' );
}

/**
 * Fuente de la información de una noticia (docs/05 Parte B §2).
 *
 * El campo acepta URL o texto: devuelve `url` solo cuando es enlazable, para
 * que la vista elija entre botón y línea de crédito.
 *
 * @param int $post_id Noticia.
 * @return array{url:string,texto:string}|null Null si no se declaró fuente.
 */
function otrn_noticia_fuente( $post_id ) {
	$valor = trim( (string) get_post_meta( $post_id, 'fuente_de_la_informacion', true ) );
	if ( '' === $valor ) {
		return null;
	}

	return array(
		'url'   => filter_var( $valor, FILTER_VALIDATE_URL ) ? $valor : '',
		'texto' => $valor,
	);
}

/**
 * Encola estilos y tipografías del tema.
 */
function otrn_enqueue_assets() {
	// Tipografías autoalojadas (assets/css/fonts.css) en vez de
	// fonts.googleapis.com: sin dependencia externa ni render bloqueante.
	// La versión = mtime del archivo, así cada cambio rompe la caché solo.
	$otrn_fonts = get_stylesheet_directory() . '/assets/css/fonts.css';
	$otrn_fonts_ver = file_exists( $otrn_fonts ) ? (string) filemtime( $otrn_fonts ) : '0.1.0';
	wp_enqueue_style(
		'otrn-fonts',
		get_stylesheet_directory_uri() . '/assets/css/fonts.css',
		array(),
		$otrn_fonts_ver
	);
	// Versión = fecha del archivo: cada cambio de CSS rompe la caché por sí solo.
	// Con una versión fija, el `Expires` de 1 mes de los estáticos dejaba a los
	// visitantes con la hoja antigua (ver docs/06 §8).
	$otrn_css = get_stylesheet_directory() . '/style.css';
	$otrn_ver = file_exists( $otrn_css ) ? (string) filemtime( $otrn_css ) : '0.2.0';
	wp_enqueue_style( 'otrn-style', get_stylesheet_uri(), array(), $otrn_ver );

	// Leaflet + OpenStreetMap solo donde se dibuja el mapa (docs/01 §5.1):
	// la vista combinada de autoevaluaciones, y la página `/mapa/` si se usa suelta.
	if ( is_post_type_archive( 'evaluacion' ) || is_page( 'mapa' ) ) {
		wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4' );
		wp_enqueue_script( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true );
	}

	// Drawer de la ficha, solo en el detalle de indicador (docs/02 §5).
	if ( is_singular( 'indicador' ) ) {
		// SheetJS vendoreado (assets/vendor/), solo cuando el indicador trae
		// un documento Excel que renderizar como tabla en el visor.
		$indicador_deps = array();
		if ( 'Excel' === get_post_meta( get_the_ID(), 'fuente_datos', true ) ) {
			wp_enqueue_script(
				'otrn-xlsx',
				get_stylesheet_directory_uri() . '/assets/vendor/xlsx.full.min.js',
				array(),
				'0.20.3',
				true
			);
			$indicador_deps[] = 'otrn-xlsx';
		}
		wp_enqueue_script(
			'otrn-indicador',
			get_stylesheet_directory_uri() . '/assets/indicador.js',
			$indicador_deps,
			'0.2.0',
			true
		);
	}

	// Menú accesible (hamburguesa + captura de foco). La cabecera la pinta
	// siempre, así que el script va en todas las vistas.
	$otrn_menu = get_stylesheet_directory() . '/assets/otrn-menu.js';
	$otrn_menu_ver = file_exists( $otrn_menu ) ? (string) filemtime( $otrn_menu ) : '0.1.0';
	wp_enqueue_script(
		'otrn-menu',
		get_stylesheet_directory_uri() . '/assets/otrn-menu.js',
		array(),
		$otrn_menu_ver,
		true
	);

	// Portada inmersiva: GSAP + ScrollTrigger + estilos/scripts locales.
	// Solo en la portada para no cargar ~115 KB de animación en las páginas
	// internas, donde no hay scrollytelling.
	if ( is_front_page() ) {
		$otrn_arauo = get_stylesheet_directory() . '/assets/css/otrn-arauco.css';
		$otrn_land_css = get_stylesheet_directory() . '/assets/css/otrn-landing.css';
		$otrn_arauo_ver = file_exists( $otrn_arauo ) ? (string) filemtime( $otrn_arauo ) : '0.1.0';
		$otrn_land_css_ver = file_exists( $otrn_land_css ) ? (string) filemtime( $otrn_land_css ) : '0.1.0';
		wp_enqueue_style(
			'otrn-arauco',
			get_stylesheet_directory_uri() . '/assets/css/otrn-arauco.css',
			array( 'otrn-style' ),
			$otrn_arauo_ver
		);
		wp_enqueue_style(
			'otrn-landing',
			get_stylesheet_directory_uri() . '/assets/css/otrn-landing.css',
			array( 'otrn-arauco' ),
			$otrn_land_css_ver
		);

		// GSAP y ScrollTrigger vendoreados en assets/vendor/ (licencia permite
		// autoalojamiento; ver docs del proyecto).
		$otrn_gsap = get_stylesheet_directory() . '/assets/vendor/gsap.min.js';
		$otrn_st   = get_stylesheet_directory() . '/assets/vendor/ScrollTrigger.min.js';
		$otrn_land = get_stylesheet_directory() . '/assets/otrn-landing.js';
		$otrn_gsap_ver = file_exists( $otrn_gsap ) ? (string) filemtime( $otrn_gsap ) : '3.12.5';
		$otrn_st_ver   = file_exists( $otrn_st ) ? (string) filemtime( $otrn_st ) : '3.12.5';
		$otrn_land_ver = file_exists( $otrn_land ) ? (string) filemtime( $otrn_land ) : '0.1.0';
		wp_enqueue_script(
			'gsap',
			get_stylesheet_directory_uri() . '/assets/vendor/gsap.min.js',
			array(),
			$otrn_gsap_ver,
			true
		);
		wp_enqueue_script(
			'otrn-scrolltrigger',
			get_stylesheet_directory_uri() . '/assets/vendor/ScrollTrigger.min.js',
			array( 'gsap' ),
			$otrn_st_ver,
			true
		);
		wp_enqueue_script(
			'otrn-landing',
			get_stylesheet_directory_uri() . '/assets/otrn-landing.js',
			array( 'gsap', 'otrn-scrolltrigger' ),
			$otrn_land_ver,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'otrn_enqueue_assets' );

/**
 * Imagen del hero de la portada.
 *
 * Prioriza lo que el editor cargue desde wp-admin (imagen destacada de la
 * página de portada, o cabecera personalizada) y solo cae al asset del tema
 * cuando no hay ninguna: la foto es contenido editorial, no diseño fijo.
 *
 * @return string URL de la imagen.
 */
function otrn_hero_portada_url() {
	$portada = (int) get_option( 'page_on_front' );

	if ( $portada && has_post_thumbnail( $portada ) ) {
		$url = get_the_post_thumbnail_url( $portada, 'full' );
		if ( $url ) {
			return $url;
		}
	}

	$cabecera = get_header_image();
	if ( $cabecera ) {
		return $cabecera;
	}

	return get_theme_file_uri( 'assets/hero-rapanui.jpg' );
}

/**
 * Página que aporta el texto de la sección «Misión» de la portada.
 *
 * El texto lo edita el cliente como una página normal (`/mision/`), no vive en
 * el código. Si la página no existe o está en borrador, la portada omite la
 * sección completa en vez de mostrar un bloque vacío.
 *
 * @return WP_Post|null
 */
function otrn_mision_portada() {
	$pagina = get_page_by_path( 'mision' );

	return ( $pagina && 'publish' === $pagina->post_status ) ? $pagina : null;
}

/**
 * Cifras destacadas de la portada, en el orden definido en wp-admin.
 *
 * @param int $limite Máximo de datos a devolver.
 * @return WP_Post[]
 */
function otrn_datos_portada( $limite = 3 ) {
	return get_posts(
		array(
			'post_type'      => 'dato_portada',
			'post_status'    => 'publish',
			'numberposts'    => $limite,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
		)
	);
}

/**
 * Clase de color para un término de una clasificación de 4 valores.
 *
 * docs/design-tokens.md §«Asignación de color por categoría»: las taxonomías
 * de 4 valores (ámbitos, categorías de indicador) usan la secuencia fija
 * primary → secondary → blue-dark → dark. La posición sale del orden de
 * creación de los términos, así que un término nuevo no descoloca a los demás.
 *
 * @param WP_Term|null $term Término a colorear.
 * @return string Clase `seq-1`…`seq-4`.
 */
function otrn_clase_secuencia( $term ) {
	if ( ! $term instanceof WP_Term ) {
		return 'seq-4';
	}

	$ids = get_terms(
		array(
			'taxonomy'   => $term->taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
			'orderby'    => 'term_id',
			'order'      => 'ASC',
		)
	);
	if ( is_wp_error( $ids ) ) {
		return 'seq-4';
	}

	$pos = array_search( $term->term_id, $ids, true );

	return 'seq-' . ( false === $pos ? 4 : ( $pos % 4 ) + 1 );
}

/**
 * Primer término de una taxonomía para un post, o null.
 *
 * @param int|WP_Post $post     Post.
 * @param string      $taxonomy Taxonomía.
 * @return WP_Term|null
 */
function otrn_primer_termino( $post, $taxonomy ) {
	$terms = get_the_terms( $post, $taxonomy );

	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

/**
 * Las dos secciones en que se divide Material de apoyo (docs/05 Parte A §2).
 *
 * Devuelve, por slug de término de `tipo_material`, el título y la bajada que
 * usan la cabecera de página, la subnavegación y las fichas de la portada. El
 * enlace sale de la taxonomía: si el término aún no existe queda fuera de la
 * lista en vez de generar una URL rota.
 *
 * @return array<string,array<string,string>> [slug => [titulo, nav, intro, url]].
 */
function otrn_material_secciones() {
	$secciones = array(
		'documentacion-tecnica' => array(
			'titulo' => 'Documentación técnica',
			'nav'    => 'Documentación',
			'intro'  => 'Guías, manuales y metodologías de apoyo para operadores y gestores turísticos.',
		),
		'reportes'              => array(
			'titulo' => 'Reportes',
			'nav'    => 'Reportes',
			'intro'  => 'Boletines, antecedentes estadísticos y resultados publicados por el Observatorio.',
		),
	);

	foreach ( $secciones as $slug => $datos ) {
		$term = get_term_by( 'slug', $slug, 'tipo_material' );
		if ( ! $term || is_wp_error( $term ) ) {
			unset( $secciones[ $slug ] );
			continue;
		}
		$enlace = get_term_link( $term );
		if ( is_wp_error( $enlace ) ) {
			unset( $secciones[ $slug ] );
			continue;
		}
		$secciones[ $slug ]['url'] = $enlace;
	}

	return $secciones;
}

/**
 * Documento descargable de un material de apoyo (docs/05 Parte A §3).
 *
 * El campo `documento` guarda el ID del adjunto cuando se sube desde ACF, pero
 * los materiales cargados por script pueden traer directamente una URL: se
 * aceptan ambos. Es independiente de `enlace_externo` — un material puede
 * ofrecer archivo, sitio externo o las dos cosas.
 *
 * @param int $post_id Material de apoyo.
 * @return array{url:string,ext:string}|null Null si no hay archivo.
 */
function otrn_material_documento( $post_id ) {
	$doc = get_post_meta( $post_id, 'documento', true );
	if ( ! $doc ) {
		return null;
	}

	$url = is_numeric( $doc ) ? wp_get_attachment_url( (int) $doc ) : $doc;
	if ( ! $url ) {
		return null;
	}

	$ext = strtoupper( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );

	return array( 'url' => $url, 'ext' => $ext );
}

/**
 * Favicon del tema, solo si en Ajustes no se ha definido un icono del sitio.
 */
function otrn_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	printf(
		'<link rel="icon" href="%s" sizes="512x512">' . "\n",
		esc_url( get_theme_file_uri( 'assets/favicon.png' ) )
	);
}
add_action( 'wp_head', 'otrn_favicon' );

/** Prácticas publicadas indexadas por el indicador que ayudan a mejorar. */
function otrn_practicas_por_codigo() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$out = array();
	foreach ( get_posts( array(
		'post_type'   => 'buena_practica',
		'post_status' => 'publish',
		'numberposts' => -1,
	) ) as $p ) {
		$codigo = trim( (string) get_post_meta( $p->ID, 'criterio_relacionado', true ) );
		if ( '' !== $codigo ) {
			$out[ $codigo ][] = (int) $p->ID;
		}
	}
	$cache = $out;
	return $out;
}

/** Mapa codigo → enunciado del indicador, para explicar por qué se sugiere. */
function otrn_indicador_por_codigo() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$out = array();
	foreach ( otrn_cargar_criterios() as $data ) {
		foreach ( $data['criterios'] as $indicadores ) {
			foreach ( $indicadores as $ind ) {
				$out[ $ind['codigo'] ] = $ind['indicador'];
			}
		}
	}
	$cache = $out;
	return $out;
}

/**
 * Buenas prácticas sugeridas para una autoevaluación (docs/01 §5.5).
 *
 * Se eligen por **indicador**: se ordenan las respuestas de peor a mejor y se
 * busca la práctica vinculada a ese indicador exacto (`criterio_relacionado`),
 * para que la recomendación responda a la pregunta que la persona reprobó y no
 * solo al ámbito. Si un indicador con brecha no tiene práctica vinculada, se
 * cae al ámbito completo, que es el comportamiento anterior.
 *
 * Se rota entre ámbitos para que las 3 sugerencias cubran brechas distintas en
 * vez de amontonarse en el peor evaluado. Los ámbitos sin brecha (100%) y los
 * que no se respondieron quedan fuera: si no hay suficientes prácticas
 * pertinentes se devuelven menos de `$limite`, nunca relleno de un ámbito que
 * ya está resuelto.
 *
 * @param array<string,mixed> $respuestas [codigo => 0..3 | 'NA'].
 * @param int                 $limite     Máximo de sugerencias (3 por defecto).
 * @return array<int,array{id:int,ambito:string,pct:float,codigo:string,indicador:string}>
 */
function otrn_buenas_practicas_sugeridas( array $respuestas, $limite = 3 ) {
	$mapa = otrn_mapa_codigo_ambito();
	$calc = otrn_calcular_desempeno( $respuestas, $mapa );

	// Cuántas respuestas en 0 tiene cada ámbito (desempate entre % iguales).
	$ceros = array();
	foreach ( $respuestas as $codigo => $valor ) {
		if ( 0 === $valor && isset( $mapa[ $codigo ] ) ) {
			$ambito           = $mapa[ $codigo ];
			$ceros[ $ambito ] = ( $ceros[ $ambito ] ?? 0 ) + 1;
		}
	}

	$orden = array();
	foreach ( $calc['por_ambito'] as $ambito => $datos ) {
		if ( null === $datos['pct'] || $datos['pct'] >= 100 ) {
			continue; // sin responder, o sin brecha que mejorar.
		}
		$orden[] = array(
			'ambito' => $ambito,
			'pct'    => $datos['pct'],
			'ceros'  => $ceros[ $ambito ] ?? 0,
		);
	}
	if ( empty( $orden ) ) {
		return array();
	}

	usort( $orden, static function ( $a, $b ) {
		if ( $a['pct'] !== $b['pct'] ) {
			return $a['pct'] <=> $b['pct']; // peor desempeño primero.
		}
		return $b['ceros'] <=> $a['ceros'];
	} );

	// Brechas concretas: un indicador respondido bajo el máximo, peor primero.
	// Un 3 no es brecha y un NA no se respondió, así que ninguno entra.
	$por_ambito = array();
	foreach ( $respuestas as $codigo => $valor ) {
		if ( ! isset( $mapa[ $codigo ] ) || null === $valor || 'NA' === $valor || '' === $valor ) {
			continue;
		}
		$valor = (int) $valor;
		if ( $valor >= 3 ) {
			continue;
		}
		$por_ambito[ $mapa[ $codigo ] ][] = array( 'codigo' => (string) $codigo, 'valor' => $valor );
	}
	foreach ( $por_ambito as &$brechas ) {
		usort( $brechas, static function ( $a, $b ) {
			if ( $a['valor'] !== $b['valor'] ) {
				return $a['valor'] <=> $b['valor']; // el 0 antes que el 1.
			}
			$ka = array_map( 'intval', explode( '.', $a['codigo'] ) );
			$kb = array_map( 'intval', explode( '.', $b['codigo'] ) );
			return $ka <=> $kb;
		} );
	}
	unset( $brechas );

	$practicas  = otrn_practicas_por_codigo();
	$indicadores = otrn_indicador_por_codigo();
	$sugeridas  = array();
	$usados     = array();

	// Vuelta 1: una práctica vinculada al indicador exacto, rotando entre ámbitos.
	$avanzo = true;
	while ( $avanzo && count( $sugeridas ) < $limite ) {
		$avanzo = false;
		foreach ( $orden as $o ) {
			$ambito = $o['ambito'];
			while ( ! empty( $por_ambito[ $ambito ] ) ) {
				$brecha = array_shift( $por_ambito[ $ambito ] );
				$avanzo = true;
				foreach ( $practicas[ $brecha['codigo'] ] ?? array() as $id ) {
					if ( isset( $usados[ $id ] ) ) {
						continue;
					}
					$usados[ $id ]  = true;
					$sugeridas[]    = array(
						'id'        => $id,
						'ambito'    => $ambito,
						'pct'       => $o['pct'],
						'codigo'    => $brecha['codigo'],
						'indicador' => $indicadores[ $brecha['codigo'] ] ?? '',
					);
					break 2;
				}
			}
		}
	}

	// Vuelta 2: si faltan, se completa por ámbito — prácticas sin indicador
	// vinculado o brechas que nadie cubre todavía.
	if ( count( $sugeridas ) < $limite ) {
		foreach ( $orden as $o ) {
			$ids = get_posts( array(
				'post_type'   => 'buena_practica',
				'post_status' => 'publish',
				'numberposts' => $limite,
				'fields'      => 'ids',
				'exclude'     => array_keys( $usados ),
				'tax_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array( 'taxonomy' => 'ambito', 'field' => 'name', 'terms' => $o['ambito'] ),
				),
			) );
			foreach ( $ids as $id ) {
				$usados[ (int) $id ] = true;
				$sugeridas[]         = array(
					'id'        => (int) $id,
					'ambito'    => $o['ambito'],
					'pct'       => $o['pct'],
					'codigo'    => '',
					'indicador' => '',
				);
				if ( count( $sugeridas ) >= $limite ) {
					return $sugeridas;
				}
				break; // una por ámbito, para seguir rotando.
			}
		}
	}

	return array_slice( $sugeridas, 0, $limite );
}

/**
 * Reúne los datos de autoevaluaciones para el mapa (docs/01 §5.1).
 *
 * Devuelve una fila por evaluación con coordenadas válidas: destino, fecha,
 * % global, tramo y enlace. La agregación por punto se hace en el cliente.
 *
 * @return array<int,array>
 */
function otrn_evaluaciones_geo() {
	$ids  = get_posts( array( 'post_type' => 'evaluacion', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) );
	$mapa = otrn_mapa_codigo_ambito();
	$out  = array();
	foreach ( $ids as $id ) {
		$lat = (float) get_post_meta( $id, 'destino_lat', true );
		$lng = (float) get_post_meta( $id, 'destino_lng', true );
		if ( 0.0 === $lat && 0.0 === $lng ) {
			continue; // sin geolocalización.
		}
		$calc = otrn_calcular_desempeno( otrn_evaluacion_respuestas( $id ), $mapa );
		$out[] = array(
			'destino' => get_post_meta( $id, 'destino', true ) ?: get_the_title( $id ),
			'fecha'   => get_the_date( 'd/m/Y', $id ),
			'pct'     => $calc['global'],
			'tier'    => otrn_tier( $calc['global'] ),
			'lat'     => $lat,
			'lng'     => $lng,
			'url'     => get_permalink( $id ),
		);
	}
	return $out;
}

/** URL de la página `Acerca de` (`page-acerca.php`), creada por scripts/crear-pagina-acerca.php. */
function otrn_acerca_url() {
	$pagina = get_page_by_path( 'acerca' );
	if ( $pagina && 'publish' === $pagina->post_status ) {
		return get_permalink( $pagina );
	}

	return home_url( '/acerca/' );
}
