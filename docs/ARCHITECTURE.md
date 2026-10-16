# Architecture

Static description of the site as of commit `5f43933` (+ the working-tree deltas in
[HANDOFF.md](HANDOFF.md)). Nothing here was runtime-verified unless stated. Paths are
relative to the repository root.

## 1. Runtime stack

- **WordPress Studio** manages the local site and the PHP environment (`STUDIO.md`).
  WordPress core is present locally but **not versioned** in this repository.
- **Database**: SQLite, via the drop-in `wp-content/db.php` (drop-in version `1.8.0`,
  `wp-content/db.php:12`). The drop-in locates `mu-plugins/sqlite-database-integration/`
  and requires its implementation (`wp-content/db.php:15-27`). The database file is
  `wp-content/database/.ht.sqlite` (≈1.25 MB at audit, gitignored; not read).
- **Plugins present locally**: Advanced Custom Fields `6.8.0`, Akismet `5.7.2`,
  `hello.php`. Only ACF is part of the project (see repo root `README.md`).
- **Themes**: `wp-content/themes/otrn` (child) and its parent `twentytwentyfive`;
  `twentytwentyfour`/`twentytwentythree` also installed. Only `otrn` is versioned.
- `wp-config.php` exists locally with real secrets and is **not versioned**.

## 2. Repository layout (tracked code)

```
wp-content/
├─ themes/otrn/                  # all custom frontend code (child theme)
│  ├─ style.css                  # base tokens + components (Spanish comments)
│  ├─ functions.php              # enqueues, helpers, guards, redirects
│  ├─ header.php / footer.php    # classic chrome for the PHP templates
│  ├─ front-page.php             # immersive landing (scrollytelling)
│  ├─ page-*.php                 # one template per key slug (see §4)
│  ├─ archive-*.php, single-*.php, taxonomy-*.php
│  ├─ guias-contenido.php        # user-guide copy + block renderer (new 2026-10-16)
│  ├─ template-parts/            # shared partials (map, cards, chart)
│  ├─ embeds/encuesta-percepcion-rapanui.html   # static dashboard (unreferenced)
│  └─ assets/                    # css/ js fonts/ logos/ img/ vendor/ guias/ (see §7)
└─ mu-plugins/
   ├─ 99-studio-loader.php       # Studio-managed (machine-specific, see HANDOFF)
   ├─ otrn-contenido.php         # CPTs: indicador, buena_practica, dato_portada, material_apoyo
   ├─ otrn-autodiagnostico.php   # CPTs: criterio_autodiag, evaluacion, sector + handler
   ├─ otrn-guias.php             # CPT: guia
   ├─ otrn-iniciativa-pladetur.php  # CPT: iniciativa_pladetur + taxonomía lineamiento
   ├─ otrn-privacidad-seccion.php   # anonymous REST/search/oEmbed/sitemap closure (private section)
   ├─ otrn-privacidad-guias.php  # same closure for the guia surfaces (new 2026-10-16)
   └─ sqlite-database-integration/  # SQLite implementation (loaded by db.php)
```

Tracked files at audit: **792**.

## 3. Boot & load order

1. `wp-config.php` (local) → `wp-content/db.php` drop-in → SQLite implementation.
2. `wp-content/mu-plugins/99-studio-loader.php` runs first (alphabetical); it defines DB
   constants if missing, sets `WP_ENVIRONMENT_TYPE=local`, and loads Studio's temporary
   mu-plugins from a machine-specific tmp path (see HANDOFF — uncommitted change).
3. The `otrn-*` mu-plugins register CPTs, taxonomies, seeds and handlers on `init`
   (seed hooks run at priority 20).
4. The `otrn` child theme provides PHP templates; routes without a matching PHP template
   fall back to the parent block theme's templates (architectural nuance, see
   [ISSUES.md](ISSUES.md) F16 — runtime-confirmed 2026-10-15, [VERIFICATION.md](VERIFICATION.md) §V9).

## 4. Theme & templates

`wp-content/themes/otrn/style.css:2-8`: *Theme Name "Observatorio Turístico Rapa Nui"*
(still Rapa Nui — F7), `Template: twentytwentyfive`, version `0.2.0`. There is **no
`theme.json`** and no `index.php`/`page.php`/`search.php`/`404.php` in the child theme.

