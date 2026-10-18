# Handoff — Repository State & Next Steps

State recorded **2026-10-15**, updated after the Phase 0 runtime pass. The `docs/` baseline
is delivered as **one local commit** on `main` (`f506104` — `docs(arauco): establish audited
project baseline`, not pushed). Neither the audit nor the runtime pass modified application
code, configuration or database. Runtime results: [VERIFICATION.md](VERIFICATION.md).

**Update 2026-10-16:** the Arauco requirement *self-assessment location search (OSM
geocoding)* is registered as **F19** (local commit `d4fa738`, documentation only). **F1
(responsive navigation)** is fixed in application code by `0dd2de4` (first pass) and
`e727756` (authenticated regression: admin bar over the fixed front-page header + wrapping);
both are browser-verified with the limitations recorded in
[VERIFICATION.md](VERIFICATION.md) §V15. The three pre-existing working-tree files were
**not** touched by any of these commits (the fix lives in the new globally enqueued
`assets/css/otrn-menu.css` plus `assets/otrn-menu.js`).

**Update 2026-10-16 (second round):** the client approved the grouped navigation, so the
header was restructured: primaries plus dropdowns, a person icon instead of the login/logout
button, a measured progressive collapse in tablet and the accessible drawer on mobile.
Committed locally as `5f17b38` (code: `header.php`, `functions.php`,
`assets/css/otrn-menu.css`, `assets/otrn-menu.js`) and documented in the commit after it.
**F1 is still open: the redesign is pending explicit UX/UI approval of the visuals** (do not
treat the current look as signed off). Breakpoints come from measured row widths (≥1152 /
910 / 680 px) and the pass is recorded in [VERIFICATION.md](VERIFICATION.md) §V16. The three
pre-existing working-tree files were again left untouched.

**Update 2026-10-16 (third round, client review):** the client reported the desktop dropdown
destinations as too tight, so the spacing was rebuilt and the underlying cascade defect fixed
(`510e981`: the file's 9 px padding had never applied — the landing reset
`.otrn-header .otrn-nav a` shares specificity with `.otrn-header .otrn-subnav a` and came
later). Destinations are now 42 px rows with a 2 px gap and an inset highlight. F1 is **still
pending the client's visual confirmation of this spacing**; see §V16. Recorded in `cd9fd09`.

**Update 2026-10-16 (fourth round — design direction confirmed):** the client confirmed that
the site **must follow the Municipalidad de Arauco visual identity**; the coral/teal palette is
no longer the authoritative design system (F2 direction closed). A full **design-token and
global-style audit** was executed read-only and persisted as
[DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md); the municipal palette was verified against
the live site `muniarauco.cl` (Elementor kit `post-5.css`):
**primary `#0070FF`, secondary `#DE232B` (red), accent `#41BCFF`, text `#000000`** — roles do
**not** follow the brief's ordering; `#0C1434` is unverifiable and `#0C0D0E`/`#D6D5D5` are
Elementor defaults ([DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) §2.5). **No CSS/PHP was modified.**
The token mapping is **pending UX/UI approval before any style change**. This round changed
**documentation only** (this file, `DESIGN_SYSTEM.md`, `ISSUES.md`, `README.md`,
`ARAUCO_MIGRATION.md`, new `DESIGN_TOKENS_AUDIT.md`).

**Update 2026-10-16 (fifth round — token implementation, Phase 2):** the client approved the
mapping, so the consolidation was **applied in code** (local commit `52a7d4d`): one
authoritative token block in `style.css :root`, both `--aru-*` layers deleted, every legacy
literal routed through tokens, status colours separated from brand colour, and the geometry
moved to the municipal radii. Two consequences worth noting:

- **F6 is resolved** (one token source) and **F2 is implemented**, but **neither is signed
  off**: the *rendered* result still awaits the client's visual review, together with the F1
  navigation. Do not treat the current look as approved.
- The **two pre-existing uncommitted CSS files are now committed**: their working-tree deltas
  (the unscoped `--aru-*` fallback and the Manrope→`--font-title` fix) were exactly what this
  pass replaced/consolidated, so they are part of `52a7d4d`. Only
  `wp-content/mu-plugins/99-studio-loader.php` (machine temp path) remains deliberately
  uncommitted.

Contrast defects latent in the baseline were exposed by the darker brand blue and fixed in the
same commit (neutral fill `#F0F0F0`→`#F6F6F6`, row hover, `.otrn-stat.accent`,
`.otrn-notice.ok`, and the `.otrn a{color:inherit}` specificity trap that turned the solid row
link into invisible-on-blue 2,84:1 text). Measured evidence: [VERIFICATION.md](VERIFICATION.md)
§V17; mapping: [DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) §13; system:
[DESIGN_SYSTEM.md](DESIGN_SYSTEM.md).

**Update 2026-10-16 (sixth round — client review of the token pass):** the client reviewed the
rendered result (logged in) and reported **two defects, both now fixed**:

- **F1 (`3dbfbc6`)**: the desktop dropdown disappeared before the pointer could reach it. The
  12 px gap that `510e981` introduced is a band outside the group, so crossing it fired the
  `mouseleave` that closes the panel. Fixed with a mouse bridge (`::after` of the group), row
  mode only, verified by hit-testing the whole descent path (375–1440 px, including the «Más»
  panel) and confirmed absent in the mobile accordion — [VERIFICATION.md](VERIFICATION.md)
  §V18.1.
- **F2 (`9142179`)**: the evaluation drawer's criterion question and answer descriptions read
  as secondary text (`--text2`, 4.58:1 and 4.95:1 — AA by the bare minimum) while the
  indicator definition above them was black. Both now use `--dark` (19.43:1 and 21:1);
  the labels keep `--text2` for hierarchy — §V18.2.

No other page was reported. The client's **final visual sign-off is still pending** (the review
found these two things, it did not approve the pass).

**Update 2026-10-16 (seventh round — client review of the home page):** the client reviewed the
**home page** and reported **two defects, both now fixed** in `e2c3a9f` (only
`assets/css/otrn-landing.css` and `assets/otrn-landing.js`: no PHP, markup, content, route or
database change):

- **F20**: the "Datos" figures are informative but carried the same white card surface as the
  clickable indicator and news cards, so they looked clickable. They are now flat data blocks
  (no background, border, radius or shadow; a 2 px `--primary` rule on top) while the actionable
  cards keep their surface — [VERIFICATION.md](VERIFICATION.md) §V19.1.
