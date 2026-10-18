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
`--grey` border is a documented gap that the client **decided to leave as is**, 2026-10-16); the
rendered focus ring, real keyboard modality, real touch devices, the iOS dynamic viewport and
`prefers-reduced-motion`.

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

## V19 — Client review, second round: home KPIs and slide legibility (2026-10-16)

The client reviewed the **home page** and left two notes, both with a screenshot: *"los KPI que
son solo informativos se ven iguales a los elementos clicables"* and *"los slides del home
tienen poca legibilidad"*. Fixed in **`e2c3a9f`** — only `assets/css/otrn-landing.css` and
`assets/otrn-landing.js`: **no PHP, no markup, no content, no route and no database change**,
and nothing outside the front page.

**Screenshots work again.** In this round the harness desktop window was visible, so
`browser.screenshot` succeeded for the first time: the immersive scenes 2 and 3 were looked at
in the real page, and the static (mobile) rendering was looked at through a same-origin iframe
placed inside the visible viewport (400 px wide, real markup and CSS). This is a **developer
look** at the rendered result — it does not replace the pending client sign-off, and it does not
change the "unverifiable" wording of §V17/§V18 for the items listed at the end of this section.

### New method — contrast of text over a photograph, pixel by pixel

Text over the scene photographs could not be measured before ("unverifiable from computed
styles", §V17 and DESIGN_SYSTEM.md §6). It is now measured deterministically: the scene image is
loaded, drawn on a `<canvas>` with **the same geometry the CSS uses** (`cover` cropping, centred
in static mode, `object-position:center 42%` in immersive mode), the veil is composited per
pixel, and the contrast is computed against the **real text colour including its alpha**
(`rgba(255,255,255,.85)` for eyebrows, `.9`/`.92` for leads). Per element the table reports the
**minimum** over its glyph box (the worst pixel), the 5th percentile and the mean. The model
ignores the rail's `backdrop-filter:blur(6px)`, which only averages the neighbourhood and
therefore softens extremes.

### V19.1 Informative KPIs read as clickable cards (F20) — `e2c3a9f`

**Cause:** `.otrn-dato-lite` is an `<article>` — it links nowhere — yet it carried exactly the
surface the actionable cards carry: `--paper` background, 1 px border, panel radius and a soft
shadow, i.e. the same treatment as `.otrn-indexcard` (indicator cards, `<a>`) and the news cards
(`.otrn-card`). **Fix:** the informative figure loses the card chrome and becomes a flat data
block — no background, no enclosing border, no radius, no shadow, and a 2 px `--primary` rule on
top that orders the column. The actionable cards were not touched.

| `.otrn-dato-lite` at 400 px | Before | After |
| --- | --- | --- |
| `background-color` | `rgb(255,255,255)` | `rgba(0,0,0,0)` |
| border | `1px solid rgba(48,74,131,.14)` | `border-top:2px solid rgb(0,112,255)` |
| `border-radius` | `10px` | `0px` |
| `box-shadow` | `rgba(0,0,0,.06) 0px 1px 3px` | `none` |
| `padding` | `26px 24px 22px` | `20px 0px 0px` |

Re-checked in the same run: `.otrn-indexcard` keeps `rgb(255,255,255)` background, a 1 px solid
border, `10px` radius and the soft shadow, so the two families are now visually distinct. Figure
text contrast is unchanged (number `--blue-dark` 8,63:1; label measured `#000000` because the
`.otrn h3` rule wins over the label class → 21:1; year `--text2` 4,95:1 — all on white). Eight
widths 375→1920 px: **0 px horizontal overflow**.

### V19.2 Scene text over the photographs (F21) — `e2c3a9f`

**Cause** — the veil never covered the text, in either mode:

- **Static** (≤768 px, no JS, reduced motion): the hero's veil was a weak vertical gradient
  (0.18 at the top, 0.34 at mid-height) exactly where the text starts, and scenes 2 and 3 used a
  **horizontal** gradient (0.82 at the left edge, 0.46 at 38 %, 0.06 at 68 %) while on mobile the
  text column spans the full width — so the right half of every paragraph sat on unveil
  photograph.
- **Immersive** (≥769 px + GSAP): scene 2's text had **no veil at all** (the shared veil was only
  faded in at timeline 5.8, with the coastal scene) and scene 3's veil died at 42 % of the window
  width while the text column reaches 620 px, so it too crossed into the photo.
- **Immersive hero**: below ~1300 px window width the 640 px column extended past the
  photographic panel (54 % of the window) and left dark text over a bright photo — measured at
  809 px: the lead reached 575 px while the panel began at 437 px.

**Fix:** (a) the static mode gets **one vertical veil for all three scenes** (0.12 → 0.55 at
10 % → 0.88 at 20 % → 0.90 at 62 % → 0.92), i.e. 0.88 before the text band — which runs 21 %→84 %
of the act height — keeping the top strip photographic; (b) the immersive veil takes **pixel
stops** (0.92 at 0 → 0.86 at 640 px → 0.34 at 900 px → 0 at 1160 px, matching the widest text
column at any window width) and the timeline fades it in at **1.6** instead of 5.8, before scene
2's text appears at 3.0; (c) the panel width is declared once (`--panel-w` on
`.otrn-stage.is-immersiva`, consumed by the clip-path *and* by the hero column) and the hero
column is `min(640px, calc(var(--panel-w) - 56px))`; (d) the rail's active 11 px number moves
from `--primary-dark` to `--dark`.

**Measured — static mode, 400 px** (minimum / 5th percentile / mean per element):

| Scene | eyebrow 12 px | title 34 px | lead 16 px | CTA |
| --- | --- | --- | --- | --- |
| hero | 5,09 / 5,13 / 5,50 | 6,43 / 6,72 / 7,60 | 5,84 / 6,11 / 7,23 | 7,21 / 7,35 / 8,48 |
| territorio | 5,23 / 5,44 / 5,86 | 6,49 / 7,01 / 7,27 | 5,66 / 5,97 / 6,57 | 6,82 / 7,09 / 8,37 |
| costa | 5,07 / 5,08 / 5,63 | 6,36 / 6,58 / 7,45 | 5,81 / 6,21 / 7,23 | 7,93 / 8,06 / 8,84 |

**Measured — static mode forced at 1440 px** (immersive class removed and inline styles cleared,
i.e. the no-JS / reduced-motion layout; minimum only, text bands as % of the act height):

| Scene | text band | eyebrow | title | lead | CTA |
| --- | --- | --- | --- | --- | --- |
| hero | 28,8→71,2 % | 5,34 | 6,52 | 6,08 | 7,43 |
| territorio | 26,5→73,5 % | 5,65 | 7,20 | 6,04 | 7,13 |
| costa | 22,9→76,5 % | 5,29 | 6,45 | 5,78 | 7,69 |

**Measured — immersive scene 2** (minimum per element, four window widths):

| Width | eyebrow | title | lead | CTA |
| --- | --- | --- | --- | --- |
| 809 px | 5,06 | 6,84 | 5,76 | 6,58 |
| 1024 px | 5,56 | 6,71 | 6,00 | 7,19 |
| 1440 px | 5,69 | 6,88 | 6,09 | 7,32 |
| 1920 px | 5,69 | 6,91 | 6,15 | 7,47 |

Scene 3 (darkest photograph) at 809 px: **4,88** / 6,50 / 5,48 / 7,57 — the lowest figure of the
pass, still above AA. Scene 1 is read on paper (dark text on white), not on the photo.

**Measured — hero column vs photographic panel** (text right edge / panel left edge): 359/415 at
769 px, 430/486 at 900, 497/553 at 1024, 592/648 at 1200, 635/691 at 1280, 640/778 at 1440 — the
text stays inside the paper at every width, and from ~1300 px up the column is the approved
640 px.

**Measured — scene rail** (active 11 px number on the translucent plate
`rgba(255,255,255,.46)` over each photograph; minimum over the glyph box): before 3,83 / 3,60 /
**2,33** (1024 px) and 3,80 / 3,45 / **2,44** (1440 px) — below AA; after (text `--dark`)
10,89 / 10,24 / **6,62** (1024 px), 10,78 / 9,81 / **6,94** (1440 px) and 11,19 / 10,15 / 7,36
(1920 px).

**Regression checks in the same pass:** 8 widths 375→1920 px with **0 px horizontal overflow**;
the static veil is a single unconditional rule, so it also applies to the no-JS and
reduced-motion paths (the 1440 px forced-static run above); the immersive veil lives inside the
photographic level, so it inherits the panel's `clip-path` and can never tint the "paper" the
hero text is read on; no PHP file, template, route, menu asset or database record was touched
(the menu/dropdown behaviour and the private gate are untouched by this commit).

**NOT VERIFIED:** the client's judgement of the veil's visual weight — with 0.88-0.92 over the
text band the mobile scene photographs read as a blue-tinted backdrop whose detail survives
mainly in the top ~20 %; **AA against the brightest pixel of each photograph is what forces
≈0.87-0.88**, so a lighter veil keeps more photograph and gives up that worst case (a client
decision, not a code task); scene 3's immersive veil is the same element as scene 2's, so it only
was measured at 809 px; the reduced-motion *preference* could not be emulated in the harness (the
layout is the measured forced-static one, but the media-query path itself is reasoned); and real
touch devices, iOS dynamic viewport and real keyboard modality stay as recorded in §V13/§V18.

## V20 — Client review, third round: mobile scenes, photograph visibility and glass on the text (2026-10-16)

