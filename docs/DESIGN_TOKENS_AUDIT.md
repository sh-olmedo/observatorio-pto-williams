# Design-Token & Global-Style Audit — Observatorio Turístico de Arauco

**Status (2026-10-16):** the UX/UI direction is **confirmed** — the site must follow the
**Municipalidad de Arauco** visual identity, and the coral/teal system is **no longer the
authoritative design system** (F2 closed as a decision). §§1–11 are the *technical baseline* of
the previous state; §12 records the verified municipal foundation and **§13 is the as-built
record of the approved mapping, applied in code on 2026-10-16** (runtime evidence:
[VERIFICATION.md](VERIFICATION.md) §V17). The resulting visuals are **pending client sign-off**.

**Scope:** investigation and documentation only. No CSS, PHP, database, content or URL
change; no commit of application code. The three pre-existing uncommitted files
(`99-studio-loader.php`, `otrn-landing.css`, `style.css`) were read **as the current
interface** (working-tree values), never reverted.

**Evidence status:** token/cascade data comes from the real files; "rendered" values were
measured as computed styles in same-origin iframes at 1440 px (the Studio window stays
hidden, so **no screenshots** were possible). The private pages (`/evaluar/`,
`/autoevaluacion/`, `/mapa/`, `/criterio/`) were read from CSS only, not rendered. Focus-ring
rendering, keyboard modality, real devices and contrast over photographs are **not verified**.

---

## 1. Cascade and stylesheet loading order

Measured on `/` (identical on internal pages minus items 7–8):

| # | Stylesheet | Rules | Layer |
| --- | --- | --- | --- |
| 1–2 | inline `wp-img-auto-sizes`, `wp-emoji-styles` | 1 + 1 | WP core |
| 3 | inline `wp-block-library` | 46 | WP core blocks |
| 4 | inline `global-styles-inline-css` | 130 | **Parent theme `theme.json` presets + global styles** |
| 5 | `assets/css/fonts.css?ver=mtime` | 9 | Theme (fonts) |
| 6 | `style.css?ver=mtime` | 349 | **Theme global tokens + components** |
| 7 | `assets/css/otrn-arauco.css` | 10 | Front page only (`.otrn.home` scope) |
| 8 | `assets/css/otrn-landing.css` | 90 | Front page only |
| 9 | `assets/css/otrn-menu.css` | 45 | Theme (nav) |
| 10 | inline `twentytwentyfive-style-inline-css` | 8 | Parent theme (loaded **after** 9) |
| 11 | inline (2 rules) | 2 | — |

- `functions.php:344-469` enqueues them with `filemtime()` cache-busting; `otrn-menu.css`/`.js`
  are enqueued last on purpose (their comments assume they "win").
- `.otrn` is the **body** class (`header.php:15`), so `.otrn.home` = `body`.
- **The documentation says "three token layers" (DESIGN_SYSTEM §1); reality is five**: (1) WP
  core, (2) parent `theme.json` presets, (3) `style.css :root`, (4) `.otrn.home` `--aru-*`,
  (5) the unscoped `otrn-landing.css :root` fallback — plus per-element runtime variables
  (`--scene` from `front-page.php:93/105/116`, `--expand` from JS).
- The parent preset layer is invisible to the design system but real:
  `--wp--preset--color--contrast:#111111`, `--wp--preset--font-family--manrope:"Manrope",sans-serif`,
  `--wp--preset--font-family--fira-code`, `--wp--preset--font-size--huge:42px`, etc. It is also
  where the inherited **`body` font-size 22 px** comes from — the child never sets a base size,
  so any text without an explicit `font-size` renders at the parent's fluid "large" (22 px).

---

## 2. Token inventory (defined / documented / used)

### 2.1 Global tokens — `style.css:17-24` (working tree)

| Token | Value | Documented | Usage |
| --- | --- | --- | --- |
| `--primary` | `#DF8056` | ✔ | 28× `style.css`, 1× `otrn-menu.css`, 5× PHP inline |
| `--primary-dark` | `#A05C3D` | ✔ | 21× `style.css`, 7× `otrn-menu.css` |
| `--primary-soft` | `#FAEEE9` | ✔ | 9× |
| `--primary-tint` | `#F0C5B2` | ✔ | tier-mid surfaces |
| `--secondary` | `#2F6F79` | ✔ | 9× |
| `--secondary-soft` | `#E3ECED` | ✔ | 9× |
| `--blue-dark` | `#0E506D` | ✔ | header/footer/charts |
| `--blue-dark-soft` | `#DFE8EC` | ✔ | **0 — never used** |
| `--dark` | `#2B2B2B` | ✔ (uncommitted fix from `#333333`) | base text |
| `--text2` | `#666666` | ✔ | 31× |
| `--text3` | `#888888` | ✔ | 31× + 10 PHP inline |
| `--grey` | `#E8EEE6` | ✔ | borders |
| `--cream` | `#F6F3E0` | ✔ | internal-page footer |
| `--lgrey` | `#F0F0F0` | ✔ | table heads / stat boxes |
| `--error` | `#EA5252` | ✔ | tier-low |
| `--paper` | `#FFFFFF` | ✔ | not always used (see §6) |
| `--radius` / `--radius-sm` | `14px` / `10px` | ✔ | 13× / 7× |
| `--font-title` | `'Montserrat',system-ui,sans-serif` | ✔ (new, uncommitted) | 4× `style.css`, 1× landing |