- **F21**: scene text was hard to read over the photographs because the veil never covered the
  text — the static hero's gradient was weakest exactly where the text starts, the static
  scenes 2–3 used a horizontal veil while the mobile text spans the full width, the immersive
  scene 2 had **no veil at all** and scene 3's died at 42 % of the window. One vertical static
  veil, a pixel-stop immersive veil faded in before scene 2's text, the hero column clamped to
  the photographic panel (`--panel-w`) and the rail number in `--dark` — §V19.2. The mobile
  scenes now read as a blue-tinted backdrop whose detail survives mainly in the top ~20 %; AA
  over the **brightest** pixel of each photograph is what forces ≈0.87–0.88, so how much
  photograph to keep is a **client decision** (documented, not decided).
- **Screenshots worked again** this round (the harness desktop window was visible), so the
  rendered result was looked at by the team for the first time — immersive scenes 2–3 in the
  real page and the static/mobile layout through a same-origin iframe. That is a **developer
  look, not the client's sign-off**, and the F1 navigation, F2/F6 identity and this round all
  remain **pending the client's visual review**.

**Update 2026-10-18 (segunda parte — el cuarto logo del pie):** el cliente señaló que faltaba corregir
el pie y envió la referencia con los **cuatro** logos institucionales en orden: GORE · Municipalidad de
Arauco · Oficina de Turismo · **RAU**. El logo de RAU ya estaba en el repositorio desde el proyecto
original (`assets/footer/logo-rau.png`, 193×120, PNG con alfa) y **ninguna plantilla lo usaba**; el pie
llevaba tres chapas. Se añadió el cuarto (`footer.php`, sin tocar CSS: la chapa y el `space-between` ya
daban el diseño) y se desplegó. Medido en navegador real a 1280/1024/768 px (una fila) y 600/390 px
(dos filas centradas); en producción las cinco rutas públicas sirven las cuatro chapas en orden y el
archivo responde `200 image/png` ([VERIFICATION.md](VERIFICATION.md) §V27).