| Template | Route / slug | Purpose | Access |
| --- | --- | --- | --- |
| `front-page.php` | `/` | Immersive 5-scene landing; CMS-driven data cards and index | Public |
| `page-acerca.php` | `/acerca/` | Institutional page; body comes from post content | Public |
| `page-noticias.php` | `/noticias/` | News listing (`post` type) | Public |
| `page-estadisticas.php` | `/estadisticas/` | Pladetur dashboard aggregated by year | Public |
| `page-geoportal.php` | `/geoportal/` | Embeds external ArcGIS StoryMaps viewer | Public |
| `page-evaluar.php` | `/evaluar/` | Autodiagnóstico form (74 criteria, 0/1/2/3/NA, drafts) | **Private** |
| `page-mapa.php` | `/mapa/` | Retired: redirects to `/autoevaluacion/#mapa` (`functions.php:232`) | **Private** |
| `archive-evaluacion.php` | `/autoevaluacion/` | Map + table of evaluations (shared partial) | **Private** |
| `single-evaluacion.php` | `/autoevaluacion/<slug>` | Evaluation results | **Private** |
| `archive-indicador.php`, `single-indicador.php` | `/indicadores/` | Indicator listing and detail (Excel drawer) | Public |
| `archive-buena_practica.php`, `single-buena_practica.php` | `/buenas-practicas/` | Good practices | Public |
| `archive-material_apoyo.php`, `single-material_apoyo.php`, `taxonomy-tipo_material.php` | `/material-de-apoyo/`, `/material/<term>/` | Support material by type | Public |
| `archive-guia.php`, `single-guia.php` | `/guias/` | User guide (help centre; 10 topics from `guias-contenido.php` + appendix) | **Private (session)** |
| `archive-iniciativa_pladetur.php`, `single-iniciativa_pladetur.php` | `/seguimiento-pladetur/` | Pladetur initiatives | Public |
| `single-post.php` | news detail | News article | Public |
| `template-parts/mapa-evaluaciones.php`, `material-lista.php`, `noticia-card.php`, `pladetur/chart-anual.php` | — | Shared partials | — |

- `header.php` renders the Arauco chrome: brand logo (`assets/logos/oficina-turismo-arauco.webp`),
  a **hardcoded nav list** (not a WP menu), a login/logout button and the mobile burger
  button. The private "Evaluación de sostenibilidad" link only shows to logged-in users
  (`header.php:36-38`).
- `footer.php` renders three institutional logos and the copyright line (`footer.php:10-27`).
- Templates must exist as **pages with the slugs above** in the database; the templates
  are matched by the `page-<slug>.php` convention, not by a `Template Name` header
  (no template headers exist). See F18.
- Private gating: `otrn_seccion_autoevaluacion_privada()` (`functions.php:250-265`)
  redirects anonymous users to login for the evaluation archive/single, criterion single
  and `/evaluar/`, `/mapa/`. Draft evaluations redirect to their continuation URL
  (`functions.php:277`). The user guide has its own gate with the same shape but
  **session-only** semantics: `otrn_guia_usuario_privada()` (priority 1 on
  `template_redirect`) covers the `guia` archive, its singles and its feed, and
  `mu-plugins/otrn-privacidad-guias.php` covers REST, search, oEmbed and the sitemap (§5,
  [VERIFICATION.md](VERIFICATION.md) §V24).

### Enqueues (`otrn_enqueue_assets()`, `functions.php:345-458`)

| Handle | Asset | Scope | Notes |
| --- | --- | --- | --- |
| `otrn-fonts` | `assets/css/fonts.css` | All pages | Self-hosted woff2; version = filemtime |
| `otrn-style` | `style.css` | All pages | Version = filemtime |
| `leaflet` | unpkg CDN 1.9.4 | `is_post_type_archive('evaluacion')` or `is_page('mapa')` | Third-party CDN (F9 area); no SRI |
| `otrn-xlsx` | `assets/vendor/xlsx.full.min.js` | Single indicator, only when meta `fuente_datos = Excel` | Vendored SheetJS |
| `otrn-indicador` | `assets/indicador.js` | Single indicator | Drawer + Excel rendering |
| `otrn-menu` | `assets/otrn-menu.js` | All pages | Focus-trap for the burger menu |
| `otrn-guias` | `assets/css/otrn-guias.css` | `is_post_type_archive('guia')` | Session-gated help centre; existing tokens only |
| `otrn-guias` (script) | `assets/otrn-guias.js` | `is_post_type_archive('guia')` | Text filter + current-topic highlight; the page is complete without it |
| `otrn-arauco` | `assets/css/otrn-arauco.css` | **Front page only** | `--aru-*` tokens scoped to `.otrn.home` |
| `otrn-landing` | `assets/css/otrn-landing.css` | **Front page only** | Landing layout + burger/panel CSS (F1) |
| `gsap`, `otrn-scrolltrigger`, `otrn-landing` | `assets/vendor/*`, `assets/otrn-landing.js` | **Front page only** | Version fallbacks "3.12.5" |