There is **no** spacing scale, **no** shadow token, **no** mono-font token, **no**
`--error-soft`/`--success`/`--warning`, **no** z-index scale, **no** focus token. Radii are
partially tokenised (14/10 px) while `12px` (10×) and `8px` (8×) are hardcoded.

### 2.2 Front-page alias tokens — `otrn-arauco.css:19-40`, scoped to `.otrn.home`

18 tokens, all **value-duplicates of the global set** (`--aru-teal = --secondary`,
`--aru-terra = --primary`, `--aru-terra-deep = --primary-dark`, `--aru-arena = --cream`,
`--aru-texto = --dark`, `--aru-radio = --radius`, `--aru-radio-sm = --radius-sm`) except the
neutrals `--aru-texto-2:#5A5A5A`, `--aru-texto-3:#8A8A8A`, `--aru-footer:#F7F7F7`,
`--aru-ink-deep:#0A2E3C` and `--aru-gap:clamp(48px,8vw,120px)`.

Never referenced anywhere: `--aru-arena`, `--aru-ink-deep`, `--aru-teal-soft`,
`--aru-terra-tint`, `--aru-radio-sm`.

### 2.3 Uncommitted landing fallback — `otrn-landing.css:26-30` (unscoped `:root`)

Redefines the same `--aru-*` names with **different values** while its comment claims they are
identical:

| Token | `.otrn.home` (§2.2) | `:root` fallback (§2.3) |
| --- | --- | --- |
| `--aru-terra` | `#DF8056` | `#C0392B` |
| `--aru-terra-deep` | `#A05C3D` | `#8E2A20` |
| `--aru-terra-soft` | `#FAEEE9` | `#F5E3E0` |
| `--aru-teal` | `#2F6F79` | `#1A6B77` |
| `--aru-teal-deep` | `#145E6B` | `#0C4E59` |
| `--aru-teal-soft` | `#E3ECED` | `#DCEAEC` |
| `--aru-ink` | `#0E506D` | `#0A2E3C` |
| `--aru-texto-2` | `#5A5A5A` | `#5A6B70` |
| `--aru-radio` / `--aru-radio-sm` | `14px` / `10px` | `10px` / `6px` |
| extras | — | `--aru-papel-2:#FAF8F3`, `--aru-linea:#E4DCD5` |

Verified live at 1440 px: on `/`, `html` resolves `--aru-terra:#C0392B` **while**
`body.otrn.home` resolves `#DF8056` — two values alive at once (F6, re-confirmed). Today the
visual impact is **latent**: every element is inside `body.otrn.home`, so the body declaration
always wins. It is a live trap the day `.otrn` moves off `<body>` (e.g. to a wrapper), when the
whole landing would flip to the brick-red palette.

### 2.4 Proposed municipal palette — not implemented in the theme

`#0070FF`, `#41BCFF`, `#DE232B`, `#0C1434`, `#0C0D0E`, `#D6D5D5` — zero occurrences in the
theme (grep). Only `#F7F7F7` exists, as `--aru-footer`. The municipal roles of these colours are
now verified against the live municipal site: see **§12**.

---

## 3. Documented vs implemented vs rendered

| Topic | DESIGN_SYSTEM says | Implemented | Rendered |
| --- | --- | --- | --- |
| Token layers | 3 | 5 (§1) | 5 layers confirmed in `document.styleSheets` |
| `--primary` contrast | 2.6:1 (comment) | — | **2.85:1** vs white, 2.51:1 vs `--primary-soft`, 2.50:1 vs `--lgrey` (computed) |
| `.otrn-login` | listed as header chrome (§4) | CSS exists (`style.css:43-44`, `otrn-arauco.css:57`) | **not in the DOM** (0 PHP hits) → dead |
| `.otrn-home-menu` | listed as a landing component (§4) | CSS exists (`otrn-landing.css:214`) | not in the DOM → dead |
| Footer | "logo strip (`--cream`)" | `style.css:55-62` + `otrn-arauco.css:66-76` | **homepage**: `#F7F7F7`, 48 px logos in white "chapas" (r12), copy `#5A5A5A`; **internal**: `--cream`, 64 px logos, **no chapa**, copy `#666666` |
| Footer logo heights | 4 heights (64/50/52/56) | `img:nth-child(1…4)` | **All three images resolve to `:nth-child(1)` = 64 px** (each `<img>` is the only child of its `<span>`) → rules 2–4 and the ≤600 px variants are dead |
| Breakpoints | 1440, 1280, 980, 768, 600, 480 | also **992, 782, 720, 640**, plus nav thresholds **1152/1151/910/909/680/679** | — |
| Typography | Manrope replaced by `var(--font-title)` | `--font-title` used only 4× (style) + 1× (landing); **26 literal `'IBM Plex Mono',monospace`** in `style.css` + 6 in landing, with no token | body inherits **22 px** from the parent preset layer |
| `--dark` fix | working-tree `#333333 → #2B2B2B` | 33 occurrences | but **`rgba(51,51,51,…)` (old `#333`) survives in 5 places** (`style.css:248, 252…` veils/shadows) |
| `--aru-*` fallback claim | values "identical" | different values (§2.3) | both live |
| Form controls | "form controls on `/evaluar/`" | `.otrn-field select/input` (`style.css:113`) sets background/font but **no `color`** | filter `<select>` renders UA default **`rgb(0,0,0)`** — black, not `--dark` |
| WP preset layer | not mentioned | present (130 inline rules) | 29 presets incl. `#111111` contrast, Manrope, Fira Code, base font 22 px |

