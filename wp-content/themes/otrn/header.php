<?php
/**
 * Cabecera del tema hijo para las vistas de Seguimiento Pladetur.
 *
 * @package otrn
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'otrn' ); ?>>
<header class="otrn-header">
	<div class="wrap">
		<a class="otrn-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img
				class="otrn-brand-logo"
				src="<?php echo esc_url( get_theme_file_uri( 'assets/logo.png' ) ); ?>"
				width="1290"
				height="722"
				alt="Rapa Nui, el Misterio Vive — Isla de Pascua, Chile"
			>
		</a>
		<nav class="otrn-nav">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a>
			<a href="<?php echo esc_url( otrn_acerca_url() ); ?>">Acerca de</a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'indicador' ) ); ?>">Indicadores</a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'buena_practica' ) ); ?>">Buenas prácticas</a>
			<?php if ( is_user_logged_in() ) : // Sección privada (functions.php). ?>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'evaluacion' ) ); ?>">Evaluación de sostenibilidad</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'iniciativa_pladetur' ) ); ?>">Planificación</a>
			<a href="<?php echo esc_url( home_url( '/geoportal/' ) ); ?>">Geoportal</a>
			<a href="<?php echo esc_url( otrn_noticias_url() ); ?>">Noticias</a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'guia' ) ); ?>">Guía de usuario</a>
			<?php
			$otrn_material = otrn_material_secciones();
			if ( $otrn_material ) :
				foreach ( $otrn_material as $otrn_sec ) :
					?>
					<a href="<?php echo esc_url( $otrn_sec['url'] ); ?>"><?php echo esc_html( $otrn_sec['nav'] ); ?></a>
					<?php
				endforeach;
			else :
				?>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'material_apoyo' ) ); ?>">Material</a>
			<?php endif; ?>
		</nav>
		<?php
		// Acceso al login. La sección de autoevaluación es privada, así que el
		// público necesita una puerta visible; quien ya entró ve la salida.
		$otrn_aqui = home_url( add_query_arg( array() ) );
		if ( is_user_logged_in() ) :
			?>
			<a class="otrn-login" href="<?php echo esc_url( wp_logout_url( $otrn_aqui ) ); ?>">Salir</a>
		<?php else : ?>
			<a class="otrn-login" href="<?php echo esc_url( wp_login_url( $otrn_aqui ) ); ?>">Iniciar sesión</a>
		<?php endif; ?>
	</div>
</header>
<main>
