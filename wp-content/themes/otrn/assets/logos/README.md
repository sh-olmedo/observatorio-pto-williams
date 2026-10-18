# Logos institucionales — assets del tema

Archivos que consumen `header.php` y `footer.php`. Se generaron el
**2026-10-18** desde los originales del cliente que viven en
`references/logos/` (ver ahí la procedencia y los md5), y **todos llevan alfa
real**: por eso el pie ya no necesita «chapas» blancas detrás de cada logo.

Fuentes anteriores (fotografías de `references/web/`) quedaron **superadas** para
los logos del tema: `19.14.06`, `19.14.15` y `19.14.31` siguen sirviendo como
referencia visual, pero los assets de abajo provienen de los originales.

## Inventario

| Archivo | Institución | Dim. | Peso | Origen | Alfa | Calidad |
|---|---|---|---|---|---|---|
| `municipalidad-arauco.webp` | Municipalidad de Arauco (escudo + texto) | 800×309 | 32 KB | `references/logos/municipalidad-arauco.jpg` (5333×3000) | ✅ | ✅ Alta |
| `oficina-turismo-arauco.webp` | Oficina de Turismo · Municipalidad de Arauco | 800×284 | 35 KB | `references/logos/oficina-turismo-arauco-color.png` (2550×857) | ✅ | ✅ Alta |
| `gore-region-biobio.webp` | Gobierno Regional · Región del Bío-Bío | 160×283 | 16 KB | `references/logos/gore-region-biobio.png` (160×283) | ✅ | ⚠️ Baja — falta original HD (F13) |

- **Cuarto logo del pie**: `assets/footer/logo-rau.png` (193×120, PNG con alfa,
  14 KB), un archivo que ya estaba en el repositorio. No se regeneró.
- **Cabecera**: usa `oficina-turismo-arauco.webp` como logo de marca
  (`header.php`), a 46 px de alto.

## Cómo se obtuvieron (sin editar los originales)

1. **Recorte del fondo blanco.** Municipalidad y Oficina de Turismo vienen con
   fondo blanco plano pero **sin alfa**, así que el fondo se quita por color con
   un **relleno desde el borde** (`min(R,G,B) >= 240`): solo desaparece el blanco
   *conectado con el borde*, de modo que los blancos interiores del arte (la
   mitad blanca del escudo, los contrafuertes de las letras) se conservan.
2. **GORE: recorte suave.** Su arte toca los cuatro bordes de la imagen, así que
   el relleno no puede avanzar entre las letras; ahí se aplica una **transición
   lineal** (`min(canal) <= 238` opaco → `>= 250` transparente) que limpia el
   fondo sin dejar rectángulo.
3. **Márgenes**: se recorta al contenido opaco y se dejan **24 px** de margen
   (misma convención que la etapa anterior, para que el tamaño aparente no
   cambie).
4. **Salida**: WebP **q92 con alfa sin pérdida**; el ancho se limita a 800 px
   (el pie pinta ~124 px) y el GORE queda en su tamaño original por falta de
   fuente mejor.

Guardas de comprobación: las esquinas de los tres archivos quedan con alfa 0 y
las imágenes se pintan con el borde del arte legible (sin halo ni caja blanca)
sobre el fondo real del pie (`--cream: #F8F8F8`).

## Caché

Los assets estáticos se sirven con `max-age=604800` (7 días). Como estos archivos
**conservan el nombre** pero cambiaron de contenido, las plantillas los emiten
con `otrn_asset()` (`functions.php`), que añade `?v=<fecha del archivo>` y evita
que un navegador siga mostrando la versión anterior. Cualquier reemplazo futuro
de un logo debería usar la misma función.

## Pendiente del cliente

- [ ] **GORE Región del Bío-Bío** en alta resolución (PNG con alfa o SVG). El
      cliente pidió no tocarlo por ahora; el archivo disponible sigue siendo
      160×283.
- [ ] ¿Se usa también la **Oficina de Turismo negra** (`references/logos/
      oficina-turismo-arauco-negro.png`, la única con alfa original) en algún
      lugar del sitio? Hoy no se usa.