---

## 4. Conflicting, unused and missing tokens

**Same name, two different values (real conflicts, all in `--aru-*`):** `--aru-teal`,
`--aru-teal-deep`, `--aru-teal-soft`, `--aru-ink`, `--aru-terra`, `--aru-terra-deep`,
`--aru-terra-soft`, `--aru-texto-2`, `--aru-radio`, `--aru-radio-sm` (10 conflicts).
`--primary`, `--secondary`, `--blue-dark` are defined **once** — the "conflict" a first pass
flagged there was a false positive from comment text.

**Defined but never used (8):** `--blue-dark-soft`, `--aru-arena`, `--aru-ink-deep`,
`--aru-linea`, `--aru-papel-2`, `--aru-radio-sm`, `--aru-teal-soft`, `--aru-terra-tint`.

**Roles with no token at all (used as literals):** error surface (`#FBE4E4`, 6×), hover rows
(`#fafbfa` 2×, `#fcfcfb` 2×), white (`#fff`, 21×), the deep-teal veil shadow colour, mono font,
spacing, shadows, focus ring.

**Dead CSS that references tokens:** `.otrn-login`, `.otrn-home-menu`, `.otrn-hero` (legacy
block `style.css:245-252, 482-484, 517` — only the `#otrn-hero` anchor id exists in markup),
`.otrn-footer-logos img:nth-child(2…4)`.

---

## 5. Why `--primary` looks inconsistently applied

`--primary` itself is **not** broken: it is defined exactly once (`style.css:17`), resolves to
`#DF8056` everywhere, and is never redefined. Four mechanisms create the *appearance* of
inconsistency:

1. **One token, three incompatible roles.** `--primary` is used simultaneously as (a) a solid
   fill under white text (`.otrn-btn`, `.otrn-rowlink`, `.otrn-tab.active`, `.otrn-pager
   .current`, `.dot`, `.otrn-split-seg`, PHP bar/dot fills), (b) **text colour** (`.otrn-crumb`,
   `.otrn-crumb a` `style.css:170-171`, `.otrn-tile .k`, `.otrn-sectionhead.grande.coral h2`,
   `.otrn .seq-1` `:298`), and (c) a border/hover hint (12 border/hover uses). Only role (a) is
   defensible; (b) and (c) need ≥3:1 and `#DF8056` gives **2.85:1** → fails AA for text and
   WCAG 1.4.11 for graphical objects (dots, bars, map pins).
2. **The accent role drifts to `--primary-dark`.** The same visual role is rendered with
   another token in most places: `.eyebrow` chips (`--primary-soft` bg + `--primary-dark`
   text), `.otrn-tag`, tier-mid, and the **active nav item**. Rendered proof: the only
   `#DF8056` *text* on `/` is one element, `span.dest.seq-1` ("Oferta", 13 px/700 → 2.85:1).
   Everything else "accent" is `#A05C3D` or `#2F6F79`.
3. **The rules that look responsible are overridden.** `style.css:41 .otrn-nav
   a.active{color:var(--primary)}` is specificity **(0,2,1)**; it loses to `otrn-menu.css:91
   .otrn-header .otrn-nav .otrn-nav__enlace.active` **(0,4,0)** and, on the homepage, to
   `otrn-arauco.css:56 .otrn.home .otrn-header .otrn-nav a.active` **(0,5,1)**. Both winners use
   `--primary-dark`. So the "primary" active-nav colour never paints (`--primary` also appears
   in `style.css:44`, dead because `.otrn-login` does not exist).
4. **A parallel `--aru-*`/`:root` layer** supplies the same colours under other names (§2.3),
   with a fallback whose values differ — an independent source of "why did this turn red here?".

**Conclusion:** it is a **role/semantics problem, not a resolution problem**. Fixing it requires
deciding which token owns "*accent as text / on light*" and which owns "*fill under white*" —
now answered by the confirmed municipal identity (§12).

---

## 6. Hardcoded colours, fonts and spacing that bypass tokens

