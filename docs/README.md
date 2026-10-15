# Documentation — Observatorio Turístico de Arauco

This directory is the documentation baseline for the Arauco Tourism Observatory WordPress
site. It was produced on **2026-10-15** from a read-only, **static** audit of the repository
at commit `5f43933` (branch `main`) plus the three pre-existing uncommitted files described in
[HANDOFF.md](HANDOFF.md), and updated after the **first runtime pass** (2026-10-15,
[VERIFICATION.md](VERIFICATION.md)). Statements are marked accordingly (verified static /
verified at runtime / unverified / provisional / pending).

The site's UI copy and database content are Spanish; **all documentation here is English**.
Proposed work is marked as proposed, never as implemented.

## Relationship with the original Rapa Nui project

This repository is a **working copy** of the original *Observatorio Turístico de Rapa Nui*
project, being adapted to the Municipality of Arauco / GORE Región del Bío-Bío. The original
project is a separate deliverable and must not be modified from this repository. The migration
status of every Rapa Nui element still present here is inventoried in
[ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md).

## Index

| Document | Answers | Main audience |
| --- | --- | --- |
| [ARCHITECTURE.md](ARCHITECTURE.md) | How the site is built: Studio, SQLite, theme, mu-plugins, CPTs, templates, assets, integrations, data flows | Developers |
| [FEATURES.md](FEATURES.md) | Which functional modules exist, their files, dependencies and verification status | Developers, PM |
| [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) | Actual tokens, typography, components, responsive behavior, scrollytelling; discrepancy with the proposed Arauco identity | Design, frontend |
| [ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md) | Rapa Nui → Arauco inventory: completed, pending, provisional, unverified | PM, client, developers |
| [SECURITY_AUDIT.md](SECURITY_AUDIT.md) | Security/privacy findings with evidence, severity, impact and verification procedures | Developers, client |
| [ISSUES.md](ISSUES.md) | The persistent register of findings F1–F18 (including F11b) with priorities | Everyone |
| [ROADMAP.md](ROADMAP.md) | Proposed implementation phases, dependencies, acceptance criteria (not approved) | PM, client, developers |
| [HANDOFF.md](HANDOFF.md) | Git baseline, uncommitted changes, blockers, pending decisions, recommended next task | Everyone |
| [VERIFICATION.md](VERIFICATION.md) | Phase 0 runtime pass: environment, counts, gates, REST, browser checks — PASS/FAIL/NOT VERIFIED per finding | Developers, PM |

## Recommended reading order

1. `README.md` (repo root) and `STUDIO.md` — repository scope and how to run WP-CLI via Studio.
2. [ARCHITECTURE.md](ARCHITECTURE.md) — structure and data flows.
3. [FEATURES.md](FEATURES.md) — what exists today and how far it is verified.
4. [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) — visual system and its discrepancies.
5. [ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md) + [ISSUES.md](ISSUES.md) — what still needs work.
6. [SECURITY_AUDIT.md](SECURITY_AUDIT.md) — security constraints before touching handlers.
7. [ROADMAP.md](ROADMAP.md) + [HANDOFF.md](HANDOFF.md) — what to do next.
8. [VERIFICATION.md](VERIFICATION.md) — what was actually tested at runtime and what remains.

For a quick start as an agent: read [HANDOFF.md](HANDOFF.md) first, then `ISSUES.md`
findings F1, F3, F11, F14, F17 (the current high-priority set) with their runtime status in
[VERIFICATION.md](VERIFICATION.md).

## Conventions used in these documents

- **Paths** are relative to the repository root (e.g. `wp-content/themes/otrn/functions.php`).
- **Evidence** cites `file:line` where the fact was statically verified during the audit.
- **Status vocabulary**:
  - *Verified static*: the code/config was read and the statement follows directly.
  - *Unverified*: requires runtime/browser/DB checks that were not performed.
  - *Provisional*: intentionally temporary, pending client data or decisions.
  - *Pending/decision pending*: blocked on a client or team decision.
- **Issue IDs (F1–F18, F11b)** are stable; keep them in branches, commits and PRs.
- **Do not invent** configuration, database contents, client decisions, or test results.
  Where something is unknown, the documents say so and describe how to verify it.

## Maintenance

When you change the site, update the documents that are affected:

- New/changed finding → update [ISSUES.md](ISSUES.md) (keep IDs; add new ones sequentially).
- Completed phase item → update [ROADMAP.md](ROADMAP.md) status and [FEATURES.md](FEATURES.md).
- Any commit that changes repo state → update [HANDOFF.md](HANDOFF.md).
- Token/component changes → update [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md).
- Security-relevant changes → update [SECURITY_AUDIT.md](SECURITY_AUDIT.md).
- Runtime verification results → update [VERIFICATION.md](VERIFICATION.md).

## Related documents (outside `docs/`)

- `README.md` (repo root) — repository scope table (what is and isn't versioned), Spanish.
- `STUDIO.md` — WordPress Studio operations; always prefix WP-CLI with `studio wp`.
- `references/web/README.md` — approved photo inventory (EXIF-free web copies).
- `wp-content/themes/otrn/assets/logos/README.md` — logo sourcing status and client pendings.
- `wp-content/themes/otrn/assets/fonts/README.md` — self-hosted font inventory.
