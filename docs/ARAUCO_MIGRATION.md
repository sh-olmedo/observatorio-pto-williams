# Arauco Migration — Rapa Nui → Arauco Inventory

Status of the rebrand/re-scope of the original **Observatorio Turístico de Rapa Nui** project
into the **Observatorio Turístico de Arauco**. Static analysis (commit `5f43933` + working
tree) plus the read-only runtime pass of 2026-10-15 ([VERIFICATION.md](VERIFICATION.md)).
The original Rapa Nui project is a separate deliverable: **do not modify it
from this repository**.

Legend: ✅ Completed (static) · 🟡 Provisional · ⏳ Pending · ❓ Unverified · 🧭 Decision pending.

## 1. Context

- This repository is a working copy of the Rapa Nui observatory project (folder name
  `observatorio-turistico-de-rapa-nui-copy`, remote `sh-olmedo/observatorio-arauco`).
- Recent commits (messages) show the intended migration order:
  `99092d1` repo init → `70b22a7` "portada scrollytelling inmersiva + branding Arauco
  (ETAPA A+B+C)" → `5f43933` "modo dual estático/inmersivo en la portada".
- The site still mixes Arauco branding with Rapa Nui copy/data. The 2026-10-15 runtime pass
  confirmed the remaining Rapa Nui elements listed below (site title, sector data, rendered
  copy counts, evaluation slugs).

## 2. ✅ Completed (static)

| Element | Evidence |
| --- | --- |
| Header brand = Oficina de Turismo Arauco logo | `header.php:20-25` |
| Footer = 3 Arauco institutional logos + Arauco copyright | `footer.php:10-27` |
| Landing photos sourced from approved Arauco references | `assets/img/arauco/`, `references/web/README.md` |
| Landing narrative rebranded ("Narrativa territorial de Arauco", 5 scenes) | `front-page.php:1-25, 76` |
| Arauco design layer for the front page | `assets/css/otrn-arauco.css` |
| Provisional figures explicitly labeled as inherited from Rapa Nui | `front-page.php:140-142` |
| Self-hosted fonts/logos directories prepared | `assets/fonts/`, `assets/logos/` |

## 3. ⏳ Pending — user-visible Rapa Nui copy (F7)

Archive listing intros are hardcoded in the templates (not editorial content):

| File:line | Current (Rapa Nui) text mentions |
| --- | --- |
| `wp-content/themes/otrn/page-noticias.php:36` | "…turismo en Rapa Nui" |
| `wp-content/themes/otrn/archive-guia.php:24` | "Observatorio Turístico Rapa Nui" |
| `wp-content/themes/otrn/archive-buena_practica.php:29` | "…patrimonio Rapa Nui" |
| `wp-content/themes/otrn/archive-indicador.php:62` | "…turismo en Rapa Nui…" |
| `wp-content/themes/otrn/archive-iniciativa_pladetur.php:61,192` | "Pladetur de Rapa Nui", "Fuente: Pladetur Rapa Nui" |
| `wp-content/themes/otrn/page-estadisticas.php:84,238` | "Pladetur de Rapa Nui", "Fuente: Pladetur Rapa Nui" |
| `wp-content/themes/otrn/archive-material_apoyo.php:25` | "…comunidad turística de Rapa Nui" |
| `wp-content/themes/otrn/single-evaluacion.php:36` | Demo banner: "…medición real de Rapa Nui" |
| `wp-content/themes/otrn/style.css:2,4,5` | Theme name/description/author still "Rapa Nui" (wp-admin/visible) |
| `wp-content/mu-plugins/otrn-{contenido,autodiagnostico,guias,iniciativa-pladetur}.php:5` | Plugin "Author: Observatorio Turístico Rapa Nui" (admin plugin screen) |

Intended Rapa Nui mentions (keep): `front-page.php:22-24, 130, 140-142` (provisional data
notice), `single-evaluacion.php:36` until real data replaces the demo.

## 4. ⏳ Pending — geography & data (F8, F18)

