# Features

Inventory of functional modules as of commit `5f43933` (+ working-tree deltas), updated after
the **read-only runtime pass of 2026-10-15** ([VERIFICATION.md](VERIFICATION.md)). Status
vocabulary:

- **Implemented (static)** — code present and coherent on read; runtime unverified.
- **Implemented, defective (F#)** — code present with a known static defect.
- **Provisional** — works but inherits Rapa Nui data/copy; migration pending.
- **Decision pending** — asset/feature exists but its fate is a client decision.
- **Unverified** — depends on data or runtime behavior that could not be inspected.

## Summary

| # | Module | Entry points | Status |
| --- | --- | --- | --- |
| 1 | Immersive landing (scrollytelling) | `/` (`front-page.php`) | Implemented; data provisional; **F5 confirmed at runtime**; pin/scrub + CLS unverified |
| 2 | Site chrome (header/footer/nav/login/burger) | all classic templates | Implemented, **defective — F1 confirmed at runtime (≤600 px, internal *and* front page)** |
| 3 | Autodiagnóstico: form + drafts | `/evaluar/` (private) | Implemented, defective (F3: no capability check) — 1 account today; sectors: CPT seeded with Rapa Nui data (F8) |
| 4 | Autodiagnóstico: archive/map + results | `/autoevaluacion/`, single (private) | Implemented; **HTML gate PASS at runtime**; 6 evaluations (3 published) |
| 5 | Indicators + Excel drawer | `/indicadores/` | Implemented (static); Excel path unverified |
| 6 | Good practices | `/buenas-practicas/` | Implemented, provisional copy (F7) |
| 7 | Support material | `/material-de-apoyo/`, `/material/<term>/` | Implemented, provisional copy (F7) |
| 8 | User guides | `/guias/` | Implemented, provisional copy (F7) |
| 9 | Pladetur follow-up + statistics | `/seguimiento-pladetur/`, `/estadisticas/` | Implemented, provisional copy (F7); data unverified |
| 10 | News | `/noticias/`, `single-post.php` | Implemented, provisional copy (F7) |
| 11 | Geoportal | `/geoportal/` | Implemented; external target decision pending (F9) |
| 12 | About page | `/acerca/` | Implemented (static) |
| 13 | Front-page data cards | `/` via `dato_portada` CPT | Implemented; data provisional |
| 14 | Survey dashboard embed | none (orphan file) | Decision pending (F10) |
| 15 | Admin capabilities seeding | wp-admin | Implemented; runtime roles = WP defaults only (no "Operador") |
| 16 | Private-section gating | `template_redirect` | HTML gate PASS; **REST exposure confirmed (F11b)** |

## 1. Immersive landing — `front-page.php`

- Five scenes: hero (vertical estuary photo), territory (bay), living coast (boats),
  data, exploration (`front-page.php:1-25` comment; markup with `data-scene` 1–5).
- Dual mode: **static** (default; stacked scenes) vs **immersive** (`.is-immersiva`,
  desktop + GSAP): shared pin + `scrub:1` crossfade timeline (`assets/otrn-landing.js:75-160`).
- Fallbacks in code: no JS / `prefers-reduced-motion` / ≤768 px → static mode; if GSAP is
  missing, IntersectionObserver still reveals content (`assets/otrn-landing.js:9-17, 37-44,
  52-54`).
- CMS data: 3 `dato_portada` cards, up to 6 indicators, up to 6 news posts
  (`front-page.php:33-36`); approved photos from `assets/img/arauco/`.
- Provisional banner: figures inherited from Rapa Nui are explicitly labeled
  (`front-page.php:140-142`).
- Verification status: runtime pass 2026-10-15 confirmed the no-JS defect (9/9 reveals stay
  hidden; 3/9 appear with JS in the control) and the mobile-menu behavior (see §2); the
  GSAP pin/scrub motion itself and CLS remain **NOT VERIFIED** (F17).

## 2. Site chrome — `header.php` / `footer.php` / `assets/otrn-menu.js`

- Header: brand logo, hardcoded nav (Inicio, Acerca de, Indicadores, Buenas prácticas,
  [Evaluación de sostenibilidad — logged-in only], Planificación, Geoportal, Noticias,
  Guía de usuario, dynamic Material links), login/logout button, burger button.
- Menu JS: aria state, Escape/click-outside close, focus trap (`assets/otrn-menu.js:1-28`).
- Defect (F1, **runtime-confirmed 2026-10-15**): at ≤600 px the nav stays `display:none` on
  internal pages **and** on the front page; burger clicks toggle state (`aria-expanded`,
  `otrn-menu-abierto`) but the nav is 0×0. At 601–768 px the internal nav stays visible
  (cramped) and the front-page panel works. Static cause: burger/panel CSS exists only in
  the front-page-only landing stylesheet.

## 3. Autodiagnóstico form — `page-evaluar.php` + `otrn-autodiagnostico.php`

- 74 criteria grouped in 4 `ambito`s (Sociocultural, Económico, Gestión, Medio Ambiente),
  scale 0/1/2/3/NA with help tooltips; server-side validation of answers and sector.
- "Guardar borrador" creates a `draft` + token link; localStorage autosave as backup
  (`page-evaluar.php:92-95, 179-256`).
- Sector list comes from the `sector` CPT with Rapa Nui fallback (F8).
- Security gap: nonce-only handler, no capability check (F3).

## 4. Autodiagnóstico archive + results — `archive-evaluacion.php`, `single-evaluacion.php`

- Single view: map (Leaflet + OSM from CDN) + table of evaluations, sharing
  `template-parts/mapa-evaluaciones.php`; `/mapa/` redirects here (`functions.php:232`).
- Results page shows computed % by ámbito (`otrn_calcular_desempeno`) and per-criterion
  answers; a demo banner marks sample content (`single-evaluacion.php:36`, provisional).
- Private: login required (`functions.php:250-265`); draft results redirect to continuation.
- Data (2026-10-15): 6 evaluations (3 published, 3 draft); anonymous HTML routes redirect to
  login (PASS); **REST exposes the published items** (F11b — [VERIFICATION.md](VERIFICATION.md) §V5).

## 5. Indicators — archives/single + `assets/indicador.js`

- Listing with category/ámbito context; detail includes a drawer and, when
  `fuente_datos = Excel`, in-browser rendering of the sheet via vendored SheetJS.
- Public. Table/detail content comes from the DB (unverified).

## 6–8. Good practices, support material, guides

- Standard archive/single pairs on the `otrn-*` mu-plugin CPTs; material has two taxonomies
  and a `taxonomy-tipo_material.php` listing; the header nav builds one entry per material
  section via `otrn_material_secciones()` (`functions.php:579`).
- All three still carry Rapa Nui intro copy on their archives (F7; see
  [ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md) for the exact lines).

## 9. Pladetur follow-up + statistics

- `iniciativa_pladetur` CPT with investment years 2024–2034, per-year amounts, priority and
  progress; pure read helpers (`functions.php:15-100`); archive with filters, dashboard page
  aggregated by year (`page-estadisticas.php`) and a chart partial
  (`template-parts/pladetur/chart-anual.php`).
- Copy and data mention Rapa Nui (F7); totals/filters depend on DB data (unverified).

## 10. News

- Standard `post` type: listing page `/noticias/`, card partial, detail in `single-post.php`
  with custom image sizes (`otrn-noticia`, `otrn-noticia-card`). Intro copy is Rapa Nui (F7).

## 11. Geoportal — `page-geoportal.php`

- Full-page embed of an external ArcGIS StoryMaps viewer with a fallback link; URL hardcoded
  at `page-geoportal.php:15` (`d414dbcd4d3743de835b15745f5bd060`). Whether the story is
  Arauco-appropriate is **unverified** (F9).

## 12. About — `page-acerca.php`

- Content fully editorial (`the_excerpt()` / `the_content()`); template matched by slug
  `/acerca/`. Static-verified; requires the page to exist in the DB (F18).

## 13. Front-page data cards — `otrn_datos_portada()`

- Reads up to 3 published `dato_portada` posts (number, label, icon mapping in PHP).
  Figures currently inherited from Rapa Nui and labeled provisional (F7).

## 14. Survey dashboard embed — `embeds/encuesta-percepcion-rapanui.html`

- Static HTML/SVG dashboard titled "Dashboard Percepción de Rapa Nui"
  (`embeds/encuesta-percepcion-rapanui.html:6`); not referenced by any template (grep).
  Fate is a client decision: reuse (re-scoped), replace, or delete (F10).

## 15. Admin capabilities seeding

- Each mu-plugin grants the `administrator` role the capabilities of its CPTs on
  `admin_init` (`otrn-contenido.php:~171+`, `otrn-guias.php:49`,
  `otrn-iniciativa-pladetur.php:169`, `otrn-autodiagnostico.php:559`).
  Specialized roles mentioned in comments (e.g. "Operador", "Editor de Seguimiento
  Pladetur") are referenced but **not created in code** — role setup lives outside the repo.
  Runtime (2026-10-15): only the six WordPress default roles exist; 1 user account.

## 16. Private-section gating

- `otrn_seccion_autoevaluacion_privada()` redirects anonymous users for the evaluation
  archive/single, criterion single, `/evaluar/` and `/mapa/` (`functions.php:250-265`).
- Scope limitation: it is a `template_redirect` gate only — it does not cover
  `admin-post.php` handlers or REST requests (F11/F11b). Runtime 2026-10-15: every gated
  HTML route redirects to login (PASS); the REST exposure is **confirmed**
  ([VERIFICATION.md](VERIFICATION.md) §V5).
