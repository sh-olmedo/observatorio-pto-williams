# AI Instructions

This is a local WordPress site managed by [WordPress Studio](https://developer.wordpress.com/studio/).
For WordPress Studio instructions, see @STUDIO.md
If this site has a `DESIGN.md`, follow it for any design or content work.

> **Customising this file:** Feel free to edit, extend, or replace the contents below.

## Project

**Observatorio Turístico de Arauco** — WordPress Studio site. All custom code lives in the
classic child theme `wp-content/themes/otrn/` and in `wp-content/mu-plugins/otrn-*.php`
(plus the Studio loader and SQLite integration). This repository is a working copy of the
original **Observatorio Turístico de Rapa Nui** project, partially rebranded and re-scoped
for the Arauco municipality (remote: `sh-olmedo/observatorio-arauco`). The original Rapa Nui
project is out of scope: never modify it from here.

- Site UI copy, code comments and database content are **Spanish**; docs under `docs/` are **English**.
- WordPress core, `wp-config.php`, the SQLite database and non-project themes/plugins are **not versioned**.
- Static audit baseline: commit `5f43933`, documented 2026-10-15. Runtime behavior is **not yet verified**.

## Read before changing anything

1. `docs/README.md` — documentation index and reading order.
2. `docs/ARCHITECTURE.md` — theme, mu-plugins, CPTs, templates, data flows.
3. `docs/ISSUES.md` — open findings F1–F18; preserve the IDs in commits, branches and PRs.
4. `docs/HANDOFF.md` — current repo state, uncommitted changes, recommended next task.
5. `docs/ROADMAP.md` — proposed phases; **nothing there is approved**.

## Ground rules

- Never commit `wp-config.php`, `wp-content/database/`, or machine-specific paths
  (e.g. the Studio temp dir inside `wp-content/mu-plugins/99-studio-loader.php`).
- Run WP-CLI through Studio: `studio wp …` (never a bare `wp`; see @STUDIO.md).
- Do not claim visual/runtime behavior as verified unless tested in a real browser.
  The documentation baseline is static-only; runtime verification is pending (ROADMAP Phase 0).
- The private section must stay private: `/evaluar/`, `/mapa/`, `/autoevaluacion/`, `/criterio/`
  are gated in `functions.php`; do not weaken that gating (see `docs/SECURITY_AUDIT.md`).
- Proposed fixes are **not approved**. Confirm client decisions before implementing: palette,
  sectors/geography, Geoportal URL, survey embed, logo originals (`docs/ARAUCO_MIGRATION.md`).
- Do not fabricate ACF schemas, database contents, or test results. ACF field groups live in
  the database; JSON exports are missing (issue F4).
- Three pre-existing uncommitted files (`99-studio-loader.php`, `otrn-landing.css`, `style.css`)
  are intentional working-tree state: don't revert them, don't mix them into unrelated changes
  (`docs/HANDOFF.md`).

## Where things live

| Area | Location |
| --- | --- |
| Theme (templates, CSS, JS, fonts, logos, images) | `wp-content/themes/otrn/` |
| Content model & handlers (CPTs, autodiagnóstico) | `wp-content/mu-plugins/otrn-*.php` |
| Documentation (English) | `docs/` |
| Photos/logos source material & inventories | `references/web/README.md`, `wp-content/themes/otrn/assets/*/README.md` |
| Studio/CLI instructions | `STUDIO.md` |