| Kind | Instance | Where |
| --- | --- | --- |
| Colour | `#fff` ×21 (should be `--paper`) | `style.css` buttons/tabs/hero text |
| Colour | `#FBE4E4` ×6 — error surface with **no token** | `style.css:180, 186, 204` (tier-low) |
| Colour | `#fafbfa` ×2, `#fcfcfb` ×2 — row hover / draft | `style.css:128, 150, 224` |
| Colour | `rgba(51,51,51,.45/.42/.38/.06)` — **old `--dark` #333** kept after the token changed to `#2B2B2B` | `style.css` veils/shadows |
| Colour | `#fff`, `#eee` inside map JS; tier map duplicated in JS | `template-parts/mapa-evaluaciones.php:41, 66, 75` |
| Colour | UA default black on form controls (no `color` declared) | `style.css:113` |
| Colour | `#C0392B`-family + `--aru-*` aliases that duplicate globals | `otrn-landing.css:26-30`, `otrn-arauco.css:19-40` |
| Font | `'IBM Plex Mono',monospace` literal **32×** with no `--font-mono` | `style.css` (26), `otrn-landing.css` (6) |
| Font | `'Montserrat',system-ui` literal (2×) alongside `var(--font-title)` (5×) | `style.css`, `otrn-landing.css` |
| Spacing | No scale exists; ~20 distinct paddings (22/18/16/20/14/12/10/8 px) and `0` ×12 | all files |
| Radius | `12px` ×10, `8px` ×8, `999px` ×13, `50%` ×4, `6/4/3/11px`, `2px` ×3 — only 14/10 px are tokens | all files |
| Shadow | 8 distinct `box-shadow` literals; **0 in `style.css`** (all in landing/menu/arauco) | `otrn-landing.css`, `otrn-menu.css`, `otrn-arauco.css` |
| Veil | `rgba(0,0,0,.45)` ×4, `rgba(255,255,255,.88/.94/.22)` | `style.css` |

---

## 7. Page-by-page comparison (rendered, 1440 px)

| | `/` (home) | `/noticias/` | `/indicadores/` | `/estadisticas/` | `/seguimiento-pladetur/` |
| --- | --- | --- | --- | --- | --- |
| text colours | `#2b2b2b`31 `#fff`13 `#a05c3d`12 `#666`11 `#5a5a5a`4 `#8a8a8a`4 `#888`4 `#0e506d`3 `#145e6b`3 `#2f6f79`3 **`#df8056`1** | `#2b2b2b`27 `#666`9 `#a05c3d`5 `#888`5 `#fff`1 | (structure shared) | `#2b2b2b`110 `#666`31 `#888`22 **`#000`11** `#2f6f79`10 `#a05c3d`9 | `#2b2b2b`428 `#888`75 `#2f6f79`64 `#666`14 `#a05c3d`6 `#000`6 |
| fills | `#fff`20 `#0e506d`7 `#faeee9`4 `#2f6f79`2 **`#df8056`1** | `#fff`6 `#faeee9`4 `#0e506d`3 `#df8056`1 `#f6f3e0`1 | — | `#f0f0f0`21 `#e3eced`10 `#df8056`5 `#2f6f79`5 `#2b2b2b`1 `#a05c3d`1 | `#e3eced`64 `#f0f0f0`35 `#2f6f79`21 `#df8056`2 |
| AA failures | text-over-photo (unverifiable) + `--text3`/`--aru-texto-3` small text | 6 (`--text3`, `.otrn-btn` white-on-coral) | — | **24** | **76** |

Consistent pattern: `--text3` `#888888` on white = **3.54:1** and on `--lgrey` = **3.11:1**,
used at 11–13 px in labels, table heads and meta lines → systematic AA failures (a large share
of the 24/76 counts). `--primary` as a fill under white text (`.otrn-btn`,
`.page-numbers.current`) = **2.85:1**. `--secondary` on `--secondary-soft` = **4.76:1** ✔.
`#000000` on `/estadisticas/` and `/seguimiento-pladetur/` = the un-styled filter `<select>`
(§6).

**Chrome differs by page family although the markup is shared:** the footer (§3) and the header
background (`#fff` vs `#0e506d`) are the two places where "same DOM, different skin" is
systematic; the nav skin is unified by `otrn-menu.css` but its accent colour still comes from
two tokens (`--primary-dark` internal / `--aru-terra-deep` home, same value for now).

---

## 8. Original Rapa Nui vs Arauco-specific

- **Arauco-specific:** `--aru-*` (`otrn-arauco.css`), `--aru-footer`, the footer logos, the
  landing, `otrn-menu.css`, `--font-title`. Per `style.css:11-15` the *global* palette was
  already "aligned with Arauco", so the global tokens are shared, not purely legacy.
- **Rapa Nui leftovers that are still live code:** the theme header name (`style.css:2`), the
  legacy `.otrn-hero` block + `--hero-img` with **no producer** (`otrn_hero_portada_url()` is
  defined at `functions.php:482` and never called — F12), `.otrn-login`, the orphaned
  logos/hero (`assets/footer/*`, `assets/hero-rapanui.jpg`).
- No legacy value is assumed correct for Arauco: the token-role decisions are now governed by
  the confirmed municipal identity (§12) rather than by the inherited values.

---

## 9. Prioritized consolidation plan

**P0 — client decisions (now resolved for palette/roles, §12; pending: the mapping approval)**
1. ✔ Palette decided: municipal identity replaces coral/teal (F2).
2. Validate the proposed mapping (§12.4) and the 5 role names that have **no** verified
   municipal source (`--primary-dark`-style text-on-light, borders, surfaces, error/success).
3. Decide the front-page alias layer fate (keep `--aru-*` as the only front-page layer, or map
   the landing onto the global tokens and delete the aliases).
4. Decide the landing `:root` fallback (`otrn-landing.css:26-30`): realign or delete (F6).
   This file is one of the three uncommitted ones — coordinate that edit.

**P1 — mechanical consistency (no visual change once P0 is decided)**
5. Remove the 10 conflicting `--aru-*` redefinitions (§4).
6. Delete dead rules: `.otrn-login`, `.otrn-home-menu`, `.otrn-hero` + `--hero-img`, and the
   dead `img:nth-child(2…4)` footer heights.
