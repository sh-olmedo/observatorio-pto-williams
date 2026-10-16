# Phase 0 — Runtime Verification Record

First runtime pass, executed **2026-10-15** on the local WordPress Studio site
(`http://localhost:8895/`), repository at commit `f506104` plus the working tree. It runs the
[ROADMAP.md](ROADMAP.md) Phase 0 checklist "where the local environment permits" and
supersedes the static-only disclaimers in the documents that link here.

Verdicts: **PASS** (behaves as intended) · **FAIL** (defect confirmed at runtime) ·
**BLOCKED** (could not run) · **NOT VERIFIED** (not attempted here; procedure documented).

## Method & constraints

- **Read-only.** No post, option, role, user or database row was written. No form was
  submitted, no evaluation created or deleted, no credentials used or recorded. Personal
  data (evaluation titles, answers, user identifiers, secrets) is not reproduced here —
  counts, statuses and field *names* only.
- WP-CLI through `studio wp` (Studio CLI 1.23.0). HTTP probes with anonymous `curl`/Python
  (no cookies), localhost only.
- Browser checks ran in a real Chromium tab (viewport 888×803) driven by the agent harness.
  No device-emulation API was available, so narrow widths were exercised with **same-origin
  iframes**: CSS media queries evaluate against the iframe viewport (390 px and 700 px used).
  The no-JS case used `sandbox="allow-same-origin"`, where the browser itself blocks script
  execution — confirmed by the console message *"Blocked script execution … the
  'allow-scripts' permission is not set"*. This is real rendering, but not a real phone;
  see the caveat in §F1.

## V1 — Environment — PASS

| Check | Result |
| --- | --- |
| `studio --version` | 1.23.0 |
| Site status | Online at `http://localhost:8895/` (site did not need starting) |
| WordPress / PHP | 7.1.3 / 8.3, Xdebug disabled |
| Active theme | `otrn` 0.2.0 (parent `twentytwentyfive` 1.5) |
| Plugins | `advanced-custom-fields` 6.8.0 active; akismet / hello inactive |
| Mu-plugins loaded | `99-studio-loader`, `otrn-contenido`, `otrn-autodiagnostico`, `otrn-guias`, `otrn-iniciativa-pladetur` |
| Roles | only WordPress defaults (Administrator … Subscriber); **no custom "Operador" role** |
| Users | 1 account (count only) |

## V2 — Registered types — PASS (matches the static audit)

- Project CPTs all present and `show_in_rest = true`: `criterio_autodiag`, `evaluacion`
  (archive at `/autoevaluacion/`), `sector`, `indicador` (archive `/indicadores/`),
  `buena_practica`, `dato_portada`, `material_apoyo`, `guia`, `iniciativa_pladetur`.
- `sector` and `dato_portada` are non-public in `public`, yet still REST-registered
  (relevant to F11b).
- Project taxonomies all `show_in_rest = true`: `ambito`, `categoria_indicador`,
  `tipo_material`, `alcance_material`, `lineamiento`.

## V3 — Content inventory (counts only) — PASS

| Type | all / published |
| --- | --- |
| `indicador` | 44 / 44 |
| `buena_practica` | 79 / 79 |
| `material_apoyo` | 6 / 6 |
| `guia` | 1 / 1 |
| `iniciativa_pladetur` | 64 / 64 |
| `dato_portada` | 3 / 3 |
| `sector` | 10 / 10 |
| `criterio_autodiag` | 74 / 74 |
| `evaluacion` | 6 / **3 published**, 3 draft |
| `acf-field-group` / `acf-field` | 0 / 0 (see V11) |

- Pages (slug → status): `acerca`, `geoportal`, `estadisticas`, `mision`, `noticias`,
  `mapa`, `evaluar` → publish; `privacy-policy` → draft; `sample-page` → publish (WP default).
- **No navigation menus registered**; the theme calls no `wp_nav_menu()` (hardcoded nav).
- Front page shows **posts**: `show_on_front=posts`, `page_on_front=0`.
- `blogname` = **"Observatorio Turístico de Rapa Nui"** (site title not yet rebranded).
- Permalink structure `/%year%/%monthnum%/%day%/%postname%/`.

## V4 — Private-section HTTP gate (F11) — PASS

Anonymous requests to every gated HTML route return the login redirect:

| Route | Result |
| --- | --- |
| `/evaluar/` | 302 → `/wp-login.php?redirect_to=…` |
| `/mapa/` | 302 → login |
| `/autoevaluacion/` (evaluation archive) | 302 → login |
| `/autoevaluacion/<published-slug>/` (single) | 302 → login |
| `/criterio/<criterion-slug>/` (single) | 302 → login |
| Controls: `/noticias/`, `/geoportal/`, `/indicadores/` | 200 |

