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
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/logo.png' ) ); ?>" alt="Rapa Nui, el Misterio Vive — Isla de Pascua, Chile">
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/footer/logo-rau.png' ) ); ?>" alt="RAU Consultores en Turismo">
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/footer/logo-corfo.png' ) ); ?>" alt="Iniciativa CORFO">
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/footer/logo-corfo-valparaiso.png' ) ); ?>" alt="Comité Desarrollo Productivo Valparaíso por CORFO">
	</div>
	<div class="wrap otrn-footer-copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Observatorio Turístico Rapa Nui</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
