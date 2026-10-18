<?php
/**
 * Plugin Name: OTRN — Campos de los contenidos
 * Description: Registra en código los grupos de campos de los CPTs del Observatorio (indicador, buena_practica, material_apoyo, dato_portada, iniciativa_pladetur y criterio_autodiag). Sin esto, las pantallas de edición mostraban solo el título: los datos existían como metadatos pero no había ninguna definición de campos (docs/ISSUES.md F4 y F31).
 * Author: Observatorio Turístico Rapa Nui
 *
 * @package otrn
 *
 * Las definiciones van en PHP a propósito: los grupos de campos no estaban
 * versionados en ningún sitio (la carpeta `docs/acf-json/` nunca existió) y así
 * viajan con el código, sin depender de la base de datos ni de exportaciones.
 *
 * Nombres y claves se tomaron del contenido real ya guardado —cada clave
 * `field_otrn_*` coincide con la referencia `_<campo>` que ya está en la base—
 * para que ACF muestre los valores existentes en lugar de vaciarlos. Los tipos
 * son deliberadamente conservadores (texto/área de texto/URL/número/archivo) y
 * solo se usan listas donde el conjunto de valores está comprobado.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Niveles territoriales (etiqueta visible => valor guardado).
 *
 * El valor guardado se mantiene tal cual: «Comunal» se muestra como «Local»
 * (misma correspondencia que en los listados del tema).
 *
 * @return array<string,string>
 */
function otrn_campos_niveles() {
	return array(
		'Comunal'       => 'Local (Comunal)',
		'Regional'      => 'Regional',
		'Nacional'      => 'Nacional',
		'Internacional' => 'Internacional',
	);
}

/**
 * Registra los grupos de campos de los CPTs de contenido.
 *
 * Se engancha a `acf/init` para no depender de que ACF esté activo: si no lo
 * está, simplemente no se registran campos (el sitio público sigue igual).
 *
 * @return void
 */