Note: `/evaluacion/` (word without the `auto` prefix) is a 301 canonical-guess redirect to an
unrelated good-practice post; the real archive is `/autoevaluacion/`.

## V5 — REST exposure (F11b) — **FAIL — CONFIRMED**

Anonymous `GET` requests (no cookies), counts only:

| Endpoint | Result | Exposure |
| --- | --- | --- |
| `/wp-json/wp/v2/evaluacion` | 200, total = 3 | published evaluation **titles / slugs / IDs / dates** readable; payload has no `content`/`excerpt` keys; `acf` key is an empty list |
| `/wp-json/wp/v2/evaluacion/<id>` | 200 | single published evaluation readable |
| `/wp-json/wp/v2/evaluacion?status=draft` | 400 | drafts are not readable anonymously |
| `/wp-json/wp/v2/criterio_autodiag` | 200, total = 74 | all criteria readable |
| `/wp-json/wp/v2/sector` | 200, total = 10 | sector entries readable |
| `/wp-json/wp/v2/` index | 200 | lists `evaluacion`, `criterio_autodiag`, `sector` and the public types |

**Interpretation**: the "private" section is enforced only for HTML rendering; through REST,
anonymous readers get the published evaluations' identifying metadata (titles/slugs contain
Rapa Nui references and dates), all criteria and the sector list. **No answer content or
personal data was observed in the payloads** (checked structurally — field names, not
values). Fixing this is Phase 1 work and has not been done (F11b stays open).

## V6 — Mobile navigation (F1) — **FAIL — confirmed, and wider than the static record**

Measured inside 390 px / 700 px iframes on `/` and `/noticias/` (menu JS executed in both):

| Width | Page | `.otrn-nav` before click | Burger | After clicking the burger |
| --- | --- | --- | --- | --- |
| 390 px | `/noticias/` | `display:none`, 0×0 | **unstyled**: 16×6 px, line-height `auto` | body gets `otrn-menu-abierto`, `aria-expanded=true`, **nav stays `display:none` 0×0** |
| 390 px | `/` | `display:none`, 0×0 | styled 46×46 px | same: state toggles, **nav stays hidden** |
| 700 px | `/noticias/` | visible flex row (cramped) | unstyled 16×6 px | class toggles, nothing changes visually |
| 700 px | `/` | off-canvas panel (`transform:translateX(360px)`) | styled | panel slides in (`transform` animates toward 0) — **works** |

- The 390 px front-page case was re-run with the iframe **on screen** to rule out
  offscreen-iframe effects: same result (nav 0×0, `display:none`, click only toggles state).
- Root cause matches the static analysis: `style.css:508-509` hides `.otrn-nav` under
  600 px; the landing stylesheet's panel rules never re-declare `display`, so even the front
  page loses the panel at ≤600 px. Between 601–768 px internal pages keep a visible (cramped)
  row; the front-page panel works there.
- **Caveat**: iframe-based viewport, not a device; touch, real media-query resize events and
  virtual keyboards are out of scope. Keyboard/Escape/focus-trap behavior NOT VERIFIED.
- **Superseded (2026-10-16)**: the F1 fix, its authenticated regression and the re-measured
  breakpoints are recorded in §V15.

## V7 — Landing without JavaScript (F5) — **FAIL — confirmed**

Sandboxed iframe (`scripts blocked`, console-confirmed), 880×844, no reduced-motion
preference (`prefers-reduced-motion: reduce` = false):

- 9 `.otrn-reveal` elements (`front-page.php` data/index cards); after scrolling to and past
  the data section: **all 9 remain at computed `opacity: 0`**; none has `is-visible`;
  there is no `<noscript>` fallback.
- Control, same iframe setup with JS allowed and identical scrolling: `3/9` reveals become
  visible (`is-visible`, `opacity ≠ 0`).
- Hero/scene containers (`.otrn-act`, `.otrn-datos`) keep `opacity: 1` — the loss is the
  reveal cards and index cards, exactly as the static review described.

## V8 — Token conflict (F6) — runtime-confirmed

On `/`, both layers resolve simultaneously: `--aru-terra` = `#C0392B` at `:root` (uncommitted
landing fallback) vs `#DF8056` on the `.otrn.home` scope. The documented mismatch is real at
runtime, not just textual.

## V9 — Fallback routes (F16) — runtime-confirmed; new `/mision/` evidence

| Route | Result |
| --- | --- |
| `/` , `/noticias/`, `/geoportal/`, `/estadisticas/`, `/indicadores/` etc. | 200, otrn chrome present |
| `/?s=test` (search) | 200 **without** otrn chrome (parent block theme markup) |
| lost URL | 404 **without** otrn chrome |
| `/mision/` | 200 **without** otrn chrome (parent theme). The page is a *content source* for the front page (`functions.php:490-497`), but its standalone URL is live and un-styled |

