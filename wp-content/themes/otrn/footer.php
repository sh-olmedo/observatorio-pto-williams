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
		// Tres logos institucionales de Arauco. Cada uno va sobre una «chapa»
		// blanca porque Municipalidad y Oficina de Turismo traen un fondo gris
		// interior opaco; el GORE tiene fondo casi blanco. (assets/logos/)
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
	</div>
	<div class="wrap otrn-footer-copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Observatorio Turístico de Arauco · Región del Bío-Bío</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
