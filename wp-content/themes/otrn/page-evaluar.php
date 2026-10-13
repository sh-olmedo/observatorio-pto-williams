<?php
/**
 * Autodiagnóstico — formulario de evaluación (docs/01 §4.1, §5.2).
 *
 * Página con slug `evaluar`. Responde los 74 indicadores 0/1/2/3/NA con la
 * escala de ayuda como tooltip; al enviar crea una `evaluacion` y redirige a
 * los resultados (single-evaluacion.php).
 *
 * Admite guardar sin terminar: «Guardar borrador» deja la evaluación en estado
 * `draft` y devuelve un enlace con token para retomarla (`?borrador=ID&token=…`).
 * Además hay autoguardado local en el navegador como red de seguridad.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

get_header();

$criterios     = otrn_cargar_criterios();
$orden_ambitos = array( 'Sociocultural', 'Económico', 'Gestión', 'Medio Ambiente' );

// ¿Se está retomando un borrador? El token de la URL es la única llave (usuario anónimo).
$borrador = otrn_borrador_valido(
	isset( $_GET['borrador'] ) ? wp_unslash( $_GET['borrador'] ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '' // phpcs:ignore WordPress.Security.NonceVerification.Recommended
);
$pre_resp   = $borrador ? otrn_evaluacion_respuestas( $borrador->ID ) : array();
$pre_sector = $borrador ? (string) get_post_meta( $borrador->ID, 'sector', true ) : '';
$guardado   = $borrador && isset( $_GET['guardado'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Clave y marca de tiempo del autoguardado local: solo se ofrece restaurar lo
// del navegador si es más reciente que lo que ya está guardado en el servidor.
$ls_key   = $borrador ? 'b' . $borrador->ID : 'nuevo';
$ls_saved = $borrador ? (int) get_post_time( 'U', true, $borrador->ID ) : 0;
if ( $borrador ) {
	$mod      = get_post_modified_time( 'U', true, $borrador->ID );
	$ls_saved = $mod ? (int) $mod : $ls_saved;
}

// Detalle de cada indicador para el drawer: ámbito, criterio al que pertenece,
// pregunta del criterio y qué significa cada nivel de la escala. Se entrega como
// JSON al cliente en vez de duplicar el marcado 74 veces.
$detalle = array();
foreach ( $orden_ambitos as $amb ) {
	foreach ( $criterios[ $amb ]['criterios'] ?? array() as $nombre_criterio => $indicadores ) {
		foreach ( $indicadores as $ind ) {
			$detalle[ $ind['codigo'] ] = array(
				'ambito'    => $amb,
				'criterio'  => $nombre_criterio,
				'pregunta'  => $ind['pregunta'],
				'indicador' => $ind['indicador'],
				'escala'    => array( $ind['escala'][0], $ind['escala'][1], $ind['escala'][2], $ind['escala'][3] ),
				'na'        => $ind['na'],
			);
		}
	}
}

// Total de indicadores, para la barra de progreso.
$total_ind = count( $detalle );
?>

<div class="wrap otrn-pagehead">
	<span class="eyebrow">Autodiagnóstico</span>
	<h1><?php echo $borrador ? 'Continuar autoevaluación' : 'Realizar autoevaluación'; ?></h1>
	<p style="margin-top:16px;"><button type="button" class="otrn-btn ghost" id="otrn-tutorial-abrir">Cómo completar la evaluación</button></p>
</div>

<div class="wrap">
	<?php if ( $guardado ) : ?>
		<div class="otrn-notice ok">
			<strong>Borrador guardado.</strong> Guarda este enlace para retomar la autoevaluación desde cualquier dispositivo; no se publicará hasta que la completes y calcules los resultados.
			<input class="otrn-linkbox mono" type="text" readonly value="<?php echo esc_url( otrn_borrador_url( $borrador->ID ) ); ?>" onclick="this.select();">
		</div>
	<?php elseif ( $borrador ) : ?>
		<div class="otrn-notice">
			<strong>Estás retomando un borrador</strong> guardado el <span class="mono"><?php echo esc_html( get_post_modified_time( 'd/m/Y H:i', false, $borrador->ID ) ); ?></span> con <span class="mono"><?php echo count( $pre_resp ); ?>/<?php echo (int) $total_ind; ?></span> respuestas. Al completarlo se publicará esta misma evaluación, sin duplicarla.
		</div>
	<?php endif; ?>

	<div class="otrn-notice" id="otrn-restore" hidden>
		<strong>Hay respuestas sin guardar en este navegador.</strong>
		<span id="otrn-restore-info"></span>
		<span class="acciones">
			<button type="button" class="otrn-btn ghost" id="otrn-restore-yes">Restaurar</button>
			<button type="button" class="otrn-btn ghost" id="otrn-restore-no">Descartar</button>
		</span>
	</div>
</div>

<form class="wrap" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="otrn-eval-form"
	data-key="<?php echo esc_attr( $ls_key ); ?>" data-saved="<?php echo (int) $ls_saved; ?>" style="padding-bottom:40px;">
	<input type="hidden" name="action" value="otrn_submit_eval">
	<?php wp_nonce_field( 'otrn_eval', 'otrn_eval_nonce' ); ?>
	<?php if ( $borrador ) : ?>
		<input type="hidden" name="borrador_id" value="<?php echo (int) $borrador->ID; ?>">
		<input type="hidden" name="borrador_token" value="<?php echo esc_attr( otrn_borrador_token( $borrador->ID ) ); ?>">
	<?php endif; ?>

	<div class="otrn-form-destino" style="grid-template-columns:1fr;">
		<div class="otrn-field" style="width:auto;">
			<label for="sector">Sector evaluado</label>
			<select id="sector" name="sector" required>
				<option value="">Selecciona un sector…</option>
				<?php foreach ( otrn_sectores_rapanui() as $slug => $s ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" data-isla="<?php echo 'isla-completa' === $slug ? '1' : '0'; ?>" <?php selected( $pre_sector, $slug ); ?>><?php echo esc_html( $s['nombre'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<span style="font-size:12px;color:var(--text3);margin-top:6px;">Elige una sección de la isla o la isla completa. Al evaluar un <strong>sector</strong> puedes marcar <strong>N/A</strong> cuando un indicador no aplique; para la <strong>isla completa</strong> se responde solo 0–3. Es obligatorio para calcular resultados.</span>
		</div>
	</div>

	<?php foreach ( $orden_ambitos as $amb ) :
		$grupos = $criterios[ $amb ]['criterios'] ?? array();
		?>
		<section class="otrn-form-amb">
			<div class="amb-h"><?php echo esc_html( $amb ); ?></div>
			<?php foreach ( $grupos as $nombre_criterio => $indicadores ) : ?>
				<h3 style="font-size:15px;margin:18px 0 10px;"><?php echo esc_html( $nombre_criterio ); ?></h3>
				<?php foreach ( $indicadores as $ind ) :
					$codigo = $ind['codigo'];
					$actual = array_key_exists( $codigo, $pre_resp ) ? (string) $pre_resp[ $codigo ] : null;
					$opts   = array(
						'0'  => array( 'o0', $ind['escala'][0] ),
						'1'  => array( 'o1', $ind['escala'][1] ),
						'2'  => array( 'o2', $ind['escala'][2] ),
						'3'  => array( 'o3', $ind['escala'][3] ),
						'NA' => array( 'ona', $ind['na'] ), // Visible solo al evaluar un sector (no la isla completa); ver JS.
					);
					?>
					<div class="otrn-q" id="otrn-q-<?php echo esc_attr( $codigo ); ?>">
						<div class="qh"><span class="code"><?php echo esc_html( $codigo ); ?></span><?php echo esc_html( $ind['indicador'] ); ?></div>
						<div class="otrn-scale">
							<?php foreach ( $opts as $val => $meta ) : ?>
								<label class="<?php echo 'NA' === $val ? 'otrn-na-opt' : ''; ?>" title="<?php echo esc_attr( $meta[1] ); ?>">
									<input type="radio" name="resp[<?php echo esc_attr( $codigo ); ?>]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $actual, (string) $val ); ?>>
									<span class="opt <?php echo esc_attr( $meta[0] ); ?>"><?php echo esc_html( $val ); ?></span>
								</label>
							<?php endforeach; ?>
							<button type="button" class="otrn-qmas" data-codigo="<?php echo esc_attr( $codigo ); ?>" aria-haspopup="dialog">Ver detalle</button>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</section>
	<?php endforeach; ?>

	<div class="otrn-formbar">
		<span class="prog"><span id="otrn-prog">0 / <?php echo (int) $total_ind; ?> respondidos</span><span class="falta" id="otrn-falta"></span></span>
		<span class="otrn-formbar-acciones">
			<button type="submit" class="otrn-btn ghost" name="modo" value="borrador" formnovalidate>Guardar borrador</button>
			<button type="submit" class="otrn-btn" name="modo" value="final" id="otrn-final" disabled>Calcular resultados →</button>
		</span>
	</div>
</form>

<!-- Drawer de detalle del indicador (patrón: un solo panel reutilizado por las 74 preguntas). -->
<div class="otrn-drawer-fondo" id="otrn-drawer-fondo" hidden></div>
<aside class="otrn-drawer" id="otrn-drawer" role="dialog" aria-modal="true" aria-labelledby="otrn-drawer-titulo" hidden>
	<header class="otrn-drawer-h">
		<div>
			<span class="eyebrow" id="otrn-drawer-ambito"></span>
			<h2 id="otrn-drawer-titulo"></h2>
		</div>
		<button type="button" class="otrn-drawer-x" id="otrn-drawer-cerrar" aria-label="Cerrar detalle">&times;</button>
	</header>
	<div class="otrn-drawer-body" id="otrn-drawer-body" tabindex="0"></div>
	<footer class="otrn-drawer-f">
		<span class="mono" id="otrn-drawer-estado"></span>
		<button type="button" class="otrn-btn ghost" data-otrn-cerrar>Cerrar</button>
	</footer>
</aside>

<script type="application/json" id="otrn-detalle-data"><?php echo wp_json_encode( $detalle, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>

<script>
(function(){
	var form  = document.getElementById('otrn-eval-form');
	var total = <?php echo (int) $total_ind; ?>;
	var prog  = document.getElementById('otrn-prog');
	var falta = document.getElementById('otrn-falta');
	var btnFinal = document.getElementById('otrn-final');
	var sector = document.getElementById('sector');
	var KEY   = 'otrn_eval_' + form.dataset.key;
	var savedAt = parseInt(form.dataset.saved || '0', 10);

	function respuestas(){
		var out = {};
		form.querySelectorAll('input[type=radio]:checked').forEach(function(i){ out[i.name] = i.value; });
		return out;
	}

	// Solo se puede calcular resultados con los 74 indicadores respondidos (N/A
	// cuenta como respuesta) y el sector elegido. Mientras tanto: guardar borrador.
	function update(){
		var n = Object.keys(respuestas()).length;
		var pendientes = total - n;
		prog.textContent = n + ' / ' + total + ' respondidos';

		if (pendientes > 0) {
			falta.textContent = '· falta' + (pendientes === 1 ? ' 1 indicador' : 'n ' + pendientes + ' indicadores');
		} else if (!sector.value) {
			falta.textContent = '· falta elegir el sector';
		} else {
			falta.textContent = '· listo para calcular';
		}

		btnFinal.disabled = pendientes > 0 || !sector.value;
		btnFinal.title = btnFinal.disabled
			? 'Responde los ' + total + ' indicadores y elige el sector para calcular los resultados. Puedes guardar un borrador mientras tanto.'
			: '';
	}

	// --- Autoguardado local: red de seguridad si se cierra la pestaña sin guardar.
	function autoguardar(){
		try {
			localStorage.setItem(KEY, JSON.stringify({
				t: Math.floor(Date.now() / 1000),
				sector: sector.value,
				resp: respuestas()
			}));
		} catch (e) {}
	}

	function aplicar(data){
		if (data.sector) { sector.value = data.sector; }
		Object.keys(data.resp || {}).forEach(function(name){
			var el = form.querySelector('input[type=radio][name="' + name.replace(/"/g, '\\"') + '"][value="' + data.resp[name] + '"]');
			if (el) { el.checked = true; }
		});
		update();
	}

	var pendiente = null;
	try { pendiente = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) {}

	if (pendiente && pendiente.t > savedAt && Object.keys(pendiente.resp || {}).length) {
		var caja = document.getElementById('otrn-restore');
		document.getElementById('otrn-restore-info').textContent =
			'Quedaron ' + Object.keys(pendiente.resp).length + ' respuestas de ' +
			new Date(pendiente.t * 1000).toLocaleString('es-CL') + ' que no llegaron a guardarse en el servidor.';
		caja.hidden = false;
		document.getElementById('otrn-restore-yes').addEventListener('click', function(){
			aplicar(pendiente);
			caja.hidden = true;
		});
		document.getElementById('otrn-restore-no').addEventListener('click', function(){
			try { localStorage.removeItem(KEY); } catch (e) {}
			caja.hidden = true;
		});
	}

	form.addEventListener('change', function(){ update(); autoguardar(); });
	form.addEventListener('submit', function(){
		// Lo enviado queda en el servidor: la copia local ya no hace falta.
		try { localStorage.removeItem(KEY); } catch (e) {}
	});

	// --- Drawer de detalle: un solo panel que se rellena con el indicador pedido.
	var detalle  = JSON.parse(document.getElementById('otrn-detalle-data').textContent);
	var drawer   = document.getElementById('otrn-drawer');
	var fondo    = document.getElementById('otrn-drawer-fondo');
	var dBody    = document.getElementById('otrn-drawer-body');
	var dTitulo  = document.getElementById('otrn-drawer-titulo');
	var dAmbito  = document.getElementById('otrn-drawer-ambito');
	var dEstado  = document.getElementById('otrn-drawer-estado');
	var abridor  = null;   // botón que abrió el drawer, para devolverle el foco.
	var codigoActual = null;
	var NIVELES  = ['0', '1', '2', '3'];
	var CLASES   = { '0': 'o0', '1': 'o1', '2': 'o2', '3': 'o3', 'NA': 'ona' };

	function radioDe(codigo, valor){
		return form.querySelector('input[name="resp[' + codigo + ']"][value="' + valor + '"]');
	}

	function respuestaDe(codigo){
		var marcado = form.querySelector('input[name="resp[' + codigo + ']"]:checked');
		return marcado ? marcado.value : null;
	}

	function bloque(tag, clase, texto){
		var el = document.createElement(tag);
		if (clase) { el.className = clase; }
		if (texto) { el.textContent = texto; }   // textContent: el contenido viene de la BD, nunca se inyecta como HTML.
		return el;
	}

	function pintarEstado(){
		var actual = respuestaDe(codigoActual);
		dEstado.textContent = null === actual
			? 'Sin responder'
			: 'Respuesta actual: ' + (actual === 'NA' ? 'N/A' : actual);
		dBody.querySelectorAll('.otrn-drawer-nivel').forEach(function(n){
			n.classList.toggle('activo', n.dataset.valor === actual);
			n.setAttribute('aria-pressed', String(n.dataset.valor === actual));
		});
	}

	function render(codigo){
		var d = detalle[codigo];
		if (!d) { return; }
		codigoActual = codigo;

		dAmbito.textContent = d.ambito;
		dTitulo.textContent = codigo + ' · ' + d.criterio;

		dBody.innerHTML = '';
		dBody.appendChild(bloque('p', 'otrn-drawer-ind', d.indicador));
		if (d.pregunta) {
			var preg = bloque('div', 'otrn-drawer-preg');
			preg.appendChild(bloque('span', 'et', 'Pregunta del criterio'));
			preg.appendChild(bloque('p', null, d.pregunta));
			dBody.appendChild(preg);
		}
		dBody.appendChild(bloque('span', 'et', 'Qué significa cada respuesta'));

		NIVELES.forEach(function(valor){
			var texto = valor === 'NA' ? d.na : d.escala[parseInt(valor, 10)];
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'otrn-drawer-nivel';
			b.dataset.valor = valor;
			b.appendChild(bloque('span', 'opt ' + CLASES[valor], valor));
			b.appendChild(bloque('span', 'txt', texto || 'Sin descripción registrada para este nivel.'));
			b.addEventListener('click', function(){
				var radio = radioDe(codigo, valor);
				if (radio) {
					radio.checked = true;
					radio.dispatchEvent(new Event('change', { bubbles: true }));
					pintarEstado();
				}
			});
			dBody.appendChild(b);
		});

		pintarEstado();
		dBody.scrollTop = 0;
	}

	function abrir(boton){
		abridor = boton;
		render(boton.dataset.codigo);
		fondo.hidden = false;
		drawer.hidden = false;
		document.body.classList.add('otrn-sin-scroll');
		void drawer.offsetWidth; // reflow: la transición corre, pero el panel se abre aunque no haya animación.
		drawer.classList.add('abierto');
		fondo.classList.add('abierto');
		document.getElementById('otrn-drawer-cerrar').focus();
	}

	function cerrar(){
		drawer.classList.remove('abierto');
		fondo.classList.remove('abierto');
		drawer.hidden = true;
		fondo.hidden = true;
		document.body.classList.remove('otrn-sin-scroll');
		if (abridor) { abridor.focus(); abridor = null; }
	}

	document.querySelectorAll('.otrn-qmas').forEach(function(b){
		b.addEventListener('click', function(){ abrir(b); });
	});
	document.getElementById('otrn-drawer-cerrar').addEventListener('click', cerrar);
	drawer.querySelector('[data-otrn-cerrar]').addEventListener('click', cerrar);
	fondo.addEventListener('click', cerrar);
	document.addEventListener('keydown', function(e){
		if (drawer.hidden) { return; }
		if ('Escape' === e.key) { cerrar(); return; }
		// El drawer es modal: el foco no debe escaparse al formulario de atrás.
		if ('Tab' === e.key) {
			var focos = drawer.querySelectorAll('button, [tabindex="0"]');
			if (!focos.length) { return; }
			var primero = focos[0];
			var ultimo  = focos[focos.length - 1];
			if (e.shiftKey && document.activeElement === primero) { e.preventDefault(); ultimo.focus(); }
			else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primero.focus(); }
		}
	});
	// Responder desde el formulario con el drawer abierto no ocurre (el fondo lo tapa),
	// pero si cambia por restauración local, el drawer debe reflejarlo.
	form.addEventListener('change', function(){ if (!drawer.hidden) { pintarEstado(); } });

	// --- N/A según el sector: solo se ofrece al evaluar un sector, no la isla completa.
	function naPermitido(){
		var opt = sector.options[sector.selectedIndex];
		return !!( sector.value && opt && '1' !== opt.dataset.isla );
	}
	function aplicarModoNA(){
		var permite = naPermitido();
		form.classList.toggle('otrn-na-on', permite);
		NIVELES = permite ? ['0', '1', '2', '3', 'NA'] : ['0', '1', '2', '3'];
		if (!permite) {
			// Al pasar a isla completa (o sin sector) se limpian las respuestas N/A previas.
			form.querySelectorAll('input[type=radio][value="NA"]:checked').forEach(function(i){ i.checked = false; });
		}
		if (!drawer.hidden && codigoActual) { render(codigoActual); }
		update();
	}
	sector.addEventListener('change', aplicarModoNA);
	aplicarModoNA(); // Estado inicial (respeta un sector precargado desde un borrador).

	update();
})();
</script>

<!-- Modal tutorial: cómo completar la evaluación. Auto-abre una vez por navegador; reabrible con el botón «Cómo completar la evaluación». -->
<div class="otrn-modal-fondo" id="otrn-tut-fondo" hidden></div>
<div class="otrn-modal" id="otrn-tut-modal" role="dialog" aria-modal="true" aria-labelledby="otrn-tut-titulo" hidden>
	<header class="otrn-modal-h">
		<h2 id="otrn-tut-titulo">Evaluación de sostenibilidad turística</h2>
		<button type="button" class="otrn-modal-x" id="otrn-tut-cerrar" aria-label="Cerrar">&times;</button>
	</header>
	<div class="otrn-modal-body">
		<p>El puntaje refleja en qué medida un indicador cumple con los requisitos mínimos de sostenibilidad. Estos puntajes varían entre <b>0 y 3 puntos</b>. Para completar esta calificación se requiere:</p>
		<ol class="otrn-modal-lista">
			<li>Identificar en cada ámbito de sostenibilidad todos los indicadores que cuentan con antecedentes oficiales (informes, estudios, estadísticas u otros) de respaldo.</li>
			<li>Marcar la calificación (0, 1, 2 ó 3) que corresponda a cada indicador.</li>
		</ol>
		<p>La calificación final de cada ámbito de sostenibilidad se calcula a partir de la suma ponderada del puntaje de indicadores, dividida por el puntaje global máximo que puede alcanzar cada ámbito. Este puntaje se obtiene multiplicando el número de indicadores por el valor 3. El valor máximo que alcanza un ámbito es de <b>100%</b>, que reflejaría que todos los indicadores registran el máximo puntaje posible. Para cada indicador que obtiene un puntaje de 0 ó 1 se despliega una <b>Buena Práctica</b>.</p>
		<h3>Ámbito de sostenibilidad</h3>
		<p>Los ámbitos de sostenibilidad son: Sociocultural, Económico, Gestión y Medio Ambiente.</p>
		<h3>Criterio de sostenibilidad</h3>
		<p>Corresponde a la denominación empleada para reconocer cada criterio de turismo sostenible. Los criterios están basados en las recomendaciones de diversas instituciones internacionales.</p>
		<h3>Indicador</h3>
		<p>El indicador es una expresión cuantitativa o cualitativa que permite medir comportamientos o fenómenos de la realidad. Se emplea para evaluar el nivel de sostenibilidad turística que alcanza el destino/comuna o territorio evaluado en algún aspecto específico.</p>
	</div>
	<footer class="otrn-modal-f">
		<button type="button" class="otrn-btn" id="otrn-tut-ok">Entendido</button>
	</footer>
</div>

<style>
.otrn-modal-fondo{position:fixed;inset:0;background:rgba(51,51,51,.5);z-index:2000;opacity:0;transition:opacity .2s;}
.otrn-modal-fondo.abierto{opacity:1;}
.otrn-modal{position:fixed;z-index:2001;top:50%;left:50%;transform:translate(-50%,-46%);width:min(720px,92vw);max-height:86vh;display:flex;flex-direction:column;background:var(--paper);border-radius:var(--radius);box-shadow:0 20px 60px rgba(0,0,0,.22);opacity:0;transition:opacity .2s,transform .2s;}
.otrn-modal.abierto{opacity:1;transform:translate(-50%,-50%);}
.otrn-modal-h{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:22px 26px;border-bottom:1px solid var(--grey);}
.otrn-modal-h h2{font-size:20px;font-weight:800;letter-spacing:-0.02em;margin:0;}
.otrn-modal-x{border:none;background:transparent;font-size:26px;line-height:1;color:var(--text3);cursor:pointer;padding:0 4px;}
.otrn-modal-x:hover{color:var(--dark);}
.otrn-modal-body{padding:22px 26px;overflow:auto;}
.otrn-modal-body p{font-size:14px;color:var(--text2);line-height:1.65;margin:0 0 12px;}
.otrn-modal-body h3{font-size:15px;font-weight:800;color:var(--dark);margin:20px 0 6px;}
.otrn-modal-lista{margin:0 0 12px;padding-left:20px;}
.otrn-modal-lista li{font-size:14px;color:var(--text2);line-height:1.6;margin-bottom:8px;}
.otrn-modal-f{padding:16px 26px;border-top:1px solid var(--grey);display:flex;justify-content:flex-end;}
body.otrn-sin-scroll{overflow:hidden;}
/* N/A: oculto por defecto; visible solo cuando el sector elegido no es la isla completa. */
.otrn-na-opt{display:none;}
.otrn-na-on .otrn-na-opt{display:inline-flex;}
</style>