7. Make the footer single-source (one rule set for both page families, or document the two
   skins).
8. Fix the footer `img:nth-child` markup/CSS mismatch.

**P2 — design-system hygiene (after approval)**
9. Add the missing variables — mono font, spacing scale, shadow scale, error surface, focus
   ring, z-index ladder — and replace the literals in §6.
10. Replace `#fff`×21, `#FBE4E4`×6, `#fafbfa`, `#fcfcfb`, `rgba(51,51,51,…)` with tokens.
11. Declare `color` on `.otrn-field` controls so filters stop rendering black.
12. Align breakpoints (992 vs 980; 720 vs 768 vs 679/680) and document the nav thresholds.
13. Document the parent `theme.json` preset layer and the inherited 22 px base size.
14. Move the JS colour map (`mapa-evaluaciones.php:41`) to a single source.

---

## 10. Recommendations (for UX/UI approval — not applied)

**Colour**
- Separate the token set by **role**, not by hue: `*`-fill vs `*-on-light`/`*-ink`.
- Add a **contrast budget** to the token table (each token: intended background + required
  ratio). Measured in the old system: `--primary` 2.85:1, `--text3` 3.54/3.11:1, `--error`
  2.97:1 on its surface — all below AA in those roles.
- Verify the municipal `#0070FF` (vivid blue) and `#41BCFF` (light blue) against AA before
  using them as text or as small icons; `#41BCFF` on white is a light colour and is likely
  suitable only as a surface/accent, not as text.

**Typography**
- Introduce a single mono token and replace the 32 literals; keep Montserrat as the only text
  family (the municipal site also uses Montserrat 400/500/600 + Poppins 400 secondary).
- Decide the **base size** deliberately: the child currently relies on the parent's 22 px.

**Components**
- Formalise an interaction matrix (hover / focus-visible / active / disabled) per component.
  Only `otrn-menu.css` defines `:focus-visible` today.
- Promote the de-facto radii (14/10/12/8/999) and shadows into tokens, or reduce them to a
  stated scale; the municipal site is flat (no template shadows) with a dominant 30 px pill
  radius.
- Give the "chapa" (white plate behind logos) a token and apply it consistently.

---

## 11. Limits

Not verified: focus-ring appearance and keyboard modality (hidden window), real touch devices,
iOS dynamic viewport, `prefers-reduced-motion`, contrast over photographs (marked "imagen"),
and the private pages (CSS-only reading). Nothing was committed or deployed; the working tree
still contains only the 3 pre-existing modified files.

---

## 12. Addendum — municipal identity verification (Phase 1, 2026-10-16)

The confirmed direction is the **Municipalidad de Arauco** visual identity. Because the
repository contained **no extracted municipal style information** (no CSS/HTML snapshot, no
brand manual; only photographs and the institutional logos), the palette was verified against
the **live municipal site**.

### 12.1 Method

- Official domain: `muniarauco.cl` (identified via search; `muniaruco.cl` without the `a` is
  not the municipal site).
- The site runs WordPress + Elementor + `hello-elementor` + child theme `arauco-child`
  ("Webart SpA"). Its global palette lives in the Elementor **kit**:
  `wp-content/uploads/elementor/css/post-5.css`.
- All 44 stylesheets the homepage loads were downloaded and searched locally; the four
  colours not in the kit were traced across them.
- In-repo cross-check: the institutional logo files were converted to BMP and histogrammed
  (`assets/logos/*.webp`, `references/web/22.04.40.webp`).

### 12.2 Verified municipal palette (source: Elementor global kit)

| Elementor global | Hex | Verified usage count (header `post-13` / footer `post-80` / home `post-109`) |
| --- | --- | --- |
| `--e-global-color-primary` | **`#0070FF`** | header 0 · footer 11 · home 7 · 5 buttons · `#0070FF9C` (62 % alpha) header button · events accent |
| `--e-global-color-secondary` | **`#DE232B`** | footer 8 · home 6 · 2 buttons · footer link colour 7 |
| `--e-global-color-accent` | **`#41BCFF`** | home 17 · footer 3 · header 2 · 5 buttons |
| `--e-global-color-text` | **`#000000`** | footer 15 · home 11 · header 3 |
| `--e-global-color-45d62ab` | `#FFFFFF` | footer 9 · home 27 · header 11 |
| kit background | `#F8F8F8` | `background-color` on the kit |
| extra (header) | `#304A83` | 1× header background |
| extra (footer) | `#333333` | 1× footer background |

**Key finding — the roles are not the documented order.** In the municipal kit the ordering is
`primary #0070FF`, **`secondary #DE232B` (red)**, **`accent #41BCFF` (light blue)**, text
`#000000`. The brief's six-colour list orders them differently, which is exactly the trap the
brief warned about ("do not assume the first colour is automatically the primary"). Verified
roles:

- **Primary = `#0070FF`** — brand blue for actions, links, active state, header/footer fills.
- **Secondary = `#DE232B`** — brand red for emphasis/highlights (footer links, buttons).
- **Accent = `#41BCFF`** — light-blue surface/highlight (large backgrounds on the home page).
- **Text = `#000000`**; **White = `#FFFFFF`**; kit background **`#F8F8F8`**.

