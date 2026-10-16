# Issue Register (F1–F23)

Persistent register of every finding from the static audit (commit `5f43933` + working
tree), **preserving the original finding IDs** (including the F11b sub-finding). Keep these
IDs in branches, commits, PRs and future updates. **F19** was registered later (2026-10-16)
as a new Arauco functional requirement — documentation only, not implemented. **F22** was
registered on 2026-10-16 too, while verifying the mobile glass pass: pre-existing, not requested,
not introduced by that commit. **F23** was registered later the same day, on the client's fourth
round: a regression that round introduced deliberately (see F21). Severities:
**P0** critical, **P1** high, **P2** medium, **P3** low. No P0 was found statically.

Status values: *Open* (needs work), *Open — verify at runtime* (needs a runtime/browser/DB
check first), *Open — decision pending* (blocked on a client/team decision).

Execution policy: **Phase 0 — runtime verification plus the baseline decisions — precedes any
implementation** ([ROADMAP.md](ROADMAP.md)), because unverified behavior (F17) can change
priorities. After Phase 0 is recorded, the recommended implementation order is
**F3 → F1 → F2/F6 → content/data migration**, verifying each change as it lands. (F2/F6 were
executed ahead of F3/F1 on 2026-10-16 at the client's explicit request; the security pair
remains first in the proposed plan.)

Runtime status (first pass, **2026-10-15**): measured results are in
[VERIFICATION.md](VERIFICATION.md); findings below marked *runtime confirmed* carry evidence.

**Update 2026-10-16 (Phase 2 — design tokens):** the client confirmed the Municipalidad de
Arauco identity and approved the token mapping, so **F2 and F6 moved from "decision pending" to
"implemented in code, pending visual sign-off"** (local commits, not pushed). The F1 navigation
was not modified by that pass and its rendered behaviour is unchanged (re-measured):
[VERIFICATION.md](VERIFICATION.md) §V17, mapping in
[DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) §13.

**Update 2026-10-16 (client review of the home page):** two findings raised by the client from
screenshots — **F20** (informative KPIs read as clickable cards) and **F21** (scene text
illegible over the photographs) — were fixed in `e2c3a9f`, still inside the front page and
without touching tokens, PHP, routes or data: [VERIFICATION.md](VERIFICATION.md) §V19.

| ID | Title | Sev | Category | Phase |
| --- | --- | --- | --- | --- |
| F1 | Mobile navigation unusable on internal pages | P1 | Accessibility/UX | 2 |
| F2 | Municipal identity implemented, pending visual sign-off | P2 | Design decision | 3 |
| F3 | Evaluation submission without capability check | P1 | Security | 1 |
| F4 | ACF schemas not versioned (JSON missing) | P2 | Configuration | 0 |
| F5 | `.otrn-reveal` invisible without JavaScript | P2 | Resilience/A11y | 2 |
| F6 | Competing token systems — resolved (one source) | P2 | Design system | 3 |
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
| F19 | Self-assessment location search (OSM geocoding) | P2 | Migration | 6 |
| F20 | Home informative KPIs read as clickable cards | P2 | UX/Affordance | 3 |
| F21 | Home scene text illegible over the photographs | P1 | Accessibility/UX | 3 |
| F22 | Home page has no print styles (scene copy lost on paper) | P3 | Resilience/A11y | 3 |
| F23 | Ghost CTA boundary over photography (&lt;3:1, WCAG 1.4.11) | P2 | Accessibility | 4 |

---

### F1 — Mobile navigation unusable on internal pages

- **Severity**: P1 · **Status**: **Fixed in code (2026-10-16, three rounds) and
  browser-verified, but the grouped-navigation redesign is _pending explicit UX/UI
  sign-off_ — see *Redesign* and the limitations below** · **Evidence**:
  - `style.css:508-509` hides `.otrn-nav` under 600 px with no replacement rule elsewhere.
  - Burger and mobile-panel CSS exist only in `assets/css/otrn-landing.css:194, 213-224`,
    enqueued only on the front page (`functions.php:409-457`).
  - `assets/otrn-menu.js:47-61` toggles `body.otrn-menu-abierto`, whose only styling lives
    in that same landing stylesheet; `style.css` has zero `.otrn-burger` rules (grep).
- **Impact**: phones (≤600 px) lose all navigation on internal pages (indicators, news,
  geoportal…). Between 601–768 px the nav renders as a cramped flex row with an unstyled
  button (static inference).
- **Runtime (2026-10-15)**: at 390 px the nav stays `display:none` on `/noticias/` **and**
  `/`; the burger click toggles `otrn-menu-abierto` + `aria-expanded` but the nav is 0×0.
  At 700 px internal pages keep a visible (cramped) row while the front-page panel works.
  Burger on internal pages is unstyled (16×6 px) vs styled on the landing (46×46 px)
  ([VERIFICATION.md](VERIFICATION.md) §V6).
- **Proposed fix (not approved)**: give the burger + panel styles a global scope (base
  stylesheet or globally enqueued menu stylesheet) so the existing JS works site-wide.
- **Verification**: browser at ≤600 px on `/`, `/indicadores/`, `/geoportal/`; open/close,
  Escape, focus trap, keyboard-only pass.