**Open after this round:** no **screenshot** was possible (the harness desktop window was not visible),
so the look of the row is unverified by us — the client's view is the check. The **GORE** logo stays the
narrowest of the four (27 px at the row's common height) because its source is a 160×283 portrait and
enlarging it would magnify a low-res file: the HD original is still pending (F13). And the reference
strip carries no white plates, so if the "chapa" treatment is to go, that is a design decision for the
client.

**Update 2026-10-18 (sixteenth round — the client reported that administrators "cannot edit"):** the
report came with two screenshots from the published site — a `buena_practica` edit screen with an
**empty main column**, and `edit.php?ambito=economico&post_type=indicador` answering *"You need a
higher level of permission."*. Three separate, pre-existing defects were behind them, all reproduced
on production and all fixed the same day:

- **F29 — custom capabilities granted too late.** The four grants ran on `admin_init`, i.e. after
  `wp-settings.php` had already built the current user (line 766) and taken the `allcaps` snapshot, so
  `current_user_can()` could not see them in that request: the **first** admin request of every miss
  was denied, the second worked. Reproduced by removing one capability from the role (request 1 →
  **403** with the client's exact message, requests 2–3 → 200). Moved to `after_setup_theme` in
  `otrn-contenido.php:204`, `otrn-guias.php:54`, `otrn-iniciativa-pladetur.php:174`,
  `otrn-autodiagnostico.php:564`; deployed, and the same single request now answers **200**.
- **F30 — the public ámbito filter never filtered.** `?ambito=<slug>` is the listings' own filter
  *and* a public taxonomy query var, so the request resolved as a taxonomy archive and the parent
  theme rendered `<h1>Archivos: </h1>` with **0** cards — while `?categoria=` worked. Fixed with a
  front-end-only `request` filter (`functions.php:253-259`); `/indicadores/?ambito=economico` now
  returns 10 cards (45 unfiltered) and `/buenas-practicas/?ambito=economico` 12 of 80. The first
  attempt (`query_var => false`) was rejected because it broke the wp-admin list filter.
- **F31 — nothing to edit.** No field definition existed anywhere for the content types: 0
  `acf-field-group`/`acf-field` rows in both databases, no code registration, and the JSON load path
  `docs/acf-json/` never existed — the visible consequence of **F4**. The data was there
  (`descripcion`, `ubicacion`, … as post meta) but unreachable from the interface. New mu-plugin
  **`otrn-campos.php`** registers six ACF groups on `acf/init`, with field names taken from the
  existing meta keys and keys reproducing the stored `_<field>` references, so the saved values load.
  Verified locally to the end of the save path (value written, reference kept, public page updated,
  value restored) and on production at the render level (post 378 shows the six fields with
  `value="Santiago"`; 411 shows 11 fields, 66 shows 20).

Deployed and purged; front page, listings, detail pages, private routes (`302`) and the admin lists
re-checked with no change and no new line in the PHP error log. Evidence: [VERIFICATION.md](VERIFICATION.md)
§V26.

**Open after this round:** the field definitions are a **reconstruction from the stored data** — the
original labels/instructions/option lists and the deferred file repeater were not available anywhere,
so the client must review them (and the `nivel_territorial`/`fuente_datos` option lists, which would
drop a value outside them). No content was written on production (the save path was proven locally
only), the client has not re-tested their accounts yet, and it is still unconfirmed whether their
*"filtros"* complaint was the admin list (F29) or the public one (F30) — both are fixed. Still
pending from earlier decisions: the `staging.observatorioarauco.cl` subdomain plus `staging` branch,
and the mobile-home performance/accessibility round with Parcel.

**Update 2026-10-17 (fifteenth round — the active theme was switched back):** hours after the site
went live the client reported that *"once authenticated, WordPress does not show the site"*: the
front page was the admin bar plus one stray paragraph. The theme had not broken — Hostinger's
onboarding/AI assistant had written `template`/`stylesheet` back to `hostinger-ai-theme`, so the
page was rendering with that theme's block templates. WordPress rejects such a switch by itself (the
AI theme declares `Requires PHP: 8.1` and the host runs PHP 8.0), so the options were written from
**outside** WordPress. Registered as **F28** and mitigated the same day: `otrn` reactivated and the
caches purged, `hostinger-easy-onboarding` **deactivated** (it is the plugin exposing that REST
route), and a new mu-plugin `otrn-vigilante-tema.php` deployed that restores the project theme at
`setup_theme`/`switch_theme` and logs each attempt. Both paths were tested on the host — including
forcing the options by hand, outside WordPress, where the **next request** put them back —
[VERIFICATION.md](VERIFICATION.md) §V25.9. Nothing else moved: options, content and plugins are as
deployed. The client's visual sign-off is of course still pending.

## Git baseline

| Item | Value |
| --- | --- |
| Branch | `main` — now tracking `origin/main`; the first push (2026-10-17) sent the full history |
| HEAD | `c5fba94` — `docs(arauco): registrar el TTL de caché de navegador del HTML (§V25.8)` (pushed; this documentation commit follows it) |
| History | `99092d1` repo init → `70b22a7` "portada scrollytelling inmersiva + branding Arauco (ETAPA A+B+C)" → `5f43933` "modo dual estatico/inmersivo" → `f506104` docs baseline → `bb38d8b` runtime record → `8d3d40b` F11b protections → `d4fa738` F19 docs → `0dd2de4` F1 nav → `e727756` F1 regression fix → `932986b` F1 docs → `5f17b38` F1 grouped navigation → `57e09a8` F1 redesign docs → `510e981` F1 dropdown spacing fix → `cd9fd09` F1 spacing docs → `9aeaa82` design-token audit docs → `52a7d4d` municipal token consolidation → `d691f03` token docs → `3dbfbc6` dropdown hover fix (F1) → `9142179` drawer contrast fix (F2) → `0d35f84` client-review docs → `e2c3a9f` home legibility + KPI affordance fix (F20, F21) → `f40cb52` home-review docs → `ce9a093` four decisions closed (docs) → `649b01f` mobile glass pass (F21) → `94714ff` mobile glass docs → `05d2ecb` glass on the lead only (F21) → `7f93114` fourth-round docs → `bef8332` plate values set by the client (F21) → `90bf739` sticky footer (F24) → `a360fcc` sticky-footer docs → `d706872` user guide (F25-F27) → `c0fc3ce` user-guide docs → `1b19d5c` F25 title fix → `b51a521` first-deployment docs → `c5fba94` cache-TTL docs → this documentation commit (all pushed to `origin/main`) |
| Remote | `https://github.com/sh-olmedo/observatorio-arauco.git` |
| Tracked files | 822 at this commit (`otrn-vigilante-tema.php`, the F28 watchdog, is the only addition since `d706872`: 821 there, 805 at `a360fcc` — `d706872` added 16, `guias-contenido.php`, `assets/css/otrn-guias.css`, `assets/otrn-guias.js`, the 12 screenshots under `assets/guias/` and `mu-plugins/otrn-privacidad-guias.php`) |

## Working tree — 1 remaining file (do not commit)

`git status --short` shows **one** modified file, unchanged and deliberately uncommitted:

| File | Change | Interpretation |
| --- | --- | --- |
| `wp-content/mu-plugins/99-studio-loader.php` | Studio temp dir name token (machine-specific) | Environment-specific path; not a project change |

The other two files that used to sit here — `wp-content/themes/otrn/assets/css/otrn-landing.css`
(the unscoped `:root` `--aru-*` fallback) and `style.css` (`--dark`, Manrope→`--font-title`,
`--font-title`) — are **now committed in `52a7d4d`**: their deltas were precisely the token
conflict this round consolidated. F14 can be closed for those two; only the machine-specific
loader remains.

## What exists today (static view)

- Working, statically verified structure: immersive landing (dual mode), classic chrome,
  autodiagnóstico (form with drafts, archive/map, results), indicators (+Excel drawer),
  good practices, support material, the **session-gated user guide** (`/guias/`, §V24),
  Pladetur follow-up + statistics, news, Geoportal embed, About page. Details and per-module
  status: [FEATURES.md](FEATURES.md).
- Architecture and data flows: [ARCHITECTURE.md](ARCHITECTURE.md).
- Design system: [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md).
- Security posture: [SECURITY_AUDIT.md](SECURITY_AUDIT.md).

## Blockers & pending decisions

| Blocker / decision | Related | Owner |
| --- | --- | --- |
| Runtime verification **partially executed** (2026-10-15): gates/REST/mobile/no-JS/fallback done; remaining items blocked on auth/mutations ([VERIFICATION.md](VERIFICATION.md) §V13) | F17 | Team (Phase 0) |
| Commit strategy for the remaining uncommitted file (only `99-studio-loader.php`, a machine-specific temp path, is still uncommitted; the two CSS deltas were committed in `52a7d4d`) | F14 | Team (Phase 0) |
| ACF workflow — runtime shows **0 field groups** and raw-meta fields; decide define-or-document (exporting is currently impossible) | F4 | Team (Phase 0) |
| Operator/role model for evaluation submission | F3 | Client/team (Phase 1) |
| Palette | **Implemented 2026-10-16**: the Municipalidad de Arauco identity was verified (primary `#0070FF`, secondary `#DE232B`, accent `#41BCFF`, text `#000000`) and applied in `52a7d4d`. Pending: **the client's visual sign-off** of the rendered result | F2, F6 | Client (review) |
| Token mapping and the fate of the colours with no municipal source (`#0C1434`, `#0C0D0E`, `#D6D5D5`) | **Applied** ([DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) §13); the three unverified colours were **not** adopted | F2, F6 | Client (review) |
| **Visual sign-off of the municipal token pass (`52a7d4d`)** plus the review rounds (`3dbfbc6`, `9142179`, `e2c3a9f`, `649b01f`, `05d2ecb`, `bef8332`) — brand blue/red/navy applied to headers, buttons, links, cards, tables, filters, forms and the footer, on the homepage and on internal pages. **First review round found a drawer contrast defect (fixed); second round, two home-page defects (F20/F21, fixed); third round, the mobile scenes (photograph hidden by the veil → clear gauze + glass plate on the text); fourth round, the plate narrowed to the lead (photograph visible, text-shadow for the rest, §V21); fifth round, the plate tinted to the client's own values (0,35, blur 9, §V22). Screenshots are possible again (harness window visible), so the rendered result has been looked at by the team — but the client's sign-off is still pending** | F2, F6, F20, F21 | Client/team |
| Readability of the scene copy: the eyebrow, the title and the CTA of scenes 2-3 sit on the photograph with a text-shadow and measure 1,49-2,73:1 raw (§V21), and the lead — on the client's lighter plate — is at 4,12-4,44:1 on its worst pixel (mean 5,15-6,90:1, §V22). Whether that reads well enough, or the lead's tint goes to 0,45 (AA-safe, 4,88-5,11:1) and/or the plate comes back behind the whole block (as in `649b01f`) or behind the eyebrow + title only | F21, F2 | Client (review) |
| Ghost CTA boundary over photography (1,80-1,81:1, WCAG 1.4.11): give the control a small glass pill of its own on mobile, or accept the gap | F23 | Client/team |
| `@media print` for the home page: the scene copy is lost on paper (pre-existing, recorded 2026-10-16 while verifying `649b01f`) | F22 | Client/team (low priority) |
| Arauco geography: sector list + coordinates | F8 | Client (Phase 4) |
| Geoportal StoryMap URL (scope unknown) | F9 | Client (Phase 4) |
| Survey embed fate | F10 | Client (Phase 4) |
| HD/transparent logos: **GORE** (the RAU one was resolved 2026-10-18 — the repository already had it and the footer now uses it) | F13 | Client (Phase 4) |
| DB content inventoried at count level (2026-10-15): pages, 74 criteria, 10 sectors, 6 evaluations, 3 data cards; copy migration still pending | F18 | Team (Phase 4) |
| Geocoding provider + usage policy for the location search (rate limits, attribution, caching; browser-direct vs server proxy) | F19 | Client/team (before Phase 6) |
| Fate of the curated sector list and of the location metas under the new search picker | F8 ↔ F19 | Client/team (Phase 4/6) |
| Git push of the local commits (`8d3d40b` … `1b19d5c` plus the two documentation commits): **decided in the fourteenth round** — the client asked for the push, together with the deployment | F14 | User (done) |
| **Production `observatorioarauco.cl` is live** (fourteenth round) with the public content set, and search engines are discouraged (`blog_public = 0`) until the client approves. Going public means flipping that option back — and the visual sign-off above still stands | §V25, F7–F13 | Client |
| Installer leftovers on production, **left in place at the client's request**: a published *Sample Page* (`/sample-page/`) and two draft copies of *Privacy Policy* | §V25.7 | Client (content) |
| **F28 — Hostinger's onboarding/AI assistant switched the active theme back** on production (`template`/`stylesheet` → `hostinger-ai-theme`), leaving the live front page as a single paragraph until it was caught. Mitigated the same day: `hostinger-easy-onboarding` **deactivated** and `otrn-vigilante-tema.php` deployed (restores the project theme at `setup_theme`/`switch_theme` and logs the attempt). **Do not run the AI assistant/builder for this site** | F28, §V25.9 | Client/team |
| No **authenticated** view has been verified **by the team** on production (only the administrator account exists and no credentials were used), so the private sections are proven closed — not proven to render. The **client** reviews them with their own account instead (their choice); their first authenticated visit is what surfaced **F28**, and no private-view finding has been reported yet | §V25.7, §V24.6 | Client (review) |
| Sticky footer (`90bf739`, **F24**): the footer now sits on the bottom edge of short pages and is pushed by the content otherwise. Pending **the client's visual confirmation**, ideally on a real phone (`dvh` + dynamic toolbars are not verifiable in the harness) | F24 | Client/team |
| **UX/UI review of the user guide** (`d706872`): its look (index, article, figures) and its Spanish copy for non-technical readers. The guide is a deliverable, **not a signed-off design** | §V24 | Client/team |
| Fate of the sample `guia` post that the guide's appendix surfaces (*"Nueva giua de usuario"*, ID 409): delete it, rewrite it or unpublish it. **The database is not editable from this repository**; the production import deliberately left the `guia` type out, so the sample post is local-only today | F27 | Client (content) |
| WordPress's own screens and the title's brand: the **local** locale is still `it_IT` and the **local** `blogname` still says *"Observatorio Turístico Rapa Nui"* (F7). Production already runs `es_CL` with the Arauco name | F26, F7 | Client/team |
| Real authenticated pass for the guide: the login → return trip and a **non-admin** logged-in account (only the administrator account exists), neither available in the harness | §V24.6 | Team (needs a disposable environment) |
| **Explicit UX/UI sign-off of the grouped navigation (F1 redesign, `5f17b38`)** — colours, wording, chevrons, «Más» behaviour; nothing else should change until it is reviewed | F1 | Client/team |
| **Visual confirmation of the dropdown spacing (`510e981`)** — 12 px rows / 2 px gap / inset highlight, and the account-menu row. **The client's review found that the 12 px gap made the panel unreachable with the mouse; the mouse bridge in `3dbfbc6` fixes it and the confirmation is pending again** (screenshots are possible again, so it can now be captured) | F1 | Client/team |
| Real authenticated-session pass for the F1 nav (login writes session tokens to the DB; the 2026-10-16 pass used a temporary `wp_set_current_user()` mu-plugin, since deleted) | F1, F17 | Team (needs a disposable environment) |
| Real-device pass: touch, iOS dynamic viewport, `prefers-reduced-motion` and the rendered focus ring (the harness could not deliver real key events while its window was hidden) | F1, F17 | Team |

## Recommended next task (proposed, not approved)

Phase 0 progress: the runtime checklist was executed on 2026-10-15
([VERIFICATION.md](VERIFICATION.md)) and it **confirmed** F1, F5, F6, F11b and F16 — so the
remaining Phase 0 items are decisions: (1) the working-tree strategy (F14) and (2) the ACF
workflow (F4). The recommended first implementation scope (proposed, not approved) is the
security pair **F3 + F11b** (Phase 1 — F11b is now a confirmed anonymous-read exposure),
followed by **F5** (Phase 2; F1 is implemented and browser-verified — `0dd2de4` + `e727756`).
The palette/token work (F2/F6) has since been **executed and verified in code** (fifth round
below), pending the client's visual review; content migration (F7–F10) still waits on the
client decisions listed above.

**2026-10-16 addition:** F19 (self-assessment location search — OSM geocoding) is registered
as a new Arauco requirement: documentation only, not approved; proposed as Phase 6 and
dependent on the geocoding-provider decision, coordinated with F8. It does not change the
recommended order above.

**2026-10-16 addition (second round):** F1 has moved from "fix the responsive layout" to
"review the approved information architecture as rendered": the grouped navigation
(`5f17b38`) is implemented and browser-verified, and the immediate next task for F1 is the
**client's visual review** (see the blocker table). It does not change the recommended order
above: the security pair **F3 + F11b** is still the proposed first implementation scope, with
**F5** next.

**2026-10-16 addition (third round):** the client's first review note was the spacing of the
dropdown destinations, now rebuilt in `510e981` (12 px rows, 2 px gap, inset highlight; the
earlier padding never applied because of a specificity clash with the landing reset). The next
task for F1 is still the same: **the client's visual confirmation**, now of this spacing in
particular. No other visual change should be started until then.

**2026-10-16 addition (fourth round — design direction):** the client confirmed the municipal
identity and asked for the design-system consolidation. Before any style change, the
deliverable is the **token mapping** (existing token → municipal token, verified source,
semantic role, affected components), presented for approval. Once approved, the implementation
order proposed by the audit is: (1) approve the mapping and the fate of the colours with no
municipal source; (2) consolidate to a single token source (remove the 10 conflicting `--aru-*`
redefinitions and the unscoped `:root` fallback); (3) replace hardcoded legacy colours; (4)
verify on the homepage and representative internal pages. This does **not** change the security
order above; the F1 visual sign-off remains outstanding in parallel.

**2026-10-16 addition (fifth round — implementation):** the mapping was approved and applied
(`52a7d4d`), so the proposed order now reads: the **client's visual review** of both the F1
navigation and the municipal token pass is the immediate next task, and the security pair
**F3 + F11b** then remains the first *code* scope, with **F5** next. Two design decisions are
still open and are not code tasks: the **non-text contrast** of form-control borders (WCAG
1.4.11 — the single light `--grey` is below 3:1) and whether to **ship Poppins** (the municipal
secondary family), which currently has no token because the font files are absent. The token
pass introduced no new component, route or dependency, and left the private gate, the DB and
the content untouched.

**2026-10-16 addition (sixth round — client review):** the client reviewed the rendered pass
and reported two defects, both fixed (`3dbfbc6` dropdown hover, `9142179` drawer contrast), with
the measurements in [VERIFICATION.md](VERIFICATION.md) §V18. What is left for the client is the
**final visual sign-off** of the municipal token pass and of the F1 navigation — the review
found defects, it did not approve either. The open design decisions are unchanged (non-text
contrast of form borders; whether to ship Poppins). The proposed code order is still
**F3 + F11b**, then **F5**.

**2026-10-16 addition (seventh round — home-page review):** the client's second review round
raised the home page only, and produced **F20** (informative KPIs read as clickable) and **F21**
(scene text illegible over the photographs), both fixed in `e2c3a9f` and measured in
[VERIFICATION.md](VERIFICATION.md) §V19. Two things follow for whoever picks this up:

1. **The immediate next task is unchanged: the client's visual sign-off**, now of four things at
   once — the F1 navigation, the municipal token pass (`52a7d4d`), the drawer contrast
   (`9142179`) and this home-page round (`e2c3a9f`). The F21 evidence also adds a **client
   decision**, not a code task: how much photograph to keep in the static (mobile) scenes
   (AA over the brightest photo pixel needs ≈0.87–0.88 veil opacity).
2. **A verification tool changed**: `browser.screenshot` worked this round (the harness window
   was visible), and a **pixel-sampling method** for text-over-photograph contrast was added to
   §V19. Re-run the contrast/overflow sweeps with them rather than trusting computed styles alone.
   The proposed code order is otherwise unchanged: **F3 + F11b**, then **F5**.

**2026-10-16 note (user guide, delivered outside this order):** the client asked for a user guide
of the site itself, and it landed ahead of the plan — `/guias/`, `d706872`, evidence in §V24. It is
**delivered and awaiting the client's UX/UI review**, and it leaves three small decisions behind it
(F27 the sample post, F25 the missing `<title>`, F26 the Italian locale). None of them displaces the
proposed code order above: **F3 + F11b**, then **F5**.

**2026-10-17 addition (deployment):** the site is now **live on `observatorioarauco.cl`** with the
public content set, and F25 was fixed on the way in (`1b19d5c`); search engines are discouraged
until the client approves (§V25). The proposed code order is unchanged — **F3 + F11b**, then
**F5** — but two housekeeping items now sit alongside it: deciding when to lift the
search-engine discouragement, and the installer leftovers on production.

**2026-10-17 addition (F28 incident):** a few hours after going live, the front page was taken over
by Hostinger's AI theme (F28). The mitigation is in place and tested — the onboarding plugin
deactivated plus the `otrn-vigilante-tema.php` watchdog (§V25.9) — and it does not change the
proposed order (**F3 + F11b**, then **F5**). One operational rule comes out of it: **do not run
Hostinger's AI assistant/builder on this site**, and re-check the active theme after any panel-side
action on it.

**Update 2026-10-16 (eighth round — four decisions closed, no code change):** the client answered
the open design questions raised after the seventh round. **No file changed**, so this is
documentation only:

1. **Scene veil weight (F21)**: **keep 0.88–0.92**. AA against the brightest pixel of each
   photograph is the governing requirement; the mobile scenes stay a blue-tinted backdrop with
   detail mainly in the top strip.
2. **Drawer `.et` labels**: **keep `--text2`** (4.58:1 / 4.95:1, AA) for hierarchy against the
   `--dark` content.
3. **Form-control borders (WCAG 1.4.11)**: **leave as is**. The single `--grey` stays below 3:1;
   it is a documented gap, not a silent one, and no second border token is added.
4. **Poppins**: **not shipped**. Montserrat + IBM Plex Mono are the system; the municipal
   secondary family is dropped from the parity list.

With these four closed, the only thing outstanding on the identity work is the **client's visual
sign-off** of `52a7d4d` + `3dbfbc6` + `9142179` + `e2c3a9f` (and of the F1 navigation).

**Update 2026-10-16 (ninth round — mobile scenes: photograph visible + glass on the text):** the
client went back to the **home page on mobile** and reversed the eighth round's trade **for the
phone only**: *"En mobile no se alcanzan a ver las imágenes de fondo debido al degradado. El
problema es específicamente con las bajadas de texto que no se leen bien en mobile. ¿Podrías
probar con efecto glass en las bajadas de texto?"*. Fixed in **`649b01f`** — one file,
`assets/css/otrn-landing.css` (the `@media (max-width:768px)` block plus the scene eyebrow
colour): no PHP, markup, content, route or database change, and nothing above 768 px except the
eyebrows.

- The veil drops to a **clear gauze** (0.06 → 0.42) so the photograph is unveiled over **89-90 %**
  of each act (6.7-7.2 % before), and the text block is read on a **glass panel**
  (`rgba(48,74,131,.65)` + `backdrop-filter:blur(16px)`) that goes **inside** the act's inner
  container, so no new element and no template change. The panel hugs its content (575 px from
  481 px up, leaving photograph to its right) and is inset 8 px.
- The glass covers the eyebrow and the title too, not only the lead: measured, the 12 px eyebrow
  and the title over a bright sky are below AA on the light veil alone — that is why the ask
  ("glass on the leads") becomes a panel for the whole text block. The scene eyebrow moves to
  `--paper`, the hero's scroll cue gets the same glass as a pill, the ghost CTA's border rises to
  0.8 (1.4.11: 2,72 → 3,92) and `@supports not (backdrop-filter)` raises the tint to 0.82.
- Measured 320/375/400/480/768 px: **every element ≥ 4,68:1, no AA failure, 0 px overflow**.
  The trade against the previous veil is ≈1.4-2.0 points of contrast for ≈82 points of visible
  photograph — [VERIFICATION.md](VERIFICATION.md) §V20.
- **New finding F22** (recorded, not fixed, not requested): the theme has no `@media print` rules,
  so the scene's light text over the photographs is lost on paper — pre-existing.
- **What is open for the client**: the *weight* of the panel (how dark, how much of the act it may
  cover) and, as before, the visual sign-off of the whole identity work. Nothing in this round is
  claimed as approved; a developer look at the rendered result (375/768 px) is not a sign-off.

**Update 2026-10-16 (tenth round — the glass moves to the lead):** the client answered the ninth
round with the **mobile scene 3 marked up**, the lead encircled in red: *«El efecto glass es solo
para el texto demarcado con rojo. Así se podrá ver la imagen de fondo»*. Fixed in **`05d2ecb`** —
one file, `assets/css/otrn-landing.css`, inside the same `@media (max-width:768px)` block: no PHP,
markup, content, route or database change, and nothing above 768 px.

- The plate leaves `.otrn-act__inner` and moves to **`.otrn-act__lead`** (14 px wider than the text,
  6 px from the viewport edge, the vertical padding compensated in the margin so the rhythm stays).
  Coverage drops from 62-84 % of each act to **14,8-32,8 %**.
- The **eyebrow, the title and the CTAs of scenes 2-3** go back over the photograph and are
  separated from it by a **text-shadow** (tight on the 12 px eyebrow and the CTA labels, wide on the
  title), scoped to the three scene acts so the light "Datos"/"Explora" sections — which reuse the
  same classes — keep dark text on paper with no shadow.
- The veil, the scroll cue's glass pill and the hero's solid CTA are unchanged.
- Measured: the lead on the plate **6,32-6,51:1**, the hero CTA **7,40:1** and the cue **6,34-7,20:1**
  pass AA at 320/375/400/480/768 px; the photograph stays unveiled over 89-90 % of each act; the
  eyebrow, the title and the CTA labels measure **1,49-2,73:1 raw** with the shadow as a mitigation
  (WCAG 1.4.3 does not count a shadow) — a **client-accepted, documented gap**; the `649b01f`
  whole-block plate remains the compliant alternative. **New finding F23**: the ghost CTA's border
  over photography measures 1,80-1,81:1 (WCAG 1.4.11) — a regression of this round, registered with
  its fix path. Detail: [VERIFICATION.md](VERIFICATION.md) §V21.
- **Open for the client**: whether the shadowed eyebrow and title read well enough on a real phone
  (the alternatives are one plate behind the whole block, or one behind the eyebrow + title only),
  the F23 boundary, and the visual sign-off. A developer look at 375/768 px is not a sign-off.

**Update 2026-10-16 (eleventh round — the client sets the plate's values):** the client sent the
plate's declaration back with values to use (**`bef8332`**, one file, same `@media (max-width:768px)`
block). **Two were corrected, not pasted:** `rgba(48, 74, 131, -0.35)` is invalid CSS (alpha accepts
0-1), so the browser would discard the declaration and leave the lead with **no tint** (measured
2,00-2,39:1 — worse than before); the tint was applied as **0,35**. And the snippet carried **two
different blurs** (`16px` in the `-webkit-` prefix, `9px` in the standard property — the standard
wins), so both were normalised to **9 px**. The radius, padding and margins already matched.

- Consequence: the lead's worst pixel drops to **4,12-4,44:1**, 0,06-0,38 under the 4,5:1 of AA
  (mean 5,15-6,90:1). **0,45 is the nearest AA-safe value** (4,88-5,11:1) and looks practically the
  same — one number, on request.
- Unchanged: the veil, the cue pill (6,34-7,20:1), the hero's solid CTA (7,40:1), the text-shadow of
  §V21, the `@supports not` fallback (0,82 → 5,42-5,60:1), 0 px overflow at 320-768 px, and everything
  above 768 px. **New upside:** with the lighter tint the photograph now reads *through* the plate,
  not only around it. Detail: [VERIFICATION.md](VERIFICATION.md) §V22.
- **Open for the client**: whether 4,12-4,44:1 on the lead's brightest pixel is acceptable or the
  tint goes to 0,45; the §V21 shadow trade; **F23**; and the visual sign-off.

**Update 2026-10-16 (twelfth round — sticky footer, F24):** the client asked for the classic
sticky footer (*"cuando el contenido sea menor a la altura de la pantalla se posicione al final de
esta y si el contenido crece más allá, el footer sea empujado con el contenido"*, with a screenshot
of `/guias/` where the footer floated mid-screen). Registered as **F24** and fixed in **`90bf739`** —
one file, `wp-content/themes/otrn/style.css` (new "Estructura de página" block): **no PHP, markup,
content, route or database change, and no token touched**.

- The page shell is now a column flex box: `body.otrn` takes
  `min-height:calc(100vh - var(--wp-admin--admin-bar--height,0px))` (with a `dvh` variant inside
  `@supports`), `main` absorbs the free space (`flex:1 0 auto`) and the footer keeps its natural
  height. Short content → the footer sits on the bottom edge of the window; long content → the
  footer is pushed with the document, exactly as before.
- **The admin-bar term is not decorative**: WordPress offsets the document with
  `html{margin-top:32px !important}` (46 px ≤782 px) and publishes the height in
  `--wp-admin--admin-bar--height`. Without subtracting it the shell is one bar taller than the
  visible area, i.e. the page gains a scroll of exactly the bar's height for logged-in authors.
- Measured (real tab, 809×804, 11 theme URLs): **the footer's bottom edge equals the document height
  everywhere** and horizontal overflow is 0 px; `/guias/` — the short page of the screenshot —
  collapses to exactly the viewport (804/804) with no scrollbar, and the element at the bottom edge
  is the footer itself. The admin bar (simulated with core's own CSS and variable) keeps the footer
  flush at 32/46 px. The **front page is unchanged**: same 6091 px document, same 3055 px ScrollTrigger
  pin spacer and the same scrollytelling sequence with the shell on and off. Detail:
  [VERIFICATION.md](VERIFICATION.md) §V23.
- **Not verified**: real devices and iOS dynamic toolbars (`dvh`), a real logged-in session, and
  `/evaluar/` (the only template with the sticky `.otrn-formbar`, a private route — its bar is
  contained by the `<form>`, so the shell does not move it, but it was not measured).
- **Open for the client**: the visual confirmation of this behaviour, and everything still pending
  from the previous rounds (the §V22 plate tint, the §V21 shadow trade, F23, F22).

**Update 2026-10-16 (thirteenth round — user guide for the site itself):** the client asked for a
**user guide of the real site**. It is implemented at the existing route `/guias/` and committed
locally as **`d706872`** (code + 12 real screenshots, 16 new files). The route, the post type, the
database, the options and the dependency list are unchanged: the guide's copy lives in a new PHP
data file, and no other page, URL, user or content is touched.

- **What it is**: a help centre with 10 topics — getting started; navigation and account;
  indicators; charts, filters and views; geoportal; good practices; PlAdetur planning and
  monitoring; sustainability self-assessments; reports and material; FAQ — plus an appendix that
  lists the published `guia` posts. Every label quoted in the copy (e.g. «Buscar»,
  «Ver evolución →», «← Volver a Planificación») was taken from the rendered interface, and the
  copy is Spanish for non-technical readers.
- **Private, server-side** (the part that had to be right): the guide requires a **session**, not a
  role. `otrn_guia_usuario_privada()` (`functions.php`, priority 1 on `template_redirect`) covers
  the archive, the single and the feed, and a new mu-plugin (`otrn-privacidad-guias.php`) closes
  REST, both searches, oEmbed and the sitemap. Measured anonymously: every HTML route 302s to the
  login with its `redirect_to` intact, REST answers **401**, the REST search answers `[]`,
  `/?s=guia` and `/wp-sitemap.xml` show nothing, oEmbed 404s. The guide is deliberately **not** in
  `otrn_privado_tipos()` — that list demands `read_private_guias` and would 403 logged-in
  non-admins — so the existing private-section rules stay exactly as they were.
- **Layout and behaviour**: sticky index plus article on desktop, one column with a `<details>`
  index at ≤979 px; the JS only adds the text filter and the current-topic highlight over a
  server-rendered baseline (verified with scripts blocked: all 10 topics, the anchors and the
  figures are there). 375→1920 px with **0 px horizontal overflow**; the keyboard tab order and
  the visible focus ring were checked with real key events. Detail: §V24.
- **What it deliberately does not ship**: a figure of the private evaluation **listing or map** —
  a theme image is a static file served without a session and those views carry territorial
  results, so the guide states the limitation in its own text instead. The figures that do ship
  show public views, public data or an *empty* form.
- **Findings it surfaced instead of fixing** (each is a client decision or a site-wide change, so
  none was applied silently): **F25** — no route emits a `<title>` (`title-tag` is never
  declared); **F26** — the site locale is `it_IT`, so WordPress's own screens (login, password
  reset, admin) are Italian while the site copy is Spanish; **F27** — the only published `guia`
  post is the WordPress sample *"Nueva giua de usuario"*, which the guide's appendix now surfaces
  to logged-in readers.
- **Open for the client**: the **UX/UI review of the guide's look and copy** (it is a deliverable,
  not a signed-off design), the fate of the sample post (F27), and whether a non-admin account
  should reach the guide at all (§V24.6: only the administrator account exists today).

