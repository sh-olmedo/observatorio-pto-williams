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
| Footer = 4 Arauco institutional logos (transparent files, no plates) + Arauco copyright | `footer.php:10-33` |
| Landing photos sourced from approved Arauco references | `assets/img/arauco/`, `references/web/README.md` |
| Landing narrative rebranded ("Narrativa territorial de Arauco", 5 scenes) | `front-page.php:1-25, 76` |
| Arauco design layer for the front page | `assets/css/otrn-arauco.css` |
| Provisional figures explicitly labeled as inherited from Rapa Nui | `front-page.php:140-142` |
| Self-hosted fonts/logos directories prepared | `assets/fonts/`, `assets/logos/` |
| User guide rewritten as the Arauco help centre: intro copy no longer names Rapa Nui, and the 10 topics are new Spanish copy written against the rendered interface | `archive-guia.php`, `guias-contenido.php` (`d706872`, [VERIFICATION.md](VERIFICATION.md) §V24) |

## 3. ⏳ Pending — user-visible Rapa Nui copy (F7)

Archive listing intros are hardcoded in the templates (not editorial content):

| File:line | Current (Rapa Nui) text mentions |
| --- | --- |
| `wp-content/themes/otrn/page-noticias.php:36` | "…turismo en Rapa Nui" |
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

**F19 (registered 2026-10-16 — documentation only, not approved):** the Arauco version must
not carry over this geographically restricted selection; the location input is to be
replaced by an OpenStreetMap-based search picker with an adjustable marker. Decide together
with F8 whether the curated sector list survives as a quick-pick/fallback or is retired —
see §8.

## 5. 🧭 Decision pending — external content & assets

| Item | Evidence | Decision needed |
| --- | --- | --- |
| Geoportal ArcGIS StoryMaps URL (scope unknown) | `page-geoportal.php:15` | Replace with Arauco story, keep, or remove (F9). **The guide's figure `assets/guias/geoportal.webp` captures the current story** (another region's viewer), so it has to be re-captured when the URL is decided ([VERIFICATION.md](VERIFICATION.md) §V24.5) |
| Survey dashboard "Percepción de Rapa Nui" (unreferenced) | `embeds/encuesta-percepcion-rapanui.html:6,20` | Reuse/adapt, replace, or delete (F10) |
| Legacy brand assets: `assets/footer/logo-{corfo,corfo-valparaiso,rau}.png`, `assets/hero-rapanui.jpg`, `assets/logo.png` | Unreferenced (hero only inside unused helper `functions.php:469-485`) | Archive or delete (F12) |
| GORE logo still low-res — client decided (2026-10-18) **not** to replace it for now; the other three logos are resolved | `references/logos/`, `assets/logos/README.md` | Request an HD GORE original whenever the client has it (F13) |
| ~~Municipal/oficina logo opaque backgrounds~~ **Resolved 2026-10-18**: transparent assets derived from the client's originals and the white "chapas" removed | `references/logos/`, `assets/logos/`, `style.css:88-92` ([VERIFICATION.md](VERIFICATION.md) §V28) | — |
| Municipal palette (`#0070FF`, `#41BCFF`, `#DE232B`, `#0C1434`, `#0C0D0E`, `#D6D5D5`) | **Implemented 2026-10-16** (`52a7d4d`); roles verified against `muniarauco.cl` (`primary #0070FF`, `secondary #DE232B`, `accent #41BCFF`, `text #000000`; `#0C1434` unverifiable, `#0C0D0E`/`#D6D5D5` are Elementor defaults and were **not** adopted) | **Applied**; pending the client's visual sign-off (F2, [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) §2, mapping §13 of the audit) |
| `--aru-*` palette interpretation | Deleted in `52a7d4d` (`otrn-arauco.css` no longer defines tokens) | **Resolved** (F6): single source of truth in `style.css :root` |

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
   without a backup/export decision (F18). **F19** (§8) may replace the fixed-selector
   mechanism itself — decide both together.
4. **Data**: audit seeded criteria/evaluations for Rapa Nui references before publish;
   export/seed procedure documented so environments are reproducible.
