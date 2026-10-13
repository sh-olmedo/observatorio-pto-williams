/**
 * Detalle de indicador: colapsar/expandir el drawer lateral de la ficha,
 * y renderizar el documento Excel (si lo hay) como tabla vía SheetJS.
 * Ver wp-content/themes/otrn/single-indicador.php y docs/02 §5.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	var detalle = document.querySelector( '.otrn-ind-detalle' );
	if ( ! detalle ) {
		return;
	}

	var handler = detalle.querySelector( '.otrn-ind-handler' );
	var cerrar  = detalle.querySelector( '.otrn-ind-close' );

	function alternar() {
		var oculta = detalle.classList.toggle( 'ficha-oculta' );
		handler.setAttribute( 'aria-expanded', oculta ? 'false' : 'true' );
		// Al reabrir, devolver el foco al botón de cerrar para no perderlo.
		( oculta ? handler : cerrar ).focus();
	}

	cerrar.addEventListener( 'click', alternar );
	handler.addEventListener( 'click', alternar );

	otrnRenderExcel();
} );

function otrnRenderExcel() {
	var contenedor = document.getElementById( 'otrn-ind-excel' );
	if ( ! contenedor ) {
		return;
	}
	var url = contenedor.getAttribute( 'data-excel-url' );
	if ( ! url || typeof XLSX === 'undefined' ) {
		return;
	}

	fetch( url )
		.then( function ( res ) {
			if ( ! res.ok ) {
				throw new Error( 'HTTP ' + res.status );
			}
			return res.arrayBuffer();
		} )
		.then( function ( buffer ) {
			var libro       = XLSX.read( buffer, { type: 'array' } );
			var nombreHoja  = libro.SheetNames[0];
			var hoja        = libro.Sheets[ nombreHoja ];
			contenedor.innerHTML = XLSX.utils.sheet_to_html( hoja, { id: 'otrn-ind-excel-tabla' } );
		} )
		.catch( function () {
			contenedor.innerHTML = '<p class="otrn-ind-nota">No se pudo cargar el documento. Usa el enlace de descarga.</p>';
		} );
}