The client went back to the **home page on mobile** (a screenshot came with the note): *"En mobile
no se alcanzan a ver las imágenes de fondo debido al degradado. El problema es específicamente con
las bajadas de texto que no se leen bien en mobile. ¿Podrías probar con efecto glass en las bajadas
de texto?"* — i.e. the veil solved legibility at the price of hiding the photograph, and the ask is
to try a **glass effect on the lead paragraphs**.

Fixed in **`649b01f`** — one file, `assets/css/otrn-landing.css`: the mobile block of the
`@media (max-width:768px)` query plus two colour rules outside it. **No PHP, no markup, no
content, no route and no database change**, nothing outside the front page, and nothing above
768 px except the scene eyebrows (§V20.5).

**Revised the same day — fourth round, `05d2ecb` (§V21):** the client came back with a marked-up
screenshot of scene 3 — *«El efecto glass es solo para el texto demarcado con rojo. Así se podrá ver
la imagen de fondo»*, the red box drawn around **the lead only** — so the plate was reduced to the
lead and the eyebrow, the title and the CTAs returned to the photograph with a text-shadow. §V21
**supersedes §V20.2-§V20.4 for mobile**; the method (§V20.1), the fallback and the desktop notes
still stand.

This **supersedes, for mobile only, the eighth round's decision** (*"velo 0.88-0.92: se mantiene"*,
[ISSUES.md](ISSUES.md) F21, commit `ce9a093`): the same client, after seeing the result, now asks
for the photograph to be visible. Desktop (immersive and static) keeps the measured 0.88-0.92
veils.

### V20.1 Method (and its limits)

The static/mobile layout was measured between **320 and 768 px** with a temporary harness
(not versioned) that reproduces, for every text element of the three scenes:

1. the **photograph**, drawn on a canvas with the same framing as the CSS (`cover` + `center`);
2. the **veil**, rebuilt from the element's computed gradient and evaluated at the text's own
   y position;
3. the **glass panel**, modelled as the **box average** of the (photo + veil) underneath, with
   radius = the CSS `blur()` radius, then the panel tint composited over that average — a
   **conservative lower bound**, because the real filter is a Gaussian (σ = radius) that averages
   a much wider neighbourhood and therefore flattens bright specks further;
4. the **text colour** (alpha included) composited over the resulting background, and the WCAG
   2.1 contrast ratio.

Two deliberate refinements over the §V19 method: the sample area is now the **real text line
boxes** (`Range.getClientRects()` per text node), not the element box — a short eyebrow was
previously judged over the ~360 px of its `<p>` including empty space — and **no assumption is
made about the image**: the actual asset is drawn, so the bright top band of each photograph (the
part that forces the veil) is measured as it is, not estimated from a mid-tone. The photograph is
the real asset (`.webp`/`.jpg`) at the real layout size, and the panel geometry is read from the
live page.

### V20.2 What changed

| Item | Before (`ce9a093`) | Now (`649b01f`, ≤768 px) |
| --- | --- | --- |
| Veil over the scene | one vertical veil, 0.12 / 0.55 at 10 % / 0.88 at 20 % / 0.90 at 62 % / 0.92 | clear gauze: 0.06 / 0.24 at 26 % / 0.34 at 58 % / 0.42 |
| Text surface | none (text sat on the veil) | **glass panel**: `rgba(48,74,131,.65)` + `backdrop-filter:blur(16px) saturate(1.15)`, `--radius`, covering eyebrow + title + lead + CTA |
| Scroll cue (hero) | white 85 % text over the veil | same glass in **pill** form, text at full white |
| Scene eyebrow (12 px) | white 85 % | `--paper` (also above 768 px: an increase there) |
| Ghost CTA border | `rgba(255,255,255,.55)` | `rgba(255,255,255,.8)` (WCAG 1.4.11, see V20.4) |
| No `backdrop-filter` | — | `@supports not (...)`: tint raised to **0.82**, which holds AA without any blur |

Panel geometry: `.otrn-act .wrap.otrn-act__inner` — `width:auto; margin:0 8px; padding:20px 12px`,
so the text sits 20 px from the viewport edge and the panel 8 px. Because the inner is a flex item
with `width:auto`, the panel **hugs its content**: full width (viewport − 16 px) up to 480 px, and
from 481 px the width of the lead's `max-width:52ch` + padding (**575 px** in Montserrat at 16 px)
— at 768 px that leaves **185 px** of photograph to the right of the panel. Nothing in the
desktop paths changed: at 1200 px the inner has no background and no `backdrop-filter`, the act's
veil declaration is still the base 0.12-0.92 profile (hidden by the immersive mode, as before), the
cue is `--text2` without glass and the hero eyebrow stays `--primary-dark` (it is read on paper).
The glass rules live inside the `@media (max-width:768px)` query, so neither the desktop static
path (no JS / reduced motion) nor the immersive path can reach them.

### V20.3 Measured contrast — final state (minimum / mean per element)

Photograph visibility is the share of the act's height where the veil is below 0.40, i.e. where
the photograph is seen essentially unveiled: **89.0 / 89.6 / 89.2 %** (hero / territorio / costa)
at 400 px, versus **6.8 / 7.2 / 6.7 %** with the previous veil. Horizontal overflow: **0 px** at
all five widths measured.

| Width | Scene | eyebrow 12 px | title 30-41 px | lead 16 px | CTA | cue 11 px |
| --- | --- | --- | --- | --- | --- | --- |
| 320 | hero | 4,78 / 5,20 | 4,83 / 6,16 | **4,70** / 6,57 | 6,67 / 8,47 | 6,88 / 7,76 |
| 320 | territorio | 5,51 / 6,05 | 5,70 / 5,97 | 4,95 / 5,56 | 7,75 / 8,19 | — |
| 320 | costa | 5,58 / 5,96 | 4,91 / 6,16 | 4,92 / 6,91 | 7,86 / 9,02 | — |
| 375 | hero | 4,87 / 5,51 | 5,16 / 6,62 | 4,85 / 6,59 | 6,45 / 8,25 | 6,95 / 8,03 |
| 375 | territorio | 5,59 / 5,99 | 5,74 / 6,01 | 4,88 / 5,61 | 7,08 / 8,11 | — |
| 375 | costa | 5,53 / 5,93 | 4,98 / 6,32 | 5,03 / 6,82 | 8,05 / 9,36 | — |
| 400 | hero | 4,86 / 5,56 | 5,22 / 6,70 | 4,82 / 6,65 | 6,32 / 8,08 | 7,04 / 8,35 |
| 400 | territorio | 5,74 / 5,97 | 5,75 / 6,02 | 4,93 / 5,58 | 6,83 / 8,16 | — |
| 400 | costa | 5,53 / 5,93 | 4,88 / 6,41 | 5,04 / 6,82 | 8,09 / 9,38 | — |
| 480 | hero | **4,68** / 5,61 | 5,21 / 6,87 | 4,72 / 6,63 | 6,12 / 7,93 | 6,85 / 8,43 |
| 480 | territorio | 5,95 / 6,01 | 5,77 / 6,07 | 4,80 / 5,57 | 6,00 / 8,38 | — |
| 480 | costa | 5,55 / 5,93 | 4,72 / 6,82 | 5,03 / 6,80 | 8,28 / 9,37 | — |
| 768 | hero | 6,88 / 7,55 | 5,46 / 7,79 | 4,97 / 6,77 | 8,50 / 8,80 | 8,47 / 8,70 |
| 768 | territorio | 5,95 / 6,03 | 5,83 / 6,15 | 4,94 / 5,45 | 5,93 / 6,48 | — |
| 768 | costa | 5,32 / 5,99 | 5,52 / 7,51 | 4,98 / 6,32 | 7,51 / 8,53 | — |

**Every element passes AA at every width** (small text ≥4,5:1, large text ≥3:1); the worst figure
of the pass is the hero eyebrow at 480 px (**4,68**), and no element shows any sampled pixel below
its threshold. The glass panel covers **68 % / 64 % / 62 %** of the act area at 400 px and
**51 % / 49 % / 48 %** at 768 px; the rest of the act is photograph, and the photograph is also
visible **through** the panel (blurred).

The 0.65 tint is not arbitrary: at **0.62** the hero eyebrow at 480 px measured 4,45 (below AA) and
at **0.65 with the same blur** it measures 4,68. Two alternatives measured the same 480 px worst
case — `blur(20px)` with a 0.62 tint (4,52) and `blur(24px)` with 0.62 (4,66) — both rejected in
favour of the smaller blur, which keeps the photograph's shapes readable through the glass (the
client's stated goal) instead of dissolving them.

### V20.4 What the glass costs, and the other two measurements

| Scene at 400 px | eyebrow | title | lead | CTA | cue | photograph unveiled |
| --- | --- | --- | --- | --- | --- | --- |
| hero | 6,69 → **4,86** | 6,91 → **5,22** | 6,22 → **4,82** | 7,57 → **6,32** | 7,96 → **7,04** | 6,8 % → **89,0 %** |
| territorio | 7,22 → **5,74** | 7,21 → **5,75** | 6,16 → **4,93** | 7,83 → **6,83** | — | 7,2 % → **89,6 %** |
| costa | 7,10 → **5,53** | 6,70 → **4,88** | 6,23 → **5,04** | 8,40 → **8,09** | — | 6,7 % → **89,2 %** |

The "before" column is the previous veil (0.88-0.92) with **no glass**, measured with the same
harness on the same geometry: the trade is **≈1,4-2,0 points of contrast for ≈82 points of visible
photograph**, with AA held in both columns.

**Without `backdrop-filter`** (the `@supports not` branch: tint 0,82, no blur), measured by
neutralising the filter in the harness: minimum **5,90** (territorio lead at 480 px), maximum
8,57 — the fallback is *more* contrast, because the tint alone has to carry the whole guarantee.

