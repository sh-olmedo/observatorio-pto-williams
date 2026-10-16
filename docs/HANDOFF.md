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

## Git baseline

| Item | Value |
| --- | --- |
| Branch | `main` (remote `origin` configured; no remote refs fetched locally) |
| HEAD | `5f17b38` — `fix(otrn): navegación agrupada, icono de cuenta y puntos de corte medidos (F1)` (local, not pushed) |
| History | `99092d1` repo init → `70b22a7` "portada scrollytelling inmersiva + branding Arauco (ETAPA A+B+C)" → `5f43933` "modo dual estatico/inmersivo" → `f506104` docs baseline → `bb38d8b` runtime record → `8d3d40b` F11b protections → `d4fa738` F19 docs → `0dd2de4` F1 nav → `e727756` F1 regression fix → `932986b` F1 docs → `5f17b38` F1 grouped navigation (last seven local, not pushed) |
| Remote | `https://github.com/sh-olmedo/observatorio-arauco.git` |
| Tracked files | 803 (`bb38d8b` added `docs/VERIFICATION.md`; `8d3d40b` added `wp-content/mu-plugins/otrn-privacidad-seccion.php`) |

## Working tree — 3 pre-existing modified files (do not revert)

`git diff --stat`: **24 insertions, 8 deletions** across:

| File | Change (uncommitted) | Interpretation |
| --- | --- | --- |
| `wp-content/mu-plugins/99-studio-loader.php` | Studio temp dir name token changed (machine-specific; temp path only) | Machine-specific; should probably not be committed as project change |
| `wp-content/themes/otrn/assets/css/otrn-landing.css` | Adds unscoped `:root` `--aru-*` fallback (13 lines) + `var(--font-title,…)` for titles | Real fix but creates the F6 token conflict; values differ from the scoped tokens |
| `wp-content/themes/otrn/style.css` | `--dark` `#333333`→`#2B2B2B`; Manrope → `var(--font-title)` (Montserrat) at 4 places; adds `--font-title` | Fixes a real font bug (Manrope was never shipped) and aligns the dark token |

These changes are part of the current documentation baseline (the audit read them as-is).
Decide their commit strategy in Phase 0 ([ROADMAP.md](ROADMAP.md), F14) — either way, they
must be reviewed, not reverted. The F1 fixes (`0dd2de4` + `e727756` + `5f17b38`) intentionally
do not touch them: they add the globally enqueued `assets/css/otrn-menu.css` (which wins by
specificity, including over `.otrn-nav a.active` in `style.css`) instead of editing
`style.css` or `otrn-landing.css`. `git status` therefore still shows exactly these 3 files.

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
| Commit strategy for the 3 uncommitted files | F14 | Team (Phase 0) |
| ACF workflow — runtime shows **0 field groups** and raw-meta fields; decide define-or-document (exporting is currently impossible) | F4 | Team (Phase 0) |
| Operator/role model for evaluation submission | F3 | Client/team (Phase 1) |
| Palette: approve proposed municipal colors or the current teal/terracotta | F2, F6 | Client (Phase 3) |
| Arauco geography: sector list + coordinates | F8 | Client (Phase 4) |
| Geoportal StoryMap URL (scope unknown) | F9 | Client (Phase 4) |
| Survey embed fate | F10 | Client (Phase 4) |
| HD/transparent logos (GORE, RAU) | F13 | Client (Phase 4) |
| DB content inventoried at count level (2026-10-15): pages, 74 criteria, 10 sectors, 6 evaluations, 3 data cards; copy migration still pending | F18 | Team (Phase 4) |
| Geocoding provider + usage policy for the location search (rate limits, attribution, caching; browser-direct vs server proxy) | F19 | Client/team (before Phase 6) |
| Fate of the curated sector list and of the location metas under the new search picker | F8 ↔ F19 | Client/team (Phase 4/6) |
| Push/deploy decision for the local commits (`8d3d40b`, `d4fa738`, `0dd2de4`, `e727756`, `932986b`, `5f17b38` + the F1 docs commit) | F14 | User |
| **Explicit UX/UI sign-off of the grouped navigation (F1 redesign, `5f17b38`)** — colours, wording, chevrons, «Más» behaviour; nothing else should change until it is reviewed | F1 | Client/team |
| Real authenticated-session pass for the F1 nav (login writes session tokens to the DB; the 2026-10-16 pass used a temporary `wp_set_current_user()` mu-plugin, since deleted) | F1, F17 | Team (needs a disposable environment) |
| Real-device pass: touch, iOS dynamic viewport, `prefers-reduced-motion` and the rendered focus ring (the harness could not deliver real key events while its window was hidden) | F1, F17 | Team |

## Recommended next task (proposed, not approved)

Phase 0 progress: the runtime checklist was executed on 2026-10-15
([VERIFICATION.md](VERIFICATION.md)) and it **confirmed** F1, F5, F6, F11b and F16 — so the
remaining Phase 0 items are decisions: (1) the working-tree strategy (F14) and (2) the ACF
workflow (F4). The recommended first implementation scope (proposed, not approved) is the
security pair **F3 + F11b** (Phase 1 — F11b is now a confirmed anonymous-read exposure),
followed by **F5** (Phase 2; F1 is implemented and browser-verified — `0dd2de4` + `e727756`).
Do not start palette/token work (F2/F6) or content migration (F7–F10) before the client
decisions above.

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
  and `5f17b38` (grouped navigation in `header.php` + `functions.php` + the two assets). The
  F19 change set is committed as `d4fa738` (documentation only). All of them are local and
  unpushed.
- Temporary files used to verify F1 (§V16) were deleted before committing: the simulation
  mu-plugin (`wp_set_current_user()`) and the local evidence pages. `git status` lists no new
  files, and the database was never written to (no login, no post/meta/option change).

## Documentation maintenance

Update the relevant document with every change: [ISSUES.md](ISSUES.md) for findings,
[ROADMAP.md](ROADMAP.md) + [FEATURES.md](FEATURES.md) for progress, this file for repo
state, [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) for tokens, [SECURITY_AUDIT.md](SECURITY_AUDIT.md)
for security-relevant changes. Keep docs in English; keep finding IDs stable.