**Update 2026-10-17 (fourteenth round — first production deployment):** the client asked to
prepare and publish the site and approved **direct-to-production** after the panel showed that
Hostinger's *Staging* requires a plan upgrade (Premium Web Hosting) and the free alternative
(subdomain + second install) was declined. Everything below happened **on the host**, not on this
machine; full evidence in [VERIFICATION.md](VERIFICATION.md) §V25.

- **Safety net first**: whole docroot (`files-antes.tar.gz`, 62 MB) and database
  (`db-antes.sql`, 117 KB) under `~/backups-arauco-20261016-2141/`. `wp db export` **fails on this
  host** (exit 255, no output); the dump was taken with `mysqldump` reading the password inside
  the remote shell — never printed, never stored here.
- **Code**: `rsync` of `themes/otrn/` (95 files) and the 6 `mu-plugins/otrn-*.php`. The Studio-only
  paths were deliberately **not** shipped: `99-studio-loader.php`, the SQLite integration and
  `wp-content/database/` (the host is MySQL).
- **Content**: 216 items of the **public** set — 79 buenas prácticas, 64 iniciativas PlAdetur,
  44 indicadores, 9 páginas, 6 material, 4 noticias, 3 datos de portada, 7 adjuntos. Left out by
  client decision: the 74 criteria, 6 evaluations and 10 sectors (the private geography) and the
  `guia` sample post (F27). 8 URL replacements (`localhost:8895` → the domain).
