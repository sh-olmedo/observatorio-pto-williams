# Security & Privacy Audit

Static review of the repository at commit `5f43933` (+ working-tree deltas), extended with the
first **read-only runtime probes on 2026-10-15** (anonymous HTTP; no mutations — see
[VERIFICATION.md](VERIFICATION.md)). Items are source-confirmed unless marked
runtime-verified. Severity scale: **P0** critical, **P1** high, **P2** medium,
**P3** low/informational. No P0 was identified statically, and the runtime pass surfaced none
either.

Related: [ISSUES.md](ISSUES.md), [ARCHITECTURE.md](ARCHITECTURE.md) §4, §8.

## Summary

| ID | Area | Severity | Status |
| --- | --- | --- | --- |
| [F3](#f3--evaluation-submission-without-capability-check) | Write endpoint authorization | P1 | Open |
| [F11b](#f11b--rest-exposure-of-private-content-confirmed-at-runtime) | Private section vs REST | P1 | **Confirmed at runtime** (fix pending) |
| [F4](#f4--acf-schemas-not-versioned) | Configuration integrity | P2 | Open |
| [S-dep-1](#s-dep-1--third-party-cdn-without-sri) | Supply chain | P3 | Open |
| [S-priv-1](#s-priv-1--form-answers-in-localstorage) | Privacy (device-local) | P3 | Informational |
| [S-inf-1](#s-inf-1--local-artifacts-in-the-webroot) | Environment hygiene | P3 | Informational |

Positive findings (verified static): nonce verification on both POST handlers
(`otrn-autodiagnostico.php:386, 504`); draft tokens compared with `hash_equals()`
(`otrn_borrador_valido()`); archive handler requires `delete_evaluacion`
(`otrn-autodiagnostico.php:512`); no custom REST routes registered (grep); page-side gating
of the whole private section (`functions.php:250-265`).

## F3 — Evaluation submission without capability check

*Severity P1 · category: authorization · mirrors ISSUES F3*

- **Description**: `otrn_submit_eval` verifies a nonce but performs **no capability
  check**. Any logged-in account that can reach `/evaluar/` can create and publish an
  `evaluacion` post.
- **Evidence**:
  - Handler comment + nonce check: `wp-content/mu-plugins/otrn-autodiagnostico.php:381-390`.
  - Hook registration: `admin_post_otrn_submit_eval` at `otrn-autodiagnostico.php:490`.
    No `admin_post_nopriv_otrn_submit_eval` registration exists in the file, so anonymous
    POSTs are not routed; the inline note "aquí queda abierto (nopriv)" appears to describe
    intended hardening (verify at runtime).
  - Page gate only checks login, not role: `wp-content/themes/otrn/functions.php:250-265`.
  - The comment says production should require the "Operador" role (docs/01 §6); that role
    is not created anywhere in the repository (see FEATURES §15).
- **Impact**: content integrity (bogus published evaluations appear in the private
  archive/map), unauthorized resource creation; no evidence of data disclosure.
- **Runtime context (2026-10-15)**: the DB has 1 account and only the six default roles —
  today's blast radius is limited to whoever holds it; the gap remains for any future
  operator accounts ([VERIFICATION.md](VERIFICATION.md) §V1, §V13).
- **Proposed fix (not approved)**: require a dedicated capability (e.g.
  `edit_evaluaciones`) for submit; keep nonce; define the operator role; test with the
  lowest role available.
- **Verification**: create a subscriber-level user in a scratch environment, submit the
  form, confirm the handler rejects it after the fix; negative-test anonymous POST.

## F11b — REST exposure of private content (confirmed at runtime)

*Severity P1 · category: privacy/authorization · mirrors ISSUES F11/F11b*

- **Description**: the "private" evaluation section is enforced **only** during HTML
  rendering (`template_redirect`). All project CPTs/taxonomies register
  `show_in_rest => true`, so REST remains a *potential* read surface that the gate does not
  cover.
- **Evidence**:
  - Gate scope: `functions.php:250-265` (evaluation archive/single, criterion single,
    `/evaluar/`, `/mapa/`).
  - `show_in_rest => true`: `otrn-autodiagnostico.php` (`criterio_autodiag` :39,
    `evaluacion` :52, `sector` :119, taxonomy `ambito` :151), `otrn-contenido.php`
    (`indicador` :39, `buena_practica` :52, `dato_portada` :69, `material_apoyo` :80,
    taxonomies :101/:113/:126), `otrn-guias.php:19`, `otrn-iniciativa-pladetur.php:58, 99`.
  - No `register_rest_route` and no `rest_*` hooks found (grep).
- **Impact (confirmed 2026-10-15)**: published evaluation **titles/slugs/IDs/dates**,
  criterion text availability and sector data are readable without logging in; drafts are
  protected by post status. No answer content was observed in the payloads. The privacy
  expectation of the section ("privada") is broken for published items.
- **Verification (executed 2026-10-15, read-only)**:
  1. `curl -s https://<site>/wp-json/wp/v2/evaluacion` — anonymous → 200, 3 published items
     (titles readable); used with `/wp-json/wp/v2/criterio_autodiag` (74), `sector` (10).
  2. `/wp-json/wp/v2/evaluacion?status=draft` — anonymous → 400 (drafts not exposed).
  3. `wp/v2` index lists the routes. Counts/field names only; no titles recorded.
  Details and commands: [VERIFICATION.md](VERIFICATION.md) §V5, §V12.
- **Low-privilege repeat**: not executed (single account exists; see §F3 context).
- **Proposed fix (not approved)**: set `show_in_rest => false` (or restrict via
  `rest_*` filters) for the private types (`evaluacion`, `criterio_autodiag`, `sector`),
  after confirming no block-editor workflow depends on REST.

## F4 — ACF schemas not versioned

*Severity P2 · category: configuration integrity · mirrors ISSUES F4*

- The loader expects `docs/acf-json/` (`otrn-contenido.php:160-167`); the directory does
  not exist (the 2026-10-15 docs baseline contains no ACF exports). No
  `acf/settings/save_json` filter was found → no automatic export. Field groups therefore
  live only in the local SQLite database.
- **Impact**: schema drift/loss on migration; new environments cannot be reproduced from
  code; risk of agents inventing fields (explicitly forbidden).
- **Verification**: compare the ACF UI field groups against any future exports; do not
  fabricate schemas.

## S-dep-1 — Third-party CDN without SRI

*Severity P3 · category: supply chain/privacy*

- Leaflet CSS/JS are loaded from `unpkg.com` (1.9.4) with no Subresource Integrity
  (`functions.php:366-367`), only on the evaluations archive (and legacy `/mapa/`).
- **Impact**: external request (IP/privacy) + supply-chain dependency for a private page.
- **Proposed fix (not approved)**: self-host Leaflet under `assets/vendor/` (consistent
  with GSAP/SheetJS) or add SRI + pinning.

## S-priv-1 — Form answers in localStorage

*Severity P3 · category: privacy (device-local)*

- The evaluation form autosaves answers to `localStorage` (`otrn_eval_<key>`) and clears
  them on submit (`page-evaluar.php:185-256`).
- **Impact**: answers persist on shared devices until submit/removal; no server exposure.
- **Note**: access to the form already requires login; treat as informational.

## S-inf-1 — Local artifacts in the webroot

*Severity P3 · category: environment hygiene (informational)*

- `wp-config.php` (real secrets) and `wp-content/database/.ht.sqlite` exist locally and are
  gitignored; the database folder ships `.htaccess` = `DENY FROM ALL` and a silence
  `index.php` (`wp-content/database/`). Whether the local server enforces `.htaccess` was
  **not verified**.
- `99-studio-loader.php` sets `WP_ENVIRONMENT_TYPE=local` if undefined and loads Studio's
  machine-specific tmp mu-plugins (`99-studio-loader.php:18-22`; uncommitted path change).
- XML-RPC (`xmlrpc.php`) is present (core default) — normal for local dev; revisit before
  any public deployment.
- **Impact**: none while the site stays local; risk appears only if this tree is deployed
  or shared as-is.

## Explicitly out of scope / unverified

- No penetration test, malware scan, dependency CVE scan, or HTTP header review was done.
- No verification of user roles/users present in the DB.
- No verification that the geodata/StoryMaps iframe does not set third-party cookies.
- Akismet is installed but its integration with project forms was not reviewed (the only
  public write path is a logged-in form).

## Verification appendix (commands)

```bash
# Execution note: prefix WP-CLI with `studio` (see STUDIO.md).
# Executed 2026-10-15 (read-only) — see VERIFICATION.md §V12:
curl -s -o /dev/null -w '%{http_code} -> %{redirect_url}\n' http://localhost:8895/evaluar/
curl -s 'http://localhost:8895/wp-json/wp/v2/evaluacion' | \
  python3 -c 'import json,sys; print(len(json.load(sys.stdin)))'

# Pending (needs a scratch user / disposable data — do not run against the working DB):
studio wp user list --role=subscriber           # who can log in today
studio wp post list --post_type=evaluacion --post_status=publish   # published evals count
```