- **Fix (2026-10-16)** — `0dd2de4` (first pass: global menu stylesheet + JS) and `e727756`
  (responsive fix of the regression reported in an authenticated session); both local, not
  pushed:
  - `assets/css/otrn-menu.css` is enqueued on every view (after `style.css` /
    `otrn-landing.css`) and scopes its selectors with `.otrn-header` so it wins by
    specificity. The duplicated landing copies stay until F5/F14 cleanup.
  - Desktop row: `gap:18px`, non-shrinking links (`flex:0 0 auto`) and `white-space:nowrap`,
    so it is always single-line. The header container (`.otrn .wrap`, ≤1440 px) leaves
    1376 px of usable width: without a session the row needs 1274 px (fits from 1338 px
    viewport, panel at ≤1360 px); **with a session the 11th link (195 px) makes the row
    1424 px, so it never fits and the panel is used at every width**.
  - Admin bar: the front-page header is `position:fixed`, so core's
    `html{margin-top:32px !important}` did not move it and `#wpadminbar` covered its first
    32 px (logo, links, button). The header now shifts 32 px (46 px at ≤782 px) and the panel
    is anchored below the bar. Internal pages (static header) were already pushed down —
    hence the homepage-only symptom.
  - Regression also fixed here: with the old `gap:26px`, the anonymous row broke 3 labels at
    1366 px, the authenticated row needed 1568 px, and on the front page the overflowing
    «Salir» button was clipped off-screen (a `fixed` element creates no scrollbar).
- **Runtime (2026-10-16)**: `/` and `/noticias/` at 1920/1600/1440/1366/1361/1360/1280/1024/
  768/390 px — 0 broken labels, 0 px header overflow, 0 horizontal page overflow, header and
  burger below the admin bar, panel from the bar to the viewport bottom; real top-level page
  (1038 px): Enter opens + focus to the first link, Tab/Shift+Tab wrap inside the panel,
  Escape and real outside/link clicks close and return focus, `aria-*` and label correct,
  resize resyncs. Public 200; private gates unchanged (302/302/302, `/criterio/` 301)
  ([VERIFICATION.md](VERIFICATION.md) §V15).
- **Limitation (not verified)**: the authenticated header could not be produced with a real
  login (no second account exists and signing in writes session tokens to the DB, which the
  task forbids), so its 11th link and the admin bar were reproduced in the DOM with the real
  markup/CSS. A real authenticated session, real touch devices, the iOS dynamic viewport and
  `prefers-reduced-motion` emulation remain pending.

#### F1 redesign — grouped navigation, account icon and measured breakpoints (2026-10-16)

Client-approved information architecture (grouping only: no destination removed, no URL
changed, private gate untouched), implemented locally on top of `e727756` and **awaiting
explicit UX/UI approval of the visuals**. Files: `header.php`, `functions.php`,
`assets/css/otrn-menu.css`, `assets/otrn-menu.js`.

- **Single source of truth**: `otrn_nav_estructura()` in `functions.php` returns the ordered
  tree (`tipo`, `etiqueta`, `url`, `hijos`, `prioridad`, `activo`, filter
  `otrn_nav_estructura`). `header.php` renders it once for the row and once inside «Más»;
  the `is_user_logged_in()` gate for the Autoevaluación group is unchanged.
- **Groups**: Observatorio ▾ (Acerca de, Noticias) · Datos ▾ (Indicadores, Geoportal) ·
  Planificación ▾ (Iniciativas Pladetur, Estadísticas) · Material de apoyo ▾ (Guía de
  usuario, Documentación, Reportes) · Autoevaluación ▾ (Autoevaluaciones, Mapa, Nueva
  autoevaluación, only with session).