- **Configuration**: site name and description moved to Arauco, `es_CL`, `America/Santiago`,
  `/%postname%/`, and **`blog_public = 0`** (discourage search engines) until the content is
  approved. ACF 6.8.10 active (6.8.0 does not exist in wp.org); the importer was deactivated
  after use. The host's PHP 8.0 was **not** changed: the theme uses no PHP 8.1+ syntax.
- **Measured anonymously**: public routes and fiches 200 with no PHP error string and no broken
  image; `/evaluar/`, `/mapa/`, `/autoevaluacion/` and `/guias/` 302 to the login; `/criterio/`
  301 exactly as it does locally (the CPT has `has_archive => false`).
- **F25 fixed in this round** (`1b19d5c`): one line, `add_theme_support( 'title-tag' )`. The
  concern that made the old fix path "not a drive-by fix" is gone — production's `blogname` is
  already Arauco. Locally the title still shows the old brand because the **local** `blogname`
  was not touched (F7).
- **Browser cache lowered to one hour** after a reviewer's browser kept serving the pre-fix page:
  the `max-age=604800` came from the host's `ExpiresDefault` in the site's `.htaccess`, not from
  LiteSpeed, so one line — `ExpiresByType text/html "access plus 1 hour"` — was added to that
  block (copy kept as `.htaccess.bak-ttl-202610170845`). Assets keep 1 month / 1 year (§V25.8).
