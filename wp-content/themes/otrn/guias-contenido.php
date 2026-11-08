<?php
/**
 * Contenido del centro de ayuda de la guía de usuario (`/guias/`).
 *
 * El texto de la guía vive aquí, en el tema, y no en la base de datos: así se
 * actualiza sin tocar la plantilla ni la maqueta (`archive-guia.php`) y sin
 * crear registros. Cada tema de la lista es una sección de la página con su
 * ancla, y su cuerpo es una lista de bloques que `otrn_guia_bloques()` sabe
 * pintar. Para añadir un tema basta con copiar un bloque `array( … )` de la
 * lista; los bloques disponibles están descritos en `otrn_guia_bloques()`.
 *
 * Reglas de redacción (acordadas con el cliente en la ronda de la guía):
 * los nombres entre «» son los textos que el usuario ve en pantalla, letra por
 * letra; no se describen funciones que el sitio no tenga y los datos que
 * cambian con el tiempo (cantidades) no se escriben en el texto.
 *
 * @package otrn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Temas del centro de ayuda, en el orden de lectura.
 *
 * Claves de cada tema:
 *   - `id`:      ancla de la sección (`#id`) y clave de la búsqueda.
 *   - `titulo`:  nombre del tema en el índice y en la cabecera de la sección.
 *   - `resumen`: una línea que describe el tema (índice, buscador y listado).
 *   - `privado`: si el tema trata una parte que exige sesión iniciada.
 *   - `bloques`: cuerpo del tema, ver `otrn_guia_bloques()`.
 *
 * @return array<int,array<string,mixed>> Temas en orden.
 */