### 12.3 Verified typography, geometry and other properties

| Property | Verified value | Source |
| --- | --- | --- |
| Fonts | **Montserrat** (primary + text) and **Poppins** (secondary) | kit `--e-global-typography-*-font-family` |
| Weights | primary **600**, text **400**, accent **500**, secondary **400**; in-page 700 (19×), 800 (7×), 500 (7×), 600 (2×) | kit + `post-13/80/109` |
| Heading sizes (home) | 32 px (7×), 24 px (5×), 29/20/18/16 px | `post-109.css` |
| Container | max-width **1200 px** (desktop) / 1024 px (≤1024) / 767 px (≤767) | kit |
| Widget spacing | 20 px | kit |
| Radii | **30 px** (dominant, 17×), 3 px, 10 px | kit + templates |
| Shadows | none on the templates (flat) | kit + templates |

### 12.4 Colours that are NOT verified as municipal (require approval)

| Colour | Finding |
| --- | --- |
| `#0C1434` | **Absent from every stylesheet the municipal site loads** and from the home HTML. It coincides with the navy family of the municipal escudo (`#002868` field) but has no verified municipal source. |
| `#0C0D0E` | Only in Elementor's generic `base-desktop.css` and `widget-divider.min.css` — Elementor defaults, **not** the municipal kit. |
| `#D6D5D5` | Only in Elementor's generic `base-desktop.css` (used as a `color`) — Elementor default, **not** the municipal kit. |
| Extra brand-ish colours seen on the site | `#304A83` (header bg), `#0050B8` (dark blue text on the accent surface), `#707070`/`#706F6F` (grey text), `#FF7E24`, `#10B981`, `#004D69`. These are page-level, not kit-level. |

**Conclusion:** three of the six documented colours (`#0C1434`, `#0C0D0E`, `#D6D5D5`) cannot be
confirmed as municipal; two of them are Elementor defaults. They must not be adopted as brand
tokens without explicit approval (see the mapping in the handoff message / DESIGN_SYSTEM §2.5).

---

## 13. Implementation record — consolidation applied (2026-10-16)

The mapping below was approved by the client and **applied in application code** on
2026-10-16 (Phase 2). §§1–11 describe the *previous* state and are kept as the technical
baseline; this section is the as-built record. Single source of truth is now
`style.css :root` ([DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) §2.1). Runtime evidence:
[VERIFICATION.md](VERIFICATION.md) §V17.

### 13.1 Global tokens — before → after

| Token | Before | After | Role in the new system |
| --- | --- | --- | --- |
| `--primary` | `#DF8056` | **`#0070FF`** | Brand blue. Fills without text, borders, chart series, ≥24 px / 800-weight headings. **Never small text on white** (4.41:1). |
| `--primary-dark` | `#A05C3D` | **`#0050B8`** | Accessible brand blue (7.40:1 on white): all small text, links, active state, focus outlines, solid fills carrying white text. |
| `--primary-soft` | `#FAEEE9` | **`#E7F1FF`** | Brand tint surface (chips, hover highlights, tables). Pairs with `--primary-dark` (6.49:1). |
| `--primary-tint` | `#F0C5B2` | **`#BBD8FF`** | Mid brand tint (maturity ramp step 2). |
| `--secondary` | `#2F6F79` | **`#DE232B`** | Municipal red. Emphasis/CTA (`4.79:1` both ways). No longer teal. |
| `--accent` | — | **`#41BCFF`** | Municipal light blue, **decorative only** (2.13:1 on white): no text on it. |
| `--blue-dark` | `#0E506D` | **`#304A83`** | Institutional dark blue surfaces (photo fallback, drawers, sparkline partner colour). |
| `--dark` | `#2B2B2B` | **`#000000`** | Body text. |
| `--text2` | `#666666` | **`#707070`** | Secondary text. Passes AA on every surface it is used on (4.58–4.95:1). |
| `--text3` | `#888888` | **removed** | Collapsed into `--text2` (3.5:1 on white failed AA). |
| `--secondary-soft` | `#E3ECED` | **removed** | No municipal equivalent; replaced by `--primary-soft` / `--success-soft`. |
| `--blue-dark-soft` | `#DFE8EC` | **removed** | Was unused after remapping. |
| `--grey` | `#E8EEE6` | **`#E0E0E0`** | Borders/dividers (neutral, not `--text2`-carrying surface). |
| `--cream` | `#F6F3E0` | **`#F8F8F8`** | Municipal neutral background (sections, footer). |
| `--lgrey` | `#F0F0F0` | **`#F6F6F6`** | Neutral component fill (tracks, headers, chips). Lightened so `--text2` reaches 4.58:1. |
| `--error` | `#EA5252` | **`#C0272D`** | Semantic error (4.85:1 on `--error-soft`). |
| `--success` / `--success-soft` | — | **`#0F7B4E` / `#E4F5EC`** | New semantic pair (4.69:1). |
| `--warning` / `--warning-soft` | — | **`#8A5A00` / `#FCF1DC`** | New semantic pair (5.29:1). |
| `--error-soft` | — | **`#FBE4E4`** | Error tint, now a token (was a literal). |
| `--neutral` / `--neutral-soft` | — | **`#52525B` / `var(--lgrey)`** | "No data"/neutral status (7.15:1). |
| `--paper` | `#FFFFFF` | `#FFFFFF` | Surface. |
| `--radius` / `--radius-sm` | `14px` / `10px` | **`10px` / `6px`** | Container/control radii (municipal 10 px). |
| `--radius-pill` | — | **`30px`** | Municipal pill radius, buttons/chips only. |
| `--space-section` | — | **`clamp(48px,8vw,120px)`** | Section rhythm (was `--aru-gap`). |
| `--font-title` | `'Montserrat',system-ui,sans-serif` | `var(--font-title)` | Montserrat (theme already self-hosts it). |
| `--font-mono` | — | **`'IBM Plex Mono',monospace`** | Numeric content (temporary). No Poppins token: the font is not shipped. |