## V10 — Migration state (F7 / F8 / F18) — updated evidence

- Site title still "Observatorio Turístico de **Rapa Nui**" (V3).
- "rapa" occurrences in rendered HTML: `/` 8, `/noticias/` 6, `/geoportal/` 3, `/acerca/` 5,
  `/mision/` 6, `/estadisticas/` 6, `/indicadores/` 6 — the F7 copy migration is clearly
  still pending on public pages.
- `sector` CPT is **seeded with 10 entries** (Rapa Nui geography) → the hardcoded fallback
  (`otrn-autodiagnostico.php:209-236`) is not presently the active path, but the data that
  feeds forms/maps is Rapa Nui regardless (F8).
- Evaluations exist (6; 3 published, 3 draft) with Rapa Nui-referencing slugs (F18 context).
- Page slugs required by the templates all exist (V3) — but only in the DB; `F18` stands
  (no seeding procedure in the repo; menus are absent too).

## V11 — ACF schemas (F4) — refined, still open

- Runtime: `acf_get_field_groups()` returns **0**; no `acf-field-group` / `acf-field` posts;
  no `acf_add_local_field_group()` anywhere in theme or mu-plugins; `docs/acf-json/` still
  does not exist.
- Custom fields nevertheless exist as **raw post meta** (e.g. the first `indicador` has
  keys `descripcion`, `institucion_responsable`, `nivel_territorial`, `fuente_datos`,
  `embed_url`), and templates mostly read them with `get_post_meta()` (a few `get_field()`
  calls fall back to raw meta in ACF).
- Consequence: **no ACF schema is defined in code, JSON or field groups — the "schema" is
  only implicit in the database values.** The F4 decision (define + export, or document the
  raw-meta model) is still pending; do not fabricate schemas.

## V12 — Executed commands (reproducible)

```bash
# Environment & inventory (prefix WP-CLI with `studio`)
studio --version && studio status
studio wp post-type list --fields=name,public,show_in_rest,has_archive --format=csv
studio wp taxonomy list --fields=name,public,show_in_rest --format=csv
studio wp role list --fields=name --format=csv
studio wp user list --format=count
studio wp theme list --fields=name,status,version --format=csv
studio wp plugin list --fields=name,status,version --format=csv
studio wp option get blogname
studio wp post list --post_type=evaluacion --post_status=any --fields=ID,post_status --format=csv
studio wp eval 'echo count(acf_get_field_groups());'

# Private gate (expect 302 -> wp-login)
curl -s -o /dev/null -w '%{http_code} -> %{redirect_url}\n' http://localhost:8895/evaluar/
curl -s -o /dev/null -w '%{http_code} -> %{redirect_url}\n' http://localhost:8895/autoevaluacion/

# REST exposure (counts only; do not paste titles into reports)
curl -s 'http://localhost:8895/wp-json/wp/v2/evaluacion' | \
  python3 -c 'import json,sys; d=json.load(sys.stdin); print("items:", len(d))'
```

Browser (same-origin iframes; run from the site tab):

```js
// 390px menu probe (F1): nav computed display + burger click
const f = document.createElement('iframe');
f.style.cssText = 'position:fixed;left:0;top:0;width:390px;height:820px;border:0;z-index:99999';
f.src = '/noticias/';
f.onload = () => {
  const d = f.contentDocument, nav = d.querySelector('.otrn-nav'), b = d.querySelector('.otrn-burger');
  console.log('nav', getComputedStyle(nav).display, 'burger', JSON.stringify(b.getBoundingClientRect()));
  b.click();
  setTimeout(() => console.log('after click', getComputedStyle(nav).display), 400);
};
document.body.appendChild(f);

// no-JS probe (F5): sandbox blocks scripts; compare with the same iframe without `sandbox`
f.setAttribute('sandbox', 'allow-same-origin'); // then scroll inside and read .otrn-reveal opacity
```

## V13 — NOT VERIFIED / BLOCKED (procedures preserved)

| Check | Verdict | Why / procedure |
| --- | --- | --- |
| Subscriber-level negative test for F3 | **BLOCKED** | Needs a second user; creating users was out of scope. Procedure: create a subscriber in a scratch environment, POST the form, confirm rejection after the fix (SECURITY_AUDIT §F3). |
| Draft save/resume, submit end-to-end, delete handler | **NOT VERIFIED** | Would mutate data. Requires an authenticated operator and a disposable evaluation. |
| Interactive Leaflet map, evaluation results page | **NOT VERIFIED** | Behind login; needs an authorized session. |
| Geoportal StoryMap territorial scope | **NOT VERIFIED** | External ArcGIS content not inspected (F9). |
| Mobile-menu keyboard / Escape / focus-trap pass | **NOT VERIFIED** | Needs a real device-width viewport (no device emulation available here). |
| CLS / performance sanity | **NOT VERIFIED** | No profiler in this environment. |
| Admin-side checks (ACF UI, plugin screens, role caps) | **NOT VERIFIED** | Not required for Phase 0 decisions; DTO procedures in SECURITY_AUDIT appendix. |
| Visual pixel review / screenshots | **NOT VERIFIED** | This pass used computed-style and HTTP measurements instead of screenshots. |