<script>
(function(){
	var KEY   = 'otrn_eval_tutorial_v1';
	var modal = document.getElementById('otrn-tut-modal');
	var fondo = document.getElementById('otrn-tut-fondo');
	var btn   = document.getElementById('otrn-tutorial-abrir');
	var previo = null;

	function abrir(origen){
		previo = origen || document.activeElement;
		fondo.hidden = false;
		modal.hidden = false;
		document.body.classList.add('otrn-sin-scroll');
		void modal.offsetWidth; // reflow para que corra la transición.
		modal.classList.add('abierto');
		fondo.classList.add('abierto');
		document.getElementById('otrn-tut-cerrar').focus();
	}
	function cerrar(){
		modal.classList.remove('abierto');
		fondo.classList.remove('abierto');
		modal.hidden = true;
		fondo.hidden = true;
		document.body.classList.remove('otrn-sin-scroll');
		if (previo && previo.focus) { previo.focus(); previo = null; }
	}

	if (btn) { btn.addEventListener('click', function(){ abrir(btn); }); }
	document.getElementById('otrn-tut-cerrar').addEventListener('click', cerrar);
	document.getElementById('otrn-tut-ok').addEventListener('click', cerrar);
	fondo.addEventListener('click', cerrar);
	document.addEventListener('keydown', function(e){ if (!modal.hidden && e.key === 'Escape') { cerrar(); } });

	// Auto-apertura: solo la primera vez en este navegador; después queda el botón.
	var visto = null;
	try { visto = localStorage.getItem(KEY); } catch (e) {}
	if (!visto) {
		abrir(btn);
		try { localStorage.setItem(KEY, '1'); } catch (e) {}
	}
})();
</script>

<?php
get_footer();