**Ghost CTA border** (WCAG 1.4.11, boundary of the control over the panel, sampled along the
border box perimeter with the same model): `rgba(255,255,255,.55)` → minimum **2,72** / mean 3,69
(**below** the 3:1 required); `rgba(255,255,255,.8)` → minimum **3,92** / mean 5,84. The change is
a fix for a regression this round would otherwise have introduced: over the previous dark veil the
0.55 border measured 4,07.

### V20.5 Regression checks in the same pass

- **Five widths, 320 / 375 / 400 / 480 / 768 px: 0 px horizontal overflow** (`scrollWidth` ==
  viewport). Above 768 px the change cannot apply (the rules are inside the mobile query); the
  computed styles were nonetheless read at 1200 px to confirm the desktop paths (glass absent,
  base veil intact, cue without glass, hero eyebrow on paper).
- The scene eyebrows are now `--paper` **in both modes**. That is the only change above 768 px:
  in the immersive mode the territorio/costa eyebrows rise from white 85 % (measured 5,06 minimum
  in §V19.2) to full white, and the hero eyebrow (read on paper, `--primary-dark`) is untouched.
  Scenes 2-3 immersive leads/titles keep their exact previous values (no rule of theirs changed).
- **No PHP file, template, route, menu asset or database record was touched.** Navigation,
  dropdowns, the account control and the private-section gate are untouched by this commit.
- The rendered mobile result was **looked at** (developer look, not client sign-off): hero,
  territorio and costa at 375 px and at 768 px, where the panels sit over clearly readable
  photographs and leave 185 px of open photograph to their right.

### V20.6 NOT VERIFIED / open

- **The client's judgement of the panel's weight** — how much glass is acceptable is a client
  decision, not a code task: the tint (0.65) and the veil (0.42 maximum) are single knobs, and
  the panel covers roughly half to two thirds of each mobile act. The look is a **developer
  look**, not a sign-off (F2 stays open).
- **`backdrop-filter` cost on real hardware.** Three panels of ≤575×330 px are blurred, and the
  blur is recomputed while the page scrolls. No real phone or tablet was available, so there is
  no frame-time measurement and no iOS Safari check (the prefixed property is present; Safari
  support is from 15.4). This is the main way the change could fail in the field.
- **Model deviations:** the blur is modelled as a box average rather than a Gaussian (a lower
  bound), `saturate(1.15)` is not modelled at all, device pixel ratios are not varied, and the
  "photograph unveiled" figure uses the veil only (not the panel's own pixels). A pass here is a
  pass on a conservative model, not a screenshot-pixel measurement.
- **The panel's width depends on content**: it hugs the lead's `52ch`, so a future change to the
  lead's copy or `max-width` moves the panel's right edge (it can never overflow: as a flex item
  with `width:auto` it shrinks to the available width).
- **Print** (F22, pre-existing, *not* introduced here): the theme has **no `@media print` rules**,
  and browsers drop background images/colours by default, so the scene's light text over the
  photographs disappears on paper. The same is true before this commit; recorded now because it
  surfaced while measuring this round.
- Real touch, iOS dynamic viewport, reduced-motion *as a browser preference* and real keyboard
  modality: unchanged from §V13/§V18.

## V21 — Client review, fourth round: the glass moves to the lead only (2026-10-16)

The client replied with the **mobile scene 3 marked up**: the lead paragraph encircled in red and
*«El efecto glass es solo para el texto demarcado con rojo. Así se podrá ver la imagen de fondo»*.
The plate of §V20 covered the whole text block, so the photograph was only visible around it; the
ask is a plate **on the lead only**, with the eyebrow, the title and the CTA read directly on the
photograph.

Fixed in **`05d2ecb`** — one file again, `assets/css/otrn-landing.css`, inside the same
`@media (max-width:768px)` block. **No PHP, no markup, no content, no route, no database change**,
and nothing above 768 px (§V21.4).

### V21.1 What changed, and the trade it reopens

| Item (≤768 px) | §V20 / `649b01f` | Now / `05d2ecb` |
| --- | --- | --- |
| Plate | `.otrn-act__inner` (eyebrow + title + lead + CTA) | **`.otrn-act__lead` only** (14 px wider than the text, 6 px from the edge) |
| Eyebrow, title, CTA (scenes 2-3) | on the plate | **on the photograph**, separated by a **text-shadow** (tight on the 12 px eyebrow and the CTA labels, wide on the title) |
| Veil, cue pill, hero CTA | gasa 0,42 / glass pill / solid fill | unchanged |
| Plate coverage of the act | 62-84 % | **14,8-32,8 %** |

§V20 documented *why* the plate covered the whole block: at 12 px, white over a bright sky measures
≈1,5-2,6:1, and white needs a veil of ≈0,85 there to reach AA. The client's instruction is to show
the photograph, so this round **accepts that trade explicitly** — the plate is smaller, the
photograph is visible, and the copy that leaves the plate is separated from the background by a
text-shadow instead of by a surface.

### V21.2 Measured contrast (minimum / mean, 320→768 px)

**Passing AA** (all five widths, no sampled pixel below its threshold):

| Element | 320 px | 375 px | 400 px | 480 px | 768 px |
| --- | --- | --- | --- | --- | --- |
| Lead on the plate (16 px, ≥4,5:1) | 6,46-6,41-6,35 | 6,49-6,39-6,41 | 6,48-6,36-6,40 | 6,43-6,32-6,42 | 6,51-6,48-6,35 |
| Hero CTA, solid fill (13 px) | 7,40 | 7,40 | 7,40 | 7,40 | 7,40 |
| Scroll cue, glass pill (11 px) | 6,34 | 6,56 | 6,80 | 6,66 | 7,20 |

(Three figures = hero / territorio / costa. The lead moves by ±0,16 across widths; it never drops
below 6,32.)

**Over the photograph** — the three elements the client asked to lift off the plate (each row is
the range over the 15 measurements: 5 widths × 3 scenes):

| Element | Raw min | Raw mean | Pixels below AA (raw) | Min over the shadow |
| --- | --- | --- | --- | --- |
| Eyebrow (12 px, needs 4,5:1) | 1,49-2,99 | 2,62-6,90 | 19,8-100 % (below 25 % only once: hero at 768 px) | **18,2-19,2** |
| Title (30-41 px, needs 3:1) | 1,55-2,73 | 3,30-6,78 | 7,3-40,9 % | **12,5-15,0** |
| CTA of scenes 2-3 (12,5-13 px, needs 4,5:1) | 1,96-4,70 | 4,13-11,68 | 0,1-90,2 % | **18,6-19,6** |

**The raw figures are below AA, and the shadow does not change the WCAG measurement.** The
`text-shadow` column models the shadow as fully covering the glyph's immediate surround — an upper
bound; at the glyph edge the real contrast lies between the raw figure and the shadow figure. WCAG
1.4.3 measures the text against its background and does not count a shadow, so this is a
**mitigation, not compliance**: with the plate reduced to the lead, the eyebrow, the title and the
CTA labels of scenes 2-3 are a **documented, client-accepted gap** (F21, fourth round). The
`649b01f` layout — one plate behind the whole block — remains the compliant alternative.

Why the *tight* shadow on the small type and the *wide* one on the title: a 2 px shadow on 34 px
type is invisible, and a 6-8 px halo on a 12 px uppercase eyebrow smears it; each column above is
what the rendered result showed (developer look at 375 and 768 px, §V21.4).

### V21.3 The rest of the measurements

- **Photograph visibility**: the veil is untouched, so the photograph is unveiled (veil < 0,40) over
  **89,4-89,6 %** of each act, as in §V20. What changed is how much the plate covers: **14,8-32,8 %**
  (hero 25,7-32,8 %, scenes 2-3 14,8-28,4 %) against 62-84 % before.
- **Plate geometry**: `padding:12px 14px; margin:0 -14px 18px` — the text keeps its 20 px inset and
  the plate runs 6 px from the viewport edge to 6 px on the other side; the vertical padding is
  compensated in the margin (18 + 12 = 30 px, the base rhythm). At 768 px the plate is 551 px wide
  and the photograph stays open to its right, as before.
- **Without `backdrop-filter`** (`@supports not`, tint 0,82, now applied to the lead): lead minimum
  **5,42** / 5,60 / 5,55 — AA holds without any blur. Neutralising the plate entirely would take the
  lead to 1,66 (scene 2), which is what the plate is for.
- **Ghost CTA border (WCAG 1.4.11)**: over the photograph it measures minimum **1,80-1,81** (mean
  3,53-5,47) at 375-768 px — below the 3:1 required. This is a **regression of this round** (the
  same border measured 3,92 over the §V20 plate) and is registered as **F23**: over arbitrary
  photography no translucent white border can reach 3:1, so the control needs a surface of its own
  (the §V20 plate) or an opaque fill.
- **Horizontal overflow: 0 px** at 320/375/400/480/768 px.

### V21.4 Regression checks and method

- Above 768 px nothing changed: the plate, the shadows and the new geometry live inside the
  `@media (max-width:768px)` query. Read back at 809 px: the `.otrn-act__inner` has no background
  and no `backdrop-filter`, the lead has `background:transparent`, no shadow and its base
  `margin:0 0 30px`, the hero eyebrow is still `--primary-dark` on paper, and the **"Datos" and
  "Explora" sections — which reuse `.otrn-act__titulo`/`.otrn-act__lead` on paper — keep
  `text-shadow:none` and no plate** (the new rules are scoped to the three scene acts for exactly
  this reason).
- **No PHP file, template, route, menu asset or database record was touched.** Navigation,
  dropdowns, the account control and the private-section gate are untouched.
- The rendered mobile result was **looked at** (developer look, not client sign-off): hero,
  territorio and costa at 375 px, and hero at 768 px — the photograph reads behind the text and the
  copy holds on the shadow.
- **The harness was rebuilt** (the temporary measurement file is not versioned and was deleted at
  the end of the previous round). Two changes: it now also composites the **element's own
  background** along the ancestor chain (that is how the hero's solid CTA is measured as a button
  rather than as white text over the photo), and it reports a **`text-shadow`** column. Against
  §V20's numbers on the §V20 layout it reproduces the same state within **±0,2** (hero at 400 px:
  eyebrow 4,77 vs 4,86, title 5,22 vs 5,22, lead 4,81 vs 4,82, cue 6,88 vs 7,04), so the rebuilt
  model is the same conservative lower bound (box blur of radius = `blur()`, `saturate` not
  modelled, one sample per pixel of the real text line boxes).