## V14 — Status after this pass (nothing fixed, nothing resolved)

| Finding | Before | After this pass |
| --- | --- | --- |
| F1 mobile nav | Open (static) | **FAIL confirmed** — ≤600 px both page types; 601–768 px internal visible but cramped; front panel works only 601–768 px — *fixed 2026-10-16, see §V15* |
| F5 no-JS reveals | Open (static) | **FAIL confirmed** — 9/9 hidden without JS; 3/9 appear with JS in the control |
| F6 token conflict | Open (static) | runtime-confirmed divergence (`#C0392B` vs `#DF8056`) |
| F11b REST exposure | verify at runtime | **CONFIRMED** — anonymous read of evaluations (titles/slugs), criteria, sectors; drafts safe |
| F16 fallback routes | verify at runtime | **confirmed** — search, 404 and `/mision/` render without otrn chrome |
| F4 ACF schemas | Open | refined — **0 field groups**; fields are raw post meta; decision still pending |
| F7 / F8 / F18 | Open (static) | evidence extended (copy counts; sector CPT seeded Rapa Nui; no menus; pages DB-only) |
| F3 capability check | Open | unchanged (1 account exists today; gap remains for future operator accounts) |
| F17 verification gap | Open | **partially executed** — remaining items in V13 |
| F14, F2, F9, F10, F12, F13 | same | unchanged (decisions / static evidence only) |

**Priority impact (proposed, not approved):** F11b is now the only *confirmed* exposure and
should be fixed together with F3 as the Phase 1 security scope; F1 and F5 are confirmed UX
defects for Phase 2; nothing gained P0 status; Phase 0's remaining decisions (F14 commit
strategy, F4 workflow) and all client decisions are unchanged.

## V15 — F1 navigation: fix, authenticated regression and measured breakpoints (2026-10-16)

Fix commits (local, not pushed): `0dd2de4` (global menu stylesheet + JS) and `e727756`
(regression fix). Method: same-origin iframes at each width on `/` and `/noticias/` (media
queries active) plus the real top-level page; the authenticated header (11th link +
`#wpadminbar` + `html{margin-top}`) was reproduced in the DOM because signing in would write
session tokens to the DB.

**Root causes of the regression reported in an authenticated session**

1. Front page: `.otrn.home .otrn-header{position:fixed;top:0}` (`otrn-arauco.css`) ignored
   core's `html{margin-top:32px !important}` (that only shifts static flow), so `#wpadminbar`
   (`position:fixed;z-index:99999;height:32px`) painted over the header's first 32 px
   (logo, links and button). Internal pages use a `static` header and were pushed down — the
   homepage-only symptom.
2. `.otrn-nav{gap:26px}` with shrinking links: the row wrapped into 2–3 lines instead of
   staying single-line, and on the front page the overflowing «Salir» button was clipped
   off-screen (a `fixed` element creates no scrollbar).

**Measured row widths** (13.5 px text; `.otrn .wrap` caps at 1440 px, i.e. 1376 px usable)

| Case | Before (`gap:26px`) | After (`gap:18px`) | Verdict |
| --- | --- | --- | --- |
| 10 links, anonymous | 1029 px → needs 1382 px viewport | 957 px → needs 1274 px | fits from 1338 px (front page, widest 158 px brand) |
| 11 links, authenticated | 1250 px → needs 1568 px | 1170 px → needs 1424 px | **never fits** (usable max 1376 px) |

**Before → after (selected)**

| Page | Width | State | Before | After |
| --- | --- | --- | --- | --- |
| `/noticias/` | 1366 | anonymous | 3 wrapped labels (nav 41 px tall) | single line, 0 wrapped |
| `/noticias/` | 1440 | authenticated | 4 wrapped labels | panel (360 px), 0 wrapped |
| `/` | 1366 | authenticated | 4 wrapped labels + «Salir» clipped (right edge 1438 px on a 1366 px viewport) | panel; header and burger below the bar |
| `/` | 1440 | authenticated | header top 0 px → top 32 px hidden under the bar | header top 32 px, panel 32→viewport bottom, first link at 128 px |

Across 1920/1600/1440/1366/1361/1360/1280/1024/768/390 px on both page types: 0 broken
labels, 0 px header overflow, 0 horizontal page overflow, header/burger below the bar, and
the bar height (32 px, 46 px at ≤782 px) reflected in the header and panel offsets.

