# Handoff — Repository State & Next Steps

State recorded **2026-10-15**, after a read-only static audit. This `docs/` baseline is
delivered as **one local commit** on `main` (`docs(arauco): establish audited project
baseline`, not pushed); the audit modified no application code, configuration or database.

## Git baseline

| Item | Value |
| --- | --- |
| Branch | `main` (tracking `origin/main`) |
| HEAD (audited revision) | `5f43933` — "feat(otrn): modo dual estatico/inmersivo en la portada scrollytelling" |
| History | `99092d1` repo init → `70b22a7` "portada scrollytelling inmersiva + branding Arauco (ETAPA A+B+C)" → `5f43933` |
| Documentation baseline | One local commit on top of `5f43933`: `docs(arauco): establish audited project baseline` (not pushed) |
| Remote | `https://github.com/sh-olmedo/observatorio-arauco.git` |
| Tracked files | 792 |

## Working tree — 3 pre-existing modified files (do not revert)

`git diff --stat`: **24 insertions, 8 deletions** across:

| File | Change (uncommitted) | Interpretation |
| --- | --- | --- |
| `wp-content/mu-plugins/99-studio-loader.php` | Studio temp dir name token changed (machine-specific; temp path only) | Machine-specific; should probably not be committed as project change |
| `wp-content/themes/otrn/assets/css/otrn-landing.css` | Adds unscoped `:root` `--aru-*` fallback (13 lines) + `var(--font-title,…)` for titles | Real fix but creates the F6 token conflict; values differ from the scoped tokens |
| `wp-content/themes/otrn/style.css` | `--dark` `#333333`→`#2B2B2B`; Manrope → `var(--font-title)` (Montserrat) at 4 places; adds `--font-title` | Fixes a real font bug (Manrope was never shipped) and aligns the dark token |

These changes are part of the current documentation baseline (the audit read them as-is).
Decide their commit strategy in Phase 0 ([ROADMAP.md](ROADMAP.md), F14) — either way, they
must be reviewed, not reverted.

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
| Runtime verification not performed (browser, REST, DB, WP-CLI) | F17 | Team (Phase 0) |
| Commit strategy for the 3 uncommitted files | F14 | Team (Phase 0) |
| ACF local-JSON workflow (export schemas or keep DB-only) | F4 | Team (Phase 0) |
| Operator/role model for evaluation submission | F3 | Client/team (Phase 1) |
| Palette: approve proposed municipal colors or the current teal/terracotta | F2, F6 | Client (Phase 3) |
| Arauco geography: sector list + coordinates | F8 | Client (Phase 4) |
| Geoportal StoryMap URL (scope unknown) | F9 | Client (Phase 4) |
| Survey embed fate | F10 | Client (Phase 4) |
| HD/transparent logos (GORE, RAU) | F13 | Client (Phase 4) |
| DB content unknown (criteria, evaluations, `dato_portada`, pages/menus) | F18 | Team (Phase 0/5) |

## Recommended next task (proposed, not approved)

Do **Phase 0** first: (1) decide the working-tree strategy (F14), (2) run the runtime
verification checklist (F17), (3) decide the ACF export workflow (F4). Phase 0 precedes
implementation decisions because unverified behavior can change priorities. Once Phase 0
is recorded, the recommended first implementation fix is **F3** (capability check on
evaluation submission — highest-value hardening), followed by **F1**.
Do not start palette/token work (F2/F6) or content migration (F7–F10) before the client
decisions above.

## How to run the site (reference only — not executed in this task)

- Local site is managed by WordPress Studio; see `STUDIO.md`.
- Always prefix WP-CLI with `studio`: e.g. `studio wp post list --post_type=evaluacion`.
- Secrets live in the unversioned `wp-config.php`; the DB is
  `wp-content/database/.ht.sqlite` (gitignored). Do not commit either.

## Guarantees for the original project

- The original **Observatorio Turístico de Rapa Nui** project was not touched; this repo is
  a copy and the migration is tracked in [ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md).
- This documentation task changed only: `AGENTS.md`, the new files under `docs/`, and a
  one-line `.gitignore` addition (`!docs/`) so the documentation is versionable — nothing
  else (verify with `git status`).

## Documentation maintenance

Update the relevant document with every change: [ISSUES.md](ISSUES.md) for findings,
[ROADMAP.md](ROADMAP.md) + [FEATURES.md](FEATURES.md) for progress, this file for repo
state, [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) for tokens, [SECURITY_AUDIT.md](SECURITY_AUDIT.md)
for security-relevant changes. Keep docs in English; keep finding IDs stable.
