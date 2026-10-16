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
