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
		// GORE, Municipalidad, Oficina de Turismo y RAU. Los cuatro van sueltos
		// sobre el fondo del pie: son WebP/PNG con transparencia real, recortados
		// desde los originales del cliente (assets/logos/README.md). Las medidas
		// son las reales de cada archivo, para que no haya saltos de maquetación.
		?>
		<span class="otrn-footer-logo">
			<img src="<?php echo esc_url( otrn_asset( 'assets/logos/gore-region-biobio.webp' ) ); ?>" width="160" height="283" alt="Gobierno Regional de la Región del Bío-Bío">
		</span>
		<span class="otrn-footer-logo">
			<img src="<?php echo esc_url( otrn_asset( 'assets/logos/municipalidad-arauco.webp' ) ); ?>" width="800" height="309" alt="Municipalidad de Arauco">
		</span>
		<span class="otrn-footer-logo">
			<img src="<?php echo esc_url( otrn_asset( 'assets/logos/oficina-turismo-arauco.webp' ) ); ?>" width="800" height="284" alt="Oficina de Turismo · Municipalidad de Arauco">
		</span>
		<span class="otrn-footer-logo">
			<img src="<?php echo esc_url( otrn_asset( 'assets/footer/logo-rau.png' ) ); ?>" width="193" height="120" alt="RAU · Consultores en Turismo">
		</span>
	</div>
	<div class="wrap otrn-footer-copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Observatorio Turístico de Arauco · Región del Bío-Bío</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
