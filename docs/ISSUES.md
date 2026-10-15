# Issue Register (F1–F18)

Persistent register of every finding from the static audit (commit `5f43933` + working
tree), **preserving the original finding IDs** (including the F11b sub-finding). Keep these
IDs in branches, commits, PRs and future updates. Severities: **P0** critical,
**P1** high, **P2** medium, **P3** low. No P0 was found statically.

Status values: *Open* (needs work), *Open — verify at runtime* (needs a runtime/browser/DB
check first), *Open — decision pending* (blocked on a client/team decision).

Execution policy: **Phase 0 — runtime verification plus the baseline decisions — precedes any
implementation** ([ROADMAP.md](ROADMAP.md)), because unverified behavior (F17) can change
priorities. After Phase 0 is recorded, the recommended implementation order is
**F3 → F1 → F2/F6 → content/data migration**, verifying each change as it lands.

| ID | Title | Sev | Category | Phase |
| --- | --- | --- | --- | --- |
| F1 | Mobile navigation unusable on internal pages | P1 | Accessibility/UX | 2 |
| F2 | Proposed Arauco palette not implemented | P2 | Design decision | 3 |
| F3 | Evaluation submission without capability check | P1 | Security | 1 |
| F4 | ACF schemas not versioned (JSON missing) | P2 | Configuration | 0 |
| F5 | `.otrn-reveal` invisible without JavaScript | P2 | Resilience/A11y | 2 |
| F6 | Competing token systems (3 layers) | P2 | Design system | 3 |
| F7 | Rapa Nui copy still user-visible | P2 | Migration | 4 |
| F8 | Hardcoded Rapa Nui sectors + coordinates | P2 | Migration | 4 |
| F9 | Geoportal hardcoded ArcGIS StoryMap | P2 | Migration decision | 4 |
| F10 | Rapa Nui survey embed orphaned | P3 | Migration decision | 4 |
| F11 | Private section enforced only at HTML render | P1 | Security | 1 |
| F11b | REST exposure of private content (unverified) | P1 | Privacy | 1 |
| F12 | Orphaned Rapa Nui brand assets | P3 | Cleanup decision | 4 |
| F13 | Logo sourcing gaps (GORE low-res, RAU missing) | P2 | Branding decision | 4 |
| F14 | Three uncommitted files at risk | P1 | Process/Git | 0 |
| F15 | Documentation artifacts referenced but absent | P2 | Documentation | 0/5 |
| F16 | Hybrid theme architecture (fallback routes) | P3 | Architecture | 5 |
| F17 | No runtime verification performed yet | P1 | Verification gap | 0 |
| F18 | Site content/bootstrap not reproducible from repo | P2 | Reproducibility | 5 |

---

### F1 — Mobile navigation unusable on internal pages

- **Severity**: P1 · **Status**: Open · **Evidence**:
  - `style.css:508-509` hides `.otrn-nav` under 600 px with no replacement rule elsewhere.
  - Burger and mobile-panel CSS exist only in `assets/css/otrn-landing.css:194, 213-224`,
    enqueued only on the front page (`functions.php:409-457`).
  - `assets/otrn-menu.js:47-61` toggles `body.otrn-menu-abierto`, whose only styling lives
    in that same landing stylesheet; `style.css` has zero `.otrn-burger` rules (grep).
- **Impact**: phones (≤600 px) lose all navigation on internal pages (indicators, news,
  geoportal…). Between 601–768 px the nav renders as a cramped flex row with an unstyled
  button (static inference).
- **Proposed fix (not approved)**: give the burger + panel styles a global scope (base
  stylesheet or globally enqueued menu stylesheet) so the existing JS works site-wide.
- **Verification**: browser at ≤600 px on `/`, `/indicadores/`, `/geoportal/`; open/close,
  Escape, focus trap, keyboard-only pass.

### F2 — Proposed Arauco palette not implemented

- **Severity**: P2 · **Status**: Open — decision pending. **Evidence**: `#0070FF`,
  `#41BCFF`, `#DE232B`, `#0C1434`, `#0C0D0E`, `#D6D5D5` absent from the theme (grep);
  implemented palette is the photographed teal/terracotta system
  (`otrn-arauco.css:17-46`), self-described as pending client confirmation
  (`otrn-arauco.css:9-12`). Typography already matches the brief.