**Keyboard and clicks (real top-level page, 1038 px, panel mode)**: Enter opens and moves
focus to the first link; Tab cycles the 10 links and wraps last→first; Shift+Tab wraps
first→last; Escape closes, restores the label to «Abrir menú de navegación», sets
`aria-hidden=true` and returns focus to the button; real clicks on the burger, on a panel
link and outside the panel all close and return focus. Closed panel = nav links absent from
the accessibility snapshot; open = `aria-expanded=true` and links exposed. No console errors.
Resize 1361→1360→open→1361→1440 resyncs (`aria-hidden` in panel mode, removed in row mode).

**Still NOT VERIFIED**: a real authenticated session (login), real touch devices, the iOS
dynamic viewport, and `prefers-reduced-motion: reduce` emulation (the rule exists in CSS
only).

## V16 — F1 redesign: grouped navigation, account icon, measured breakpoints (2026-10-16)

Second round on top of §V15: the client approved grouping the 11 destinations into primaries
plus dropdowns and replacing the login/logout button with a person icon. Commits: local,
listed in [HANDOFF.md](HANDOFF.md). Files: `header.php`, `functions.php`,
`assets/css/otrn-menu.css`, `assets/otrn-menu.js`. **Status: pending explicit UX/UI approval
of the visuals** — nothing here is signed off.

**Method.** Same-origin iframes with a cache-buster at each width, so the media queries apply
without touching the harness window (the real window is ≈1038×803 px). The authenticated
state was produced by a **temporary mu-plugin** (`wp_set_current_user(1)` when
`?otrn_sim_auth=1`), which renders the *real* server-side header, the real admin bar and the
real `logged-in admin-bar` body classes **without a login and without writing any session
token to the DB**; the file was deleted after the pass and `git status` confirms a clean
tree. This supersedes the DOM-injection method of §V15.

**Row width per collapse stage** (measured with `width:max-content`; worst case = front-page
brand, 158 px; `.otrn .wrap` padding 64 px, capped at 1440 px):

| Stage | Row advertises | Row width (anonymous) | Row width (session) | Minimum viewport | Threshold chosen (slack) |
| --- | --- | --- | --- | --- | --- |
| Full row | 6 / 7 primaries | 927 px | **1066 px** | 991 / **1130 px** | **≥1152 px** (+22) |
| Narrow row | 5 primaries + «Más» | 831 px | 831 px | 895 px | **910–1151 px** (+15) |
| Compact row | 3 primaries + «Más» | 590 px | 590 px | 654 px | **680–909 px** (+26) |
| Drawer | all destinations | — | — | — | **≤679 px** |

The thresholds are derived from those measurements, not from device categories. The
session-only Autoevaluación group costs 139 px, which is why the full row is only offered
from 1152 px even though the anonymous row would fit from 991 px.

**Width matrix** (`/` and `/noticias/`, anonymous and authenticated, 1920/1440/1366/1280/
1152/1151/1024/960/910/909/768/700/680/679/600/390/375 px): **0 px horizontal page
overflow at every width on both pages**, single-line rows (nav list height 18 px), 0 collapsed
labels, brand-to-row clearance ≥86 px at the tightest point, and the expected contents per
stage. Admin bar: 32 px above 782 px and 46 px at/below it, with the header top matching
exactly on the front page (`fixed`) and on internal pages (`static`) — the §V15 defect does
not come back. Drawer lists all destinations and never «Más».

**Interaction pass** (synthetic `click`/`mouseenter`/`keydown` in the iframes; real clicks
were not possible while the browser window was not visible):

| Check | Result |
| --- | --- |
| Group dropdown open/close, `aria-expanded`, panel 12 px below the trigger, inside the viewport | pass |
| Only one group open at a time | pass |
| Hover opens / leaving closes (fine pointer) | pass |
| Click outside closes | pass |
| Escape closes and returns focus to the trigger button | pass |
| Choosing a link closes the panel (click default prevented in the test) | pass |
| Drawer: burger → `otrn-menu-abierto`, `aria-expanded`, drawer in view, focus on the first link | pass |
| Drawer: accordions open/close, several open at once, links focusable (2/2 for Datos) | pass |
| Drawer: Tab from the last focusable wraps to the first; Shift+Tab from the first to the last (9 focusables) | pass |
| Drawer: Escape closes an open accordion first, then the drawer, and focus returns to the burger | pass |
| Drawer ↔ account menu mutual exclusion (opening one closes the other) | pass |
| Account menu: opens, `aria-expanded`, «Cerrar sesión» → `/wp-login.php?action=logout`, Escape returns focus to the icon, outside click closes, panel inside the viewport at 390 and 1440 px | pass |
| Account icon: 44×44 px box, 21 px glyph, `border:0`, transparent background, no shadow, `z-index:61` above the drawer (hit-tested with `elementFromPoint`) | pass |
| Resize resync (drawer → row and back): no stale `aria-hidden` or open panels | pass |
| Console errors on the authenticated page | none |

