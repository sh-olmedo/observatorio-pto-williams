# Roadmap (Proposed)

**Nothing in this document is approved.** It proposes an implementation order for the
findings in [ISSUES.md](ISSUES.md), based on the static audit of commit `5f43933` + the
working tree. Phases assume client decisions where noted; each phase ends with verification
and documentation updates. Do not start a phase that depends on a pending decision.

## Phase 0 — Stabilize the baseline (process, no feature work)

| Item | Related | Exit criteria |
| --- | --- | --- |
| Decide the fate of the 3 uncommitted files | F14 | Intentional commit strategy agreed; machine-specific Studio path not committed as-is; changes still present until decided |
| Run the runtime verification checklist | F17 | Results recorded in HANDOFF/ISSUES; discrepancies filed as new IDs |
| Decide ACF local-JSON workflow | F4 | Decision recorded; if enabled, real exports committed (never fabricated) |

**Phase 0 status (2026-10-15):** the runtime checklist was executed and recorded in
[VERIFICATION.md](VERIFICATION.md) (F1, F5, F6, F11b and F16 confirmed; F17 partially
executed). Still open: the working-tree decision (F14) and the ACF workflow (F4 — runtime
shows **0 field groups**; fields exist only as raw post meta, so "export" is not currently
possible and the decision is define-or-document).

**Verification checklist (executed 2026-10-15 — results in
[VERIFICATION.md](VERIFICATION.md); remaining items listed there as NOT VERIFIED):** landing
immersive/static modes at desktop and ≤768 px; no-JS landing (F5); mobile nav ≤600 px on
internal pages (F1); `/mapa/` redirect; login gate for `/evaluar/`, `/autoevaluacion/`,
`/criterio/`; draft save/resume; eval submit end-to-end; Leaflet map; geportal embed;
REST endpoints (F11b); CLS/performance sanity.

## Phase 1 — Security & authorization

| Item | Related | Dependencies |
| --- | --- | --- |
| Capability check on eval submission | F3 | Operator/role model decision (the docs/01 §6 role does not exist in code) |
| Verify/limit REST exposure of private types | F11, F11b | Runtime access (Phase 0 environment) |

**Acceptance criteria (proposed):** a subscriber-level user cannot create/publish an
`evaluation`; anonymous and low-privilege REST probes return no private data (or exposure is
explicitly accepted and documented); draft flow and archive handler still work.

## Phase 2 — Mobile navigation & no-JS resilience

| Item | Related |
| --- | --- |
| Move burger/panel styles to a global scope | F1 |
| Make `.otrn-reveal` progressive (visible without JS) | F5 |

**Acceptance criteria (proposed):** at 360–768 px the nav is reachable on every template
(front page and internal pages), keyboard/AT behavior preserved (aria, focus trap);
with JavaScript disabled the landing shows all cards; reduced-motion behavior unchanged;
no desktop regressions.

## Phase 3 — Design identity (client decision first)

| Item | Related | Dependencies |
| --- | --- | --- |
| Formal palette approval and token implementation | F2 | Client approval of colors (proposed set or current teal/terracotta) |
| Consolidate tokens into one source of truth | F6 | Same F2 decision |

**Acceptance criteria (proposed):** one documented token layer; implemented values equal
approved values; the landing fallback block no longer contradicts the system; visual
spot-check of landing + internal pages.

**Status 2026-10-16:** the client approved the Municipalidad de Arauco identity and the
mapping, and both items were **executed ahead of this plan** in `52a7d4d` (single token layer
in `style.css :root`; the landing fallback block deleted). The acceptance criteria above are
met at the **measured** level (contrast + responsive passes, [VERIFICATION.md](VERIFICATION.md)
§V17); the **visual spot-check by the client is still pending**, so this phase is implemented
but **not closed**. Two client review rounds have since produced three fixes (`3dbfbc6`,
`9142179`, `e2c3a9f` — the latter closing **F20/F21** on the home page, §V18–§V19); neither
round was a sign-off, and one new client decision is open (how much photograph to keep under
the mobile scene text).

