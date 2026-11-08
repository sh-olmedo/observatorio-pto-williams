# Observatorio Turístico de Puerto Williams (clon provisional)

Repositorio del proyecto **Observatorio Turístico de Puerto Williams**: clon provisional
del sitio **Observatorio Turístico de Arauco** (que a su vez hereda y reencuadra la
estructura editorial S0–S6 del Observatorio Turístico de Rapa Nui). La identidad visible
está rebrandada provisionariamente hacia **Puerto Williams / Municipalidad de Cabo de
Hornos y Antártica**; los logos, las fotos, la paleta y los contenidos (cifras, sectores)
siguen siendo herencia del proyecto original y quedan pendientes de material y decisiones
del cliente. Inventario del clon: [`docs/PW_CLONE.md`](docs/PW_CLONE.md).

Gestionado localmente con
[WordPress Studio](https://developer.wordpress.com/studio/).

## Alcance del repositorio

Este repo contiene **solo el proyecto** (no el núcleo de WordPress). El núcleo,
los temas por defecto, la base de datos y los secretos **no** se versionan:

| Sí se versiona | No se versiona |
| --- | --- |
| Tema `otrn` (vistas, `style.css`, `template-parts/`, `assets/` optimizados) | Núcleo WP (`wp-admin/`, `wp-includes/`, `wp-*.php`) |
| `mu-plugins/` necesarios (studio-loader, `otrn-*`, SQLite integration) | Temas/plugins por defecto (`twentytwenty*`, akismet, hello) |
| Plugin **Advanced Custom Fields** | Base de datos / SQLite (`wp-content/database/`, `*.sqlite`, `*.db`) |
| `wp-content/uploads/` (media y contenido demo) | Credenciales: `wp-config.php`, `.env` |
| `references/web/` (recursos optimizados sin EXIF) | Originals con metadatos (EXIF/GPS): `references/*.jpeg` |
| Documentación (`STUDIO.md`, `AGENTS.md`, `CLAUDE.md`, READMEs de `assets/`) | Cachés, logs, temporales, artefactos de SO/IDE |

## Estructura

```
otrn (wp-content/themes/otrn)
├─ assets/
│  ├─ fonts/      # Montserrat + IBM Plex Mono (woff2), self-hosted
│  ├─ logos/      # GORE Biobío, Municipalidad de Arauco, Oficina de Turismo
│  ├─ footer/     # logos institucionales del pie
│  └─ vendor/     # dependencias JS (p. ej. xlsx)
├─ style.css, header.php, footer.php, front-page.php, page-*.php, archive-*.php, single-*.php
mu-plugins/
├─ 99-studio-loader.php, otrn-*.php, sqlite-database-integration/
wp-config-sample.php   # plantilla (los valores reales van en wp-config.php, no versionado)
references/web/        # imágenes optimizadas a WebP/JPG sin EXIF/GPS
```

## Puesta en marcha

1. Abre el proyecto en **WordPress Studio** (o descarga el núcleo WP 6.x al
   mismo nivel). Studio regenera `wp-admin/`, `wp-includes/` y la DB SQLite.
2. Copia `wp-config-sample.php` a `wp-config.php` y ajusta credenciales locales.
3. Activa el tema `otrn` (tema hijo de *Twenty Twenty-Five*).
4. Asegúrate de que el mu-plugin `sqlite-database-integration/` esté presente.

> **Seguridad:** `wp-config.php` y `wp-content/database/` están en `.gitignore`.
> Nunca subas credenciales ni la base de datos.