**Contrast.** A per-element scan of `.otrn-header` in nine states (front page anonymous, a
dropdown open, authenticated, account menu open, `/noticias/` and `/indicadores/` with the
current child visible, tablet «Más» panel, mobile drawer with an accordion open) reports **0
WCAG AA failures**. `--primary` (#DF8056, 2.6:1) was replaced by `--primary-dark` (#A05C3D,
5.1–5.4:1) for active/hover text and focus outlines, and the «Más» headings moved from
`--text3` (3.5:1) to `--text2` (5.7:1). Lighthouse accessibility on the front page: 0.92, with
the only contrast failure in pre-existing landing content (`.otrn-stage__cue`, `.otrn-act__*`
over images), not in the header.

**Defects found and fixed during this pass**: (1) the closed «Más» panel added 99 px of
horizontal page overflow on internal pages (the header is `static` there, so an absolutely
positioned panel extends the document) — the panel is now right-anchored; (2) the «Más» item
leaked into the mobile drawer; (3) `esFocable()` treated `visibility:hidden` panels as
focusable; (4) Escape could return focus to `<body>` when the browser had not focused the
burger, so the burger is used as the fallback target.

**Follow-up after client review (2026-10-16, `510e981`).** The client reported the desktop
dropdown destinations as «muy juntos y pegados». The cause was the cascade, not the value:
the landing reset `.otrn-header .otrn-nav a` has the *same* specificity (0,2,1) as
`.otrn-header .otrn-subnav a` and comes later in the file, so it cancelled the `padding` of
the destinations, which rendered 18 px tall with no separation. The reset now targets
`.otrn-nav__enlace` / `.otrn-nav__boton`; each destination is a row of 12 px padding with a
2 px gap and an inset rounded highlight, the «Más» headings are indented to match, and the
account menu reuses the same row. Re-measured at 1440/768/700/390 px (anonymous and simulated
session): rows **42 px** with a 2 px gap; panels 220 px wide (Autoevaluación: 3 destinations,
151 px tall, right edge 1429 px at 1440 px; Planificación: «Iniciativas Pladetur» on one
line); «Más» 250×438 with headings and destinations both starting 9 px from the panel edge
(21 px of text indent); account panel 190×66 with a 44 px row; mobile accordion unchanged
(`padding:11px 0`, `border-radius:0`, no gap, 1 px separator); **0 px page overflow from 1920
to 375 px**; and at 700 px on the front page the landing rules do not leak into the dropdown
(`padding:12px`, `border:0 none`, 13.5 px/600, `#2B2B2B`). **Still pending: the client's
visual confirmation of the new spacing** — screenshots remain impossible while the harness
window is not visible.

**NOT VERIFIED in this pass**: the rendered focus ring and real keyboard modality (the
harness refuses key events while its window is hidden — the `:focus-visible` rules are read
from the stylesheet, not exercised); real touch devices and the iOS dynamic viewport;
`prefers-reduced-motion: reduce` emulation; a real login (the session was simulated
server-side). Also untouched by design: the landing/internal page content, the private gates
(re-confirmed 302/302/302 and `/criterio/` 301) and the orphan routes.

## V17 — Municipal token implementation (F2/F6) — applied and measured (2026-10-16)

Phase 2 of the approved direction: the global design tokens were consolidated onto the
verified Municipalidad de Arauco identity and applied site-wide. **This pass did change
application code** (`style.css`, the three asset stylesheets and seven PHP templates). It does
not change content, URLs, authentication, database rows or the private gate. The mapping was
approved by the client before implementation; the **visual result is still pending the
client's sign-off** (the F1 nav review is likewise still open).

**Scope of the change**

| Area | Change |
| --- | --- |
| `style.css :root` | Single authoritative token block (brand + neutrals + semantic status + rhythm + radii + fonts). 14 legacy tokens removed. |
| `assets/css/otrn-arauco.css` | Rewritten: the front-page `--aru-*` token block deleted; only the fixed translucent header and the footer top margin remain. |
| `assets/css/otrn-landing.css` | Unscoped `:root` `--aru-*` fallback deleted (the F6 conflict); every `--aru-*` use remapped; dead `.otrn-home-menu` rule removed; `:root` veil/shadow colours converted to the institutional blue. |
| `assets/css/otrn-menu.css` | Comments only (stale coral/terracota and `--text3` references); two dropdown radii moved to `--radius`. **No behavioural or colour change.** |
| PHP (7 files) | `--text3` → `--text2`, literal mono font → `var(--font-mono)`, inline `border-radius:14px` → `var(--radius)`, `rgba(51,51,51,…)` → `rgba(0,0,0,…)`, map legend/JS tier colours → semantic status tokens, chart/sparkline series colours → brand tokens. |
| Explicitly **not** touched | Content, copy, URLs, `functions.php` logic, the private gate, the account icon markup, `assets/otrn-landing.js`, vendor files. |

