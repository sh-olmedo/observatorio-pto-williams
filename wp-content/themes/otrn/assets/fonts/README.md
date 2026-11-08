# Fuentes autoalojadas — Etapa 1 (preparación)

Fuentes **woff2** (subset `latin`), descargadas desde Google Fonts y alojadas en el
tema para eliminar la dependencia de `fonts.googleapis.com`. **Sin modificar aún**
`functions.php`/`style.css`: este archivo es solo inventario para la Etapa 2.

| Archivo | Familia | Peso | Uso |
|---|---|---|---|
| `montserrat-latin-400.woff2` | Montserrat | 400 | Cuerpo / texto |
| `montserrat-latin-500.woff2` | Montserrat | 500 | Énfasis suave || `montserrat-latin-600.woff2` | Montserrat | 600 | Subtítulos |
| `montserrat-latin-700.woff2` | Montserrat | 700 | Encabezados |
| `montserrat-latin-800.woff2` | Montserrat | 800 | Títulos H1/H2 (800) |
| `ibm-plex-mono-latin-400.woff2` | IBM Plex Mono | 400 | Cifras / datos |
| `ibm-plex-mono-latin-500.woff2` | IBM Plex Mono | 500 | Cifras |
| `ibm-plex-mono-latin-600.woff2` | IBM Plex Mono | 600 | Cifras destacadas |
| `ibm-plex-mono-latin-700.woff2` | IBM Plex Mono | 700 | Cifras titulares |

> Montserrat 400–800 = pesos de tipografía principal (decisión 1).
> IBM Plex Mono 400–700 = cifras, indicadores y módulos de datos (se conserva la
> identidad "observatorio"). Sin Poppins por ahora.
> Tamaño total: ~180 KB (9 archivos), cada woff2 14–19 KB.

## 2026-11-08 · Reddit Sans (familia variable) — tipografía del proyecto Cabo de Hornos

El cliente entregó la lista de la familia completa **Reddit Sans** (16 estilos, TTF en
`references/font/`) como fuente institucional del proyecto, aunque los binarios llegaron
como HTML inválido (descarga de OneDrive mal guardada). Al ser tipografía libre **SIL OFL**,
se instaló la copia idéntica publicada en el repositorio `google/fonts` de GitHub:

| Archivo | Familia | Rango | Uso |
|---|---|---|---|
| `redditsans/RedditSans-wght.ttf` | Reddit Sans | variable 200–900 | Títulos y cuerpo (`--font-title`) |
| `redditsans/RedditSans-Italic-wght.ttf` | Reddit Sans Italic | variable 200–900 | Itálicas |
| `redditsans/OFL.txt` | — | — | Licencia |

Declaradas en `assets/css/fonts.css`; Montserrat queda como respaldo en la cadena
`--font-title`. Pendiente: cuando el cliente confirme/renueve los binarios enviados,
comparar los pesos disponibles (su lista incluye ExtraLight y Black).

## Reglas `@font-face` para la Etapa 2 (referencia, no aplicadas)

```css
@font-face{font-family:'Montserrat';font-style:normal;font-weight:400;font-display:swap;
  src:url('assets/fonts/montserrat-latin-400.woff2') format('woff2');}
@font-face{font-family:'Montserrat';font-style:normal;font-weight:500;font-display:swap;
  src:url('assets/fonts/montserrat-latin-500.woff2') format('woff2');}
@font-face{font-family:'Montserrat';font-style:normal;font-weight:600;font-display:swap;
  src:url('assets/fonts/montserrat-latin-600.woff2') format('woff2');}
@font-face{font-family:'Montserrat';font-style:normal;font-weight:700;font-display:swap;
  src:url('assets/fonts/montserrat-latin-700.woff2') format('woff2');}
@font-face{font-family:'Montserrat';font-style:normal;font-weight:800;font-display:swap;
  src:url('assets/fonts/montserrat-latin-800.woff2') format('woff2');}
@font-face{font-family:'IBM Plex Mono';font-style:normal;font-weight:400;font-display:swap;
  src:url('assets/fonts/ibm-plex-mono-latin-400.woff2') format('woff2');}
@font-face{font-family:'IBM Plex Mono';font-style:normal;font-weight:500;font-display:swap;
  src:url('assets/fonts/ibm-plex-mono-latin-500.woff2') format('woff2');}
@font-face{font-family:'IBM Plex Mono';font-style:normal;font-weight:600;font-display:swap;
  src:url('assets/fonts/ibm-plex-mono-latin-600.woff2') format('woff2');}
@font-face{font-family:'IBM Plex Mono';font-style:normal;font-weight:700;font-display:swap;
  src:url('assets/fonts/ibm-plex-mono-latin-700.woff2') format('woff2');}
```

Reemplazo en la Etapa 2:
- `style.css` línea 24/25: `font-family:'Manrope'` → `'Montserrat'` (cuerpo y H1/H2/H3).
- `functions.php` línea ~347: retira el `link` de `fonts.googleapis.com` y encola
  `@font-face` locales (o un `fonts.css` dedicado).
- `.otrn .mono` / `.otrn-stat .n`: siguen en `'IBM Plex Mono'`.