- **Impact**: visual identity may diverge from the approved municipal brand; blocks
  design-token work. **Do not assume the proposed palette is approved.**
- **Proposed fix (not approved)**: obtain formal approval, then map approved colors onto
  tokens (see [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) §2).
- **Verification**: written client sign-off + token diff before/after.

### F3 — Evaluation submission without capability check

- **Severity**: P1 · **Status**: Open · **Evidence**: nonce-only handler
  (`otrn-autodiagnostico.php:381-390`), hook `admin_post_otrn_submit_eval` at `:490`
  (no `nopriv` variant found), page gate checks login only (`functions.php:250-265`);
  inline note admits production should require the "Operador" role (docs/01 §6).
- **Impact**: any logged-in account can create/publish evaluations → bogus data in the
  private archive/map; content-integrity and authorization defect.
- **Proposed fix (not approved)**: enforce a capability for submit; define the operator
  role; keep nonce.
- **Verification**: subscriber-level negative test + code review (details in
  [SECURITY_AUDIT.md](SECURITY_AUDIT.md#f3--evaluation-submission-without-capability-check)).

### F4 — ACF schemas not versioned (JSON missing)

- **Severity**: P2 · **Status**: Open · **Evidence**: load path `ABSPATH . 'docs/acf-json'`
  (`otrn-contenido.php:160-167`); directory does not exist; ACF 6.8.0 installed; no
  `acf/settings/save_json` filter found. Field groups live in the DB (not inspected).
- **Impact**: schema drift/loss; environments not reproducible; risk of invented fields.
- **Proposed fix (not approved)**: enable local JSON save, export the **real** groups after
  review, commit; never fabricate schemas.
- **Verification**: ACF UI vs exports diff; load test on a clean environment.

### F5 — `.otrn-reveal` invisible without JavaScript

- **Severity**: P2 · **Status**: Open · **Evidence**: `otrn-landing.css:187-188`
  pre-hides reveal elements; `front-page.php:150, 189` uses the class; only the
  IntersectionObserver (`otrn-landing.js:37-44`) or the reduced-motion override
  (`otrn-landing.css:241-245`) makes them visible.
- **Impact**: with JS blocked and no reduced-motion preference, data cards and index cards
  never appear (content loss for no-JS users/crawlers).
- **Proposed fix (not approved)**: progressive enhancement — visible by default, hidden
  only when a JS flag is present.
- **Verification**: disable JavaScript, load `/`, confirm all reveal content visible.

### F6 — Competing token systems

- **Severity**: P2 · **Status**: Open · **Evidence**: three layers —
  `style.css:16-25`, `.otrn.home` `--aru-*` (`otrn-arauco.css:17-46`) and the **uncommitted**
  unscoped `:root` fallback (`otrn-landing.css:25-31`) whose values differ from the scoped
  tokens (e.g. `--aru-terra` `#C0392B` vs `#DF8056`) while its comment claims they are
  identical; load order + specificity decide per element.
- **Impact**: inconsistent colors if markup scope changes; misleading comments; fragile
  theming. Ties into the F2 decision.
- **Proposed fix (not approved)**: one source of truth; align or delete the fallback.
- **Verification**: computed-style audit + token diff; re-check after F2 decision.

### F7 — Rapa Nui copy still user-visible

- **Severity**: P2 · **Status**: Open · **Evidence**: full table in
  [ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md) §3 — e.g. `page-noticias.php:36`,
  `archive-guia.php:24`, `archive-indicador.php:62`, `archive-buena_practica.php:29`,
  `archive-iniciativa_pladetur.php:61,192`, `page-estadisticas.php:84,238`,
  `archive-material_apoyo.php:25`, `single-evaluacion.php:36`, theme header
  `style.css:2-5`, mu-plugin author headers.
- **Impact**: public pages still describe the Rapa Nui observatory.
- **Proposed fix (not approved)**: replace with client-approved Arauco copy; keep the
  intended provisional notes (`front-page.php:140-142`).
- **Verification**: grep for `Rapa` after the change + browser pass over the affected pages.

### F8 — Hardcoded Rapa Nui sectors + coordinates

- **Severity**: P2 · **Status**: Open · **Evidence**: `otrn_sectores_default()`
  (`otrn-autodiagnostico.php:209-236`, 10 Rapa Nui sectors with lat/lng);
  `otrn_sectores_rapanui()` falls back to it when the `sector` CPT is empty (`:239-250`);
  the form consumes it (`page-evaluar.php:106`).
- **Impact**: evaluations and map pin Rapa Nui geography; coordinates are wrong for Arauco.
- **Proposed fix (not approved)**: seed Arauco sectors (wp-admin) and replace the fallback;
  coordinate with the data migration (F18).
- **Verification**: form sector options, saved coordinates, map pins after seeding.

### F9 — Geoportal hardcoded ArcGIS StoryMap

- **Severity**: P2 · **Status**: Open — decision pending · **Evidence**:
  `page-geoportal.php:15` (story id `d414dbcd4d3743de835b15745f5bd060`); rendered as an
  external embed. Territorial scope of the story **unverified**.
- **Impact**: if the story depicts Rapa Nui, the page violates the rebrand; any change
  requires code edit (hardcoded).
- **Proposed fix (not approved)**: client decision (replace/keep/remove); make the URL
  configurable.
- **Verification**: open the page/story in a browser; confirm scope with the client.

### F10 — Rapa Nui survey embed orphaned

- **Severity**: P3 · **Status**: Open — decision pending · **Evidence**:
  `embeds/encuesta-percepcion-rapanui.html:6,20` ("Dashboard Percepción de Rapa Nui");
  no PHP reference to the file (grep).
- **Impact**: dead asset; if reused as-is it misrepresents the territory.
- **Proposed fix (not approved)**: decide reuse/adapt/replace/delete with the client.
- **Verification**: confirm no template/iframe reference; record the decision here.

### F11 — Private section enforced only at HTML render

- **Severity**: P1 · **Status**: Open — verify at runtime · **Evidence**: gating in
  `functions.php:250-265` (`template_redirect`) covers HTML routes only; handlers live in
  `admin-post.php` (see F3) and REST is untouched; no custom REST routes exist (grep);
  CPTs register `show_in_rest => true` (list in [SECURITY_AUDIT.md](SECURITY_AUDIT.md)).
- **Impact**: the "private" label of the section describes the UI, not necessarily every
  data surface.
- **Proposed fix (not approved)**: review REST exposure for `evaluacion`,
  `criterio_autodiag`, `sector`; restrict or disable as needed.
- **Verification**: see F11b.

### F11b — REST exposure of private content (unverified)

- **Severity**: P1 · **Status**: Open — verify at runtime · **Evidence**: registrations as
  above; `template_redirect` does not run for REST requests.
- **Impact if exposed**: published evaluation titles/IDs, criterion text and sector data
  readable without login (drafts normally status-protected). **Not confirmed.**
- **Verification (not executed)**: anonymous and low-privilege
  `curl /wp-json/wp/v2/evaluacion`, `/wp-json/wp/v2/criterio`, `/wp-json/wp/v2/sector`,
  plus `/wp-json/wp/v2/` index listing. Do not claim exposure until this passes.

### F12 — Orphaned Rapa Nui brand assets

- **Severity**: P3 · **Status**: Open — decision pending · **Evidence**:
  `assets/footer/logo-corfo.png`, `logo-corfo-valparaiso.png`, `logo-rau.png` and
  `assets/logo.png` unreferenced (grep); `assets/hero-rapanui.jpg` referenced only inside
  `otrn_hero_portada_url()` (`functions.php:469-485`), which no template calls.
- **Impact**: repo weight/confusion; a Rapa Nui hero can resurface as a fallback.
- **Proposed fix (not approved)**: replace the fallback image with an approved Arauco photo;
  archive/delete orphans after confirming no client expectation.
- **Verification**: grep usage; visual check after replacement.

### F13 — Logo sourcing gaps

- **Severity**: P2 · **Status**: Open — decision pending · **Evidence**:
  `assets/logos/README.md` ("Pendiente del cliente"): GORE logo only 160×283 (needs HD),
  RAU logo exists only inside a low-res strip, municipal/oficina logos carry an opaque
  `#ECECEC` interior handled by the "chapa" treatment (`otrn-arauco.css:66-75`).
- **Impact**: footer/branding quality; missing institution (RAU) in the Arauco footer.
- **Proposed fix (not approved)**: request original assets; confirm chapa vs transparent PNGs.
- **Verification**: asset swap + visual comparison.

### F14 — Three uncommitted files at risk

- **Severity**: P1 · **Status**: Open (process) · **Evidence**: `git status` shows
  `99-studio-loader.php`, `otrn-landing.css`, `style.css` modified (24 insertions,
  8 deletions total): machine-specific Studio temp path (`99-studio-loader.php:20`),
  landing token fallback + font token, `--dark` fix and Manrope→Montserrat.
- **Impact**: losing these changes would revert real fixes (fonts/dark token) and the
  landing fallback; committing blindly would publish a machine-specific path.
- **Proposed fix (not approved)**: review and commit intentionally, separating the
  machine-specific loader change from the theme fixes (details in
  [HANDOFF.md](HANDOFF.md)). **Do not revert.**
- **Verification**: `git diff` before/after the decision.

### F15 — Documentation artifacts referenced but absent

- **Severity**: P2 · **Status**: Open (partially mitigated) · **Evidence**: references to
  `docs/01`–`docs/07`, `docs/design-tokens.md` (`style.css:11`),
  `scripts/crear-pagina-acerca.php` (`page-acerca.php:7`, `scripts/` absent), no `DESIGN.md`.
  Note the code comments also reference `CLAUDE.md` conventions which do exist.
- **Impact**: development context loss (the primary motivation for this `docs/` baseline).
- **Proposed fix (not approved)**: recover the original docs from the Rapa Nui project if
  permitted; otherwise keep this baseline as source of truth and update references.
- **Verification**: grep references vs existing files.

### F16 — Hybrid theme architecture (fallback routes)

- **Severity**: P3 · **Status**: Open — verify at runtime · **Evidence**: classic child of
  `twentytwentyfive` (`style.css:6`), no `theme.json` and no `index.php`/`page.php`/
  `search.php`/`404.php` in the child; parent theme installed.
- **Impact**: routes without a PHP template (search, 404, generic pages) presumably render
  with parent block templates **without** the otrn chrome — unverified.
- **Proposed fix (not approved)**: decide target architecture (add PHP fallback templates vs
  block-based), then align.
- **Verification**: load `/?s=test`, a non-existent URL, and a generic page in a browser.

### F17 — No runtime verification performed yet

- **Severity**: P1 · **Status**: Open (verification gap) · **Evidence**: the entire audit
  and this baseline are static; nothing was checked in a browser, with WP-CLI, or against
  the DB. Unverified list: landing pin/scrub + dual mode, CLS/performance, no-JS rendering,
  mobile menu (F1), redirects (`/mapa/`, drafts), login-gate redirects, REST (F11b),
  Leaflet CDN map, form submission end-to-end.
- **Impact**: hidden defects may invalidate part of the documentation.
- **Proposed fix (not approved)**: execute the Phase 0 verification checklist
  ([ROADMAP.md](ROADMAP.md)) and record results here/HANDOFF.
- **Verification**: the checklist itself.

### F18 — Site content/bootstrap not reproducible from the repository

- **Severity**: P2 · **Status**: Open · **Evidence**: the DB is gitignored; templates depend
  on DB content — pages with exact slugs (`functions.php:232` `is_page('mapa')`; slug-based
  templates; `mision` page at `functions.php:492-498`; `page_on_front` option used by
  `otrn_hero_portada_url()` `:469-485`); no seed/bootstrap scripts exist (`scripts/` absent).
- **Impact**: a fresh clone/new environment renders fallback templates or missing sections;
  migrations/handoffs require the database or a seeding procedure.
- **Proposed fix (not approved)**: document and script a bootstrap procedure (pages, menus,
  options) + a content export policy (with the studio loader and privacy in mind).
- **Verification**: clone into a new Studio site and load the key routes.
