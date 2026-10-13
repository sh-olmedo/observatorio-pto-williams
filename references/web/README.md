# Fotografías optimizadas — Etapa 1 (preparación)

Copias web de las 10 imágenes de `references/` (originales **intactos**). Generadas
con `sharp` q82, **EXIF/GPS eliminados** (6 de 10 traían metadatos de WhatsApp).
`webp` (recomendado) + `jpg` (compatibilidad). Dimensiones conservadas (ninguna supera
1600 px en el lado mayor).

| Base | Dim. | WebP | JPEG | Contenido | Rol sugerido |
|---|---|---|---|---|---|
| `22.09.17` | 900×1180 | 87 KB | 117 KB | Bahía aérea + pueblo | **Hero vertical** (dec. 4) |
| `22.08.51` | 900×1180 | 180 KB | 176 KB | Estuario / boca de río | Narrativa territorial |
| `22.09.37` | 900×1180 | 232 KB | 220 KB | Bueyes con carreta en playa | Pesca artesanal (identidad) |
| `22.17.07` | 1600×1200 | 168 KB | 218 KB | Lanchas en muelle al atardecer | **Escena de lanchas** |
| `22.17.19` | 640×481 | 52 KB | 58 KB | Muelle con lanchas (menor) | Variante / miniatura |
| `22.18.13` | 1280×960 | 178 KB | 203 KB | Lanchas en muelle, primer plano | Variante lanchas |
| `19.14.06` | 160×283 | 9 KB | 12 KB | Emblema GORE Bío-Bío | → `assets/logos/` |
| `19.14.15` | 1600×900 | 39 KB | 65 KB | Logo Municipalidad de Arauco | → `assets/logos/` |
| `19.14.31` | 1600×537 | 33 KB | 58 KB | Logo Oficina de Turismo | → `assets/logos/` |
| `22.04.40` | 1600×900 | 16 KB | 20 KB | Tira de 4 logos institucionales | Referencia (baja res) |

- **Total**: originales 1730 KB → **WebP 997 KB (−42%)** / JPEG-moz 1152 KB (−33%).
- **Hero**: `22.09.17` a 900×1180 es la resolución de fuente; en pantallas 4K conviene
  pedir una versión de mayor resolución. No se escala para no degradar.
- No se tocan `front-page.php`, `style.css` ni `indicador.js`; este es solo inventario.