function otrn_guia_temas() {
	$temas = array(

		/* ---------------------------------------------------------------- 1 */
		array(
			'id'      => 'primeros-pasos',
			'titulo'  => 'Primeros pasos',
			'resumen' => 'Qué encuentras en el Observatorio y cómo está ordenado el sitio.',
			'bloques' => array(
				array(
					'tipo'  => 'parrafo',
					'texto' => 'El Observatorio Turístico de Puerto Williams reúne en un solo sitio los datos, las buenas prácticas y las herramientas de planificación del turismo del territorio. Esta guía explica, paso a paso, cómo usar cada sección con lo que el sitio ofrece hoy.',
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Cómo está ordenado el sitio',
				),
				array(
					'tipo'       => 'tabla',
					'cabeceras'  => array( 'Sección del menú', 'Para qué sirve' ),
					'filas'      => array(
						array( 'Inicio', 'La portada: el relato del territorio, las cifras destacadas y los accesos a cada sección.' ),
						array( 'Observatorio → Acerca de', 'Qué es el Observatorio, con qué metodología trabaja y quién lo mantiene.' ),
						array( 'Observatorio → Noticias', 'Publicaciones y novedades del Observatorio.' ),
						array( 'Datos → Indicadores', 'Ficha y evolución de cada indicador, con su fuente.' ),
						array( 'Datos → Geoportal', 'Visor cartográfico externo con información del territorio.' ),
						array( 'Buenas prácticas', 'Iniciativas sostenibles que se pueden replicar.' ),
						array( 'Planificación → Iniciativas Pladetur', 'Estado, inversión y prioridad de las iniciativas del plan.' ),
						array( 'Planificación → Estadísticas', 'Los mismos datos del Pladetur, agregados por año.' ),
						array( 'Material de apoyo → Guía de usuario', 'Este centro de ayuda. Requiere sesión iniciada.' ),
						array( 'Material de apoyo → Documentación técnica y Reportes', 'Documentos y reportes para descargar.' ),
						array( 'Autoevaluación', 'Autodiagnóstico de sostenibilidad y resultados. Requiere sesión iniciada.' ),
					),
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Qué es público y qué no',
				),
				array(
					'tipo'  => 'parrafo',
					'texto' => 'Casi todo el sitio es público: para consultar indicadores, buenas prácticas, planificación o material de apoyo no hace falta ninguna cuenta. Solo piden iniciar sesión dos partes:',
				),
				array(
					'tipo'  => 'lista',
					'items' => array(
						'Esta <strong>Guía de usuario</strong>.',
						'La sección <strong>Autoevaluación</strong>: el listado de autoevaluaciones, su mapa, el formulario para realizar una y sus resultados.',
					),
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Si abres una página privada sin sesión, el sitio te lleva a la pantalla de acceso y, después de entrar, te devuelve solo a la página que habías pedido. No se pierde el destino.',
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'navegacion-escritorio.webp',
					'alt'   => 'Cabecera del sitio con el menú principal: Inicio, Observatorio, Datos, Buenas prácticas, Planificación, Material de apoyo y, a la derecha, el icono de cuenta.',
					'pie'    => 'El menú principal, con el icono de cuenta en el extremo derecho.',
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Cómo leer esta guía',
				),
				array(
					'tipo'  => 'pasos',
					'items' => array(
						'Recorre los temas en el índice de la izquierda (o en el desplegable «Temas» si estás en el teléfono). El tema que estás leyendo queda marcado.',
						'Si buscas algo concreto, escribe una palabra en «Buscar en la guía»: se quedan a la vista solo los temas que la mencionan.',
						'Al final de cada tema tienes los enlaces al tema anterior y al siguiente, y un enlace para volver arriba.',
					),
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'guia-escritorio.webp',
					'alt'   => 'La propia guía de usuario: a la izquierda el buscador y el índice numerado de los diez temas, con el tema en lectura resaltado, y a la derecha el artículo con su título, su resumen y su contenido.',
					'pie'    => 'Esta misma página: índice de temas a la izquierda y artículo a la derecha.',
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Los nombres entre comillas son los textos que verás en pantalla. Cuando dos elementos se llaman parecido — por ejemplo «Autoevaluación» (la sección del menú) y «Autoevaluaciones» (el listado) — la cita te dice exactamente cuál es el que tienes delante.',
				),
			),
		),

		/* ---------------------------------------------------------------- 2 */
		array(
			'id'      => 'navegacion-cuenta',
			'titulo'  => 'Navegación y cuenta',
			'resumen' => 'Cómo moverte por el menú y cómo entrar y salir de tu cuenta.',
			'bloques' => array(
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'El menú principal',
				),
				array(
					'tipo'  => 'parrafo',
					'texto' => 'El menú está en la cabecera y se adapta al ancho de la pantalla:',
				),
				array(
					'tipo'  => 'lista',
					'items' => array(
						'<strong>En un computador:</strong> una fila con «Inicio» y los grupos «Observatorio», «Datos», «Buenas prácticas», «Planificación» y «Material de apoyo». Cada grupo abre su lista de enlaces.',
						'<strong>En una pantalla intermedia:</strong> los grupos que no caben se juntan en «Más».',
						'<strong>En un teléfono:</strong> el botón de tres líneas (la hamburguesa) abre el panel con toda la navegación.',
					),
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'navegacion-movil.webp',
					'alt'   => 'Vista de teléfono con el panel de navegación desplegado: Inicio, y los grupos Observatorio, Datos, Buenas prácticas, Planificación, Material de apoyo y Autoevaluación.',
					'pie'    => 'En el teléfono, la hamburguesa abre el panel completo.',
				),
				array(
					'tipo'  => 'nota',
					'texto' => '«Autoevaluación» y «Guía de usuario» solo aparecen en el menú con la sesión iniciada. Si no las ves, es que no has entrado con tu cuenta.',
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Entrar a tu cuenta',
				),
				array(
					'tipo'  => 'pasos',
					'items' => array(
						'Pulsa el icono de cuenta (la silueta de persona) en el extremo derecho de la cabecera, o abre directamente una página privada.',
						'En la pantalla de acceso, escribe tu nombre de usuario o tu correo electrónico y tu contraseña.',
						'Pulsa el botón de acceso. Volverás a la página que habías pedido; si entraste desde el icono de cuenta, llegarás a la portada.',
					),
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'La pantalla de acceso no lleva el diseño del Observatorio: es la pantalla propia de WordPress. Sus botones y enlaces están en italiano (<em>«Accedi»</em>, <em>«Ricordami»</em>, <em>«Password dimenticata?»</em>) porque el idioma del sitio está configurado así; el contenido del Observatorio sí se escribe en español. En «Preguntas frecuentes» tienes el detalle.',
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Salir de la cuenta',
				),
				array(
					'tipo'  => 'pasos',
					'items' => array(
						'Pulsa el icono de cuenta.',
						'En el panel que se abre elige «Cerrar sesión».',
					),
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'cuenta-sesion.webp',
					'alt'   => 'Panel de la cuenta desplegado bajo el icono de persona, con la opción «Cerrar sesión».',
					'pie'    => 'El panel de la cuenta: se abre desde el icono de persona y es donde se cierra la sesión.',
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'La sesión vive en el navegador que usas. Si cambias de navegador o de dispositivo, o si borras los datos del navegador, tendrás que volver a entrar.',
				),
			),
		),

		/* ---------------------------------------------------------------- 3 */
		array(
			'id'      => 'indicadores',
			'titulo'  => 'Explorar los indicadores',
			'resumen' => 'Buscar y filtrar el listado de indicadores del Observatorio.',
			'bloques' => array(
				array(
					'tipo'  => 'parrafo',
					'texto' => 'La sección «Datos → Indicadores» es el catálogo de indicadores del Observatorio. Cada indicador es una tarjeta con sus etiquetas, su nombre y un resumen de su descripción; al final de la tarjeta, «Ver evolución →» abre su ficha.',
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Los filtros del listado',
				),
				array(
					'tipo'  => 'claves',
					'items' => array(
						array( 'Categoría', 'El grupo temático del indicador. «Todas las categorías» no filtra.' ),
						array( 'Ámbito', 'El ámbito de sostenibilidad del indicador. «Todos los ámbitos» no filtra.' ),
						array( 'Nivel territorial', 'Local, Regional, Nacional o Internacional. «Todos los niveles» no filtra.' ),
						array( 'Buscar', 'Busca por el nombre del indicador (campo «Nombre del indicador»).' ),
					),
				),
				array(
					'tipo'  => 'pasos',
					'items' => array(
						'Elige lo que quieras en «Categoría», «Ámbito» o «Nivel territorial», o escribe un nombre en «Buscar».',
						'Pulsa «Filtrar». El listado se recarga solo con lo que coincide.',
						'Para volver al listado completo, usa «Limpiar». Ese botón aparece únicamente cuando tienes algún filtro puesto.',
					),
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'El filtro «Nivel territorial» llega puesto en «Local» (el nivel «Comunal» del sistema), así que al abrir el listado no ves todos los indicadores. Cambia a «Todos los niveles» para recorrerlos completos.',
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'indicadores-listado.webp',
					'alt'   => 'Listado de indicadores con la barra de filtros arriba (Categoría, Ámbito, Nivel territorial, Buscar y el botón Filtrar) y las tarjetas de resultados debajo.',
					'pie'    => 'El listado de indicadores con sus filtros y sus tarjetas.',
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Los filtros van escritos en la dirección de la página, así que puedes guardarla en favoritos o enviarla por correo: quien la abra verá el mismo listado filtrado.',
				),
			),
		),

		/* ---------------------------------------------------------------- 4 */
		array(
			'id'      => 'visores',
			'titulo'  => 'Gráficos, filtros y vistas de datos',
			'resumen' => 'Leer la ficha de un indicador: su panel de detalle y su visor de datos.',
			'bloques' => array(
				array(
					'tipo'  => 'parrafo',
					'texto' => 'La ficha de un indicador tiene dos zonas: el panel «Detalle indicador» y el visor con los datos. En pantalla de computador el panel va al costado; en pantalla pequeña se abre encima del visor.',
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'El panel «Detalle indicador»',
				),
				array(
					'tipo'  => 'parrafo',
					'texto' => 'Se abre y se cierra con el tirador «Detalle indicador →» y con la equis (×) de su cabecera. Dentro encontrarás, cuando el indicador los tenga:',
				),
				array(
					'tipo'  => 'lista',
					'items' => array(
						'<strong>Descripción</strong>: qué mide el indicador.',
						'<strong>Fórmula</strong>: cómo se calcula.',
						'<strong>Nivel territorial</strong>: Local, Regional, Nacional o Internacional.',
						'<strong>Institución responsable</strong>: quién produce el dato.',
						'<strong>«Sitio web ↗»</strong>: enlace a la fuente original, en una pestaña nueva.',
					),
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Qué puedes encontrarte en el visor',
				),
				array(
					'tipo'      => 'tabla',
					'cabeceras' => array( 'Vista', 'Qué muestra' ),
					'filas'     => array(
						array( 'Visor externo', 'El tablero de la fuente original, incrustado en la página. Debajo aparece el aviso «Visor externo. Si no carga, ábrelo en una pestaña nueva ↗».' ),
						array( 'Visor externo bloqueado', 'Si el sitio de origen no permite mostrarse dentro de otra página, en su lugar verás el aviso «Este visor externo no permite mostrarse dentro de otras páginas» y un botón para abrirlo en una pestaña nueva.' ),
						array( 'Documento PDF', 'El documento se muestra dentro de la página, con su enlace de respaldo para abrirlo en una pestaña nueva.' ),
						array( 'Gráfico de línea', 'El título «Evolución temporal», la línea con un punto por fecha, las fechas bajo el gráfico y el «Último valor (fecha)».' ),
						array( 'Planilla de cálculo', 'Cuando un indicador trae una planilla, se muestra como tabla con los valores (sin fórmulas ni formatos) y con su enlace de descarga.' ),
						array( 'Sin datos cargados', 'El aviso «Sin serie de datos ni visor cargado».' ),
					),
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Si el indicador tiene una «Ficha metodológica», aparece en un panel debajo del visor, también con su enlace de respaldo.',
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'indicador-detalle.webp',
					'alt'   => 'Ficha de un indicador: a la izquierda el panel Detalle indicador con la descripción, la fórmula y la institución responsable, y a la derecha el gráfico de evolución temporal.',
					'pie'    => 'La ficha de un indicador: panel de detalle a la izquierda, visor de datos a la derecha.',
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Los visores son de terceros: el Observatorio los incrusta tal como los publica su fuente. Si uno se comporta de forma rara, abre el enlace de respaldo y consulta el original.',
				),
			),
		),

		/* ---------------------------------------------------------------- 5 */
		array(
			'id'      => 'geoportal',
			'titulo'  => 'Usar el geoportal',
			'resumen' => 'Cómo recorrer el visor cartográfico del Observatorio.',
			'bloques' => array(
				array(
					'tipo'  => 'parrafo',
					'texto' => 'El «Geoportal» (sección «Datos → Geoportal») es un visor cartográfico externo que se muestra dentro de la página del Observatorio: ocupa el ancho completo y se recorre con el desplazamiento hacia arriba y hacia abajo como el resto del sitio.',
				),
				array(
					'tipo'  => 'pasos',
					'items' => array(
						'Desplázate por el visor para recorrer sus secciones (mapas, capas y textos).',
						'Usa los controles del propio visor para acercar, alejar y mover el mapa: el zoom con los botones funciona con el clic, y en el mapa también puedes usar la rueda del ratón o los dedos.',
						'Si tienes el visor en pantalla completa, el botón de pantalla completa está entre sus propios controles.',
						'Si el visor no carga, usa el enlace «una pestaña nueva ↗» que aparece justo debajo.',
					),
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'El contenido, los colores y los botones del visor los define la plataforma externa que lo publica; no son parte del diseño del Observatorio.',
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'geoportal.webp',
					'alt'   => 'Página del geoportal con el visor externo ocupando el ancho completo y el enlace de respaldo debajo.',
					'pie'    => 'La página del geoportal: el visor externo ocupa todo el ancho.',
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Si trabajas con datos móviles o una red lenta, el visor puede tardar en aparecer: es contenido pesado que se descarga de otro servidor.',
				),
			),
		),

		/* ---------------------------------------------------------------- 6 */
		array(
			'id'      => 'buenas-practicas',
			'titulo'  => 'Consultar buenas prácticas',
			'resumen' => 'Buscar experiencias sostenibles y leer su ficha.',
			'bloques' => array(
				array(
					'tipo'  => 'parrafo',
					'texto' => '«Buenas prácticas» reúne iniciativas de la municipalidad, instituciones, comunidad y operadores. En el listado, cada tarjeta lleva su etiqueta de ámbito, su nombre, un resumen y el enlace «Ver práctica →».',
				),
				array(
					'tipo'  => 'pasos',
					'items' => array(
						'Filtra por «Ámbito» o busca por nombre en el campo «Nombre de la práctica».',
						'Pulsa «Filtrar» para quedarte solo con lo que coincide, o «Limpiar» para volver al listado completo.',
						'Pulsa «Ver práctica →» en la tarjeta que te interese.',
					),
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'La ficha de una buena práctica',
				),
				array(
					'tipo'  => 'lista',
					'items' => array(
						'Una cita destacada con la descripción de la práctica.',
						'Tres recuadros: <strong>Ámbito</strong>, <strong>Fuente de la información</strong> y <strong>Ubicación</strong>.',
						'El <strong>Nivel territorial</strong> de la práctica.',
						'El botón <strong>«Sitio web ↗»</strong>, cuando la práctica tiene un sitio propio.',
					),
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Algunas fichas avisan de que sus documentos adjuntos están aún por cargar: en esos casos la ficha no ofrece archivos. Es información pendiente del equipo del Observatorio, no un error tuyo.',
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'buenas-practicas.webp',
					'alt'   => 'Listado de buenas prácticas con el filtro por ámbito, el buscador por nombre y las tarjetas de resultados.',
					'pie'    => 'El listado de buenas prácticas con su filtro y sus tarjetas.',
				),
			),
		),

		/* ---------------------------------------------------------------- 7 */
		array(
			'id'      => 'pladetur',
			'titulo'  => 'Planificación y seguimiento Pladetur',
			'resumen' => 'Seguir las iniciativas del plan y ver sus estadísticas por año.',
			'bloques' => array(
				array(
					'tipo'  => 'parrafo',
					'texto' => 'La sección «Planificación» mira los mismos datos de dos maneras: «Iniciativas Pladetur» los ordena por lineamiento y «Estadísticas» los agrupa por año de asignación. Las dos vistas se filtran y recalculan sus totales con lo que queda a la vista.',
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Iniciativas Pladetur («Planificación → Iniciativas Pladetur»)',
				),
				array(
					'tipo'  => 'pasos',
					'items' => array(
						'Filtra por «Lineamiento» y, si buscas algo concreto, escribe en «Buscar» el nombre o el código de la iniciativa.',
						'Pulsa «Filtrar». Para dejarlo como estaba, «Limpiar»; para saltar a la vista por año, «Ver estadísticas».',
						'Revisa los cuatro recuadros de cifras: <strong>Iniciativas</strong>, <strong>Inversión total ($MM)</strong>, <strong>Iniciativas prioritarias</strong> y el de la inversión del año en curso («Inversión <em>año</em> ($MM)»).',
						'Compara lineamientos en «Inversión por lineamiento» (cada barra indica el monto en millones de pesos y cuántas iniciativas agrupa) y la planificación anual en «Inversión planificada por año».',
						'Baja a la tabla para el detalle: <strong>Iniciativa</strong>, <strong>Lineamiento</strong>, <strong>Duración</strong>, <strong>Total $MM</strong>, <strong>Asig. (año) $MM</strong> y <strong>% Avance</strong>. El enlace «Ver más →» abre la ficha de la iniciativa.',
						'Debajo de la tabla, la línea «Mostrando … de … iniciativas» te dice cuántas estás viendo del total.',
					),
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Los montos van en millones de pesos ($MM). Cuando una iniciativa no tiene avance informado, su celda muestra «s/d». Lo que filtres se refleja también en las cifras y los gráficos de arriba.',
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'pladetur-seguimiento.webp',
					'alt'   => 'Vista de seguimiento del Pladetur con los filtros, los cuatro recuadros de cifras, el gráfico de inversión por lineamiento y la tabla de iniciativas.',
					'pie'    => 'El seguimiento del Pladetur: cifras, gráficos y tabla.',
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Estadísticas Pladetur («Planificación → Estadísticas»)',
				),
				array(
					'tipo'  => 'pasos',
					'items' => array(
						'Elige el «Año» (o «Todos los años») y pulsa «Filtrar».',
						'Lee el total del período y las tarjetas por lineamiento, con el número de iniciativas y su monto.',
						'Mira la «Distribución por prioridad»: la barra y su leyenda separan las iniciativas prioritarias de las que no lo son.',
						'En «Inversión planificada por año», el año que filtraste queda resaltado.',
						'Usa la tabla y su paginación para recorrer las iniciativas una por una; esta tabla añade la columna <strong>Prioridad</strong>.',
						'Para volver al seguimiento, usa el enlace «← Volver a Planificación».',
					),
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'El año reservado para cierres futuros aún no tiene datos y no aparece entre las opciones del filtro.',
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'estadisticas-pladetur.webp',
					'alt'   => 'Página de estadísticas del Pladetur con el filtro por año, las tarjetas por lineamiento, la distribución por prioridad y la tabla paginada.',
					'pie'    => 'Las estadísticas del Pladetur: la misma información, agrupada por año.',
				),
			),
		),

		/* ---------------------------------------------------------------- 8 */
		array(
			'id'      => 'autoevaluacion',
			'titulo'  => 'Completar una autoevaluación',
			'resumen' => 'Cómo registrar el autodiagnóstico de sostenibilidad de un sector o de todo el destino.',
			'privado' => true,
			'bloques' => array(
				array(
					'tipo'  => 'nota',
					'texto' => 'Esta sección pide sesión iniciada, igual que esta guía. Sin sesión se ve el acceso y, después de entrar, vuelves a la página que pedías. El listado muestra las autoevaluaciones publicadas; los borradores solo los ve quien los gestiona.',
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Antes de empezar',
				),
				array(
					'tipo'  => 'parrafo',
					'texto' => 'Una autoevaluación se responde con dos cosas a mano: el <strong>sector</strong> que estás evaluando y el <strong>puntaje</strong> de cada indicador. Al abrir el formulario, la primera vez en cada navegador, se abre la ventana «Cómo completar la evaluación» con los criterios de puntuación. Si la cierras, puedes volver a abrirla con el botón «Cómo completar la evaluación» de la cabecera.',
				),
				array(
					'tipo'  => 'claves',
					'items' => array(
						array( 'Ámbito', 'El gran tema de sostenibilidad que se evalúa: Sociocultural, Económico, Gestión y Medio Ambiente.' ),
						array( 'Criterio', 'El grupo de indicadores dentro de un ámbito. Cada criterio agrupa varios indicadores parecidos.' ),
						array( 'Indicador', 'La pregunta concreta, con su código. Cada una se puntúa de 0 a 3.' ),
					),
				),
				array(
					'tipo'  => 'pasos',
					'items' => array(
						'Entra a «Autoevaluación» en el menú y pulsa «+ Nueva autoevaluación» (o «Nueva autoevaluación» en el menú).',
						'Elige el «Sector evaluado». Es obligatorio para poder calcular los resultados.',
						'Responde los indicadores: cada uno muestra su código y su enunciado, y al lado las opciones 0, 1, 2 y 3.',
						'Sigue tu avance en la barra de abajo, que va contando «X / 74 respondidos».',
						'Guarda con «Guardar borrador» mientras trabajas, o pulsa «Calcular resultados →» cuando esté completo.',
					),
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Elegir el sector',
				),
				array(
					'tipo'  => 'parrafo',
					'texto' => 'El sector es la parte del territorio que evalúas, incluida la opción de evaluarlo completo. La diferencia importa:',
				),
				array(
					'tipo'  => 'lista',
					'items' => array(
						'Al evaluar un <strong>sector</strong>, puedes marcar <strong>N/A</strong> en los indicadores que no apliquen a esa parte del territorio.',
						'Al evaluar el <strong>destino completo</strong>, se responde solo de 0 a 3: no aparece el N/A.',
					),
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Responder los indicadores',
				),
				array(
					'tipo'  => 'lista',
					'items' => array(
						'Las preguntas van agrupadas por ámbito y, dentro de cada ámbito, por criterio.',
						'Cada opción (0, 1, 2, 3 o N/A) tiene una explicación: pasa el cursor sobre la letra para leerla.',
						'El botón «Ver detalle» abre un panel con la explicación larga del criterio. También puedes responder desde ahí, y se cierra con «Cerrar».',
					),
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Guardar, retomar y calcular',
				),
				array(
					'tipo'  => 'claves',
					'items' => array(
						array( 'Guardar borrador', 'Guarda el avance sin publicarlo y te entrega un enlace para continuar más tarde o desde otro dispositivo. Guarda ese enlace: es la llave del borrador.' ),
						array( 'Calcular resultados →', 'Se activa cuando respondiste los 74 indicadores y elegiste el sector. Calcula y publica la autoevaluación con sus resultados.' ),
						array( 'Copia en el navegador', 'Mientras respondes, el navegador guarda una copia. Si cierras la pestaña sin guardar, al volver el sitio te ofrece «Restaurar» o «Descartar» esas respuestas.' ),
					),
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'autoevaluacion-formulario.webp',
					'alt'   => 'Formulario de autoevaluación: selector de sector, indicadores agrupados por ámbito con las opciones 0 a 3 y la barra inferior con el avance y los botones Guardar borrador y Calcular resultados.',
					'pie'    => 'El formulario de autoevaluación, con la barra de avance al pie.',
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Si vuelves más tarde por un borrador, la página te recuerda la fecha en que lo guardaste y sigue donde lo dejaste.',
				),
				array(
					'tipo'  => 'subtitulo',
					'texto' => 'Resultados, listado y mapa',
				),
				array(
					'tipo'  => 'lista',
					'items' => array(
						'La ficha de resultados muestra el puntaje global y el de cada ámbito, además de las respuestas por criterio. Si es una evaluación de ejemplo, lo anuncia con su aviso.',
						'El listado («Autoevaluaciones registradas») permite filtrar por <strong>Sector evaluado</strong> y por rango de fechas, y abrir cada ficha con «Ver más →».',
						'«Dónde se ha evaluado» es el mapa: cada punto es una autoevaluación y su color indica el tramo — <strong>Bajo 0–33%</strong>, <strong>Medio 34–66%</strong> y <strong>Bueno 67–100%</strong>. Al pulsar un recuadro de arriba, el mapa viaja a ese punto; si en un mismo lugar hay varias evaluaciones, el marcador muestra cuántas y el promedio.',
						'«Mapa» en el menú lleva directo a esa parte del listado.',
					),
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Por privacidad, esta guía no incluye capturas del listado ni del mapa de autoevaluaciones: son vistas con resultados del territorio. Para verlas, abre la sección con tu sesión iniciada.',
				),
			),
		),

		/* ---------------------------------------------------------------- 9 */
		array(
			'id'      => 'material',
			'titulo'  => 'Reportes y documentación',
			'resumen' => 'Descargar documentos técnicos y reportes del Observatorio.',
			'bloques' => array(
				array(
					'tipo'  => 'parrafo',
					'texto' => '«Material de apoyo» tiene dos listados: «Documentación técnica» (guías, manuales y metodologías) y «Reportes» (boletines y resultados publicados). Se llega a ellos desde el menú y desde el grupo «Material de apoyo».',
				),
				array(
					'tipo'  => 'pasos',
					'items' => array(
						'Elige «Documentación» o «Reportes» en el menú, dentro de «Material de apoyo».',
						'Si buscas un documento concreto, escríbelo en «Buscar» (campo «Nombre del material») y pulsa «Buscar».',
						'En cada fila, usa «Descargar» para bajarte el archivo o «Ver sitio ↗» para abrir el enlace externo del material.',
					),
				),
				array(
					'tipo'  => 'nota',
					'texto' => 'Cada fila lleva su etiqueta de alcance y, cuando no hay archivo ni enlace, la advertencia «sin archivo»: en ese caso el material existe en el catálogo, pero todavía no tiene documento cargado. «Limpiar» devuelve el listado completo y aparece solo cuando has buscado algo.',
				),
				array(
					'tipo'  => 'captura',
					'img'   => 'material-apoyo.webp',
					'alt'   => 'Listado de material de apoyo con el buscador por nombre, la etiqueta de alcance de cada documento y los enlaces Descargar y Ver sitio.',
					'pie'    => 'El material de apoyo: una fila por documento, con su descarga o su enlace.',
				),
			),
		),

		/* --------------------------------------------------------------- 10 */
		array(
			'id'      => 'preguntas-frecuentes',
			'titulo'  => 'Preguntas frecuentes',
			'resumen' => 'Dudas habituales al usar el Observatorio.',
			'bloques' => array(
				array(
					'tipo'      => 'preguntas',
					'items'     => array(
						array(
							'q' => '¿Por qué la Autoevaluación me pide iniciar sesión?',
							'a' => 'Porque forma parte de la sección privada del Observatorio: guarda los diagnósticos del territorio y sus borradores. Si necesitas acceso, pide una cuenta al equipo del Observatorio; no hay registro público.',
						),
						array(
							'q' => 'Abrí un enlace y me llevó a la pantalla de acceso. ¿Pierdo el enlace?',
							'a' => 'No. Después de entrar, el sitio te devuelve a la misma página que habías pedido. Vuelve a intentarlo desde el enlace original si cerraste el navegador.',
						),
						array(
							'q' => 'Veo muy pocos indicadores en el listado.',
							'a' => 'Es el filtro por defecto: «Nivel territorial» viene en «Local». Cambia a «Todos los niveles» y pulsa «Filtrar» para ver el catálogo completo.',
						),
						array(
							'q' => 'El gráfico o el visor de un indicador no se ve.',
							'a' => 'Los visores son de terceros y algunos no permiten mostrarse dentro de otra página. Usa el enlace que aparece bajo el visor («una pestaña nueva ↗») para abrirlo en su sitio original.',
						),
						array(
							'q' => '¿Puedo descargar los datos?',
							'a' => 'Sí: en «Material de apoyo» los documentos llevan su enlace de descarga, y cuando un indicador trae planilla o documento, el propio visor ofrece su enlace de descarga o de apertura.',
						),
						array(
							'q' => 'La pantalla de acceso aparece en otro idioma.',
							'a' => 'El sitio está configurado con el italiano como idioma de su interfaz, así que la pantalla de acceso y el panel de administración se ven en italiano («Accedi», «Ricordami», «Password dimenticata?»). El contenido del Observatorio se escribe en español. La pantalla de acceso permite cambiar el idioma a inglés con su selector, pero el español todavía no está instalado: está pendiente de corregirse.',
						),
						array(
							'q' => 'Me devolvió a la pantalla de acceso sin haber cerrado sesión.',
							'a' => 'La sesión se guarda en el navegador. Si cambias de navegador o de dispositivo, borras los datos del navegador o la sesión caduca, tendrás que entrar otra vez. Para cerrar sesión a propósito, usa «Cerrar sesión» en el panel de la cuenta.',
						),
						array(
							'q' => 'La portada dice que las cifras son provisionales.',
							'a' => 'El Observatorio está migrando su base de datos al territorio de Cabo de Hornos y Antártica, y lo avisa en la portada. Mientras esa carga no termine, las cifras corresponden a la base anterior; el propio sitio avisará cuando se actualicen.',
						),
						array(
							'q' => 'No encuentro lo que busco en esta guía.',
							'a' => 'Escribe la palabra clave en «Buscar en la guía»: la búsqueda revisa los títulos y el texto de todos los temas. Si aun así no aparece, es probable que el sitio todavía no tenga esa función; el equipo del Observatorio puede confirmártelo.',
						),
					),
				),
			),
		),
	);

	/**
	 * Permite ajustar los temas del centro de ayuda sin editar este archivo.
	 *
	 * @param array<int,array<string,mixed>> $temas Temas en orden.
	 */
	return (array) apply_filters( 'otrn_guia_temas', $temas );
}

