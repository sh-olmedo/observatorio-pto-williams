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
| F1 mobile nav | Open (static) | **FAIL confirmed** — ≤600 px both page types; 601–768 px internal visible but cramped; front panel works only 601–768 px |
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