## Phase 4 — Content & data migration

| Item | Related | Dependencies |
| --- | --- | --- |
| Replace Rapa Nui copy on public templates | F7 | Client-approved Arauco copy |
| Seed Arauco sectors + coordinates; replace fallback | F8 | Geography decision; DB backup/export policy (F18) |
| Decide/replace Geoportal StoryMap | F9 | Client decision |
| Decide the survey embed's fate | F10 | Client decision |
| Replace hero fallback; archive/delete orphan assets | F12 | Approved photos |
| Obtain HD/transparent logos | F13 | Client-provided assets |

**Acceptance criteria (proposed):** no unintended Rapa Nui text remains (grep + browser
pass); the form/map show Arauco sectors; every decision above is recorded in
[ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md).

## Phase 5 — Architecture & reproducibility

| Item | Related | Output |
| --- | --- | --- |
| Decide fallback-route architecture (PHP templates vs block) | F16 | Decision + implementation (proposed) |
| Bootstrap/seed procedure for new environments | F18 | Documented, tested procedure (pages, menus, options) |

**Acceptance criteria (proposed):** a fresh Studio environment can reproduce the key routes
from the repo + documented procedure; search/404/generic pages render as decided.

## Phase 6 — Arauco location search (F19 — new requirement, registered 2026-10-16)

| Item | Related | Dependencies |
| --- | --- | --- |
| Location picker in the self-assessment form: OSM-geocoding search → suggestions → map centering + visible marker → manual adjustment → explicit confirmation; draft round-trip; storage consistent with the evaluation model | F19 | Geocoding-provider decision + usage policy; F8 sector-list fate; evaluation meta model; DB backup/export policy (F18); private-section gate for any server-side proxy (F3/F11) |

**Acceptance criteria (proposed):** the criteria in
[ARAUCO_MIGRATION.md](ARAUCO_MIGRATION.md#8-new-arauco-requirement--self-assessment-location-search-f19)
§8.3 — ≥5 readable suggestions when the provider has them; map centering + visible
draggable marker; coordinates shown and explicit confirmation before continuing; draft
save/resume keeps the confirmed location; storage compatible with the existing readers
(archive map, `destino` filter); loading/no-results/error/keyboard states; mobile
behavior; OSM attribution + provider policy respected.

**Sequencing note (proposed):** F19 is not part of the original migration scope and does
**not** reorder Phases 1–2 (security and the confirmed UX defects come first). It interacts
with F8 in Phase 4 — decide together whether the curated Arauco sector list survives as a
quick-pick/fallback or is retired in favor of search-only.

## Explicitly not proposed here

- No redesign beyond the approved identity decision, and no new features beyond the ones
  explicitly registered as Arauco requirements (currently F19, Phase 6 — documentation only).
- No deployment, no touching the original Rapa Nui project, no database edits without a
  backup/export decision.

## Notes

- Execution policy: **Phase 0 (including the runtime verification of F17) precedes
  implementation decisions** — unverified behavior can change priorities. The default order
  for Phase 1 onward is **F3 → F1 → F2/F6 → content/data**, with per-change verification.
- After the runtime pass (2026-10-15): F1, F5, F6, F11b and F16 are runtime-confirmed; the
  recommended Phase 1 scope is the security pair **F3 + F11b** (F11b is a confirmed
  anonymous read exposure), followed by Phase 2 (F1 + F5).
- **2026-10-16:** F19 (self-assessment location search — OSM geocoding) registered as a new
  Arauco requirement: **documentation only, not approved**; proposed as Phase 6, pending
  the geocoding-provider decision and coordinated with F8 (Phase 4).
- Every completed item must update [ISSUES.md](ISSUES.md), [FEATURES.md](FEATURES.md) and
  [HANDOFF.md](HANDOFF.md).
