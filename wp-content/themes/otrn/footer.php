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
		// Marca municipal única: «Crece · Ilustre Municipalidad de Cabo de
		// Hornos y Antártica», versión BLANCA sobre el pie azul (#0071BC,
		// igual que el portal imcabodehornos.cl). Originales: vector del
		// cliente en references/logos/ + logo-crece-blanco-sitiooficial.png.
		// El resto del conjunto institucional del pie (GORE regional y
		// organismos con que se relacione el observatorio) queda pendiente
		// del cliente: ver assets/logos/README.md y docs/PW_CLONE.md.
		?>
		<span class="otrn-footer-logo">
			<img src="<?php echo esc_url( otrn_asset( 'assets/logos/crece-imcabodehornos-blanco.png' ) ); ?>" width="1027" height="537" alt="Crece · CABO DE HORNOS — Ilustre Municipalidad de Cabo de Hornos y Antártica (logotipo blanco)">
		</span>
	</div>
	<div class="wrap otrn-footer-copy">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Observatorio Turístico de Puerto Williams · Región de Magallanes y la Antártica Chilena</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