/**
 * Guías publicadas por el equipo desde el panel de WordPress (CPT `guia`).
 *
 * El centro de ayuda es fijo, pero el CPT sigue disponible para colgar guías
 * sueltas sin tocar el tema: lo que se publique aparece al final de la página,
 * bajo «Guías publicadas por el equipo». Si no hay ninguna publicada, el bloque
 * no se muestra.
 *
 * @return array<int,array<string,string>> Guías con `titulo`, `resumen` y `url`.
 */
function otrn_guia_anexos() {
	$posts = get_posts(
		array(
			'post_type'      => 'guia',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	$anexos = array();
	foreach ( $posts as $post ) {
		$resumen  = trim( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
		$anexos[] = array(
			'titulo'  => get_the_title( $post ),
			'resumen' => $resumen ? wp_trim_words( $resumen, 26 ) : '',
			'url'     => get_permalink( $post ),
		);
	}
	return $anexos;
}

/**
 * Pinta el cuerpo de un tema del centro de ayuda.
 *
 * Bloques disponibles (clave `tipo`):
 *   - `parrafo`   · `texto`
 *   - `subtitulo` · `texto`
 *   - `lista`     · `items[]`  (viñetas)
 *   - `pasos`     · `items[]`  (lista numerada)
 *   - `claves`    · `items[]{clave,valor}`
 *   - `preguntas` · `items[]{q,a}`
 *   - `nota`      · `texto`
 *   - `enlaces`   · `items[]{etiqueta,ruta|url}`
 *   - `tabla`     · `cabeceras[]`, `filas[][]`
 *   - `captura`   · `img`, `alt`, `pie`
 *
 * El texto admite las etiquetas en línea que `wp_kses_post()` permite (para
 * poder usar <strong> o <em> en la redacción); todo lo demás se escapa.
 *
 * @param array<int,array<string,mixed>> $bloques Bloques del tema.
 */
function otrn_guia_bloques( $bloques ) {
	foreach ( $bloques as $bloque ) {
		$tipo = isset( $bloque['tipo'] ) ? $bloque['tipo'] : '';

		switch ( $tipo ) {

			case 'parrafo':
				echo '<p class="otrn-guia-p">' . wp_kses_post( $bloque['texto'] ) . '</p>';
				break;

			case 'subtitulo':
				echo '<h3 class="otrn-guia-h">' . wp_kses_post( $bloque['texto'] ) . '</h3>';
				break;

			case 'lista':
				echo '<ul class="otrn-guia-lista">';
				foreach ( $bloque['items'] as $item ) {
					echo '<li>' . wp_kses_post( $item ) . '</li>';
				}
				echo '</ul>';
				break;

			case 'pasos':
				echo '<ol class="otrn-guia-pasos">';
				foreach ( $bloque['items'] as $item ) {
					echo '<li>' . wp_kses_post( $item ) . '</li>';
				}
				echo '</ol>';
				break;

			case 'claves':
				echo '<dl class="otrn-guia-claves">';
				foreach ( $bloque['items'] as $item ) {
					echo '<dt>' . esc_html( $item[0] ) . '</dt><dd>' . wp_kses_post( $item[1] ) . '</dd>';
				}
				echo '</dl>';
				break;

			case 'preguntas':
				echo '<dl class="otrn-guia-faq">';
				foreach ( $bloque['items'] as $item ) {
					echo '<dt>' . esc_html( $item['q'] ) . '</dt>';
					echo '<dd>' . wp_kses_post( $item['a'] ) . '</dd>';
				}
				echo '</dl>';
				break;

			case 'nota':
				echo '<p class="otrn-guia-nota">' . wp_kses_post( $bloque['texto'] ) . '</p>';
				break;

			case 'enlaces':
				echo '<ul class="otrn-guia-enlaces">';
				foreach ( $bloque['items'] as $item ) {
					$externo = isset( $item['url'] );
					$href    = $externo ? $item['url'] : home_url( $item['ruta'] );
					printf(
						'<li><a href="%s"%s>%s</a></li>',
						esc_url( $href ),
						$externo ? ' target="_blank" rel="noopener"' : '',
						esc_html( $item['etiqueta'] )
					);
				}
				echo '</ul>';
				break;

			case 'tabla':
				echo '<div class="otrn-guia-tablawrap"><table class="otrn-guia-tabla"><thead><tr>';
				foreach ( $bloque['cabeceras'] as $th ) {
					echo '<th scope="col">' . esc_html( $th ) . '</th>';
				}
				echo '</tr></thead><tbody>';
				foreach ( $bloque['filas'] as $fila ) {
					echo '<tr>';
					foreach ( $fila as $i => $celda ) {
						$tag = 0 === $i ? 'th' : 'td';
						$sc  = 0 === $i ? ' scope="row"' : '';
						echo '<' . $tag . $sc . '>' . wp_kses_post( $celda ) . '</' . $tag . '>';
					}
					echo '</tr>';
				}
				echo '</tbody></table></div>';
				break;

			case 'captura':
				$ruta = get_theme_file_path( 'assets/guias/' . $bloque['img'] );
				if ( ! file_exists( $ruta ) ) {
					// Sin archivo no se inventa la imagen: se deja la limitación a
					// la vista y se documenta en docs/VERIFICATION.md.
					echo '<p class="otrn-guia-nota">Captura pendiente: <span class="mono">' . esc_html( $bloque['img'] ) . '</span>.</p>';
					break;
				}
				$size = wp_getimagesize( $ruta );
				$url  = get_theme_file_uri( 'assets/guias/' . $bloque['img'] );
				echo '<figure class="otrn-guia-fig">';
				printf(
					'<img src="%s" alt="%s" loading="lazy" decoding="async"%s%s>',
					esc_url( $url ),
					esc_attr( $bloque['alt'] ),
					$size ? ' width="' . (int) $size[0] . '"' : '',
					$size ? ' height="' . (int) $size[1] . '"' : ''
				);
				if ( ! empty( $bloque['pie'] ) ) {
					echo '<figcaption>' . wp_kses_post( $bloque['pie'] ) . '</figcaption>';
				}
				echo '</figure>';
				break;
		}
	}
}
