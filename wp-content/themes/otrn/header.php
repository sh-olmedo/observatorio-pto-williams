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
				src="<?php echo esc_url( otrn_asset( 'assets/logos/oficina-turismo-arauco.webp' ) ); ?>"
				width="800"
				height="284"
				alt="Logotipo provisional: Oficina de Turismo · Municipalidad de Arauco (pendiente el de Cabo de Hornos y Antártica)"
			>
		</a>
		<?php
		// Navegación principal: una estructura (functions.php) para la fila de
		// escritorio, el desplegable «Más» de tableta y el panel lateral de móvil.
		// Los grupos con prioridad > 2 se replican dentro de «Más»; su copia solo
		// se muestra cuando el original está colapsado (ver otrn-menu.css).
		$otrn_nav     = otrn_nav_estructura();
		$otrn_en_mas  = array();
		$otrn_mas_act = false;
		foreach ( $otrn_nav as $otrn_item ) {
			if ( 'grupo' === $otrn_item['tipo'] && $otrn_item['prioridad'] > 2 ) {
				$otrn_en_mas[] = $otrn_item;
				$otrn_mas_act  = $otrn_mas_act || $otrn_item['activo'];
			}
		}
		// Icono de cuenta: persona en contorno. El tema no carga ninguna fuente
		// de iconos (Material Icons no está disponible), así que se usa el mismo
		// lenguaje de iconos del proyecto: SVG en línea, trazo 1,7 y currentColor.
		$otrn_icono_cuenta = '<svg class="otrn-cuenta__icono" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="12" cy="8.3" r="3.6"/><path d="M4.9 19.4c0-3.6 3.2-5.8 7.1-5.8s7.1 2.2 7.1 5.8"/></svg>';
		?>
		<nav id="otrn-nav" class="otrn-nav" aria-label="Principal">
			<ul class="otrn-nav__lista">
				<?php foreach ( $otrn_nav as $otrn_item ) : ?>
					<?php if ( 'enlace' === $otrn_item['tipo'] ) : ?>
						<li class="otrn-nav__item">
							<a class="otrn-nav__enlace<?php echo $otrn_item['activo'] ? ' active' : ''; ?>" href="<?php echo esc_url( $otrn_item['url'] ); ?>"<?php echo $otrn_item['activo'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $otrn_item['etiqueta'] ); ?></a>
						</li>
					<?php else : ?>
						<li class="otrn-nav__item otrn-nav__item--grupo" data-prioridad="<?php echo esc_attr( (string) $otrn_item['prioridad'] ); ?>">
							<button class="otrn-nav__boton<?php echo $otrn_item['activo'] ? ' active' : ''; ?>" type="button" aria-expanded="false" aria-controls="otrn-sub-<?php echo esc_attr( $otrn_item['id'] ); ?>"<?php echo $otrn_item['activo'] ? ' aria-current="true"' : ''; ?>>
								<?php echo esc_html( $otrn_item['etiqueta'] ); ?><span class="otrn-nav__caret" aria-hidden="true"></span>
							</button>
							<ul class="otrn-subnav" id="otrn-sub-<?php echo esc_attr( $otrn_item['id'] ); ?>">
								<?php foreach ( $otrn_item['hijos'] as $otrn_hijo ) : ?>
									<li>
										<a href="<?php echo esc_url( $otrn_hijo['url'] ); ?>"<?php echo $otrn_hijo['activo'] ? ' class="active" aria-current="page"' : ''; ?>><?php echo esc_html( $otrn_hijo['etiqueta'] ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php if ( $otrn_en_mas ) : ?>
					<li class="otrn-nav__item otrn-nav__item--mas">
						<button class="otrn-nav__boton<?php echo $otrn_mas_act ? ' active' : ''; ?>" type="button" aria-expanded="false" aria-controls="otrn-sub-mas"<?php echo $otrn_mas_act ? ' aria-current="true"' : ''; ?>>
							Más<span class="otrn-nav__caret" aria-hidden="true"></span>
						</button>
						<ul class="otrn-subnav otrn-subnav--mas" id="otrn-sub-mas">
							<?php foreach ( $otrn_en_mas as $otrn_grupo ) : ?>
								<li class="otrn-mas__grupo" data-prioridad="<?php echo esc_attr( (string) $otrn_grupo['prioridad'] ); ?>">
									<p class="otrn-mas__titulo"><?php echo esc_html( $otrn_grupo['etiqueta'] ); ?></p>
									<ul class="otrn-mas__lista">
										<?php foreach ( $otrn_grupo['hijos'] as $otrn_hijo ) : ?>
											<li>
												<a href="<?php echo esc_url( $otrn_hijo['url'] ); ?>"<?php echo $otrn_hijo['activo'] ? ' class="active" aria-current="page"' : ''; ?>><?php echo esc_html( $otrn_hijo['etiqueta'] ); ?></a>
											</li>
										<?php endforeach; ?>
									</ul>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endif; ?>
			</ul>
		</nav>
		<?php
		// Acceso: icono de cuenta en contorno, sin etiqueta visible (el nombre
		// accesible va en aria-label). Anónimo enlaza al login; con sesión abre un
		// desplegable mínimo con «Cerrar sesión». Fuera de `.otrn-nav` para que
		// siga visible con el panel móvil abierto.
		$otrn_aqui = home_url( add_query_arg( array() ) );
		?>
		<div class="otrn-cuenta">
			<?php if ( is_user_logged_in() ) : ?>
				<button class="otrn-cuenta__boton" type="button" aria-expanded="false" aria-controls="otrn-cuenta-panel" aria-label="Cuenta">
					<?php echo $otrn_icono_cuenta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG estático del tema. ?>
				</button>
				<div class="otrn-cuenta__panel" id="otrn-cuenta-panel">
					<a class="otrn-cuenta__salir" href="<?php echo esc_url( wp_logout_url( $otrn_aqui ) ); ?>">Cerrar sesión</a>
				</div>
			<?php else : ?>
				<a class="otrn-cuenta__boton" href="<?php echo esc_url( wp_login_url( $otrn_aqui ) ); ?>" aria-label="Iniciar sesión">
					<?php echo $otrn_icono_cuenta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG estático del tema. ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
		// Botón de menú móvil: siempre presente en el DOM; el CSS lo oculta en
		// escritorio y el script (assets/otrn-menu.js) gestiona aria + foco.
		?>
		<button class="otrn-burger" type="button" aria-expanded="false" aria-controls="otrn-nav" aria-label="Abrir menú de navegación">
			<span class="otrn-burger__box" aria-hidden="true">
				<span class="otrn-burger__line"></span>
				<span class="otrn-burger__line"></span>
				<span class="otrn-burger__line"></span>
			</span>
		</button>
	</div>
</header>
<main>