### 13.2 Removed token layers

| Layer | Action |
| --- | --- |
| `otrn-arauco.css` `.otrn.home{--aru-*}` (17 aliases) | **Deleted.** The file now holds only what is intrinsic to the front page: the fixed translucent header and the footer top margin. |
| `otrn-landing.css` unscoped `:root` fallback (12 tokens) | **Deleted** (this was the F6 conflict: same names, different values). |
| `--aru-papel-2`, `--aru-arena`, `--aru-ink-deep`, `--aru-linea`, `--aru-radio-sm`, `--aru-gap` | Removed; the last one replaced by `--space-section`. |

Alias mapping used for that remap: `--aru-papel`→`--paper`, `--aru-terra`→`--primary`,
`--aru-terra-deep`→`--primary-dark`, `--aru-terra-soft`→`--primary-soft`,
`--aru-teal`→`--primary-dark` (page CTA fill), `--aru-teal-deep`→`--blue-dark`,
`--aru-ink`→`--blue-dark`, `--aru-texto`→`--dark`, `--aru-texto-2`/`--aru-texto-3`→`--text2`,
`--aru-radio`→`--radius`, `--aru-footer`→`--cream`.

### 13.3 Semantic remapping (brand ≠ status)

| Element | Before | After |
| --- | --- | --- |
| Tier scale `tier-high/mid/low/na` (status pills, KPIs, score boxes, percent badges) | teal / coral / coral-tint / grey | traffic light: **success / warning / error / neutral** |
| Maturity scale `.opt.o0–o3` / `.otrn-score-tag.s0–s3` | teal ramp with a red floor | brand-blue ramp with a red floor: `o0/s0` error-soft, `o1/s1` primary-soft, `o2/s2` primary-tint, `o3/s3` primary-dark, `ona/sna` neutral |
| `.otrn-tag` | `--secondary-soft` / `--secondary` | `--primary-soft` / `--primary-dark` (categorical chip, distinct from grey `.borrador`) |
| `.otrn-avance .fill`, `--primary` bars | `--secondary` | `--primary` |
| `.otrn-dato` photo fallback | `--secondary` | `--blue-dark` |
| `.teal` heading, `.otrn-btn.teal` | teal (`--secondary`) | red `--secondary` (4.79:1) |
| `.coral` (dead class, kept) | `--primary` | `--primary` |
| Map legend + Leaflet tier colours | hardcoded hex in PHP/JS | read from `--success`/`--warning`/`--error`/`--neutral` via `getComputedStyle` (no duplicated palette) |
| Chart/sparkline series | `--secondary` | `--primary` (active bars: `--primary`, inactive: `--blue-dark`) |

### 13.4 Literal replacements (hardcoded values that bypassed tokens)

`#fff` → `var(--paper)` · `#FBE4E4` → `var(--error-soft)` · `#fafbfa` (row hover) →
`var(--lgrey)` · `#fcfcfb` → `var(--lgrey)` · `rgba(51,51,51,…)` → `rgba(0,0,0,…)` ·
`rgba(10,46,60,…)` / `rgba(14,80,109,…)` → `rgba(48,74,131,…)` ·
`rgba(20,94,107,.28)` → `rgba(0,80,184,.28)` · `rgba(160,92,61,.25)` → `rgba(138,90,0,.25)` ·
`'IBM Plex Mono',monospace` → `var(--font-mono)` · `'Manrope',…` → `var(--font-title)` ·
inline `border-radius:14px` → `var(--radius)` · `border-radius:999px` → `var(--radius-pill)`.

### 13.5 Dead CSS deleted

`.otrn-login` (button, no markup), `.otrn-hero` (+ `--hero-img`, the class was unused — only the
`#otrn-hero` anchor id remains), the four `.otrn-footer-logos img:nth-child()` heights,
`.otrn-home-menu`, and the front-page-specific footer override. `.otrn-mision` (dead but not
authorised for removal) is kept.

### 13.6 Deliberate decisions

- **Content vs label inside drawers.** The text the evaluator must read to decide an answer (the
  criterion question, the description of each level) uses `--dark`; the small uppercase labels
  (`.et`) and the state text keep `--text2`. With `--text2` the question sat at 4.58:1 on
  `--lgrey` and the descriptions at 4.95:1 on white — AA by the minimum, but out of step with
  the indicator definition, which is black. Fixed in `9142179`
  ([VERIFICATION.md](VERIFICATION.md) §V18.2).
- **No Poppins token — decided.** The theme ships only Montserrat + IBM Plex Mono
  (`assets/fonts`), so a `--font-secondary` would be a broken fallback; the client decided
  (2026-10-16) **not to ship Poppins**, so it stays documented instead.