5. **Verify** every change in a browser (F17 checklist in [ROADMAP.md](ROADMAP.md)).

### 7.1 First deployment — executed 2026-10-17 (out of the order above)

The client asked to publish the site, so a **direct-to-production** deployment happened before
step 1 was closed, on the client's own Hostinger account (`observatorioarauco.cl`):

- **Shipped**: the theme `otrn` and the six project mu-plugins, plus the **public** content set
  (216 items). The seeded criteria, evaluations and sectors (F8/F18) were **left out**, and so was
  the sample `guia` post (F27) — so the deployment does not publish the pending geography.
- **Not published yet**: search engines are discouraged (`blog_public = 0`) until the client
  approves the content, which includes the Rapa Nui copy of §3 and the §5 decisions.
- **Still open for production**: the Rapa Nui copy (F7), the geography (F8/F18), the geoportal URL
  (F9), the survey embed (F10) and the logo originals (F13) — the site carries its provisional
  notices in the meantime. Evidence: [VERIFICATION.md](VERIFICATION.md) §V25.

**Hotfixes deployed to the same install after that first pass** (no content written): the `<title>`
fix (F25, `1b19d5c`), the HTML browser-cache TTL in `.htaccess` (§V25.8), the theme watchdog
`otrn-vigilante-tema.php` (F28, §V25.9) — and on **2026-10-18**, from the client's editing report,
the capability grants moved to `after_setup_theme` (F29), the front-end-only `request` filter that
keeps `?ambito=` a listing filter instead of a taxonomy archive (F30), and the six ACF field groups
registered **in code** by the new mu-plugin `otrn-campos.php` (F31) so the content is editable again.
Evidence: §V26.

**Client requests on the live site, same day (2026-10-18):** the footer was corrected to carry the
**four** institutional logos of the reference strip — GORE, Municipalidad de Arauco, Oficina de Turismo
and RAU — by adding the RAU logo that already existed in the repository and no template used (F13;
§V27).

## 8. New Arauco requirement — self-assessment location search (F19)

Registered **2026-10-16** — **documentation only: not implemented, not approved**. It must
not carry over the geographically restricted location selection of §4. Issue:
[ISSUES.md](ISSUES.md) F19; proposed work: [ROADMAP.md](ROADMAP.md) Phase 6.

### 8.1 Existing behavior (Rapa Nui, static evidence)

| Aspect | Current implementation | Evidence |
| --- | --- | --- |
| Location input | Required `<select name="sector">` with fixed entries; choosing it is the only location step | `page-evaluar.php:103-111` |
| Data source | `sector` CPT (seeded with 10 Rapa Nui entries) with a hardcoded Rapa Nui fallback | `otrn-autodiagnostico.php:239-261`, `:213-226` |
| Coordinates | Assigned server-side from the selected entry; the user never sees or edits them | `otrn-autodiagnostico.php:394-401` |
| Persistence | Meta `sector` (slug), `destino` (name), `destino_lat`, `destino_lng`; a draft may have no sector/coordinates | `otrn-autodiagnostico.php:468-477`; draft preload `page-evaluar.php:29` |
| Map | Leaflet + OSM for **display only** (archive / `/mapa/`); none in the form | `functions.php:363-368`, `template-parts/mapa-evaluaciones.php` |
| Search / marker / confirmation | None | — |

### 8.2 Requirement — expected behavior (as registered 2026-10-16)

1. Users can search for an address, locality, business, or geographic place.
2. Search must use an OpenStreetMap-based geocoding provider.
3. Display at least five matching suggestions when the provider returns enough results.
4. Each suggestion must show a human-readable address or place description.
5. Selecting a result must center the map on that location and display a visible marker.
6. Allow the user to adjust the marker manually to support rural locations and incomplete addresses.
7. Show the selected location and coordinates before proceeding.
8. Require explicit location confirmation before continuing the self-assessment.
9. Preserve the confirmed location when saving drafts and resuming an evaluation.
10. Store the selected coordinates and address consistently with the existing evaluation data model.
11. Provide loading, no-results, geocoding-error, and keyboard-accessible states.
12. Ensure responsive behavior on mobile devices.
13. Respect OpenStreetMap attribution and the selected geocoding provider's usage policy, including rate limits and caching restrictions.

