# Logos institucionales — Etapa 1 (preparación)

Logotipos extraídos de las fotografías de `references/` y normalizados a WebP q92
(fondo conservado, márgenes recortados + 24 px). **Sin modificar aún** `header.php` /
`footer.php` / `style.css`. Los logos Rapa Nui de `assets/footer/` quedan **intactos**.

## Inventario

| Archivo | Institución | Dim. final | Peso | Fuente en `references/` | Calidad |
|---|---|---|---|---|---|
| `municipalidad-arauco.webp` | Municipalidad de Arauco (escudo + texto) | 1513×586 | 59 KB | `19.14.15` (1600×900) | ✅ Alta |
| `oficina-turismo-arauco.webp` | Oficina de Turismo · Municipalidad de Arauco | 1289×456 | 52 KB | `19.14.31` (1600×537) | ✅ Alta |
| `gore-region-biobio.webp` | Gobierno Regional · Región del Bío-Bío | 160×283 | 14 KB | `19.14.06` (160×283) | ⚠️ Baja — requiere original HD |

- `22.04.40` (1600×900) es una **tira de footer** con las 4 instituciones en orden
  (GORE, Municipalidad, Oficina de Turismo, RAU · Consultores en Turismo), pero con
  logotipos en baja resolución: **no recomendable** para uso individual.
- **RAU · Consultores en Turismo**: solo aparece embebido en `22.04.40` (baja res).
  No se generó archivo independiente → **requiere original** del cliente.

## Fondo y decisión 3 (footer blanco continuo)

- `gore-region-biobio.webp`: fondo blanco/near-white (`#FFFBF9`) → se integra bien
  sobre un footer blanco.
- `municipalidad-arauco.webp` y `oficina-turismo-arauco.webp`: **fondo gris interior
  ≈ `#ECECEC` opaco**. Sobre un footer blanco puro dejaría un rectángulo gris.

  Opciones para la Etapa 2 (elegir 1):
  1. **PNG transparentes** de los 2 logos (recomendado: pedir original a la municipalidad).
  2. **Quitar fondo gris** por umbral de color (aproximado; riesgo en el contorno del escudo).
  3. **Franja gris ligera** (`#ECECEC`) para el footer en lugar de blanco puro.

## Pendiente del cliente (no se descarga sin autorización)

- [ ] **GORE Región del Bío-Bío** en alta resolución (PNG/SVG transparente).
- [x] **RAU · Consultores en Turismo** — resuelto el 2026-10-18: el cliente señaló el archivo que ya
      estaba en el repositorio, `assets/footer/logo-rau.png` (193×120, PNG con alfa), y el pie lo usa
      como cuarto logo institucional. Sigue pendiente una versión en alta resolución si se quiere
      ampliar.
- [ ] Confirmar si **Municipalidad** y **Oficina de Turismo** se usan con fondo gris,
      se pasan a transparente, o se usa franja gris en el footer.