**Token resolution** (computed `:root`, `/`): `--primary #0070FF`, `--primary-dark #0050B8`,
`--primary-soft #E7F1FF`, `--primary-tint #BBD8FF`, `--secondary #DE232B`, `--accent #41BCFF`,
`--blue-dark #304A83`, `--dark #000000`, `--text2 #707070`, `--grey #E0E0E0`,
`--cream #F8F8F8`, `--lgrey #F6F6F6`, `--paper #FFFFFF`, `--success #0F7B4E`,
`--warning #8A5A00`, `--error #C0272D`, `--neutral #52525B`, `--radius 10px`,
`--radius-sm 6px`, `--radius-pill 30px`, `--font-title Montserrat`, `--font-mono IBM Plex
Mono`, `--space-section clamp(48px,8vw,120px)`. All 26 tokens resolve; `--text3`,
`--secondary-soft`, `--blue-dark-soft` and every `--aru-*` name are gone from the tree (grep).

**Responsive pass — 63 combinations, 0 px horizontal overflow.** 7 pages (`/`, `/indicadores/`,
`/seguimiento-pladetur/`, `/material/documentacion-tecnica/`, `/buenas-practicas/`,
`/noticias/`, `/acerca/`) × 9 widths (375, 414, 679, 680, 768, 910, 1152, 1440, 1920 px),
measured as computed styles in same-origin iframes. Navigation behaviour is unchanged at every
width: burger `display:flex` at ≤679 px with `.otrn-nav` as a `fixed` full-height panel, and
burger `display:none` with `.otrn-nav` `static` from 680 px up — identical on the front page
and on internal pages, i.e. the rewritten `otrn-landing.css` did not disturb the F1 nav.

**Contrast pass — 0 WCAG AA failures.** A per-element computed-style scan (every text-bearing
element, effective background resolved by walking ancestors, large text judged at its own 3:1
threshold) over the 11 public pages above: **1 143 elements checked, 0 failures**. 10 elements
on `/` were excluded because they sit on the immersive photo layer (scene text over
photographs — not measurable from computed styles and unchanged by this pass).

A synthetic lab page injected into `/indicadores/` exercised the state and scoring components
that only occur on the private pages (`tier-high/mid/low/na` KPIs, status pills, score boxes,
score tags `s0`–`sna`, the 0–3 maturity scale, notices, tags, priorities, tabs, buttons):
**75 elements, 0 failures**. Measured pairs (text on tint): success `4.69`, warning `5.29`,
error `4.85`, neutral `7.15`; score tags `s0 4.85`, `s1 6.49`, `s2 5.06`, `s3 7.40`,
`sna 7.15`; `.otrn-tag` 6.49 on `--primary-soft`; `.otrn-tag.borrador` / table head /
question index / inactive tab 4.58 on `--lgrey`; white on `--primary-dark` 7.40; white on
`--secondary` 4.79; `.otrn-dato` white on `--blue-dark` 8.63.

**Contrast defects found and fixed during this pass** (all were latent, exposed by the darker
brand blue):

1. `.otrn-table thead th` and every other `--text2` label on a neutral fill failed AA
   (4.35:1 on the old `#F0F0F0`): the fill token moved to `#F6F6F6` (4.58:1) and the row-hover
   fill from `--primary-soft` (4.35:1) to `--lgrey` (4.58:1).
2. `.otrn-stat.accent` kept `--text2` on `--primary-soft` (4.35:1) → its label now uses
   `--primary-dark` (6.49:1).
3. `.otrn-notice.ok` kept `--text2` on `--success-soft` (4.38:1) → the notice now takes
   `--success` (4.69:1).
4. `.otrn a.otrn-rowlink` and `.otrn a.otrn-more` lost their colour to the existing
   `.otrn a{color:inherit}` rule (0,1,1). With the old light coral the black text was still
   readable; with `--primary-dark` the solid row link dropped to **2.84:1**. Both link
   variants now carry an explicit higher-specificity rule (mirroring the existing
   `.otrn a.otrn-btn` fix) and the row-link hover darkens to `--blue-dark`.
5. The landing «outline» CTA (`Ver todos los indicadores`) keeps its white border, which is
   invisible on the light section where it is also used; the light-section variant now takes
   the neutral border and `--primary-dark`.

**Unchanged functionality, re-checked**: nav destination list and URLs (identical), account
control (`<a class="otrn-cuenta__boton" aria-label="Iniciar sesión">` with the 21 px inline
SVG person glyph, 44×44 hit box), private gate `/evaluar/`, `/autoevaluacion/`, `/mapa/` →
302 to `wp-login.php` and `/criterio/` → 301 (same as §V16), and the PHP syntax check of every
modified template (`php -l`, all clean).