- **`--accent` is decorative.** #41BCFF is 2.13:1 on white, so it is used for icon/spots only
  (`.otrn-dato-lite__ico`), never for text.
- **Retained legacy token names** (`--cream`, `--lgrey`, `--paper`, `--dark`, `--text2`) to keep
  the diff reviewable; their values are municipal/neutral, and the names are documented here.
- **Remaining gap, decided:** non-text contrast (WCAG 1.4.11) for form-control borders — the
  system has a single light `--grey` (#E0E0E0, 1.3:1 on white), below the 3:1 needed to identify
  an input boundary. The client decided (2026-10-16) to **leave it as it is** instead of inventing
  a second border token; it stays documented as a known gap.

### 13.7 Files changed by the implementation

`wp-content/themes/otrn/`: `style.css`, `assets/css/otrn-arauco.css`,
`assets/css/otrn-landing.css`, `assets/css/otrn-menu.css` (comments/radii here; the dropdown
mouse bridge arrived later, in `3dbfbc6`), `page-evaluar.php`,
`page-estadisticas.php`, `page-noticias.php`, `archive-buena_practica.php`,
`archive-evaluacion.php`, `archive-guia.php`, `archive-indicador.php`,
`archive-iniciativa_pladetur.php`, `single-buena_practica.php`, `single-evaluacion.php`,
`single-guia.php`, `single-indicador.php`, `single-iniciativa_pladetur.php`, `single-post.php`,
`template-parts/material-lista.php`, `template-parts/mapa-evaluaciones.php`,
`template-parts/pladetur/chart-anual.php`.

### 13.8 Home-page review round — component geometry, no token change (`e2c3a9f`)

The client's second review round touched the home page only (F20, F21); **no token changed**, so
this section records component-level decisions that a future token pass must respect (measurements
in [VERIFICATION.md](VERIFICATION.md) §V19):

- **Affordance rule.** Card chrome (white surface + border + `--radius` + shadow) is reserved for
  **actionable** elements (`.otrn-indexcard`, `.otrn-card`, link buttons). Informative blocks do
  not wear it: `.otrn-dato-lite` (the home "Datos" figures, an `<article>` that links nowhere) is
  now flat — no background, no enclosing border, no radius, no shadow — and carries a
  `border-top:2px solid var(--primary)` rule. This is the token system's first explicit
  information-vs-navigation rule, so new "data" blocks should follow it.
- **Veils are token-derived but alpha-literal.** The scene veils keep using
  `rgba(48,74,131,…)` (the RGB of `--blue-dark`) with per-use alphas, because CSS cannot apply
  alpha to a hex token without `color-mix()` and a fallback would reproduce the original legibility
  defect. Two documented profiles: static `0.12 → 0.88 → 0.92` vertical; immersive
  `0.92 → 0.86 @640px → 0 @1160px` horizontal (pixel stops, aligned with the text column).
- **`--panel-w` is a layout variable, not a token.** The immersive photographic panel's width
  (54 %) is declared once on `.otrn-stage.is-immersiva` and consumed by the media `clip-path` and
  by the hero column clamp `min(640px, calc(var(--panel-w) - 56px))`, so the two cannot drift.
- **Over-photograph text is dark or white, never brand mid-tones.** The rail's active number moved
  from `--primary-dark` (2.33:1 measured) to `--dark`; on photographs, `--primary-dark` and
  `--primary` cannot be assumed legible (they are tuned for white and `--primary-soft`).

### 13.9 Mobile scene round — glass panel, still no token change (`649b01f`)

The client's third review round (mobile scenes, [VERIFICATION.md](VERIFICATION.md) §V20) added a
second legibility device over the photographs and **again changed no token**:

- **The glass panel and its pill use the same literal navy as the veils:** `rgba(48,74,131,.65)`
  plus `backdrop-filter:blur(16px) saturate(1.15)` for the panel, and the same tint in pill form
  for the hero's scroll cue (`--radius` / `--radius-pill` for geometry). Same rationale as §13.8:
  the alpha has to be per-use, and the RGB is `--blue-dark`'s; the 0.65 value is measured, not
  decorative (0.62 fails AA at the hero's brightest spot, §V20.3). The `@supports not
  (backdrop-filter)` branch uses 0.82. **If a second glass surface ever appears, this is the point
  at which a `--glass-*` token family should be created** — the audit's "hardcoded values that
  bypass tokens" list (§13.4) is otherwise unchanged by this round.
- **One literal retired:** the scene eyebrows passed from `rgba(255,255,255,.85)` to
  `var(--paper)` (measured 4.1:1 over the panel vs 4.7:1), i.e. one more hardcoded white replaced
  by a token — the direction §13.4 asks for. The same applies to the scroll cue (to `--paper`) and
  to the ghost CTA's border over the panel (`rgba(255,255,255,.8)`, kept literal as a
  non-token UI boundary; §13.6 records the client's decision on form-control borders).
- **Geometry added:** panel inset 8 px / text 20 px, `--radius`; cue pill `--radius-pill`. Nothing
  else in the component inventory (`DESIGN_SYSTEM.md` §4) changed, and no file outside
  `assets/css/otrn-landing.css` was touched.