Other helpers in `functions.php`: `otrn_cargar_criterios()` (:153), `otrn_noticias_url()` (:301),
`otrn_hero_portada_url()` (:469 — last-resort fallback to the Rapa Nui hero image, unused by
any template), `otrn_mision_portada()` (:492), `otrn_datos_portada()` (:508),
`otrn_material_secciones()` (:579), `otrn_acerca_url()` (:868). Image sizes:
`otrn-noticia` 1120×560, `otrn-noticia-card` 640×400 (`add_image_size` in `otrn_setup_theme()`).

## 5. mu-plugins

| File | Responsibility | Key references |
| --- | --- | --- |
| `99-studio-loader.php` | Studio-managed loader; DB constants, `WP_ENVIRONMENT_TYPE=local`, temp mu-plugins dir (machine-specific) | file head; see HANDOFF |
| `otrn-contenido.php` | CPTs `indicador`, `buena_practica`, `dato_portada`, `material_apoyo`; taxonomies `categoria_indicador`, `tipo_material`, `alcance_material`; admin caps; ACF JSON load path | :19-33, :36-126, :160-167 |
| `otrn-autodiagnostico.php` | CPTs `criterio_autodiag`, `evaluacion`, `sector`; shared taxonomy `ambito`; seeds; performance calculation; evaluation submit/archive handlers; draft-token validation | :25-135, :138-166, :213-250, :381-521 |
| `otrn-guias.php` | CPT `guia` + admin caps | :16-27, :28, :49 |
| `otrn-iniciativa-pladetur.php` | CPT `iniciativa_pladetur` + taxonomy `lineamiento` + seeds + caps | :57-64, :91-100, :119, :169 |
| `otrn-privacidad-seccion.php` | Closes the private section's non-HTML surfaces for anonymous visitors (REST 401, search exclusion, oEmbed off, sitemap entry removed) | file head; F11b |
| `otrn-privacidad-guias.php` | The same closure for the `guia` type, whose HTML gate is session-only | file head; [VERIFICATION.md](VERIFICATION.md) §V24 |

All CPTs and taxonomies register `show_in_rest => true` (potential REST surface — F11/F11b);
the two `otrn-privacidad-*` plugins filter those surfaces for the gated types, and no custom
REST routes are registered.

## 6. Content model

| Post type | Archive / rewrite | Notes |
| --- | --- | --- |
| `indicador` | `/indicadores/` | Sustainability indicators; taxonomy `ambito` + `categoria_indicador` |
| `buena_practica` | `/buenas-practicas/` | Good practices; taxonomy `ambito` |
| `dato_portada` | none | Front-page data cards via `otrn_datos_portada()` |
| `material_apoyo` | `/material-de-apoyo/` | Taxonomies `tipo_material` (rewrite `material`), `alcance_material` |
| `guia` | `/guias/` | Title + editor only |
| `iniciativa_pladetur` | `/seguimiento-pladetur/` | Fields: `codigo_iniciativa`, `monto_total`, `porcentaje_avance`, `asignado_<year>` (2024–2034), `prioridad`… (`functions.php:15-45`) |
| `criterio_autodiag` | single `/criterio/<slug>` | The 74 assessable criteria (number stated in code comments); taxonomy `ambito` |
| `evaluacion` | `/autoevaluacion/` | Saved results (answers in meta `respuestas`); draft state + `borrador_token` |
| `sector` | none (`public => false`) | Evaluation sectors with lat/lng; feeds form + map; **seeded with Rapa Nui data** (F8) |

Taxonomies: `ambito` is **shared** across `criterio_autodiag`, `buena_practica`, `indicador`;
`categoria_indicador` (Oferta/Demanda/Otros/Gobernanza family), `tipo_material`,
`alcance_material`, `lineamiento` (Pladetur). Seeding helpers run on `init` (priority 20)
for `ambito`, `lineamiento` and material terms; `sector` falls back to hardcoded defaults
when the CPT is empty (`otrn-autodiagnostico.php:239-250`).