### V21.5 NOT VERIFIED / open

- **The client's judgement** — whether the shadowed eyebrow and title read well enough on a real
  phone is the client's call, and the round exists because the previous answer changed. The
  alternatives, all measured: the `649b01f` block plate (AA everywhere, photograph covered 62-84 %),
  or a plate behind the eyebrow + title only (AA for the copy, photograph covered ≈45-60 %).
- **Readability of the shadowed type is a subjective call**, not a measurement: the model brackets
  it (raw → shadow) and the rendered screenshots were inspected by the developer only.
- **`backdrop-filter` cost on real hardware** (one plate of ≤551×156 px now, three in the page) and
  **iOS Safari** remain unmeasured, as in §V20.6.
- **F23** (the ghost CTA's boundary over photography, §V21.3) is registered, not fixed.
- Real touch, iOS dynamic viewport, reduced-motion as a browser preference and real keyboard
  modality: unchanged from §V13/§V18.

## V22 — Client review, fifth round: the lead plate gets lighter (2026-10-16)

The client sent the plate's declaration back with values to use:

```css
@media (max-width: 768px) {
    .otrn-act .otrn-act__lead {
        background: rgba(48, 74, 131, -0.35);
        -webkit-backdrop-filter: blur(16px) saturate(1.15);
        backdrop-filter: blur(9px) saturate(1.15);
        border-radius: var(--radius);
        padding: 12px 14px;
        margin: 0 -14px 18px;
    }
}
```

**Two of those values cannot be applied as written**, so they were corrected rather than pasted:

1. **`rgba(48, 74, 131, -0.35)` is an invalid declaration.** CSS alpha accepts 0-1, so the browser
   discards the whole `background` declaration and the lead is left with **no tint at all** — only
   the blur, i.e. *worse* legibility, not better. Measured in that state the lead falls to
   **2,00-2,39:1**. The sign was taken as a typo and the tint applied as **0,35**.
2. **Two different blurs** (`16px` behind the `-webkit-` prefix, `9px` in the standard property):
   the later declaration wins, so the effective value is 9 px. **Both prefixes were normalised to
   9 px** — the project keeps one value per declaration (DESIGN_TOKENS_AUDIT.md §13.10).

Everything else in the snippet (the radius, the padding, the negative margins) matched the code
that was already there and is unchanged. Fixed in **`bef8332`** — one file, `assets/css/otrn-landing.css`,
inside the `@media (max-width:768px)` block; this documentation commit follows it.

### V22.1 What the lighter tint costs

The lead, on the plate, minimum / mean, at the five widths (hero / territorio / costa):

| Tint + blur | 320 px | 375 px | 400 px | 480 px | 768 px |
| --- | --- | --- | --- | --- | --- |
| **0,35 + blur 9 (applied)** | 4,14 / 4,29 / 4,41 | 4,14 / 4,30 / 4,44 | 4,13 / 4,30 / 4,44 | **4,12** / 4,27 / 4,43 | 4,27 / 4,79 / 4,35 |
| 0,45 + blur 9 | 4,90 / 4,99 / 5,09 | 4,90 / 4,99 / 5,11 | 4,88 / 4,97 / 5,11 | — | — |
| 0,65 + blur 16 (previous round) | 6,46 / 6,41 / 6,35 | 6,49 / 6,39 / 6,41 | 6,48 / 6,36 / 6,40 | 6,43 / 6,32 / 6,42 | 6,51 / 6,48 / 6,35 |

- **The worst pixel of the lead is now 4,12-4,44:1, i.e. 0,06-0,38 below the 4,5:1 of AA**, at every
  width except the 768 px territory scene (4,79). The **mean is 5,15-6,90:1**, comfortably above, so
  most of each paragraph passes and the shortfall is confined to the brightest spots of the plate's
  backdrop.
- **0,45 is the nearest value that holds AA** (4,88-5,11:1) and would look practically the same.
  The client's own value was applied because it was given explicitly; the one-number change is
  available. Recorded as the **fifth round of F21**.
- **Blur 9 vs 16 moves nothing meaningful**: at 0,65 the lead measures 6,28-6,36 with blur 9 against
  6,32-6,49 with blur 16. The tint, not the blur, is the knob.

### V22.2 What did not change, and the checks

- **Nothing else in the snippet's scope changed**: the veil (gasa ≤0,42), the scroll cue's glass pill
  (6,34-7,20:1), the hero's solid CTA (7,40:1) and the **text-shadow** that separates the eyebrow, the
  title and the CTAs of scenes 2-3 from the photograph (1,49-2,73:1 raw, 12,5-19,6 over the shadow)
  are as recorded in §V21.
- **Fallback without `backdrop-filter`** is untouched and still AA: the `@supports not` branch raises
  the tint to 0,82 on the lead (5,42-5,60:1 without any blur).
- **0 px horizontal overflow** at 320/375/400/480/768 px; the change lives inside the
  `@media (max-width:768px)` query, so **above 768 px the plate does not exist at all** (§V21.4).
- **No PHP, markup, content, route or database change.**
- Rendered result looked at again (375 px: hero, territorio, costa): the photograph now reads
  **through** the plate as well as around it — the client's goal — while the lead keeps its glass.

### V22.3 NOT VERIFIED

- **The client's judgement** on the lighter plate, and whether the 0,06-0,38 AA shortfall at the
  brightest pixel is acceptable or should be closed with 0,45.
- **F23** (the ghost CTA's boundary over photography) and the **F21 third/fourth-round gaps** are
  open as described in §V21.
- `backdrop-filter` cost on real hardware and iOS Safari: unchanged from §V20.6.

## V23 — Sticky footer: the page shell (F24, client request) — 2026-10-16

The client asked for the classic sticky footer: *"cuando el contenido sea menor a la altura de
la pantalla se posicione al final de esta y si el contenido crece más allá, el footer sea
empujado con el contenido"*, with a screenshot of `/guias/` where the footer floated mid-screen.
Fixed in **`90bf739`** — one file, `wp-content/themes/otrn/style.css` (new "Estructura de
página" block, 8 declarations + the `@supports (min-height:100dvh)` variant): **no PHP, markup,
content, route or database change**, and no token value touched.

### V23.1 The defect, measured before the fix

`/guias/` in the real tab (viewport **809×804**), with the shell disabled: the content of `main`
ended at **444 px**, the footer ran **504-681 px** (its `margin-top:60px` included) and the
**last 123 px of the window were empty paper**. `header.php` opens `<main>` and `footer.php`
closes it, so the document was a plain block stack: `main` was only ever as tall as its content.
The same page with the shell enabled fills the window exactly.

### V23.2 After the fix — the whole theme navigation, real tab (809×804)

Document height (docH), footer box and overflow, via the harness:

| URL | docH | footer bottom | below the footer | scrolls | h-overflow |
| --- | --- | --- | --- | --- | --- |
| `/` (immersive home) | 6091 | 6091 | — | yes | 0 px |
| `/guias/` (short) | **804** | **804** | **0 px** | **no** | 0 px |
| `/material/documentacion-tecnica/` | 922 | 922 | — | yes | 0 px |
| `/material/reportes/` | 920 | 920 | — | yes | 0 px |
| `/acerca/` | 1157 | 1157 | — | yes | 0 px |
| `/geoportal/` | 1254 | 1254 | — | yes | 0 px |
| `/indicadores/` | 1418 | 1418 | — | yes | 0 px |
| `/noticias/` | 1533 | 1533 | — | yes | 0 px |
| `/estadisticas/` | 2991 | 2991 | — | yes | 0 px |
| `/seguimiento-pladetur/` | 9145 | 9145 | — | yes | 0 px |
| `/buenas-practicas/` | 11993 | 11993 | — | yes | 0 px |

- **On every page the footer's bottom edge equals the document height**, i.e. the footer is the
  last thing on the page and no background band is left under it. On the one page that is shorter
  than the window, the document collapses to exactly the viewport (804 = 804) and **no scrollbar
  appears**; the content pages scroll as before with the footer pushed behind them.
- **What is actually at the bottom edge**: `document.elementFromPoint(centre, 803)` returns the
  `footer` element (`closest('.otrn-footer')` true), not a background strip.
- **0 px horizontal overflow** on all 11 pages.
- The account control points at `/wp-login.php` for anonymous visitors: WordPress's own page, no
  theme footer, not part of the shell.

### V23.3 With the admin bar — simulated (the tab is anonymous)

Core injects `html { margin-top: 32px !important }` (46 px ≤782 px) in
`wp-includes/admin-bar.php:1393` and publishes the same height as
`--wp-admin--admin-bar--height` (`wp-includes/css/admin-bar.css:2/791`). The tab has no session,
so the condition was reproduced with core's own CSS and variable plus a fixed bar (no theme
change):

| Condition | body min-height | footer bottom | viewport | phantom scroll |
| --- | --- | --- | --- | --- |
| Anonymous (no bar) | 804 px | 804 | 804 | 0 |
| Bar 32 px (desktop) | 772 px | 804 | 804 | 0 |
| Bar 46 px (≤782 px) | 758 px | 804 | 804 | 0 |

Without the subtraction the shell would have been 32/46 px taller than the visible area and the
page would have gained a scroll of exactly the bar's height. `--wp-admin--admin-bar--height` is
undefined for anonymous visitors, where the `0px` fallback applies.

### V23.4 The front page is unchanged

The home page is the one page that already exceeded the viewport, and it is the only one with a
pinned (ScrollTrigger) element, so it was measured **with and without** the shell in the same
document:

| Metric | With the shell | Shell overridden to `display:block` |
| --- | --- | --- |
| Document height | 6091 px | 6091 px |
| `main` height | 5914 px | 5914 px |
| Pin spacers | 1 (3055 px) | 1 (3055 px) |
| Immersive viewport | 804 px | 804 px |
| h-overflow | 0 px | 0 px |

The pinned element lives inside `main` (a block box), so the flex context of `body` never reaches
it. The scrollytelling transitions were re-walked after the change (scroll 0 / 1600 / 2600):
hero act 100 %, then territorio, then costa, matching the pre-change sequence.

### V23.5 Narrow widths (same-origin iframes)

- A **synthetic short document** inside `/guias/` at **375×667** (the card content replaced by
  three lines): `body` min-height **667 px**, footer bottom **667 px**, 0 px below it, no scroll —
  the mobile case works and the `dvh` variant resolves against the iframe viewport.
- **No page in the current content set is shorter than a phone viewport**: at 375×667 the shortest
  is still `/guias/` (873 px) because the footer stacks taller and the copy wraps. So mobile
  "short page" behaviour is proven on a synthetic document, not on real content.
- At 375/320 px every page kept **0 px horizontal overflow**.

### V23.6 NOT VERIFIED

- **Real devices**: iOS/Android dynamic toolbars (`dvh`) and touch scrolling; the harness window
  is 809×804 and cannot be resized, so live widths/heights other than that one were not exercised
  (mobile widths only through iframes).
- **A real logged-in session** (the bar was simulated with core's own rules; see §V23.3), and any
  browser whose admin bar offset differs.
- **`/evaluar/`** (the only template with the sticky `.otrn-formbar`, and a private route): not
  logged in, not measured. Its sticky bar's containing block is the `<form>` inside `main`, so the
  shell does not move it; unverified in the browser.
- **`@media print`** (F22) and `prefers-reduced-motion`: unchanged, not re-tested.
- **The client's confirmation** that this is the behaviour asked for.

## V24 — "Guía de usuario": a session-gated help centre for the real site (new, client request) — 2026-10-16

A client-requested deliverable, implemented ahead of any roadmap phase and committed as
**`d706872`** (code + assets; this documentation commit follows it): a help centre at the
existing route `/guias/` that explains the site as it actually behaves (content model, private
gating, filters, charts, geoportal, PlAdetur, self-assessment, reports) to non-technical staff.
It **adds no route, no post type, no database row, no option and no dependency**, and it
changes no other page: the guide's copy lives in one PHP data file. It is **pending the
client's UX/UI review**; nothing here is a design sign-off.

| Area | File |
| --- | --- |
| Topic copy + block renderer + appendix helper | `wp-content/themes/otrn/guias-contenido.php` (**new**) |
| Template (route `/guias/` unchanged) | `wp-content/themes/otrn/archive-guia.php` (rewritten) |
| Guide-only styles / behaviour | `assets/css/otrn-guias.css`, `assets/otrn-guias.js` (**new**, enqueued only on the guide) |
| Session gate (HTML routes) | `functions.php` — `otrn_guia_usuario_privada()` on `template_redirect`, priority 1 |
| Anonymous surfaces (REST/search/oEmbed/sitemap) | `wp-content/mu-plugins/otrn-privacidad-guias.php` (**new**) |
| Figures | `assets/guias/*.webp` (12, **new**) |

### V24.1 The anonymous surface, measured (no session, `curl`)

| Probe | Result |
| --- | --- |
| `/guias/` | **302** → `/wp-login.php?redirect_to=…%2Fguias%2F` |
| `/guias/nueva-giua-de-usuario/` | **302** → login, own destination preserved |
| `/guias/feed/` | **302** → login |
| `/?post_type=guia`, `/?guia=nueva-giua-de-usuario` | **302** → login (query rebuilt verbatim in `redirect_to`) |
| `/wp-json/wp/v2/guia`, `/wp-json/wp/v2/guia/409` | **401** |
| `/wp-json/wp/v2/search?search=guia` | **200**, body `[]` (2 bytes) |
| `/?s=guia` | **200**, 0 matches for the guide's title or copy |
| `/wp-sitemap.xml` | **200**, no `guia` entry; `wp-sitemap-posts-guia-1.xml` → **404** |
| `/wp-json/oembed/1.0/embed?url=…/guias/nueva-giua-de-usuario/` | **404** |
| Home page `/` | 0 occurrences of `otrn-guia`, `Guía de usuario` or the guide's copy |

- **Only a session is required**, not a capability: adding `guia` to `otrn_privado_tipos()`
  would have demanded `read_private_guias` (held by administrators only) and 403'd every
  logged-in non-admin, so the type gets its own gate with "logged-in" semantics while the
  **existing** private-section rules are untouched.
- **Residual surface, same as the private section**: `/wp-json/wp/v2/types` still advertises
  the `guia` type (it advertises `evaluacion` too — the F11b shape). The item route itself
  answers 401, and no content was observed in any payload.
- **No caching add-on needed**: the guide answers with WordPress's own logged-in headers,
  `cache-control: no-cache, must-revalidate, max-age=0, no-store, private` (identical to
  `/evaluar/`), so a shared cache cannot hand the HTML to an anonymous visitor.

### V24.2 Authenticated rendering (temporary session shim, deleted afterwards)

The harness has no credentials (only account `admin` exists), so the private views were
rendered with a temporary `determine_current_user` mu-plugin — the same method as §V16 — which
was **deleted before committing** (`wp-content/mu-plugins/` lists no `zz-` file, and the
anonymous probes in §V24.1 were re-run after deleting it).

- `/guias/` → **200**, **88 887 B**, 11 `.otrn-guia-tema` sections (10 topics + the appendix),
  **12 figures**, **0 "Captura pendiente"**, no `Fatal error`/`Warning`/`Notice`/`Deprecated`.
- Block census (same renderer call): 22 paragraphs, 17 subheadings, 7 bullet lists, 10 step
  lists, 3 definition lists, 1 FAQ list, 18 notes, 2 tables, 1 appendix entry
  (the pre-existing post 409, "Nueva giua de usuario" — §F27).
- The template renders one index twice (desktop `<nav>`, mobile `<details>`) and swaps them by
  CSS; the count of 45 index links (2 × 22 + headings) confirms both copies carry the same
  destinations.

### V24.3 Responsive behaviour (real rendering, same-origin iframes)

Ten widths, one already-loaded guide document resized in place, measuring the document itself:

| Widths | Layout | Article width | Horizontal overflow |
| --- | --- | --- | --- |
| 375, 414, 679, 768, 979 px | single column; sticky index hidden, `<details>` index visible | 311-767 px (fluid) | **0 px** |
| 980, 1024, 1280, 1440, 1920 px | two columns (index + article) | 640-820 px, **capped at 820** | **0 px** |

- `scrollWidth == clientWidth` at every width: the **page** never scrolls sideways.
- The only element wider than a 375 px viewport is the guide's own 2-column table (553 px),
  which lives inside `.otrn-guia-tablawrap{overflow-x:auto}` — the same convention as the
  application's `.otrn-tablewrap`, so it scrolls inside its container, not the page.
- The sticky index measured `top: 56 px` with the admin-bar variable defined (24 px + 32 px),
  i.e. it parks below the bar as intended; with no bar the `0px` fallback applies.

### V24.4 Keyboard, focus, search and current-topic (real key events, 809×804)

Real `Tab` presses in a real tab (not synthetic events), logging every `focusin`:

- Order: WordPress's own skip link → brand → nav (`Inicio`, `Datos`, `Buenas prácticas`,
  `Más`) → account control → **`input#otrn-guia-q` (guide search)** → **`<summary>` "Temas de la
  guía"** → the article's prev/next and "Volver arriba" → next topic → … No trap, no skipped
  control, and every stop matched `:focus-visible` with `outline: 2px solid rgb(0, 80, 184)`
  (`--primary-dark`) — the theme's own ring, not the browser default.
- **Search** (no results are requested from the server; it filters the rendered copy): "glosario"
  → 0 topics, counter "Sin resultados.", empty state shown, 22 of 45 index entries hidden;
  "pladetur" → 4 topics ("4 temas coinciden.": primeros-pasos, buenas-practicas, pladetur,
  autoevaluacion); clearing → all 11 sections back. Accent- and case-insensitive.
- **Current topic**: after scrolling, `aria-current="true"` sits on the index entry of the
  section in view (both index copies), driven by an IntersectionObserver.
- **Mobile index**: choosing a topic closes the `<details>` and applies the anchor (checked:
  `open` true → false, hash `#pladetur`).
- **No broken links**: the 51 in-page anchors all resolve to existing ids.

### V24.5 Images, no-JS and the figures

- Every figure carries `loading="lazy"`, `width`/`height` from `wp_getimagesize()` (no layout
  shift) and a descriptive `alt`; a figure scrolled into view loads (`naturalWidth` 1618).
- **No JavaScript** (`sandbox="allow-same-origin"` iframe, no `allow-scripts`): the 10 topics,
  the appendix, the 22 index links, the 38 steps, the 9 FAQ questions and the 12 figures are
  all server-rendered; the `<noscript>` line is visible ("La búsqueda necesita JavaScript…");
  the search input is inert (all 11 sections stay visible, no counter, no empty state) — the
  documented baseline.
- **Captured from the real interface only** (12 webp, 1618 px wide = 809 CSS px at DPR 2, one
  element-referenced screenshot per figure). Method: a scaled same-origin `<iframe>` at a real
  layout width (1360 px desktop, 390 px phone) inside a temporary overlay, the rest of the body
  hidden and the admin-bar offset neutralised, then the overlay captured by element reference.
  No figure is drawn or retouched; the geoportal figure needed a **9 s wait** for the external
  ArcGIS viewer to paint, and it shows the **current** story (a Rapa Nui/Valparaíso asset) —
  i.e. it also documents the open F9 decision.
- **Two views deliberately have no figure**: the self-assessment **listing and map**. They show
  territorial results, and a theme image is a static file that any anonymous request can fetch
  once its URL is known, so the guide states the limitation in its own text ("Por privacidad,
  esta guía no incluye capturas del listado ni del mapa…") instead of shipping the exposure.
  The form figure shows an **empty** form (0/74 answered, no sector chosen).
- The account-panel figure shows only «Cerrar sesión» — the panel has no name or address
  (verified against `header.php`), so no account data is reproduced.

### V24.6 NOT VERIFIED

- **A real login**: no credentials in the harness; the private views were rendered through the
  temporary shim (§V24.2). The gate is exercised anonymously (§V24.1) and as user 1
  (administrator) only — a **non-admin logged-in account was not available**, so the
  "session is enough" claim rests on the code path, not on a second account.
- **No account menu → login → return trip** in a browser: the 302 carries the exact
  `redirect_to`, but the round trip after authenticating was not performed.
- Real devices: touch targets, iOS dynamic viewport (`dvh`), `prefers-reduced-motion` and real
  screen readers (the harness cannot resize its window or deliver touch).
- The guide's content as **UX copy for non-technical users** — that is exactly what the client
  review has to judge; no usability test was run.
- Whether a non-admin account should see the guide's appendix entry, and whether the appendix
  should ship at all (F27 — client content decision).

## V25 — First production deployment: `observatorioarauco.cl` (Hostinger) — 2026-10-17

The client asked to prepare and publish the site. This is the **first round of the project that
writes outside this machine**, so the method differs from V1–V24: the evidence is the live host,
not localhost. The client chose **direct-to-production** after the panel showed that Hostinger's
*Staging* feature requires a plan upgrade (Premium Web Hosting) and the free alternative
(subdomain + second install) was declined.

### V25.1 Safety net taken first

| Item | Result |
| --- | --- |
| Files | `~/backups-arauco-20261016-2141/files-antes.tar.gz` (62 MB, whole docroot) |
| Database | `~/backups-arauco-20261016-2141/db-antes.sql` (117 KB) |
| Host's own backups | untouched (panel *Backups*) |

`wp db export` **fails on this host** (exit 255, no output, no file; `mysqldump` is present at
`/usr/bin/mysqldump`). The dump was taken with `mysqldump` directly, reading `DB_PASSWORD` from
`wp-config.php` inside the remote shell — the password was never printed, echoed or stored here.

### V25.2 Host facts (read-only, before writing)

| Check | Result |
| --- | --- |
| Docroot | `/home/u997291669/domains/observatorioarauco.cl/public_html` |
| WordPress / PHP (CLI) | 7.1.3 / **8.0.30** (local is 8.3) |
| DB | MySQL `u997291669_eVCNN` (production) vs SQLite (local) |
| Server | LiteSpeed + `hcdn`; SSH `u997291669@46.202.199.89:65002`; `rsync`, `git`, `unzip`, `tar` present |
| Disk | 2.0 T free |
| Existing content | **0** published posts/pages (only the installer's draft Privacy Policy) |
| Existing theme/plugins | `hostinger-ai-theme`; Hostinger mu-plugins + `litespeed-cache` |

PHP 8.0 was **not** changed: the theme and mu-plugins use no PHP 8.1+ syntax (checked by grep for
`readonly`/`enum`/`never`/first-class callable syntax — the only `readonly` hit is an HTML
attribute), and every route rendered without errors afterwards (§V25.5).

### V25.3 What was deployed (file scope)

`rsync` over SSH, no `--delete`; the Studio-only paths were **excluded on purpose**:
`mu-plugins/99-studio-loader.php`, the SQLite integration and `wp-content/database/`.

| Payload | Files | Bytes |
| --- | --- | --- |
| `themes/otrn/` | 95 (82 transferred) | 4,255,533 |
| `mu-plugins/otrn-*.php` | 6 | 50,892 |
| `uploads/` | 41 (36 transferred) | 4,995,915 |

Local migrations did **not** apply (production is MySQL, so no `db.php` drop-in was shipped), and
no `wp-config.php`, core file or other site on the account was touched.

### V25.4 Content selection — 216 items

Exported with `studio wp export --post_type=...` and imported with `wp import
--authors=create`:

| Included | Count | | Excluded | Count |
| --- | --- | --- | --- | --- |
| Buena práctica | 79 | | Criterio de autodiagnóstico | 74 |
| Iniciativa Pladetur | 64 | | Evaluación | 6 |
| Indicador | 44 | | Sector (`public => false`) | 10 |
| Página | 9 | | `guia` sample post (F27) | 1 |
| Material de apoyo | 6 | | | |
| Noticia (`post`) | 4 | | | |
| Dato de portada | 3 | | | |
| Adjunto | 7 | | | |

The private geography (`criterio`, `evaluacion`, `sector`) and the WordPress sample post the guide
appendix surfaced (*"Nueva giua de usuario"*) were left out by client decision. The 7 attachments
report *"Failed to import Media"* (the importer tries to download the file from
`localhost:8895`); the files themselves were copied by `rsync` and the URLs rewritten afterwards,
so the images resolve.

URL rewrite: `wp search-replace` made **8 replacements** (6 in `wp_postmeta`, 2 in `wp_posts`).

### V25.5 Configuration applied and the anonymous surface, measured

| Setting | Value |
| --- | --- |
| `blogname` | Observatorio Turístico de Arauco |
| `blogdescription` | Observatorio Turístico de la comuna de Arauco |
| `WPLANG` | `es_CL` (language pack installed) |
| `timezone_string` | `America/Santiago` |
| `permalink_structure` | `/%postname%/` (the host's default; local uses date-based — CPT slugs are unaffected) |
| `blog_public` | **0** — *discourage search engines* until the content is approved |
| Plugins | `advanced-custom-fields` **6.8.10** active (6.8.0 does not exist in wp.org); `wordpress-importer` 0.9.6 installed and **deactivated** after the import |

| Route (anonymous `curl`) | Result |
| --- | --- |
| `/`, `/indicadores/`, `/buenas-practicas/`, `/material-de-apoyo/`, `/noticias/`, `/acerca/`, `/geoportal/`, `/mision/`, `/estadisticas/` | **200**, no PHP error string, no broken `<img>` |
| `/evaluar/`, `/mapa/`, `/autoevaluacion/`, `/guias/` | **302** to `wp-login.php` with `redirect_to` intact |
| `/criterio/` | **301** to a good-practice post — the CPT has `has_archive => false`, so the bare path 404s and WordPress guesses. **Identical locally** (checked): not a deployment defect |
| Fiches: indicador, buena práctica, iniciativa, material, noticia | **200**, 0 error strings |
| Theme assets (`fonts.css`, `otrn-arauco.css`, `otrn-landing.css`, `otrn-menu.css`) | **200** |
| `robots.txt`, `/wp-sitemap.xml`, `/feed/`, `/wp-json/wp/v2/pages` | **200** |

The home page renders 49,373 bytes with the Arauco header/logo, the hero *"Arauco, territorio que
inspira"* and 5 mentions of "Rapa Nui" (the provisional copy the client accepted).

### V25.6 F25 fixed during this round — every route now emits one `<title>`

`add_theme_support( 'title-tag' )` in `otrn_setup_theme()` (`functions.php`), with the finding
reference in the code. Local: `current_theme_supports( 'title-tag' )` = `true`, and
`/`, `/indicadores/`, `/noticias/`, `/acerca/`, `/wp-login.php` each return **exactly one**
`<title>`; locally the brand still reads *"Observatorio Turístico de Rapa Nui"* because the
**local** `blogname` was not touched (F7).

Production, after the change:

| Route | `<title>` |
| --- | --- |
| `/` | Observatorio Turístico de Arauco – Observatorio Turístico de la comuna de Arauco |
| `/indicadores/` | Indicadores – Observatorio Turístico de Arauco |
| `/noticias/` | Noticias – Observatorio Turístico de Arauco |
| `/acerca/` | Acerca de – Observatorio Turístico de Arauco |
| `/buenas-practicas/` | Buenas prácticas – Observatorio Turístico de Arauco |

`noindex, nofollow` is present on every HTML route checked (`/`, `/indicadores/`, `/noticias/`,
`/buenas-practicas/`, `/acerca/`, `/wp-login.php` → `noindex, nofollow, noarchive`). **Feeds carry
no robots meta** (recorded, not fixed). Note for the next pass: `do_robots()` in WP 7.1.3 no
longer emits `Disallow: /` for a non-public site — verified by reading
`wp-includes/functions.php:1713` on the host — so `robots.txt` stays permissive and the
"discourage search engines" signal is the **meta tag** only.

### V25.7 NOT VERIFIED / open

- **Any authenticated view on production**: only the administrator account exists and no
  credentials were used, so the private sections are proven closed to anonymous visitors and
  *not* proven to render once logged in.
- Real devices, iOS dynamic toolbars, and the visual result: the live home page was looked at
  once by the team (see the deployment summary), which is **a developer look, not a client
  sign-off**. Every pending visual decision from V16–V24 applies to this deployment unchanged.
- The installer leftovers the client chose to leave: a published *Sample Page* (`/sample-page/`)
  and two draft copies of *Privacy Policy*.
- The HTML cache TTL: **changed during this round** — see §V25.8.
- Anything outside this site on the same account: untouched.

### V25.8 HTML browser cache lowered to one hour (`max-age=604800` → `3600`)

A reviewer's browser kept showing the pre-fix page after the F25 deploy and a normal reload did
not help: the response carried `cache-control: public, max-age=604800`. The header does **not**
come from the LiteSpeed plugin — lowering `cache-ttl_pub` to 3600 changed nothing, the plugin's
browser cache is off and its `cache-ttl_browser` was not touched — but from the host's default
expiry block in the site's own `.htaccess`:

```
ExpiresDefault "access plus 1 weeks"
```

Fix applied inside the same `<IfModule mod_expires.c>` block, with a copy of the file first
(`.htaccess.bak-ttl-202610170845`):

```
ExpiresByType text/html "access plus 1 hour"
```

Measured after a purge: HTML `max-age=3600` on `/`, `/indicadores/`, `/acerca/` and `/noticias/`,
while CSS keeps `max-age=2592000` (1 month) and images keep 1 year — nothing that matters for
performance changed. `cache-ttl_pub` and `cache-ttl_frontpage` were also lowered from 604800 to
3600 while diagnosing; they govern the **server-side** page cache, not the browser copy, and both
are one-line reversions.

### V25.9 Hostinger's assistant switched the active theme (incident and defence)

**Reported by the client**, from their own logged-in session: the public site showed the admin bar
followed by one centred paragraph, with no header, navigation or footer.

**Diagnosis.** The earlier anonymous checks had looked fine because they hit a **cached** copy; the
theme was correct when that copy was generated. A cache-busting request to `/` told another story:

| Check (cache-busting request to `/`) | Result |
| --- | --- |
| `<body class>` | `home blog … wp-theme-hostinger-ai-theme hostinger-ai-builder-gutenberg` |
| `<header>` / `<footer>` / `.otrn-act` | 0 / 0 / 0 |
| `wp-block-post-content` | 1 — a single paragraph |
| `wp option get template` / `stylesheet` | `hostinger-ai-theme` / `hostinger-ai-theme` |
| `wp theme list` | `hostinger-ai-theme` **active**, `otrn` inactive |

The AI theme declares `Requires PHP: 8.1` and the host runs PHP 8.0.30, so WordPress refuses that
switch on its own (`validate_theme_requirements()`, `wp-includes/theme.php:962`, called at the top of
`switch_theme()`): the options were written **from outside WordPress**. The only `switch_theme()`
calls in the Hostinger plugins live in `hostinger-easy-onboarding`
(`includes/Rest/StepRoutes.php:248`, fallback at `:301`), and `hostinger_ai_version` was stamped
`1792228950` (2026-10-17 ≈ 08:42 UTC).

**Recovery.** `wp theme activate otrn` plus `wp litespeed-purge all`, re-measured per route with a
cache-busting query: `/`, `/indicadores/`, `/noticias/`, `/acerca/`, `/buenas-practicas/` and
`/geoportal/` all come back as `wp-child-theme-otrn`; `/evaluar/`, `/mapa/`, `/autoevaluacion/` and
`/guias/` still answer **302**; `<title>` and `noindex, nofollow` intact. Nothing else moved —
`blogname`, `blogdescription`, `WPLANG`, `timezone_string`, `permalink_structure`, `blog_public` and
the item counts are exactly as deployed, and no post was created during the incident.

**Defence (two layers).**

1. `hostinger-easy-onboarding` **deactivated** — it is the plugin exposing that REST route, and the
   site is already set up. Reversible with `wp plugin activate hostinger-easy-onboarding`.
2. New mu-plugin `otrn-vigilante-tema.php`, deployed: it checks the active theme at `setup_theme`
   — before the intruding theme includes its `functions.php` — and at `switch_theme`, restores the
   project theme and logs the attempt and the acting user to the site's PHP error log
   (`~/.logs/error_log_observatorioarauco_cl`). It stands down in recovery mode and when the project
   theme is paused, and is disabled by deleting the file or defining `OTRN_VIGILANTE_TEMA_OFF`.

**Both paths tested on the host (2026-10-17).**

| Test | Action | Result |
| --- | --- | --- |
| A · WordPress path | `wp theme activate twentytwentyfour` | `Error: Could not switch to 'Twenty Twenty-Four' theme.`; `stylesheet` stays `otrn`; log: *"…detectado en switch_theme; se restaura otrn"* |
| B · external write (the real path) | options set to `hostinger-ai-theme` with `mysqli` from outside WordPress | next request: `stylesheet=otrn`, `template=twentytwentyfive`, page rendered `wp-child-theme-otrn`; log: *"Tema \"hostinger-ai-theme\" activo (detectado en setup_theme); se restaura \"otrn\". Usuario: 0."* |

Test B's line is `[17-Oct-2026 09:48:40 UTC]` in `~/.logs/error_log_observatorioarauco_cl`.

**NOT VERIFIED / open**: **who** called the switch. The host exposes no activity log for that option
write, the panel session was not recorded and the client's own steps through the panel were not
captured either, so the caller is inferred from the plugin code and the version timestamp, not
proven. Whether the panel can bring the theme back by another path is unproven as well — which is
exactly why the watchdog is in place, and why **the AI assistant/builder should not be used on this
site**.

## V26 — The client's editing report: permissions, filters and fields (F29/F30/F31) — 2026-10-18

**Reported by the client**, with two screenshots from the published site: a `buena_practica` edit
screen with an empty main column, and `/wp-admin/edit.php?ambito=economico&post_type=indicador`
answering *"Necesitas un nivel de permisos superior."*. Both were reproduced on
`observatorioarauco.cl`, and behind them stood **three** independent defects. All three are fixed and
deployed; F29 and F30 were hotfixes on the live site, F31 adds one mu-plugin.

**Method.** Authenticated production requests used a **session minted with WP-CLI**
(`WP_Session_Tokens::create()` plus `wp_generate_auth_cookie()` for the `logged_in`, `auth` and
`secure_auth` cookies) — no password was read, changed or recorded, and the shim file was removed
afterwards. Admin HTML is never cached (`cache-control: no-cache, no-store, private`;
`x-litespeed-cache-control: no-cache`), so what the checks read is what WordPress rendered. Where a
write was needed to prove behaviour (capability removal, save round-trip) it was done on the **local**
copy or reverted immediately, and production content was left as it was; LiteSpeed was purged after
each deployment.

### V26.1 F29 — the capability had been granted too late (fixed)

Reproduced on production by taking `edit_indicadores` off the `administrator` role with WP-CLI and
then making **one** request to the filtered list:

| Request to `/wp-admin/edit.php?ambito=economico&post_type=indicador` | Before the fix | After the fix |
| --- | --- | --- |
| 1st (capability missing at user-build time) | **403**, `<h1>Necesitas un nivel de permisos superior.</h1>`, “no tienes permisos para editar este tipo de entradas” | **200** |
| 2nd | 200 | 200 |
| 3rd | 200 | 200 |

Blueprint: `WP_User::allcaps` is a snapshot taken when WordPress builds the current user in
`wp-settings.php` (`after_setup_theme` at line **757** → `$GLOBALS['wp']->init()` at line **766** →
`init` at **779**), and `admin_init` runs later still, inside `wp-admin/admin.php`. A capability added
on `admin_init` therefore cannot be seen by `current_user_can()` in that same request — the second
request works because the grant already happened in the first one.

The four grants moved from `admin_init` to `after_setup_theme` (`otrn-contenido.php:204`,
`otrn-guias.php:54`, `otrn-iniciativa-pladetur.php:174`, `otrn-autodiagnostico.php:564`). After
deploying, the same single request returned **200** with the capability still removed, and the eight
capability sets were listed complete in the role: for each of the four types,
`edit_<plural>`, `edit_others_<plural>`, `publish_<plural>`, `read_private_<plural>`,
`delete_<plural>`, `delete_others_<plural>`, `delete_private_<plural>`, `delete_published_<plural>`
(and `create_<plural>` where the type defines it), plus the type-specific ones. Restoring the
capability afterwards changed nothing (the grants are self-repairing on any request type). Cache
purged; admin lists, private routes (`302`) and the front page unchanged; no new line in
`~/.logs/error_log_observatorioarauco_cl`.

### V26.2 F30 — the public ámbito filter was resolving as a taxonomy archive (fixed)

Measured on both the local site and production (identical before the fix), with a cache-busting
request:

| URL | Before the fix | After the fix |
| --- | --- | --- |
| `/indicadores/?ambito=economico` | parent theme's archive: `<h1>Archivos: </h1>`, **0** `.otrn-card` | `<h1>Indicadores</h1>`, **10** cards |
| `/buenas-practicas/?ambito=economico` | same generic archive, **0** cards | `<h1>Buenas prácticas</h1>`, **12** cards (of 80) |
| `/indicadores/?categoria=demanda` | filtered correctly | unchanged |
| `/ambito/economico/` | 200 (taxonomy archive) | 200, unchanged |

The cause is a name collision, not a template bug: `ambito` is a public taxonomy query var
(`otrn-autodiagnostico.php:128-144`) **and** the parameter the listings use for their own filter
(`archive-indicador.php:13,41,78`; `archive-buena_practica.php:12,17,36`). `WP_Query::parse_tax_query()`
consumes `$taxonomy->query_var`, `is_tax()` wins over `is_post_type_archive()` in `template-loader.php`,
the child theme ships no `taxonomy-ambito.php`, and the parent theme's generic archive renders.

Fixed with a front-end-only `request` filter, `otrn_ambito_es_filtro_de_listado()`
(`functions.php:253-259`): it unsets `ambito` from the main query only when a `post_type` is present
and it is not wp-admin. Local measurements after the fix, with the sibling filter kept alive:

| Filter | `/indicadores/` | `/buenas-practicas/` |
| --- | --- | --- |
| none | 45 cards | 80 cards |
| `ambito=economico` | 10 | 12 |
| `ambito=gestion` | 7 | — |
| `ambito=medio-ambiente` | 15 | — |
| `ambito=sociocultural` | 12 | — |

The `wp-admin` list filter was re-tested in a real browser because the **first** attempt at this fix
(`query_var => false` on the taxonomy) fixed the front end and broke the admin list silently — the
list table depends on that query var — so that approach was reverted and is recorded here as
rejected. With the final fix, the admin lists still filter: indicador 20 → 9 (`economico`) / 11
(`sociocultural`), buena_practica 20 → 11. The correct option is also rendered as `selected` in the
form. Production re-measured after deployment plus a LiteSpeed purge.

### V26.3 F31 — the content types had no fields to edit (fixed)

Inventory taken before writing anything, from the stored data itself (names, the ACF reference in
`_<field>`, and one real value each): the types hold 6–20 meta keys per item, and the reference keys
that exist (`field_otrn_bp_*`, `field_otrn_ip_*`, `field_otrn_cri_*`, `field_otrn_ind_pdf`) show the
original project used ACF — but there are **0** `acf-field-group` and **0** `acf-field` rows in the
local and the production database, no field group is registered in code and the JSON load path
`docs/acf-json/` does not exist (F4). That is why the edit screens showed only the title.

New mu-plugin **`otrn-campos.php`** registers six groups on `acf/init` (they are skipped cleanly if
ACF is not active):

| Group | Type | Fields |
| --- | --- | --- |
| `group_otrn_buena_practica` | `buena_practica` | descripción, fuente de la información, ubicación, nivel territorial, sitio web, criterio relacionado |
| `group_otrn_indicador` | `indicador` | descripción, institución responsable, nivel territorial, fuente de datos, fórmula, sitio web, URL del visor, serie de datos (solo lectura), documento PDF, documento Excel, ficha metodológica |
| `group_otrn_material` | `material_apoyo` | documento, enlace externo |
| `group_otrn_dato_portada` | `dato_portada` | cifra, año |
| `group_otrn_iniciativa` | `iniciativa_pladetur` | 10 descriptive fields + `asignado_2024`…`asignado_2033` |
| `group_otrn_criterio` | `criterio_autodiag` | código, criterio, pregunta, indicador, no aplica, escalas 0–3 |

Field names are the existing meta keys; field keys reproduce the stored references, so ACF finds the
saved values. Checked with WP-CLI that every field resolves its stored value:

| Check | Result |
| --- | --- |
| Groups registered (local and production) | 6 of 6, expected keys and titles |
| `get_field(…, 378)` for the six `buena_practica` fields | all return the stored values (`descripcion`, `Ilustre Municipalidad de Santiago`, `Santiago`, `Comunal`, `https://www.munistgo.cl/…`, `48.1`) |
| Edit screens on production (admin session, HTML) | post 378: the six labels present and the saved value `value="Santiago"` in the form; post 411 (`indicador`): **11** `acf-field` blocks; post 66 (`iniciativa_pladetur`): **20** |

The save path was exercised **locally** (the live content was not written to): in a real browser
session, `Ubicación` on post 378 was changed to a test value, the form submitted normally, and the
value persisted — `get_post_meta()` returned it, the `_ubicacion` reference stayed
`field_otrn_bp_ubicacion` and the public detail page rendered the new value. The original value
(`Santiago`) was then written back the same way and confirmed in the database and on the page again.
Deployed to production and LiteSpeed purged; the site's PHP error log gained no line.

### V26.4 Regression checks in the same pass

| Check | Result |
| --- | --- |
| Front page, `/indicadores/`, `/buenas-practicas/`, `/noticias/`, `/acerca/` (production) | render as `wp-child-theme-otrn`, unchanged |
| Private routes `/evaluar/`, `/mapa/`, `/autoevaluacion/`, `/criterio/` | **302** for anonymous requests |
| Public detail pages of the types (`single-buena_practica.php`, `single-indicador.php`) | unchanged — they read `get_post_meta()`; the three `get_field()` calls still resolve |
| Admin lists (`indicador`, `buena_practica`) | filter correctly (see §V26.2) |
| PHP error log | no new line from these changes |

### V26.5 NOT VERIFIED / open

- **No content was written on production** to test the save path there: the round-trip was measured on
  the local copy only. The same ACF code runs in both, but a live save was deliberately not performed.
- **The field definitions are a reconstruction.** The original labels, instructions, option lists and
  the deferred file repeater were not available (F4), so they were derived from the stored keys,
  the stored values and the labels the theme itself prints. The client must review them; nothing here
  is a copy of the original project's schema.
- `nivel_territorial` and `fuente_datos` are dropdowns whose option lists were built from the values
  present in the database (plus the one the theme documents). A value outside those lists would be
  dropped if that item is saved: confirm the lists before the client edits freely.
- The client has not re-tested their own two accounts yet, and their *"filtros"* complaint may or may
  not be F30 — the screenshot they sent was the **wp-admin** URL, and the public filter was found
  while investigating. Both are fixed; which one they saw is still unconfirmed.
- Keyboard, device (real phone) and CLS behaviour remain unverified, as in earlier rounds — and the
  whole visual layer is still pending the client's sign-off.

## V27 — The footer's fourth logo: RAU (client request) — 2026-10-18

**Reported by the client**, with the reference strip they want the row to follow: four institutions in
that order — **GORE · Municipalidad de Arauco · Oficina de Turismo · RAU**— with RAU at the right end.

**Diagnosis.** The footer carried **three** plates (`footer.php`), and the RAU logo was not used by any
template: the file had been sitting in the repository since the original project —
`assets/footer/logo-rau.png`, 193×120, RGBA with a real alpha channel — as an unreferenced leftover
(F12/F13). The **original** footer of the project (commit `99092d1`) did use four logos, with a
per-child height each (`.otrn-footer-logos img:nth-child(1..4){64/50/52/56px}`; mobile 48/38/40/42),
and that rule set was dropped in `52a7d4d` when the Arauco logos went onto white "chapas" with a single
height. So the reference strip is the project's own footer, not a new design.

**Change.** One `<span class="otrn-footer-logo">` added to `footer.php`, in fourth place, with the
logo's real dimensions (`width="193" height="120"`) and the alt text *"RAU · Consultores en Turismo"*.
No CSS was touched: the existing plate (`background: var(--paper)`, one border, `border-radius`,
`padding:14px 20px`) and the row's `space-between` already produce the four-in-a-row layout, and the
mobile rule (`justify-content: center`, 40 px images) already wraps and centres.

**Measured in a real browser** (local, same-origin iframes for narrow widths; all four images load):

| Viewport | Row | Plates (width × height, in document order) |
| --- | --- | --- |
| 1280 px | 1 row, `space-between` | 69×78 · 166×78 · 178×78 · 119×78 |
| 1024 px | 1 row | 69×78 · 166×78 · 178×78 · 119×78 |
| 768 px | 1 row | 69×78 · 166×78 · 178×78 · 119×78 |
| 600 px | 2 rows, centred | 65×70 · 145×70 / 155×70 · 106×70 |
| 390 px | 2 rows, centred | 65×70 · 145×70 / 155×70 · 106×70 |

The RAU logo renders at 77×48 px inside its 119 px plate (its 193×120 original at the row's common
height). The footer's **height does not change on desktop** (177 px, before and after: the row is one
plate tall), and it does not change at 390 px either (280 px, where three plates already wrapped into
two rows); at 600 px the fourth plate makes the row wrap that one step earlier — 169 → 261 px — because
the row needs 521 px with three plates and 660 px with four, against 536 px of content width. The
copyright line is unchanged.

**On production**, after deploying `footer.php` (the logo file was already there) and purging LiteSpeed:

| Check | Result |
| --- | --- |
| `/`, `/indicadores/`, `/buenas-practicas/`, `/noticias/`, `/acerca/` | **4** `otrn-footer-logo` plates each, in order, with `alt="RAU · Consultores en Turismo"` |
| `…/assets/footer/logo-rau.png` | `200`, `image/png`, 14 242 bytes |

**NOT VERIFIED / open.**

- **No screenshot was taken this round**: the harness desktop window was not visible, so the pictures
  could not be captured. The geometry, the images' load state and the production HTML were measured,
  but the *look* has not been looked at — the client's own view is the verification.
- **The GORE logo is the narrowest of the four** (27 px wide at the row's common height) because the
  source is a 160×283 portrait strip and the row equalises **height**, not width; the reference strip
  appears to show it larger. Enlarging it would magnify a 160 px original (F13 asks for the HD file),
  so the height was left alone rather than guessing.
- The client's reference strip carries no white plates: the row keeps the "chapa" treatment that is
  deployed today (the municipal and oficina logos have an opaque `#ECECEC` interior, so a bare row
  would show grey rectangles). Confirm if the plates are to go.


