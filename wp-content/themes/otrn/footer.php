<?php
/**
 * Pie del tema hijo para las vistas de Seguimiento Pladetur.
 *
 * @package otrn
 */

?>
</main>
<footer class="otrn-footer">
	<div class="wrap otrn-footer-logos">
		<?php
		// Cuatro logos institucionales, en el orden del ejemplo del cliente:
		// GORE, Municipalidad, Oficina de Turismo y RAU. Cada uno va sobre una
		// «chapa» blanca porque Municipalidad y Oficina de Turismo traen un fondo
		// gris interior opaco; el GORE tiene fondo casi blanco y RAU es un PNG
		// transparente. (assets/logos/ y assets/footer/logo-rau.png)
		?>
		<span class="otrn-footer-logo">
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/logos/gore-region-biobio.webp' ) ); ?>" width="160" height="283" alt="Gobierno Regional de la Región del Bío-Bío">
		</span>
		<span class="otrn-footer-logo">
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/logos/municipalidad-arauco.webp' ) ); ?>" width="1513" height="586" alt="Municipalidad de Arauco">
		</span>
		<span class="otrn-footer-logo">
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/logos/oficina-turismo-arauco.webp' ) ); ?>" width="1289" height="456" alt="Oficina de Turismo · Municipalidad de Arauco">
		</span>
		<span class="otrn-footer-logo">
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/footer/logo-rau.png' ) ); ?>" width="193" height="120" alt="RAU · Consultores en Turismo">
		</span>
	</div>
	<div class="wrap otrn-footer-copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Observatorio Turístico de Arauco · Región del Bío-Bío</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