## 7. Assets & third-party dependencies

| Asset | Location | Notes |
| --- | --- | --- |
| Fonts | `assets/fonts/*.woff2` | Montserrat (400–800) + IBM Plex Mono (400–700), subset latin, `font-display:swap` |
| Landing images | `assets/img/arauco/` | WebP/JPG derived from `references/web/` (EXIF stripped) |
| Institutional logos | `assets/logos/` | Arauco footer/header logos (sourcing gaps — F13) |
| Guide figures | `assets/guias/` | 12 real screenshots of the guide's own views (1618 px wide, DPR 2). They are theme assets, so any anonymous request that knows the URL gets the bytes — which is why the guide deliberately ships **no** figure of the private listing/map ([VERIFICATION.md](VERIFICATION.md) §V24.5) |
| Vendor JS | `assets/vendor/` | GSAP + ScrollTrigger + SheetJS (`xlsx.full.min.js`), self-hosted |
| Leaflet | unpkg CDN | Loaded only for the evaluations archive; no SRI (see SECURITY_AUDIT) |
| StoryMaps | external iframe | Hardcoded URL in `page-geoportal.php:15` (F9) |
| Survey embed | `embeds/encuesta-percepcion-rapanui.html` | Static HTML/SVG dashboard, **not referenced** by any template (F10) |
| Legacy assets | `assets/footer/*`, `assets/hero-rapanui.jpg`, `assets/logo.png` | Not referenced by code (hero image only inside an unused helper) — F12 |

## 8. Data flows

1. **Autodiagnóstico submit** (`otrn-autodiagnostico.php:381-490`): `page-evaluar.php`
   posts to `admin-post.php` with `action=otrn_submit_eval`, nonce `otrn_eval`, answers
   `resp[<code>]`. The handler validates nonce, validates the sector against the canonical
   list (server-side coordinates), validates answers, then creates/updates an `evaluacion`
   (`draft` for "Guardar borrador", published otherwise) and redirects to the results page.
   **No capability check is performed** (F3).
2. **Draft continuation**: a `draft` evaluation is resumed through `?borrador=ID&token=…`;
   the token is compared with `hash_equals()` in `otrn_borrador_valido()`. Drafts redirect
   away from the results template (`functions.php:277`).
3. **Archive evaluation** (`:495-521`): `action=otrn_archivar_eval` with per-post nonce;
   requires `current_user_can('delete_evaluacion', $id)`.
4. **Landing data**: `front-page.php` calls `otrn_datos_portada()` (`dato_portada` CPT) and
   `get_posts()` for up to 6 indicators and 6 news posts (`front-page.php:33-36`).
5. **Pladetur dashboard**: `page-estadisticas.php` + archive/single read `iniciativa_pladetur`
   via pure helpers (`functions.php:15-100`); chart partial in `template-parts/pladetur/`.
6. **Geoportal**: `page-geoportal.php` renders an iframe to an external ArcGIS StoryMaps URL.
7. **News**: standard `post` type; listing on `/noticias/`, detail in `single-post.php`.

## 9. Persistence, configuration & reproducibility

- ACF field groups live **in the database**; the code only registers a JSON load path
  `docs/acf-json/` that does not exist (`otrn-contenido.php:160-167`) — F4.
- Pages, menus, options (`page_on_front`, front-page sections, `mision` page) and all
  content live in the local SQLite database, which is gitignored (F18).
- No custom REST routes are registered (grep), no custom WP-CLI commands, no build step.

## 10. Known architectural constraints

- Landing-only mobile menu CSS (F1) and landing-only token fallback (F6) — both
  runtime-confirmed 2026-10-15.
- `.otrn-reveal` invisible without JavaScript (F5) — runtime-confirmed 2026-10-15.
- Private HTML gating does not extend to REST (F11/F11b) — the anonymous REST exposure is
  confirmed.
- Classic child of a block theme without `theme.json`; fallback routes (search, 404,
  `/mision/`) confirmed without otrn chrome (F16).
- Documentation artifacts referenced by code (`docs/01`–`docs/07`, `docs/design-tokens.md`,
  `scripts/crear-pagina-acerca.php`) are absent (F15); this `docs/` baseline replaces part
  of that gap but not the ACF exports.

See [ISSUES.md](ISSUES.md) for IDs, severity and verification steps.
