<?php
/**
 * Plugin Name: OTRN · Vigilante del tema
 * Description: Devuelve el tema del proyecto si otro lo reemplaza, y lo registra en el log de PHP.
 *
 * Contexto (2026-10-17): el sitio publicado amaneció con `hostinger-ai-theme` activo
 * encima del tema del proyecto. La portada se pintaba con plantillas de bloque y
 * mostraba el contenido de una entrada suelta, sin cabecera ni pie. El cambio no pasó
 * por `switch_theme()`, que el core bloquea porque ese tema exige PHP 8.1 y el host va
 * por PHP 8.0: lo escribió algo de fuera (el asistente con IA / el panel de Hostinger)
 * directamente en las opciones `template` y `stylesheet`.
 *
 * Este vigilante revisa el tema activo en `setup_theme` —antes de que el tema intruso
 * cargue su `functions.php`— y también en `switch_theme`, y devuelve el tema del
 * proyecto en cuanto otro ocupa su lugar.
 *
 * Se desactiva borrando el archivo, o definiendo `OTRN_VIGILANTE_TEMA_OFF` en
 * `wp-config.php` (útil antes de cambiar de tema a propósito). El tema protegido se
 * cambia con el filtro `otrn_tema_vigilado`.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'OTRN_VIGILANTE_TEMA_OFF' ) && OTRN_VIGILANTE_TEMA_OFF ) {
	return;
}

/**
 * Tema que este vigilante mantiene activo.
 *
 * @return string
 */
function otrn_tema_vigilado() {
	return (string) apply_filters( 'otrn_tema_vigilado', 'otrn' );
}

/**
 * ¿Se puede restaurar el tema en esta petición?
 *
 * Respeta las redes de seguridad del propio WordPress: en modo recuperación, o si el
 * tema del proyecto está pausado por haber roto el sitio, el vigilante se aparta en
 * vez de pelearse con ellas.
 *
 * @return bool
 */
function otrn_vigilante_puede_restaurar() {
	static $puede = null;

	if ( null !== $puede ) {
		return $puede;
	}

	$objetivo = otrn_tema_vigilado();
	$puede    = '' !== $objetivo
		&& wp_get_theme( $objetivo )->exists()
		&& ! ( function_exists( 'wp_is_recovery_mode' ) && wp_is_recovery_mode() );

	if ( $puede && function_exists( 'wp_paused_themes' ) ) {
		$pausados = wp_paused_themes();
		if ( $pausados && $pausados->get( $objetivo ) ) {
			$puede = false;
		}
	}

	return $puede;
}

/**
 * Devuelve el tema del proyecto y deja rastro en el log de PHP.
 *
 * @param string $reemplazo Tema que había ocupado su lugar.
 * @param string $via       Punto de entrada que lo detectó: `setup_theme` o `switch_theme`.
 */
function otrn_vigilante_restaurar( $reemplazo, $via ) {
	if ( ! otrn_vigilante_puede_restaurar() ) {
		return;
	}

	$objetivo = otrn_tema_vigilado();
	$usuario  = function_exists( 'wp_get_current_user' ) ? (int) wp_get_current_user()->ID : 0;

	error_log(
		sprintf(
			'[otrn-vigilante] Tema "%s" activo (detectado en %s); se restaura "%s". Usuario: %d.',
			$reemplazo,
			$via,
			$objetivo,
			$usuario
		)
	);

	switch_theme( $objetivo );
}

/**
 * Revisa el tema activo antes de que ningún tema cargue su código.
 *
 * El asistente con IA de Hostinger escribe las opciones `template`/`stylesheet`
 * directamente, sin pasar por `switch_theme()`, así que este es el único punto donde
 * se le puede ganar la partida: `setup_theme` se dispara antes de que WordPress
 * incluya el `functions.php` del tema activo.
 */
function otrn_vigilante_revisar_tema() {
	static $revisado = false;

	if ( $revisado ) {
		return;
	}
	$revisado = true;

	$objetivo = otrn_tema_vigilado();
	if ( '' === $objetivo || get_stylesheet() === $objetivo ) {
		return;
	}

	otrn_vigilante_restaurar( get_stylesheet(), 'setup_theme' );
}
add_action( 'setup_theme', 'otrn_vigilante_revisar_tema', 1 );

/**
 * Devuelve el tema si alguien lo cambia por la vía normal de WordPress.
 *
 * `switch_theme()` ya actualizó las opciones cuando dispara esta acción, así que aquí
 * se compara contra el tema recién activado. El guard estático evita la recursión de
 * la llamada propia.
 *
 * @param string   $nuevo_nombre Nombre del tema nuevo.
 * @param WP_Theme $nuevo_tema   Objeto del tema nuevo.
 * @param WP_Theme $antiguo_tema Objeto del tema anterior.
 */
function otrn_vigilante_tema( $nuevo_nombre, $nuevo_tema, $antiguo_tema ) {
	static $restaurando = false;

	if ( $restaurando ) {
		return;
	}

	$objetivo  = otrn_tema_vigilado();
	$reemplazo = $nuevo_tema instanceof WP_Theme ? $nuevo_tema->get_stylesheet() : (string) $nuevo_nombre;

	if ( '' === $objetivo || $reemplazo === $objetivo ) {
		return;
	}

	$restaurando = true;
	otrn_vigilante_restaurar( $reemplazo, 'switch_theme' );
	$restaurando = false;
}
add_action( 'switch_theme', 'otrn_vigilante_tema', 10, 3 );