### 8.3 Acceptance criteria (proposed — verify in a browser)

- A search the provider answers with ≥5 matches lists ≥5 selectable suggestions, each with
  a readable address/place description (req. 1–4).
- Selecting a suggestion centers the map and shows a visible marker; the marker can be
  repositioned manually, including for rural locations and incomplete addresses (req. 5–6).
- The form displays the chosen location and its coordinates before continuing, and
  submission stays blocked until the user confirms the location explicitly (req. 7–8).
- Saving a draft and resuming it via the token link restores the confirmed location (map,
  marker and fields) (req. 9).
- The saved evaluation keeps the address and coordinates in the evaluation data model so
  the existing readers still work: the archive map (`otrn_evaluaciones_geo()` in
  `functions.php:843-865`, `template-parts/mapa-evaluaciones.php`), the `destino` filter
  (`archive-evaluacion.php:41-57`) and the single view (`single-evaluacion.php:20,41`);
  the exact meta mapping is an open decision (§8.4) (req. 10).
- Loading, empty-result and provider-error states are visible; the full flow (search →
  suggest → select → adjust → confirm) is operable with the keyboard alone (req. 11).
- The picker is usable at mobile widths (map does not trap page scroll; touch marker
  adjustment) (req. 12).
- OSM attribution is visible where tiles/geocoder results are shown, and requests comply
  with the chosen provider's policy (rate limits, caching, identifying headers or API
  key) (req. 13).

### 8.4 Dependencies & unresolved decisions (at registration)

| # | Decision | Options / notes | Owner |
| --- | --- | --- | --- |
| D1 | Geocoding provider | Public Nominatim vs self-hosted OSM geocoder vs a third-party OSM-based service (API key/quota); each has its own usage policy, rate limits and caching rules — verify the provider's current policy before implementing | Client/team |
| D2 | Request path | Browser-direct calls vs a server-side proxy (the proxy must sit behind the private-section gate — F3/F11) | Team (after D1) |
| D3 | Fate of the curated sector list (F8) | Keep the select as a quick-pick/fallback for known Arauco sectors, or retire it in favor of search-only | Client/team |
| D4 | Evaluation meta model | How the confirmed address maps to `sector`/`destino`/`destino_lat`/`destino_lng`; whether a new meta (formatted address, provider place id) is added | Team |
| D5 | Reverse geocoding after manual marker adjustment | Needed to keep the displayed address truthful; costs extra requests against the provider's limits | Team |
| D6 | DB backup/export policy (F18) | Precondition for any evaluation-data change; the private-section gate for the form must also cover the new endpoint | Client/team |

### 8.5 Technical risks (at registration — not verified)

- **Provider policy**: a public OSM geocoder expects low request rates, identifying
  requests and result caching; a client-side autocomplete pattern may violate its policy.
  Confirm the chosen provider's current rules before implementing.
- **Rural coverage**: OSM addresses in Arauco may be missing or coarse → rely on marker
  adjustment + clearly labeled approximate locations; "no results" must be a normal state.
- **Draft round-trip**: the local autosave stores only `sector` + answers
  (`page-evaluar.php:216-224`); the confirmed location must be added or restored drafts can
  lose it.
- **Reader compatibility**: archive map, `destino` filter and single view read the current
  metas (`single-evaluacion.php:20,41`); storage changes must not break them.
- **Accessibility**: Leaflet is pointer-first; a keyboard path for search/select/adjust
  (e.g. numeric coordinate inputs or keyboard marker controls) needs explicit design.
- **Privacy**: user-typed addresses would be sent to a third-party service; the site has
  no consent/privacy layer today.

Related: [ISSUES.md](ISSUES.md) (F7–F13, F18, F19), [HANDOFF.md](HANDOFF.md) (pending decisions).
