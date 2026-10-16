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
found these two things, it did not approve the pass); the non-text contrast of form borders and
the Poppins decision remain open.

## Git baseline

| Item | Value |
| --- | --- |
| Branch | `main` (remote `origin` configured; no remote refs fetched locally) |
| HEAD | `d691f03` — `docs(arauco): registrar la implementación de tokens municipales y su verificación (F2, F6)` (local, not pushed; the two client-review fixes `3dbfbc6`/`9142179` and their docs commit follow it) |
| History | `99092d1` repo init → `70b22a7` "portada scrollytelling inmersiva + branding Arauco (ETAPA A+B+C)" → `5f43933` "modo dual estatico/inmersivo" → `f506104` docs baseline → `bb38d8b` runtime record → `8d3d40b` F11b protections → `d4fa738` F19 docs → `0dd2de4` F1 nav → `e727756` F1 regression fix → `932986b` F1 docs → `5f17b38` F1 grouped navigation → `57e09a8` F1 redesign docs → `510e981` F1 dropdown spacing fix → `cd9fd09` F1 spacing docs → `9aeaa82` design-token audit docs → `52a7d4d` municipal token consolidation → `d691f03` token docs → `3dbfbc6` dropdown hover fix (F1) → `9142179` drawer contrast fix (F2) (last eighteen local, not pushed) |
| Remote | `https://github.com/sh-olmedo/observatorio-arauco.git` |
| Tracked files | 803 (`bb38d8b` added `docs/VERIFICATION.md`; `8d3d40b` added `wp-content/mu-plugins/otrn-privacidad-seccion.php`; `52a7d4d` added/removed no files) |

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
  good practices, support material, guides, Pladetur follow-up + statistics, news,
  Geoportal embed, About page. Details and per-module status: [FEATURES.md](FEATURES.md).
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
| **Visual sign-off of the municipal token pass (`52a7d4d`)** — brand blue/red/navy applied to headers, buttons, links, cards, tables, filters, forms and the footer, on the homepage and on internal pages. Measured, not seen: no screenshot is possible while the harness window is not visible. **First review round (2026-10-16) found one contrast defect in the evaluation drawer, fixed in `9142179`; final sign-off still pending** | F2, F6 | Client/team |
| Arauco geography: sector list + coordinates | F8 | Client (Phase 4) |
| Geoportal StoryMap URL (scope unknown) | F9 | Client (Phase 4) |
| Survey embed fate | F10 | Client (Phase 4) |
| HD/transparent logos (GORE, RAU) | F13 | Client (Phase 4) |
| DB content inventoried at count level (2026-10-15): pages, 74 criteria, 10 sectors, 6 evaluations, 3 data cards; copy migration still pending | F18 | Team (Phase 4) |
| Geocoding provider + usage policy for the location search (rate limits, attribution, caching; browser-direct vs server proxy) | F19 | Client/team (before Phase 6) |
| Fate of the curated sector list and of the location metas under the new search picker | F8 ↔ F19 | Client/team (Phase 4/6) |
| Push/deploy decision for the local commits (`8d3d40b`, `d4fa738`, `0dd2de4`, `e727756`, `932986b`, `5f17b38`, `57e09a8`, `510e981`, `cd9fd09`, `9aeaa82`, `52a7d4d` and the docs commit of this round) | F14 | User |
| **Explicit UX/UI sign-off of the grouped navigation (F1 redesign, `5f17b38`)** — colours, wording, chevrons, «Más» behaviour; nothing else should change until it is reviewed | F1 | Client/team |
| **Visual confirmation of the dropdown spacing (`510e981`)** — 12 px rows / 2 px gap / inset highlight, and the account-menu row. Measured, not seen: the harness window is not visible from here, so no screenshot could be taken. **The client's review found that the 12 px gap made the panel unreachable with the mouse; the mouse bridge in `3dbfbc6` fixes it and the confirmation is pending again** | F1 | Client/team |
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
  (`style.css`: two text colours in the evaluation drawer). All of them are local and unpushed.
  The visual result of `52a7d4d` is **not** claimed as approved: the client's review found two
  defects and the final sign-off is pending.
- Temporary files used to verify F1 (§V16) were deleted before committing: the simulation
  mu-plugin (`wp_set_current_user()`, re-created and deleted again for the `510e981` re-check)
  and the local evidence pages. The token pass (§V17) created **no** files: it measured
  computed styles in same-origin iframes and injected a throwaway lab fragment into an iframe
  body only. `git status` lists no new files, and the database was never written to (no login,
  no post/meta/option change; the private-gate check was anonymous `curl`).

## Documentation maintenance

Update the relevant document with every change: [ISSUES.md](ISSUES.md) for findings,
[ROADMAP.md](ROADMAP.md) + [FEATURES.md](FEATURES.md) for progress, this file for repo
state, [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) for tokens, [SECURITY_AUDIT.md](SECURITY_AUDIT.md)
for security-relevant changes. Keep docs in English; keep finding IDs stable.
