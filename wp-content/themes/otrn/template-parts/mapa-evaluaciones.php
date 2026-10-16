<?php
/**
 * Mapa de autoevaluaciones (docs/01 §5.1).
 *
 * Parcial reutilizable: Leaflet + OpenStreetMap con los marcadores agregados por
 * coordenada exacta (promedio de % + contador) y la lista lateral con cada
 * evaluación individual, sin agregar.
 *
 * Se usa desde `archive-evaluacion.php` (la vista combinada mapa + listado).
 * Requiere que Leaflet esté encolado — ver `otrn_enqueue_assets()`.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

$puntos = otrn_evaluaciones_geo();
?>

<?php if ( empty( $puntos ) ) : ?>
	<div class="otrn-empty" style="border:1px solid var(--grey);border-radius:var(--radius);">Aún no hay autoevaluaciones geolocalizadas.</div>
<?php else : ?>
	<div class="otrn-kpigrid" id="otrn-mapkpis">
		<?php foreach ( $puntos as $i => $p ) : ?>
			<button type="button" class="otrn-kpi tier-<?php echo esc_attr( $p['tier'] ); ?>" data-idx="<?php echo (int) $i; ?>">
				<span class="n"><?php echo null === $p['pct'] ? '—' : esc_html( otrn_mm( $p['pct'] ) ) . '%'; ?></span>
				<span class="d"><?php echo esc_html( $p['destino'] ); ?></span>
				<span class="f mono"><?php echo esc_html( $p['fecha'] ); ?></span>
			</button>
		<?php endforeach; ?>
	</div>

	<div id="otrn-map"></div>
	<p style="color:var(--text2);font-size:12px;margin:10px 0 40px;">Tramos: <b style="color:var(--error);">Bajo 0–33%</b> · <b style="color:var(--warning);">Medio 34–66%</b> · <b style="color:var(--success);">Bueno 67–100%</b>. Tiles © OpenStreetMap contributors.</p>

	<script>
	document.addEventListener('DOMContentLoaded', function(){
		var puntos = <?php echo wp_json_encode( $puntos ); ?>;
		if (!window.L || !puntos.length) { return; }

		// Tramos leídos del sistema de diseño (style.css :root) para no duplicar
		// la paleta de estado: alto = éxito, medio = aviso, bajo = error, s/d = neutro.
		var raiz = getComputedStyle(document.documentElement);
		var token = function(n){ return raiz.getPropertyValue(n).trim(); };
		var colores = {
			high: token('--success'), mid: token('--warning'),
			low: token('--error'), na: token('--neutral')
		};
		var map = L.map('otrn-map', { scrollWheelZoom:false });
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '© OpenStreetMap contributors'
		}).addTo(map);

		// Agrupa por coordenada redondeada (~1 m).
		var grupos = {};
		puntos.forEach(function(p, idx){
			p._idx = idx;
			var key = p.lat.toFixed(5) + ',' + p.lng.toFixed(5);
			(grupos[key] = grupos[key] || []).push(p);
		});

		var markerPorKey = {}, keyPorIdx = {}, bounds = [];

		Object.keys(grupos).forEach(function(key){
			var g = grupos[key];
			var lat = g[0].lat, lng = g[0].lng;
			var pcts = g.map(function(x){ return x.pct; }).filter(function(v){ return v !== null; });
			var prom = pcts.length ? Math.round(pcts.reduce(function(a,b){return a+b;},0)/pcts.length*10)/10 : null;
			var tier = prom === null ? 'na' : (prom >= 67 ? 'high' : (prom >= 34 ? 'mid' : 'low'));

			var marker = L.circleMarker([lat,lng], {
				radius: 12, color: token('--paper'), weight:2, fillColor:colores[tier], fillOpacity:0.95
			}).addTo(map);

			var lista = g.map(function(x){
				return '<div style="display:flex;justify-content:space-between;gap:12px;margin:2px 0;">'
					+ '<a href="'+x.url+'">'+x.fecha+'</a><b>'+(x.pct===null?'—':x.pct+'%')+'</b></div>';
			}).join('');
			marker.bindPopup('<b>'+g[0].destino+'</b>'
				+ (g.length>1 ? '<br>Promedio: <b>'+(prom===null?'—':prom+'%')+'</b> · '+g.length+' evaluaciones' : '')
				+ '<hr style="margin:6px 0;border:none;border-top:1px solid var(--grey);">' + lista);

			if (g.length > 1) {
				L.marker([lat,lng], { icon: L.divIcon({
					className:'', html:'<div class="otrn-mapcount">'+g.length+'</div>',
					iconSize:[22,22], iconAnchor:[-6,18]
				}) }).addTo(map).on('click', function(){ marker.openPopup(); });
			}

			markerPorKey[key] = marker;
			g.forEach(function(x){ keyPorIdx[x._idx] = key; });
			bounds.push([lat,lng]);
		});

		if (bounds.length === 1) { map.setView(bounds[0], 12); }
		else { map.fitBounds(bounds, { padding:[40,40], maxZoom:13 }); }

		// Sincroniza los KPI de arriba ↔ marcador.
		var items = document.querySelectorAll('#otrn-mapkpis .otrn-kpi');
		items.forEach(function(it){
			it.addEventListener('click', function(){
				items.forEach(function(x){ x.classList.remove('active'); });
				it.classList.add('active');
				var m = markerPorKey[keyPorIdx[+it.dataset.idx]];
				if (m) { map.panTo(m.getLatLng()); m.openPopup(); }
			});
		});
	});
	</script>
<?php endif; ?>