- **Account control**: the prominent `Iniciar sesión` / `Salir` button is replaced by a
  21 px inline person SVG (44×44 px hit area, no border/fill/pill/shadow, Material `person`
  geometry, site's `stroke 1.7 / currentColor` language) with an accessible name; logged out
  it links to the existing login, logged in it opens a small panel with «Cerrar sesión».
  **Material Icons is not available in the project** (no icon font/library), so no new
  dependency was added.
- **Measured breakpoints** (worst case = front page brand; row width measured in-browser):
  ≥1152 px full row (needs 1130 px with session) · 910–1151 px row keeps `Inicio,
  Observatorio, Datos, Buenas prácticas, Planificación` + «Más» (needs 895 px) · 680–909 px
  keeps `Inicio, Datos, Buenas prácticas` + «Más» (needs 654 px) · ≤679 px accessible drawer
  with accordions. Collapsed groups are duplicated inside «Más» and each copy is shown only
  when its row original is hidden, so no destination ever appears twice.
- **Fixed while verifying**: the closed «Más» panel (right-anchored now) added 99 px of
  horizontal page overflow on internal pages, where the header is `static`; the «Más» item
  leaked into the mobile drawer; `esFocable()` ignored `visibility:hidden`; focus return fell
  back to `<body>` when the browser did not focus the burger.
- **Spacing of the dropdown destinations — client review, 2026-10-16** (`510e981`, local): the
  client reported that on desktop the dropdown destinations looked «muy juntos y pegados». The
  cause was not the padding value but the cascade: the landing-panel reset
  (`.otrn-header .otrn-nav a`) has the **same specificity** (0,2,1) as the destination rule
  (`.otrn-header .otrn-subnav a`) and sits lower in the file, so it cancelled the padding and
  every destination rendered at 18 px — the value in the stylesheet had never been applied.
  The reset now targets `.otrn-nav__enlace` / `.otrn-nav__boton` (the two real elements of the
  row), and each destination is a row with 12 px padding, a 2 px gap and an inset rounded
  pointer highlight; the «Más» headings are indented 12 px to align with the destinations and
  the account menu uses the same row. Measured: 42 px rows, 2 px apart; panels stay 220 px wide
  (Autoevaluación, 3 destinations, 151 px tall, right edge 1429 px at a 1440 px viewport);
  mobile accordion unchanged; 0 px page overflow from 1920 to 375 px.
- **Contrast**: `--primary` (#DF8056) is only 2.6:1 on white, so active/hover text and focus
  outlines use `--primary-dark` (#A05C3D, 5.1–5.4:1) — the same colour the front page already
  used for `a.active`, which `style.css:41` had been overriding with the failing token ever
  since it was written but never applied. «Más» headings moved from `--text3` (3.5:1) to
  `--text2` (5.7:1). A per-element contrast scan of `.otrn-header` in nine states (row,
  dropdowns open, tablet «Más», drawer, with/without session) reports **0 failures**.
- **Verified**: see [VERIFICATION.md](VERIFICATION.md) §V16 — width matrix on `/` and
  `/noticias/` (anonymous and authenticated) at 1920/1440/1366/1280/1152/1151/1024/960/910/
  909/768/700/680/679/600/390/375 px with 0 px page overflow and single-line rows; real
  admin-bar geometry (32 px, 46 px ≤782 px) on both page types; interaction pass with
  synthetic events (open/close, one-at-a-time, hover, outside click, Escape + focus return,
  drawer trap, resize resync, drawer ↔ account exclusivity).
- **Pending (do not treat as approved)**: explicit UX/UI sign-off of the visuals; the focus
  ring rendered state and real keyboard modality (the harness keyboard could not be driven
  while the browser window was not visible); real touch devices, the iOS dynamic viewport and
  `prefers-reduced-motion` emulation. Session states were produced with a temporary,
  **deleted** mu-plugin (`wp_set_current_user()`, no DB write, no session token), not a real
  login.
- **2026-10-16 (design-token pass)**: the navigation was **not** modified by the F2/F6
  implementation; the row/dropdown/drawer thresholds and the account icon were re-measured on
  the front page and on internal pages (7 pages × 9 widths, 375–1920 px): same breakpoints,
  0 px horizontal overflow, 0 WCAG AA failures in the header
  ([VERIFICATION.md](VERIFICATION.md) §V17). The pending items remain the client's visual
  confirmation of the redesign and of the dropdown spacing.
- **2026-10-16 (client review of the token pass)**: the client could not reach the desktop
  dropdown — *"desaparece antes de posicionar el mouse"*. The 12 px gap that `510e981` added
  between the trigger and the panel (`top:calc(100% + 12px)`) is a band that does not belong to
  the group, so crossing it fired the `mouseleave` that closes the panel (the no-JS `:hover`
  fallback had the same hole). Fixed in **`3dbfbc6`** with a mouse bridge (`::after` of the
  group) the exact height of the gap, row mode only; verified by hit-testing the whole descent
  path from 375 to 1440 px, including the «Más» panel and the accordion mode where the bridge
  must not exist ([VERIFICATION.md](VERIFICATION.md) §V18.1). The client's visual confirmation
  of the redesign is still pending.

### F2 — Municipal identity not implemented (direction confirmed)

- **Severity**: P2 · **Status**: **Implemented in code 2026-10-16 (local, not pushed) — the
  rendered result is _pending client sign-off_**. **Evidence (baseline)**: `#0070FF`, `#41BCFF`,
  `#DE232B`, `#0C1434`, `#0C0D0E`, `#D6D5D5` absent from the theme (grep); the implemented
  palette was the photographed teal/terracotta system (`otrn-arauco.css:17-46`),
  self-described as pending client confirmation (`otrn-arauco.css:9-12`).
- **Update 2026-10-16 (client decision)**: the site **must follow the Municipalidad de Arauco
  visual identity**; the coral/teal palette is no longer the authoritative design system.
  The palette was verified against the live municipal site (`muniarauco.cl`, Elementor global
  kit `post-5.css`): **primary `#0070FF`, secondary `#DE232B` (red), accent `#41BCFF` (light
  blue), text `#000000`** — the roles do **not** follow the brief's ordering. `#0C1434` is
  absent from the municipal site entirely; `#0C0D0E` and `#D6D5D5` appear only in Elementor's
  generic base styles, **not** in the municipal kit. See
  [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) §2.5 and
  [DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) §12.
- **Implementation (2026-10-16)**: the approved mapping was applied to the theme — single
  `style.css :root` token block, all `--aru-*` layers deleted, every hardcoded legacy colour and
  font literal routed through tokens, and the tier/maturity scales moved to semantic status
  colours. Before → after mapping: [DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) §13.
  Measured result: 1 143 text elements across 11 public pages with **0 WCAG AA failures**, 63
  page × width combinations with **0 px horizontal overflow**, nav/gate/account behaviour
  unchanged ([VERIFICATION.md](VERIFICATION.md) §V17).
- **Impact**: the visual identity changed site-wide; blocks/derives from the token consolidation
  (F6, now resolved).
- **Remaining (not a code task)**: the client's **visual sign-off** of the rendered result, and
  the three colours that must not be treated as brand tokens (`#0C1434`, `#0C0D0E`, `#D6D5D5`).
- **Client review (2026-10-16)**: the evaluation drawer's criterion question and answer
  descriptions were reported as too faint: they are the content the evaluator must read, but
  they carried `--text2` (4.58:1 on `--lgrey`, 4.95:1 on white — AA by the minimum, and out of
  step with the indicator definition, which is black). Fixed in **`9142179`**: both now use
  `--dark`; the `.et` labels keep `--text2` for hierarchy — confirmed by the client on
  2026-10-16, they stay secondary. Measured: 19.43:1 and 21:1
  ([VERIFICATION.md](VERIFICATION.md) §V18.2). No other page was reported.
- **Client review, second round (2026-10-16)**: two home-page notes — informative KPIs that look
  clickable (**F20**) and poorly legible slides (**F21**) — fixed in **`e2c3a9f`**; no token
  changed ([VERIFICATION.md](VERIFICATION.md) §V19). With the harness window visible again,
  `browser.screenshot` worked for the first time, so the rendered result was **looked at** by the
  team (immersive scenes and the static/mobile layout): that is a developer look, not the client's
  sign-off.
- **Verification**: rendered token diff before/after + contrast pass + responsive pass — done
  ([VERIFICATION.md](VERIFICATION.md) §V17); visual confirmation by the client — **pending**.

### F3 — Evaluation submission without capability check

- **Severity**: P1 · **Status**: Open · **Evidence**: nonce-only handler
  (`otrn-autodiagnostico.php:381-390`), hook `admin_post_otrn_submit_eval` at `:490`
  (no `nopriv` variant found), page gate checks login only (`functions.php:250-265`);
  inline note admits production should require the "Operador" role (docs/01 §6).
- **Impact**: any logged-in account can create/publish evaluations → bogus data in the
  private archive/map; content-integrity and authorization defect.
- **Runtime context (2026-10-15)**: only 1 account and the six default roles exist today —
  the gap matters for future operator accounts (subscriber negative test not executed,
  [VERIFICATION.md](VERIFICATION.md) §V13).
- **Proposed fix (not approved)**: enforce a capability for submit; define the operator
  role; keep nonce.
- **Verification**: subscriber-level negative test + code review (details in
  [SECURITY_AUDIT.md](SECURITY_AUDIT.md#f3--evaluation-submission-without-capability-check)).

### F4 — ACF schemas not versioned (JSON missing)

- **Severity**: P2 · **Status**: Open · **Evidence**: load path `ABSPATH . 'docs/acf-json'`
  (`otrn-contenido.php:160-167`); directory does not exist; ACF 6.8.0 installed; no
  `acf/settings/save_json` filter found. Field groups live in the DB (not inspected).
  **Runtime (2026-10-15)**: `acf_get_field_groups()` returns 0; no `acf-field-group`/`acf-field`
  posts; no PHP-registered groups; custom fields exist as raw post meta
  ([VERIFICATION.md](VERIFICATION.md) §V11) — the schema exists only implicitly in DB values.
- **Impact**: schema drift/loss; environments not reproducible; risk of invented fields.
- **Proposed fix (not approved)**: enable local JSON save, export the **real** groups after
  review, commit; never fabricate schemas.
- **Verification**: ACF UI vs exports diff; load test on a clean environment.

### F5 — `.otrn-reveal` invisible without JavaScript

- **Severity**: P2 · **Status**: Open — **runtime confirmed (FAIL)** · **Evidence**: `otrn-landing.css:187-188`
  pre-hides reveal elements; `front-page.php:150, 189` uses the class; only the
  IntersectionObserver (`otrn-landing.js:37-44`) or the reduced-motion override
  (`otrn-landing.css:241-245`) makes them visible.
- **Impact**: with JS blocked and no reduced-motion preference, data cards and index cards
  never appear (content loss for no-JS users/crawlers).
- **Runtime (2026-10-15)**: with scripts blocked (sandbox iframe, console-confirmed) all 9
  `.otrn-reveal` elements stay at `opacity:0` after scrolling past the data section; the
  same iframe with JS shows 3/9 revealing. No `<noscript>` fallback exists
  ([VERIFICATION.md](VERIFICATION.md) §V7).
- **Proposed fix (not approved)**: progressive enhancement — visible by default, hidden
  only when a JS flag is present.
- **Verification**: disable JavaScript, load `/`, confirm all reveal content visible.

### F6 — Competing token systems

- **Severity**: P2 · **Status**: **Resolved in code 2026-10-16 (local, not pushed)** — one token
  source (`style.css :root`); pending only the client's visual sign-off. **Evidence (baseline)**:
  three layers — `style.css:16-25`, `.otrn.home` `--aru-*` (`otrn-arauco.css:17-46`) and the
  **uncommitted** unscoped `:root` fallback (`otrn-landing.css:25-31`) whose values differed from
  the scoped tokens (e.g. `--aru-terra` `#C0392B` vs `#DF8056`) while its comment claimed they
  were identical; load order + specificity decided per element.
- **Impact**: inconsistent colours if markup scope changed; misleading comments; fragile
  theming. Tied into the F2 decision.
- **Runtime (2026-10-15)**: on `/`, `--aru-terra` resolved simultaneously to `#C0392B`
  (`:root` fallback) and `#DF8056` (`.otrn.home` scope) ([VERIFICATION.md](VERIFICATION.md) §V8).
- **Resolution (2026-10-16)**: both `--aru-*` layers (25 definitions in total, 10 with
  conflicting values) and the unscoped fallback were **deleted**; every use was remapped to a
  global token. No `--aru-*`, `--text3`, `--secondary-soft` or `--blue-dark-soft` reference
  remains (grep). Full remap table: [DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) §13.1–13.2;
  token resolution re-measured per page: [VERIFICATION.md](VERIFICATION.md) §V17.
- **Verification**: computed-style audit + token diff, re-executed after the change — done;
  the remaining step is the client's visual confirmation.

### F7 — Rapa Nui copy still user-visible

- **Severity**: P2 · **Status**: Open · **Evidence**: full table in
  [ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md) §3 — e.g. `page-noticias.php:36`,
  `archive-guia.php:24`, `archive-indicador.php:62`, `archive-buena_practica.php:29`,
  `archive-iniciativa_pladetur.php:61,192`, `page-estadisticas.php:84,238`,
  `archive-material_apoyo.php:25`, `single-evaluacion.php:36`, theme header
  `style.css:2-5`, mu-plugin author headers.
- **Impact**: public pages still describe the Rapa Nui observatory.
- **Runtime (2026-10-15)**: "rapa" still present in rendered HTML — `/` 8, `/noticias/` 6,
  `/geoportal/` 3, `/acerca/` 5, `/mision/` 6, `/estadisticas/` 6, `/indicadores/` 6; site
  title still "Observatorio Turístico de Rapa Nui" ([VERIFICATION.md](VERIFICATION.md) §V10).
- **Proposed fix (not approved)**: replace with client-approved Arauco copy; keep the
  intended provisional notes (`front-page.php:140-142`).
- **Verification**: grep for `Rapa` after the change + browser pass over the affected pages.

### F8 — Hardcoded Rapa Nui sectors + coordinates

- **Severity**: P2 · **Status**: Open · **Evidence**: `otrn_sectores_default()`
  (`otrn-autodiagnostico.php:209-236`, 10 Rapa Nui sectors with lat/lng);
  `otrn_sectores_rapanui()` falls back to it when the `sector` CPT is empty (`:239-250`);
  the form consumes it (`page-evaluar.php:106`).
- **Impact**: evaluations and map pin Rapa Nui geography; coordinates are wrong for Arauco.
- **Runtime (2026-10-15)**: the `sector` CPT is seeded with 10 entries (Rapa Nui geography),
  so the fallback list is not the active path today — the data itself is still the pending
  migration ([VERIFICATION.md](VERIFICATION.md) §V10).
- **Proposed fix (not approved)**: seed Arauco sectors (wp-admin) and replace the fallback;
  coordinate with the data migration (F18).
- **Verification**: form sector options, saved coordinates, map pins after seeding.

### F9 — Geoportal hardcoded ArcGIS StoryMap

- **Severity**: P2 · **Status**: Open — decision pending · **Evidence**:
  `page-geoportal.php:15` (story id `d414dbcd4d3743de835b15745f5bd060`); rendered as an
  external embed. Territorial scope of the story **unverified** (page reachable at runtime,
  HTTP 200, 2026-10-15).
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

- **Severity**: P1 · **Status**: Open — **HTML gate runtime-verified (PASS, 2026-10-15)**;
  REST side confirmed via F11b · **Evidence**: gating in
  `functions.php:250-265` (`template_redirect`) covers HTML routes only; handlers live in
  `admin-post.php` (see F3) and REST is untouched; no custom REST routes exist (grep);
  CPTs register `show_in_rest => true` (list in [SECURITY_AUDIT.md](SECURITY_AUDIT.md)).
- **Impact**: the "private" label of the section describes the UI, not necessarily every
  data surface.
- **Proposed fix (not approved)**: review REST exposure for `evaluacion`,
  `criterio_autodiag`, `sector`; restrict or disable as needed.
- **Verification**: see F11b.

### F11b — REST exposure of private content (confirmed at runtime)

- **Severity**: P1 · **Status**: Open — **runtime confirmed (FAIL, 2026-10-15)** · **Evidence**:
  registrations as above; `template_redirect` does not run for REST requests; anonymous
  `GET /wp-json/wp/v2/evaluacion` returns the 3 published evaluations with readable
  titles/slugs/IDs/dates, `criterio_autodiag` returns 74 items and `sector` 10; drafts are
  rejected (`?status=draft` → 400); no content/answers observed in the payloads
  ([VERIFICATION.md](VERIFICATION.md) §V5).
- **Impact**: the private section's published items are anonymously readable via REST
  (identifying metadata), despite the login-gated HTML section; drafts are safe.
- **Verification (executed, read-only)**: anonymous enumerations of every project REST route
  plus the `wp/v2` index — counts and field names only, no titles recorded.

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
- **Update (2026-10-15)**: the `docs/` baseline was committed (`f506104`); the 3 files remain
  uncommitted — the commit-strategy decision is still open.
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

- **Severity**: P3 · **Status**: Open — **runtime confirmed (2026-10-15)** · **Evidence**: classic child of
  `twentytwentyfive` (`style.css:6`), no `theme.json` and no `index.php`/`page.php`/
  `search.php`/`404.php` in the child; parent theme installed.
- **Impact**: routes without a PHP template (search, 404, generic pages) presumably render
  with parent block templates **without** the otrn chrome — unverified.
- **Proposed fix (not approved)**: decide target architecture (add PHP fallback templates vs
  block-based), then align.
- **Verification**: load `/?s=test`, a non-existent URL, and a generic page in a browser.
- **Runtime (2026-10-15)**: search, a lost URL and `/mision/` (a live standalone page) all
  render the parent block theme **without** otrn chrome; classic templates keep it
  ([VERIFICATION.md](VERIFICATION.md) §V9).

### F17 — No runtime verification performed yet

- **Severity**: P1 · **Status**: Partially executed — **first pass 2026-10-15** · **Evidence**:
  executed: environment/data inventory, page slugs, private-HTTP gate, REST (F11b), mobile
  nav (F1), no-JS (F5), fallback routes (F16), token divergence (F6) — results in
  [VERIFICATION.md](VERIFICATION.md). Remaining: landing pin/scrub motion + CLS, draft
  save/resume, form submission end-to-end, subscriber negative test (F3), Leaflet/StoryMap,
  keyboard/focus-trap, real-device behavior.
- **Impact**: hidden defects may invalidate part of the documentation.
- **Proposed fix (not approved)**: execute the remaining Phase 0 verification items
  ([ROADMAP.md](ROADMAP.md)) and record results in [VERIFICATION.md](VERIFICATION.md) §V13.
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
- **Runtime (2026-10-15)**: all template-required page slugs exist (`acerca`, `evaluar`,
  `geoportal`, `noticias`, `mapa`, `mision`, `estadisticas`); **no menus are registered**;
  content counts in [VERIFICATION.md](VERIFICATION.md) §V3 — everything remains DB-only.

### F19 — Self-assessment location search (OSM geocoding)

- **Severity**: P2 · **Status**: Open — **new Arauco requirement registered 2026-10-16**
  (documentation only; **not implemented, not approved**) · **Category**: Migration
  (new scope).
- **Requirement**: the form must let users search an address, locality, business or place
  with an OpenStreetMap-based geocoder, and select, adjust and confirm the location on a
  map — instead of the fixed, geographically restricted sector selector inherited from Rapa
  Nui. Full requirement, acceptance criteria, dependencies and risks:
  [ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md#8-new-arauco-requirement--self-assessment-location-search-f19)
  §8. Proposed phase: [ROADMAP.md](ROADMAP.md) Phase 6.
- **Current behavior (evidence, static)**:
  - The only location input is a required `<select name="sector">` with fixed entries
    (`page-evaluar.php:103-111`), fed by `otrn_sectores_rapanui()`
    (`otrn-autodiagnostico.php:239-261`; hardcoded Rapa Nui fallback `:213-226`).
  - Coordinates are assigned server-side from the selected entry
    (`otrn-autodiagnostico.php:394-401`) and stored as `destino_lat`/`destino_lng`, with
    `sector` (slug) and `destino` (name) (`:468-477`). The user cannot search, move a
    marker or visually confirm a location.
  - Leaflet + OSM exist for **display only** (evaluations archive/`/mapa/`):
    `functions.php:363-368`, `template-parts/mapa-evaluaciones.php`.
- **Impact**: without this requirement the Arauco form inherits a geographically wrong
  selector (F8) and cannot locate rural destinations or addresses outside a fixed list.
- **Dependencies / open decisions**: geocoding provider + usage policy; fate of the curated
  sector list (F8 ↔ F19); evaluation location meta model; DB backup/export policy (F18)
  before data changes; private-section gating for any server-side proxy. Details in §8.4–8.5
  of the migration doc.
- **Verification (future)**: acceptance criteria in
  [ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md#8-new-arauco-requirement--self-assessment-location-search-f19)
  §8.3 + a browser pass on the private form (needs an authorized session, cf.
  [VERIFICATION.md](VERIFICATION.md) §V13).

### F20 — Home informative KPIs read as clickable cards

- **Severity**: P2 · **Status**: **Fixed in code 2026-10-16** (`e2c3a9f`, local, not pushed) ·
  **Category**: UX/affordance. **Reported by the client** from a screenshot: *"los KPI que son
  solo informativos se ven iguales a los elementos clicables"*.
- **Evidence**: `.otrn-dato-lite` (`front-page.php:150`, the three figures of the "Datos" scene)
  is an `<article>` that navigates nowhere, but carried the same surface as the actionable cards:
  `--paper` background, `1px solid rgba(48,74,131,.14)` border, `--radius` and
  `box-shadow:0 1px 3px rgba(0,0,0,.06)` (`assets/css/otrn-landing.css:161-163`), i.e. the same
  treatment as `.otrn-indexcard` (indicator cards, `<a>`; `style.css:294` + landing override
  `:193`) and `.otrn-card` (news cards; `style.css:225`). Both families are white cards.
- **Impact**: an affordance defect — information and navigation look identical on the home page,
  so users try to click figures that do not go anywhere (consistency/WCAG 3.2.4 expectations).
- **Fix (applied)**: the informative figure loses the card chrome and becomes a flat data block:
  no background, no enclosing border, no radius, no shadow, plus a `border-top:2px solid
  var(--primary)` rule that orders the column. The actionable cards keep their card surface
  (re-measured in the same run). No copy, markup or token changed.
- **Verification**: computed styles before/after at 400 px and the actionable card re-check in
  [VERIFICATION.md](VERIFICATION.md) §V19.1; 8 widths 375→1920 px with 0 px horizontal overflow.
  The client's confirmation that the two families now read differently — **pending**.

### F21 — Home scene text illegible over the photographs

- **Severity**: P1 · **Status**: **Fixed in code 2026-10-16** (`e2c3a9f`, local, not pushed) ·
  **Category**: Accessibility/UX. **Reported by the client** from a screenshot: *"los slides del
  home tienen poca legibilidad"*.
- **Evidence (three separate defects, both rendering modes)**:
  - Static mode (≤768 px, no JS, `prefers-reduced-motion`): the hero used a weak vertical
    gradient (0.18 → 0.34 → 0.74) starting exactly where the text begins, and scenes 2–3 a
    **horizontal** gradient (0.82 at the left edge → 0.06 at 68 %) while the mobile text column
    spans the full width, leaving the right half of each paragraph on unveil photograph
    (`assets/css/otrn-landing.css:64-70`, pre-fix).
  - Immersive mode (≥769 px + GSAP): scene 2's text had **no veil at all** — the shared veil was
    faded in at timeline 5.8, with the coastal scene — and scene 3's veil ended at 42 % of the
    window width while the text column reaches 620 px (`assets/otrn-landing.js:115-116`,
    `otrn-landing.css:113-116`, pre-fix).
  - Immersive hero: below ~1300 px window width the 640 px column extended under the
    photographic panel (54 % of the window), leaving dark text over a bright photo.
  - Scene rail (same family): the active 11 px number (`--primary-dark`) sat on the translucent
    plate `rgba(255,255,255,.46)` over the photograph at **2,33:1** (measured at 1024 px), below
    AA.
- **Impact**: marketing copy, scene titles and calls to action were unreadable on the home page —
  the page the client reviews and the most visible content of the site.
- **Fix (applied)**: one vertical veil for the three static scenes (0.88 before the text band,
  21→84 % of the act height); pixel-based immersive veil (0,92 → 0 at 1160 px) faded in at 1.6
  (before scene 2's text appears at 3.0); the panel width declared once (`--panel-w`) and the hero
  column limited to it; rail number in `--dark`. No token, PHP, route or data change.
- **Verification** (new method: the real photograph is drawn on a `<canvas>` with the CSS
  geometry, the veil composited per pixel and the actual text colour — alpha included — measured):
  static 400 px all elements ≥ 5,07:1; static forced at 1440 px ≥ 5,29:1; immersive scene 2 at
  809/1024/1440/1920 px ≥ 5,06:1; immersive scene 3 (darkest photo) ≥ 4,88:1; rail 6,62:1
  minimum after the fix. Detail and the NOT VERIFIED list:
  [VERIFICATION.md](VERIFICATION.md) §V19.2.
- **Decided 2026-10-16 (client, not a code task)**: **keep the veil at 0.88–0.92**. Guaranteeing
  AA against the **brightest** pixel of each photograph is the governing requirement; the mobile
  scenes stay a blue-tinted backdrop whose detail survives mainly in the top ~20 %. A lighter
  veil would show more photograph and give up that worst case — deliberately rejected.
  [HANDOFF.md](HANDOFF.md) eighth round.
- **Third round — 2026-10-16, superseded for mobile** (`649b01f`, local, not pushed). The client
  saw the result and reversed the trade for the phone: *"En mobile no se alcanzan a ver las
  imágenes de fondo debido al degradado. El problema es específicamente con las bajadas de texto
  que no se leen bien en mobile. ¿Podrías probar con efecto glass en las bajadas de texto?"*.
  The eighth-round decision (a veil heavy enough to make the photograph's brightest pixel safe)
  **still holds above 768 px**; at ≤768 px the legibility guarantee moves from the veil to a
  **glass panel** behind the text block (tint 0.65 + `backdrop-filter:blur(16px)`), the veil drops
  to a clear gauze (0.42 maximum) and the photograph goes from unveiled in 6.7-7.2 % of the act
  height to **89-90 %**. The glass had to cover the eyebrow and the title too, not only the lead:
  measured, the 12 px eyebrow over the light veil (or over the panel, if left at white 85 %) and
  the title over a bright sky fall below AA on their own. The scene eyebrows therefore move to
  `--paper` (both modes), the scroll cue gets the same glass as a pill, the ghost CTA's border
  rises to 0.8 (1.4.11: 2,72 → 3,92) and a `@supports not` branch raises the tint to 0.82 for
  browsers without `backdrop-filter`. Measured 320-768 px: every element ≥ 4,68:1, no AA failure,
  0 px overflow. Detail and limits: [VERIFICATION.md](VERIFICATION.md) §V20.
- **Fourth round — 2026-10-16, same day** (`05d2ecb`, local, not pushed). The client answered with a
  marked-up screenshot of scene 3, the lead boxed in red: *«El efecto glass es solo para el texto
  demarcado con rojo. Así se podrá ver la imagen de fondo»*. The plate is reduced to the **lead
  only** (it now runs 6 px from the viewport edge and covers 14,8-32,8 % of each act, against
  62-84 % before); the eyebrow, the title and the CTA of scenes 2-3 return to the photograph and are
  separated from it by a **text-shadow** (tight on the small type, wide on the title). The veil, the
  cue pill and the hero's solid CTA are unchanged, and the photograph stays unveiled over 89-90 % of
  each act. Measured: the lead on the plate 6,32-6,51:1, the hero CTA 7,40:1 and the cue 6,34-7,20:1
  pass AA at 320-768 px; the copy that left the plate measures 1,49-2,73:1 raw (a shadow is not
  counted by WCAG 1.4.3), so it is a **client-accepted, documented gap** — the §V20 block plate
  remains the compliant alternative. This round also **reintroduces a 1.4.11 gap** on the ghost
  CTA's border over photography: registered as **F23**. Detail: §V21.
- **Fifth round — 2026-10-16** (`bef8332`, local, not pushed). The client sent the plate's
  declaration back with values to use. Two were corrected, not pasted: `rgba(48, 74, 131, -0.35)`
  is **invalid CSS** (alpha accepts 0-1, so the browser discards the declaration and the lead is
  left with no tint at all — measured 2,00-2,39:1), so the tint was applied as **0,35**; and the
  snippet carried **two different blurs** (`16px` in the `-webkit-` prefix, `9px` in the standard
  property — the standard one wins), so both were normalised to **9 px**. Everything else already
  matched. Consequence: the lead's worst pixel drops to **4,12-4,44:1** — 0,06-0,38 below the 4,5:1
  of AA — with a mean of 5,15-6,90:1. **0,45 is the nearest value that holds AA** (4,88-5,11:1) and
  looks practically the same; the client's own number was kept because it was given explicitly.
  The rest of the scene is unchanged (veil, cue pill, hero CTA and the text-shadow of §V21), the
  fallback without `backdrop-filter` still holds 5,42-5,60:1, and above 768 px the plate does not
  exist. Detail: §V22.

### F22 — Home page has no print styles: the scene copy disappears on paper

- **Severity**: P3 · **Status**: **Open — recorded while verifying `649b01f`, not requested and
  not introduced by it** (pre-existing) · **Category**: Resilience/Accessibility.
- **Evidence**: `grep -rn '@media print'` over `wp-content/themes/otrn/` returns **nothing**. The
  three scenes are built as `background-image` (`--scene` photograph) plus a veil built from
  `linear-gradient`, with **light text** on top (`.otrn-act__titulo` = `--paper`, eyebrow = white,
  lead = white 90 %); in the immersive mode the same is true of scenes 2-3. Browsers do not print
  background images or background colours by default.
- **Impact**: printing the home page drops the photographs and the veils, leaving light text on
  white paper — the scene copy, and therefore most of the story, is lost on paper. The "Datos" and
  "Explora" sections (dark text on white) print fine.
- **Proposed fix (not approved)**: an `@media print` block that (a) forces the scene text to
  `--dark`, (b) drops the veil and the glass plate, and (c) optionally prints the hero photograph
  as an `<img>` or inlines a light plate behind the scene text. Decide first whether the home page
  is meant to be printed at all.
- **Verification**: static (grep + the colour declarations above). Not exercised in a print
  preview or a print-to-PDF run.

### F23 — Ghost CTA over photography: its boundary does not reach 3:1 (WCAG 1.4.11)

- **Severity**: P2 · **Status**: **Open — introduced by the client's fourth-round decision
  (`05d2ecb`), measured, not fixed** · **Category**: Accessibility.
- **Evidence**: with the plate reduced to the lead, the outline CTA of scene 2
  (`.otrn-act__cta--outline`, 1.5 px `rgba(255,255,255,.8)` border, transparent fill) sits directly
  on the photograph. Measuring the border box against the photographed background: minimum
  **1,80-1,81:1**, mean 3,53-5,47:1, at 375/400/480/768 px. WCAG 1.4.11 requires 3:1 for the visual
  information that identifies a control. The same border measured **3,92:1** over the §V20 plate, so
  this is a regression of the fourth round, not a pre-existing gap.
- **Impact**: on the mobile scenes the ghost CTA's outline can be hard to see and the control reads
  mainly through its label (which this round separates with a text-shadow). The other two scene CTAs
  are unaffected: the hero's is a solid `--primary-dark` fill (7,40:1 text) and scene 3's is a plain
  text link, identified by its words rather than by a boundary.
- **Why it is not simply fixed**: over arbitrary photography **no translucent white border reaches
  3:1** — on a bright sky even an opaque white border measures ≈1,4:1. A 1.4.11-safe control needs
  its own surface (as in `649b01f`) or an opaque contrasting fill; both are visible surfaces, which
  is what the client asked to reduce.
- **Proposed fix (not approved)**: give `.otrn-act__cta--outline` a glass pill of its own on mobile
  (same tint as the lead's plate, `--radius-pill`), which restores the boundary without touching the
  scene copy; or accept the gap as a documented client decision, like the form-control border gap of
  §2.3 of [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md).
- **Verification**: measured with the temporary harness (§V21.3, method in §V20.1) at four widths;
  no real-device check.