| Element | Evidence | Notes |
| --- | --- | --- |
| Sector fallback list (10 Rapa Nui sectors with lat/lng) | `otrn-autodiagnostico.php:213-236` | Used when the `sector` CPT is empty |
| `otrn_sectores_rapanui()` reads the `sector` CPT | `otrn-autodiagnostico.php:239-250` | **Runtime: CPT seeded with 10 Rapa Nui entries** → this is the active path; data migration required |
| Seed comment: "Sectores por defecto de Rapa Nui (semilla del CPT `sector`)" | `otrn-autodiagnostico.php:209` | New Arauco sectors + coordinates needed |
| Criteria/evaluations/`dato_portada` content | DB (`wp-content/database/.ht.sqlite`, gitignored) | **Runtime counts: 74 criteria, 6 evaluations (3 published), 3 cards**; Rapa Nui references confirmed in slugs |
| Pages required by templates (`/acerca/`, `/evaluar/`, `/geoportal/`, `/noticias/`, `/mapa/`, `/mision/` option, `page_on_front`) | DB only; e.g. `functions.php:232,492` | **Runtime: all slugs exist**; still not reproducible from the repo (F18); no menus registered |

## 5. 🧭 Decision pending — external content & assets

| Item | Evidence | Decision needed |
| --- | --- | --- |
| Geoportal ArcGIS StoryMaps URL (scope unknown) | `page-geoportal.php:15` | Replace with Arauco story, keep, or remove (F9) |
| Survey dashboard "Percepción de Rapa Nui" (unreferenced) | `embeds/encuesta-percepcion-rapanui.html:6,20` | Reuse/adapt, replace, or delete (F10) |
| Legacy brand assets: `assets/footer/logo-{corfo,corfo-valparaiso,rau}.png`, `assets/hero-rapanui.jpg`, `assets/logo.png` | Unreferenced (hero only inside unused helper `functions.php:469-485`) | Archive or delete (F12) |
| GORE logo low-res; RAU logo missing | `assets/logos/README.md` ("Pendiente del cliente") | Request HD originals from client (F13) |
| Municipal/oficina logo opaque backgrounds | `assets/logos/README.md`; "chapa" treatment in `otrn-arauco.css:66-75` | Accept chapa, get transparent PNGs, or recolor footer |
| Proposed municipal palette (`#0070FF`, `#41BCFF`, `#DE232B`, `#0C1434`, `#0C0D0E`, `#D6D5D5`) | Absent from code (grep) | Formal approval + implementation plan (F2) |
| `--aru-*` palette interpretation | `otrn-arauco.css:1-17` ("para confirmación del cliente") | Confirm or replace with approved tokens (F6) |

## 6. Runtime status (2026-10-15) — [VERIFICATION.md](VERIFICATION.md)

Confirmed:
- `sector` CPT seeded with 10 Rapa Nui entries; evaluation data lives in the DB (6; 3
  published) and criteria (74) were inspected at count level; all template-required page
  slugs exist; **no menus are registered**.
- Site title still "Observatorio Turístico de Rapa Nui"; rendered copy still mentions Rapa
  Nui on `/`, `/noticias/`, `/geoportal/`, `/acerca/`, `/mision/`, `/estadisticas/`,
  `/indicadores/` (8/6/3/5/6/6/6 occurrences).
- REST exposes the private section's published items anonymously (F11b).
- Mobile menu (F1) and no-JS reveals (F5) are broken; `/mision/`, search and 404 render
  without otrn chrome (F16).

Still unverified: the Geoportal story's territorial scope (F9); evaluation contents beyond
titles; indicator/Pladetur data quality.

## 7. Suggested migration approach (proposed — not approved)

1. **Freeze the pending decisions** (§5) with the client: palette, geoportal, survey embed,
   logos, geography.
2. **Copy**: replace the hardcoded intro strings (§3) once authorized; keep Spanish, brand
   as "Observatorio Turístico de Arauco / Región del Bío-Bío".
3. **Geography**: create Arauco `sector` entries in wp-admin (coordinates), then remove or
   replace the Rapa Nui fallback (`otrn-autodiagnostico.php:213-236`). Do not edit the DB
   without a backup/export decision (F18).
4. **Data**: audit seeded criteria/evaluations for Rapa Nui references before publish;
   export/seed procedure documented so environments are reproducible.
5. **Verify** every change in a browser (F17 checklist in [ROADMAP.md](ROADMAP.md)).

Related: [ISSUES.md](ISSUES.md) (F7–F13, F18), [HANDOFF.md](HANDOFF.md) (pending decisions).