function otrn_registrar_campos_contenido() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$niveles = otrn_campos_niveles();

	/* ------------------------------------------------------------------ */
	/* Buenas prácticas                                                    */
	/* ------------------------------------------------------------------ */
	acf_add_local_field_group( array(
		'key'      => 'group_otrn_buena_practica',
		'title'    => 'Buena práctica — datos',
		'fields'   => array(
			array(
				'key'       => 'field_otrn_bp_descripcion',
				'label'     => 'Descripción',
				'name'      => 'descripcion',
				'type'      => 'textarea',
				'rows'      => 5,
				'new_lines' => '',
				'required'  => 0,
			),
			array(
				'key'   => 'field_otrn_bp_fuente',
				'label' => 'Fuente de la información',
				'name'  => 'fuente_de_la_informacion',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_otrn_bp_ubicacion',
				'label' => 'Ubicación',
				'name'  => 'ubicacion',
				'type'  => 'text',
			),
			array(
				'key'           => 'field_otrn_bp_nivel',
				'label'         => 'Nivel territorial',
				'name'          => 'nivel_territorial',
				'type'          => 'select',
				'choices'       => $niveles,
				'default_value' => 'Comunal',
				'allow_null'    => 1,
				'ui'            => 1,
				'instructions'  => 'El valor guardado no cambia («Comunal» se muestra como «Local»).',
			),
			array(
				'key'   => 'field_otrn_bp_sitio',
				'label' => 'Sitio web',
				'name'  => 'sitio_web',
				'type'  => 'url',
			),
			array(
				'key'          => 'field_otrn_bp_criterio',
				'label'        => 'Criterio relacionado',
				'name'         => 'criterio_relacionado',
				'type'         => 'text',
				'instructions' => 'Código del criterio de autodiagnóstico (por ejemplo 45.2).',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'buena_practica',
				),
			),
		),
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'active'                => true,
		'description'           => '',
		'show_in_rest'          => 0,
		'menu_order'            => 0,
	) );

	/* ------------------------------------------------------------------ */
	/* Indicadores                                                         */
	/* ------------------------------------------------------------------ */
	acf_add_local_field_group( array(
		'key'    => 'group_otrn_indicador',
		'title'  => 'Indicador — datos',
		'fields' => array(
			array(
				'key'       => 'field_otrn_ind_descripcion',
				'label'     => 'Descripción',
				'name'      => 'descripcion',
				'type'      => 'textarea',
				'rows'      => 4,
				'new_lines' => '',
			),
			array(
				'key'   => 'field_otrn_ind_institucion',
				'label' => 'Institución responsable',
				'name'  => 'institucion_responsable',
				'type'  => 'text',
			),
			array(
				'key'           => 'field_otrn_ind_nivel',
				'label'         => 'Nivel territorial',
				'name'          => 'nivel_territorial',
				'type'          => 'select',
				'choices'       => $niveles,
				'default_value' => 'Comunal',
				'allow_null'    => 1,
				'ui'            => 1,
				'instructions'  => 'El valor guardado no cambia («Comunal» se muestra como «Local»).',
			),
			array(
				'key'     => 'field_otrn_ind_fuente',
				'label'   => 'Fuente de datos',
				'name'    => 'fuente_datos',
				'type'    => 'select',
				'choices' => array(
					'Manual' => 'Manual',
					'Embed'  => 'Embed',
					'PDF'    => 'PDF',
					'Excel'  => 'Excel',
				),
				'allow_null' => 1,
				'ui'         => 1,
			),
			array(
				'key'   => 'field_otrn_ind_formula',
				'label' => 'Fórmula',
				'name'  => 'formula',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_otrn_ind_sitio',
				'label' => 'Sitio web',
				'name'  => 'sitio_web',
				'type'  => 'url',
			),
			array(
				'key'          => 'field_otrn_ind_embed',
				'label'        => 'URL del visor',
				'name'         => 'embed_url',
				'type'         => 'url',
				'instructions' => 'Dirección que se carga en el visor embebido de la ficha.',
			),
			array(
				'key'          => 'field_otrn_ind_serie',
				'label'        => 'Serie de datos (JSON)',
				'name'         => 'serie_datos',
				'type'         => 'textarea',
				'rows'         => 4,
				'readonly'     => 1,
				'instructions' => 'Se mantiene desde la fuente de datos; no se edita aquí.',
			),
			array(
				'key'           => 'field_otrn_ind_pdf',
				'label'         => 'Documento PDF',
				'name'          => 'documento_pdf',
				'type'          => 'file',
				'return_format' => 'array',
				'library'       => 'all',
				'mime_types'    => 'pdf',
			),
			array(
				'key'           => 'field_otrn_ind_excel',
				'label'         => 'Documento Excel',
				'name'          => 'documento_excel',
				'type'          => 'file',
				'return_format' => 'array',
				'library'       => 'all',
				'mime_types'    => 'xlsx,xls',
			),
			array(
				'key'           => 'field_otrn_ind_ficha',
				'label'         => 'Ficha metodológica',
				'name'          => 'ficha_metodologica',
				'type'          => 'file',
				'return_format' => 'array',
				'library'       => 'all',
				'mime_types'    => 'pdf',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'indicador',
				),
			),
		),
		'position'        => 'normal',
		'style'           => 'default',
		'label_placement' => 'top',
		'active'          => true,
	) );

	/* ------------------------------------------------------------------ */
	/* Material de apoyo                                                   */
	/* ------------------------------------------------------------------ */
	acf_add_local_field_group( array(
		'key'    => 'group_otrn_material',
		'title'  => 'Material de apoyo — datos',
		'fields' => array(
			array(
				'key'          => 'field_otrn_mat_documento',
				'label'        => 'Documento',
				'name'         => 'documento',
				'type'         => 'text',
				'instructions' => 'URL del archivo o su ID en la biblioteca de medios.',
			),
			array(
				'key'   => 'field_otrn_mat_enlace',
				'label' => 'Enlace externo',
				'name'  => 'enlace_externo',
				'type'  => 'url',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'material_apoyo',
				),
			),
		),
		'position'        => 'normal',
		'style'           => 'default',
		'label_placement' => 'top',
		'active'          => true,
	) );

	/* ------------------------------------------------------------------ */
	/* Datos de portada                                                    */
	/* ------------------------------------------------------------------ */
	acf_add_local_field_group( array(
		'key'    => 'group_otrn_dato_portada',
		'title'  => 'Dato de portada — datos',
		'fields' => array(
			array(
				'key'          => 'field_otrn_dp_cifra',
				'label'        => 'Cifra',
				'name'         => 'cifra',
				'type'         => 'text',
				'instructions' => 'Se muestra tal cual, con su formato (por ejemplo 5.316).',
			),
			array(
				'key'   => 'field_otrn_dp_anio',
				'label' => 'Año',
				'name'  => 'anio',
				'type'  => 'number',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'dato_portada',
				),
			),
		),
		'position'        => 'normal',
		'style'           => 'default',
		'label_placement' => 'top',
		'active'          => true,
	) );

	/* ------------------------------------------------------------------ */
	/* Iniciativas PLADETUR                                                */
	/* ------------------------------------------------------------------ */
	$campos_iniciativa = array(
		array(
			'key'   => 'field_otrn_ip_codigo_iniciativa',
			'label' => 'Código',
			'name'  => 'codigo_iniciativa',
			'type'  => 'text',
		),
		array(
			'key'       => 'field_otrn_ip_descripcion',
			'label'     => 'Descripción',
			'name'      => 'descripcion',
			'type'      => 'textarea',
			'rows'      => 4,
			'new_lines' => '',
		),
		array(
			'key'   => 'field_otrn_ip_anio_implementacion',
			'label' => 'Año de implementación',
			'name'  => 'anio_implementacion',
			'type'  => 'number',
		),
		array(
			'key'   => 'field_otrn_ip_periodo_inversion',
			'label' => 'Periodo de inversión',
			'name'  => 'periodo_inversion',
			'type'  => 'text',
		),
		array(
			'key'          => 'field_otrn_ip_monto_total',
			'label'        => 'Monto total',
			'name'         => 'monto_total',
			'type'         => 'text',
			'instructions' => 'Puede ser un número o una nota («Incorporado en cuenta RRHH PLADETUR»).',
		),
		array(
			'key'          => 'field_otrn_ip_prioridad',
			'label'        => 'Prioridad',
			'name'         => 'prioridad',
			'type'         => 'number',
			'instructions' => '0 o 1.',
		),
		array(
			'key'   => 'field_otrn_ip_porcentaje_avance',
			'label' => 'Porcentaje de avance',
			'name'  => 'porcentaje_avance',
			'type'  => 'number',
		),
		array(
			'key'   => 'field_otrn_ip_organismos_responsables',
			'label' => 'Organismos responsables',
			'name'  => 'organismos_responsables',
			'type'  => 'text',
		),
		array(
			'key'       => 'field_otrn_ip_otros_organismos_vinculados',
			'label'     => 'Otros organismos vinculados',
			'name'      => 'otros_organismos_vinculados',
			'type'      => 'textarea',
			'rows'      => 2,
			'new_lines' => '',
		),
		array(
			'key'   => 'field_otrn_ip_fuente_financiamiento',
			'label' => 'Fuente de financiamiento',
			'name'  => 'fuente_financiamiento',
			'type'  => 'text',
		),
	);

	for ( $anio = 2024; $anio <= 2033; $anio++ ) {
		$campos_iniciativa[] = array(
			'key'     => 'field_otrn_ip_asignado_' . $anio,
			'label'   => 'Asignado ' . $anio,
			'name'    => 'asignado_' . $anio,
			'type'    => 'number',
			'wrapper' => array( 'width' => '20' ),
		);
	}

	acf_add_local_field_group( array(
		'key'             => 'group_otrn_iniciativa',
		'title'           => 'Iniciativa PLADETUR — datos',
		'fields'          => $campos_iniciativa,
		'location'        => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'iniciativa_pladetur',
				),
			),
		),
		'position'        => 'normal',
		'style'           => 'default',
		'label_placement' => 'top',
		'active'          => true,
	) );

	/* ------------------------------------------------------------------ */
	/* Criterios de autodiagnóstico                                        */
	/* ------------------------------------------------------------------ */
	acf_add_local_field_group( array(
		'key'    => 'group_otrn_criterio',
		'title'  => 'Criterio de autodiagnóstico — datos',
		'fields' => array(
			array(
				'key'   => 'field_otrn_cri_codigo',
				'label' => 'Código',
				'name'  => 'codigo',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_otrn_cri_criterio',
				'label' => 'Criterio',
				'name'  => 'criterio',
				'type'  => 'text',
			),
			array(
				'key'       => 'field_otrn_cri_pregunta',
				'label'     => 'Pregunta',
				'name'      => 'pregunta_criterio',
				'type'      => 'textarea',
				'rows'      => 3,
				'new_lines' => '',
			),
			array(
				'key'       => 'field_otrn_cri_indicador',
				'label'     => 'Indicador esperado',
				'name'      => 'indicador',
				'type'      => 'textarea',
				'rows'      => 3,
				'new_lines' => '',
			),
			array(
				'key'   => 'field_otrn_cri_na',
				'label' => 'No aplica',
				'name'  => 'na',
				'type'  => 'text',
			),
			array(
				'key'       => 'field_otrn_cri_escala_0',
				'label'     => 'Escala 0',
				'name'      => 'escala_0',
				'type'      => 'textarea',
				'rows'      => 2,
				'new_lines' => '',
			),
			array(
				'key'       => 'field_otrn_cri_escala_1',
				'label'     => 'Escala 1',
				'name'      => 'escala_1',
				'type'      => 'textarea',
				'rows'      => 2,
				'new_lines' => '',
			),
			array(
				'key'       => 'field_otrn_cri_escala_2',
				'label'     => 'Escala 2',
				'name'      => 'escala_2',
				'type'      => 'textarea',
				'rows'      => 2,
				'new_lines' => '',
			),
			array(
				'key'       => 'field_otrn_cri_escala_3',
				'label'     => 'Escala 3',
				'name'      => 'escala_3',
				'type'      => 'textarea',
				'rows'      => 2,
				'new_lines' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'criterio_autodiag',
				),
			),
		),
		'position'        => 'normal',
		'style'           => 'default',
		'label_placement' => 'top',
		'active'          => true,
	) );
}
add_action( 'acf/init', 'otrn_registrar_campos_contenido' );