- **Open**: no authenticated view on production (only the administrator account exists and no
  credentials were used); the installer leftovers the client chose to leave (*Sample Page* and two
  *Privacy Policy* drafts); and every pending visual decision from §V16–§V24, which a deployment
  does not resolve.

## How to run the site (reference only — not executed in this task)

- Current local site (2026-10-15): `http://localhost:8895/`, WordPress 7.1.3, PHP 8.3 —
  always re-check with `studio status`.
- Local site is managed by WordPress Studio; see `STUDIO.md`.
- Always prefix WP-CLI with `studio`: e.g. `studio wp post list --post_type=evaluacion`.
- Secrets live in the unversioned `wp-config.php`; the DB is
  `wp-content/database/.ht.sqlite` (gitignored). Do not commit either.

## Guarantees for the original project

- The original **Observatorio Turístico de Rapa Nui** project was not touched; this repo is
  a copy and the migration is tracked in [ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md).
- The documentation baseline commit `f506104` changed only: `AGENTS.md`, the new files under
  `docs/`, and a one-line `.gitignore` addition (`!docs/`) so the documentation is
  versionable — nothing else (verify with `git show --stat f506104`).
- The 2026-10-15 runtime pass changed only documentation (the new
  [VERIFICATION.md](VERIFICATION.md) and status updates in the existing documents); those
  edits were committed locally as `bb38d8b` (not pushed). Application-code changes since:
  `8d3d40b` (F11b protections: one new mu-plugin) and the F1 navigation changes `0dd2de4` +
  `e727756` (new `assets/css/otrn-menu.css`, `assets/otrn-menu.js`, `functions.php` enqueue)
  and `5f17b38` (grouped navigation in `header.php` + `functions.php` + the two assets), plus
  the dropdown-spacing fix `510e981` (only `assets/css/otrn-menu.css`). The
  F19 change set is committed as `d4fa738` (documentation only). The 2026-10-16 design audit
  changed **documentation only** (new `docs/DESIGN_TOKENS_AUDIT.md` plus updates to
  `DESIGN_SYSTEM.md`, `ISSUES.md`, `ARAUCO_MIGRATION.md`, `README.md` and this file) and
  touched no application code. The municipal token consolidation is committed as **`52a7d4d`**
  (application code: `style.css`, the three asset stylesheets, and 17 PHP templates — colour,
  font and radius values only; no markup, no logic, no content, no route, no new file) and it
  **absorbs** the two pre-existing CSS working-tree deltas. The client's review of that pass
  produced two further fixes: **`3dbfbc6`** (`assets/css/otrn-menu.css`: a mouse bridge over the
  dropdown gap, plus one line of reasoning-only CSS for the account panel) and **`9142179`**
  (`style.css`: two text colours in the evaluation drawer). The client's second review round
  (home page only) is committed as **`e2c3a9f`** (application code: `assets/css/otrn-landing.css`
  and `assets/otrn-landing.js` — one veil rule, one panel-width variable, one hero clamp, one
  rail colour and one timeline position; no markup, logic, content, route or new file).
  The client's third review round (mobile scenes) is committed as **`649b01f`** (application code:
  `assets/css/otrn-landing.css` — the `@media (max-width:768px)` block: one lighter veil, one glass
  plate on the text container, one glass pill for the scroll cue and one ghost-CTA border, plus two
  scene eyebrow colour declarations outside the query; no markup, logic, content, route or new
  file). Its fourth round (the plate on the lead only) is **`05d2ecb`** (same file and query again:
  the plate moved from the inner to the lead, the veil untouched, a scoped text-shadow for the copy
  that returned to the photograph, and the `@supports not` branch retargeted; no markup, logic,
  content, route or new file). Its fifth round (the client's own plate values) is **`bef8332`**
  (same file and query once more: the lead's tint to 0,35 and its blur to 9 px — the invalid
  negative alpha corrected and the two prefix blurs normalised — and nothing else touched).
  All of them are local and unpushed.
  The visual result of `52a7d4d` is **not** claimed as approved: the client's reviews found
  defects and the final sign-off is pending.
  The **user guide** is **`d706872`**: application code `functions.php` (one session gate, one
  conditional enqueue and one nav condition), `archive-guia.php`, the new `guias-contenido.php`,
  the new `assets/css/otrn-guias.css` and `assets/otrn-guias.js`, the 12 new screenshots under
  `assets/guias/` and the new `mu-plugins/otrn-privacidad-guias.php` — 16 new files, with no
  route, post type, database row, option or dependency added and no other page, URL, user or
  content touched. It is local and unpushed too, and its rendered result is **not** claimed as
  approved either.
- The **first production deployment** (fourteenth round, §V25) wrote **outside this repository**,
  on the client's Hostinger account, and only inside the site `observatorioarauco.cl`: the files
  under `public_html/wp-content/themes/otrn/` and six `mu-plugins/otrn-*.php`, that site's own
  MySQL database (the content import, 8 URL replacements and seven options), two plugins
  installed (one deactivated after use), and one line appended to that site's own `.htaccess`
  expiry block — with the previous file kept beside it as `.htaccess.bak-ttl-202610170845`. No
  core file, no `wp-config.php`, no other site on the
  account, no DNS record and no panel setting outside that site was touched; its whole docroot and
  its database were backed up **before** the first write. The repository changed only in
  `functions.php` (`1b19d5c`, the F25 fix — one line — plus the comment that records the finding)
  and in this documentation, and the Studio-only paths (`99-studio-loader.php`, the SQLite
  integration and `wp-content/database/`) were never shipped. The local database was not written
  to, and the local site's name and locale are left as they were (F7/F26).
- Temporary files used to verify F1 (§V16) were deleted before committing: the simulation
  mu-plugin (`wp_set_current_user()`, re-created and deleted again for the `510e981` re-check)
  and the local evidence pages. The guide's pass (§V24) used the same device — a temporary
  `determine_current_user` mu-plugin — **deleted before `d706872`**, after which every anonymous
  probe of §V24.1 was re-run. The token pass (§V17) created **no** files: it measured
  computed styles in same-origin iframes and injected a throwaway lab fragment into an iframe
  body only. `git status` lists no new files, and the database was never written to (no login,
  no post/meta/option change; the private-gate check was anonymous `curl`).

## Documentation maintenance

Update the relevant document with every change: [ISSUES.md](ISSUES.md) for findings,
[ROADMAP.md](ROADMAP.md) + [FEATURES.md](FEATURES.md) for progress, this file for repo
state, [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) for tokens, [SECURITY_AUDIT.md](SECURITY_AUDIT.md)
for security-relevant changes. Keep docs in English; keep finding IDs stable.
