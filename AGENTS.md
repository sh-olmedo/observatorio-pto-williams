# AI Instructions

This is a local WordPress site managed by [WordPress Studio](https://developer.wordpress.com/studio/).
For WordPress Studio instructions, see @STUDIO.md
If this site has a `DESIGN.md`, follow it for any design or content work.

> **Customising this file:** Feel free to edit, extend, or replace the contents below.

> **Clone provenance (2026-11-08):** this repository is a provisional clone of
> `sh-olmedo/observatorio-arauco` (theme `otrn`, Rapa Nui heritage), rebranded
> provisionally toward the **Observatorio Turístico de Puerto Williams**
> (Municipalidad de Cabo de Hornos y Antártica). Visible copy was swapped to
> Puerto Williams; identity graphics (logos), photos, palette and database demo
> content remain inherited and pending client decisions — see
> `docs/PW_CLONE.md`. The inherited Arauco audit docs (`docs/`) describe the
> progenitor project: treat them as reference, not as this site's status. The
> remote to the Arauco repository was detached (local history only).

## Project

**Observatorio Turístico de Arauco** — WordPress Studio site. All custom code lives in the
classic child theme `wp-content/themes/otrn/` and in `wp-content/mu-plugins/otrn-*.php`
(plus the Studio loader and SQLite integration). This repository is a working copy of the
original **Observatorio Turístico de Rapa Nui** project, partially rebranded and re-scoped
for the Arauco municipality (remote: `sh-olmedo/observatorio-arauco`). The original Rapa Nui
project is out of scope: never modify it from here.

- Site UI copy, code comments and database content are **Spanish**; docs under `docs/` are **English**.
- WordPress core, `wp-config.php`, the SQLite database and non-project themes/plugins are **not versioned**.
- Baseline: static audit at `5f43933` + docs commit `f506104`; first runtime pass 2026-10-15
  (`docs/VERIFICATION.md`). Device/keyboard/CLS behavior still requires manual verification.

## Read before changing anything

1. `docs/README.md` — documentation index and reading order.
2. `docs/ARCHITECTURE.md` — theme, mu-plugins, CPTs, templates, data flows.
3. `docs/ISSUES.md` — open findings F1–F18; preserve the IDs in commits, branches and PRs.
4. `docs/HANDOFF.md` — current repo state, uncommitted changes, recommended next task.
5. `docs/VERIFICATION.md` — Phase 0 runtime results (what is verified, what is not).
6. `docs/ROADMAP.md` — proposed phases; **nothing there is approved**.

## Ground rules

- Never commit `wp-config.php`, `wp-content/database/`, or machine-specific paths
  (e.g. the Studio temp dir inside `wp-content/mu-plugins/99-studio-loader.php`).
- Run WP-CLI through Studio: `studio wp …` (never a bare `wp`; see @STUDIO.md).
- Do not claim visual/runtime behavior as verified unless tested in a real browser.
  The 2026-10-15 runtime record is in `docs/VERIFICATION.md`; keyboard/device/CLS checks
  are still pending.
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
