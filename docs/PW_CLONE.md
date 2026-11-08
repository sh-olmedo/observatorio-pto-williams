# Puerto Williams Clone — Provenance & Status

**Date:** 2026-11-08. This repository (`observatorio-turistico-pto-williams`) is a
**provisional clone** of the Arauco Tourism Observatory project
(`github.com/sh-olmedo/observatorio-arauco`, HEAD `e09dd25` at clone time), which itself
inherits from the original *Observatorio Turístico de Rapa Nui*.

Purpose: local WordPress (Studio) site for the **Observatorio Turístico de Puerto
Williams** (Municipalidad de Cabo de Hornos y Antártica, GORE Región de Magallanes y
la Antártica Chilena). Everything below is provisional until the client provides the
Cabo de Hornos identity and local data.

## What was done in the clone

1. **Site creation** — the folder was registered as a WordPress Studio site
   (`studio create`): Studio scaffolded its own core files, SQLite integration and
   `99-studio-loader.php`; the copied `wp-content` (theme `otrn`, plugins
   `advanced-custom-fields` + `observatorio-engine`, `uploads/`) was preserved.
   Local URL: `http://localhost:8897` (port assigned by Studio — verify with
   `studio status`).
2. **Database cloned as-is** (`wp-content/database/.ht.sqlite`). Studio rewrote
   `siteurl` / `home` to the new URL; a dry-run `search-replace` of the old URL
   (`localhost:8895`→`8897`) reported 0 remaining occurrences.
3. **Site identity (DB, provisional):**
   - `blogname` → `Observatorio Turístico de Puerto Williams`
   - `blogdescription` → `Municipalidad de Cabo de Hornos y Antártica`
4. **Visible copy swapped in the theme (`wp-content/themes/otrn`, provisional wording):**
   - `front-page.php` — hero H1 (`Puerto Williams, territorio que inspira`), scene
     leads, CTA, datos intro + provisional notice, news heading, stage `aria-label`,
     photo `alt` texts marked `(imagen provisional)`.
   - `footer.php` — copyright line (`Observatorio Turístico de Puerto Williams ·
     Región de Magallanes y la Antártica Chilena`); logo `alt`s marked
     `Logotipo provisional: … (pendiente el de Cabo de Hornos y Antártica)`.
   - `header.php` — brand logo `alt` marked provisional.
   - `functions.php` — comment about `blogname` updated.
   - `archive-material_apoyo.php`, `archive-indicador.php`, `archive-iniciativa_pladetur.php`,
     `archive-guia.php`, `archive-buena_practica.php`, `page-noticias.php`,
     `page-estadisticas.php`, `guias-contenido.php` (intro + FAQ answer) — "de/en
     Arauco" → "de/en Puerto Williams"; Pladetur references → "Pladetur de Cabo de Hornos".
   - Runtime check: home page returns 200, `<title>` shows the new identity; the only
     remaining case-sensitive "Arauco" strings on the front page are the deliberate
     provisional logo `alt`s and inherited file paths.

## Content withdrawal (2026-11-08)

The inherited demo content of the previous project was retired from this local DB
(every item moved to **draft**, nothing deleted; backup first):

- `respaldo-antes-retiro-contenido-20261108.zip` (Studio export, 34.5 MB)
  taken before the change.
- Drafted: the 3 `dato_portada` cifras (385/387/389 — Rapa Nui figures), the 4 notices
  (`post` 1/158/159/160 incl. "Hello world!" and the Rapa Nui Pladetur notice), the
  local-monitoring indicators 145–148/411/414 (Rapa Nui and 1 test), and all 64
  `iniciativa_pladetur` items (Pladetur 2026–2034 de Rapa Nui programme).
- Kept published: the **national reference datasets** (INE/CEPAL/ODS/SERNATUR
  indicators 184–222+, buenas_practica library incl. a Cabo de Hornos entry) — platform
  reference data, not former-site identity content.
- Front-end effects (committed): landing sections Datos/Noticias auto-hide with the
  existing guards; scene 03 «Costa viva» disabled (inherited Arauco photo) with a PHP
  guard; front-page rail renders items conditionally; `otrn-landing.js` pin/rail
  choreography now adapts to 2 or 3 scenes.
- Landing now shows only: header + ribbon, hero (semanero/Dientes), territory (letras),
  the national-indicators EXPLORA section, and the blue footer.

## Deliberately left unchanged (inherited)

- **Asset directories and slugs** (`assets/img/arauco/`, `assets/logos/*arauco.webp`,
  `otrn*` prefixes, `style.css` theme slug): renaming would touch every reference;
  deferred until the client's identity assets exist.
- **Arauco municipal design tokens / palette** (`style.css :root`,
  `assets/css/otrn-arauco.css`): the implemented design follows the Municipalidad de
  Arauco identity. A Cabo de Hornos palette has **not** been provided; do not invent one.
- **Logos and photos**: the shown files are the inherited Arauco ones (marked
  provisional in `alt`s only). Originals for Cabo de Hornos / Puerto Williams are
  **pending from the client** (never fabricated).
- **Database content**: demo data is the Rapa Nui inheritance — 462 case-sensitive
  "Rapa Nui" strings and 14 "Arauco" strings, the latter **inside
  `observatorio-engine` `_obseng_*` JSON demo artifacts** (dataset profiles/dashboards).
  These are NOT renamed: blanket replacement would corrupt the demo datasets and would
  fabricate content. Real localisation requires client decisions (sectors/geography,
  indicator bases) — this project's phase, not the clone step.
- **`docs/` inherited audit** (ARAUCO_MIGRATION, ISSUES F1–F27, etc.): reference to the
  progenitor project. This file and the repo `README.md` / `AGENTS.md` notes are the
  authoritative provenance of this clone.
- **Git**: full history inherited from the Arauco repo; the `origin` remote was
  removed (local history only). Working tree still carries the pre-clone deletions in
  `references/logos/` and `references/web/` (untouched user state, restorable from
  history with `git checkout -- references/` if unintended).

## Pending decisions (client)

- Original identity assets: municipal/oficina logos, institutional photos of Puerto
  Williams / Isla Navarino, palette, typography direction.
- Territory model: sectors and coordinates for the comuna (inherited fallback data is
  Rapa Nui), geoportal URL, survey embed.
- Content localisation of the DB demo data (figures, indicators, Pladetur initiatives).

## Backup

A full pre-clone backup of the folder exists outside the repo:
`~/Studio/observatorio-turistico-pto-williams.bak-20261108` (231 MB).