**NOT VERIFIED in this pass**: the visual result (no screenshot is possible while the harness
window is not visible, so every statement here is a computed style); the private pages as
rendered (their components were exercised synthetically, and their gate was confirmed, but the
pages themselves were not opened); text over photographs on the landing (excluded above);
non-text contrast of form-control borders against white (WCAG 1.4.11 — the single light
`--grey` border is a documented gap pending a UX decision); the rendered focus ring, real
keyboard modality, real touch devices, the iOS dynamic viewport and `prefers-reduced-motion`.

## V18 — Client review of the token pass: two defects fixed (2026-10-16)

The client reviewed the rendered result (logged in, so the admin bar is present) and reported
**two defects**; both are fixed in local commits after `52a7d4d`.

### V18.1 Desktop dropdown closed before the pointer reached it (F1) — `3dbfbc6`

**Report:** *"no puedo acceder al menú ya que desaparece antes de posicionar el mouse sobre el
menú desplegado"* (the «Datos» panel, screenshot). **Cause:** `otrn-subnav` is positioned
`top: calc(100% + 12px)`, so between the trigger and the panel there is a 12 px band that does
**not** belong to the group `<li>`. Crossing it fired the `li`'s `mouseleave`
(`assets/otrn-menu.js:163`), which closed the panel — and the same happened to the no-JS
`:hover` fallback. This is the gap introduced by the `510e981` spacing fix. **Fix:** a
hit-area "mouse bridge" the exact height of the gap, as an `::after` of the group
(`.otrn-nav__item--grupo::after`, `--mas` included), inside the existing
`@media (min-width:680px)` row-mode block. It hangs off the group rather than the panel
because the «Más» panel has `overflow-y:auto` and would clip a child pseudo-element, and it is
excluded from the mobile accordion (≤679 px, `position:static` subnav) so it cannot cover the
accordion button. It does not overlap the trigger, so it intercepts no clicks.

**Verification** — hit-testing with `elementFromPoint` on the exact descent path (from the
trigger's top edge to just inside the panel, every 3 px):

| Page (width) | Mode | Path stays inside the group | Trigger still hit at its centre | Bridge computed |
| --- | --- | --- | --- | --- |
| `/` 1024 px | row | yes (10/10 points) | yes | `"" / top:17.55px / h:12px` |
| `/` 768 px | row | yes | yes | present |
| `/` 700 px | row | yes | yes | present |
| `/indicadores/` 1440 px | row | yes | yes | present |
| `/indicadores/` 1152 px | row | yes | yes | present |
| `/indicadores/` 910 px («Más») | row | yes | yes | present |
| `/indicadores/` 768 px («Más») | row | yes | yes | present |
| `/indicadores/` 700 px («Más») | row | yes | yes | present |
| `/indicadores/` 679 px | accordion | n/a | n/a | **`content:none`** (absent) |
| `/indicadores/` 375 px | accordion | n/a | n/a | **`content:none`** (absent) |

The «button still hit at its centre» column is what rules out the bridge swallowing clicks on
the trigger: the scan returns `button.otrn-nav__boton` for every point inside the button and
`li.otrn-nav__item` only below its bottom edge.

**NOT VERIFIED:** the mobile accordion button under the open drawer (the harness iframe never
completes the drawer's `transform` transition while the browser window is not visible, so
`elementFromPoint` returns `null` there) — but the bridge does not exist in that mode, so the
change cannot affect it; and the account panel's bridge (the panel only renders with a session,
and a real login was not performed) — same mechanism, reasoned only.

### V18.2 Drawer content read as secondary text (F2) — `9142179`

**Report:** the criterion question and the "what each answer means" options looked washed out.
**Cause:** both are the content the evaluator must read, but `.otrn-drawer-preg p` used
`--text2` on `--lgrey` (4.58:1) and `.otrn-drawer-nivel .txt` used `--text2` on white
(4.95:1) — AA by the bare minimum, and inconsistent with `.otrn-drawer-ind`, which is black.
**Fix:** both now use `--dark`.

**Measured** (synthetic reproduction of the drawer DOM, real markup and classes, computed
styles): question **19.43:1**, answer descriptions **21:1** (18.42:1 on the highlighted row),
19 elements checked, 0 failures. Unchanged: the `.et` labels (4.58:1 inside the grey panel,
4.95:1 on white — AA) and the level chips `o0 4.85` / `o1 6.49` / `o2 5.06` / `o3 7.40` /
`ona 7.15`. The labels were left in `--text2` on purpose to keep the hierarchy; if the client
wants them darker it is a one-line change.
