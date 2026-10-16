# Design System

Description of the **implemented** visual system. As of 2026-10-16 the theme follows the
**Municipalidad de Arauco** identity: the global tokens were consolidated onto the verified
municipal palette and applied across the site (Phase 2). The coral/teal system is no longer the
design system; it survives only as the `5f43933` baseline, recorded in
[DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) §§1–11 and in §8 below.

**Status of this document.** The token mapping was approved by the client before
implementation (2026-10-16) and is now **applied in code**; the *visual* result is **pending the
client's sign-off**, as is the F1 navigation review. Nothing here is a "final approval".
Runtime evidence for every claim below: [VERIFICATION.md](VERIFICATION.md) §V17.

## 0. Confirmed direction (2026-10-16)

The client confirmed that the site must follow the **Municipalidad de Arauco** visual identity.
The verified foundation (palette roles, typography, geometry, read from the municipal site's
Elementor kit) is in §2.5. Three of the six colours in the original brief have **no verified
municipal source** and were **not** adopted (§2.6).

## 1. One source of truth (F6 resolved)

`style.css :root` (§2.1) is the **only** place where the theme defines tokens. The two
competing layers are gone:

| Removed layer | Was | Now |
| --- | --- | --- |
| `otrn-arauco.css:17-46` — `--aru-*` aliases scoped to `.otrn.home` (10 real value conflicts) | Loaded only on the front page; self-described as an unapproved *interpretation* of the photographs | Deleted. The file keeps only what is intrinsic to the front page: the fixed translucent header and the footer top margin. |
| `otrn-landing.css` — **unscoped** `:root` `--aru-*` fallback with *different* values from the scoped block | Two live values for the same name (e.g. `--aru-terra` `#C0392B` vs `#DF8056`) | Deleted entirely. Every `--aru-*` use was remapped to a global token. |

No `--aru-*`, `--text3`, `--secondary-soft` or `--blue-dark-soft` reference remains anywhere in
the theme (grep). Remaining token names that look legacy (`--cream`, `--lgrey`, `--paper`,
`--dark`, `--text2`) are kept deliberately to keep the diff reviewable; their values are
municipal or neutral — see the mapping in [DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) §13.1.

## 2. Tokens

### 2.1 Global tokens — `style.css :root` (authoritative)

| Group | Token | Value | Role |
| --- | --- | --- | --- |
| Brand | `--primary` | `#0070FF` | Municipal primary. Fills **without** text, borders, chart series, ≥24 px or 800-weight headings. **Not** for small text (4.41:1 on white, below AA). |
| Brand | `--primary-dark` | `#0050B8` | Accessible brand blue (7.40:1 on white): all small text, links, active state, focus outlines, and any solid fill that carries small white text. |
| Brand | `--primary-soft` | `#E7F1FF` | Brand tint surface (chips, hover highlights, table heads). Pairs with `--primary-dark` (6.49:1). |
| Brand | `--primary-tint` | `#BBD8FF` | Mid brand tint (maturity ramp step 2; 5.06:1 with `--primary-dark`). |
| Brand | `--secondary` | `#DE232B` | Municipal red. Emphasis and the news CTA (4.79:1 both ways). |
| Brand | `--accent` | `#41BCFF` | Municipal light blue. **Decorative only** (2.13:1 on white) — icons and spots, never text. |
| Brand | `--blue-dark` | `#304A83` | Institutional dark blue surfaces (photo fallback, indicator drawer, inactive chart bars; 8.63:1 with white). |
| Neutral | `--dark` | `#000000` | Body text (21:1 on white). |
| Neutral | `--text2` | `#707070` | Secondary text. Passes AA on every surface it is used on (4.58:1 on `--lgrey`, 4.66:1 on `--cream`, 4.95:1 on white). |
| Neutral | `--grey` | `#E0E0E0` | Borders and dividers. Never a surface behind `--text2`. |
| Neutral | `--cream` | `#F8F8F8` | Municipal neutral background: sections, footer. |
| Neutral | `--lgrey` | `#F6F6F6` | Neutral component fill: table heads, progress tracks, chips. Lightened from `#F0F0F0` so `--text2` reaches 4.58:1. |
| Neutral | `--paper` | `#FFFFFF` | Surface. |
| Status | `--success` / `--success-soft` | `#0F7B4E` / `#E4F5EC` | 4.69:1 |
| Status | `--warning` / `--warning-soft` | `#8A5A00` / `#FCF1DC` | 5.29:1 |
| Status | `--error` / `--error-soft` | `#C0272D` / `#FBE4E4` | 4.85:1 |
| Status | `--neutral` / `--neutral-soft` | `#52525B` / `var(--lgrey)` | 7.15:1 |
| Rhythm | `--space-section` | `clamp(48px,8vw,120px)` | Vertical section spacing. |
| Geometry | `--radius` / `--radius-sm` / `--radius-pill` | `10px` / `6px` / `30px` | Containers and controls / dense controls / pills (buttons and chips only). |
| Type | `--font-title` / `--font-mono` | `'Montserrat',…` / `'IBM Plex Mono',monospace` | UI text / numeric content. |

### 2.2 Brand and status are separate systems

Municipal brand colour never carries meaning, and status colour never carries brand. Status
components always pair a **soft tint** with the **strong colour** (badge, pill, KPI, score box,
percent badge), and the traffic-light scale is:

| Tier | Meaning | Colours |
| --- | --- | --- |
| `tier-high` | Bueno (67–100 %) | `--success` on `--success-soft` |
| `tier-mid` | Medio (34–66 %) | `--warning` on `--warning-soft` |
| `tier-low` | Bajo (0–33 %) | `--error` on `--error-soft` |
| `tier-na` | Sin datos | `--neutral` on `--neutral-soft` |

The **maturity scale** (autodiagnóstico 0–3, `.opt.o*` / `.otrn-score-tag.s*`) is *not* a status
scale: it is a brand-blue ramp with a red floor — `o0/s0` error-soft, `o1/s1` primary-soft,
`o2/s2` primary-tint, `o3/s3` primary-dark, `ona/sna` neutral. Before 2026-10-16 both scales
were painted with the same teal/coral tokens, which is why a "good" answer and a "high" tier
looked identical.

The Leaflet map reads these tokens at runtime
(`template-parts/mapa-evaluaciones.php`: `getComputedStyle(document.documentElement)`), so the
map, its legend and the CSS cannot drift apart.

### 2.3 Contrast rules of thumb

- Small text on white/`--cream`: `--primary-dark`, `--dark`, `--text2`, or a status strong colour.
- `--primary` only where it is a fill (no text on it), a border, a chart series, or a heading
  ≥24 px / weight 800 (4.41:1 clears the 3:1 large-text threshold).
- Solid fills that carry small white text use `--primary-dark` (7.40:1), never `--primary`.
- White on `--secondary` (4.79:1) and on `--blue-dark` (8.63:1) are both safe.
- **Known gap:** non-text contrast (WCAG 1.4.11) of form-control borders. The system has a single
  light `--grey` (#E0E0E0, 1.3:1 on white), below the 3:1 needed to identify an input boundary.
  Held for a UX decision instead of inventing a second border token.

### 2.4 Geometry

Municipal language is flat: **no decorative shadows** (the few remaining ones are 1 px borders
emulations on cards and the drawer/panel shadow), the dominant radius is the **30 px pill** for
buttons and chips, **10 px** for cards, panels, tables and larger controls, and **6 px** for
dense controls (scale options, sub-nav rows, focus rings). Pill styling is **not** forced on
cards, tables or containers.

### 2.5 Municipal foundation — verified 2026-10-16

Verified against the live municipal site **`muniarauco.cl`** (WordPress + Elementor; its global
palette is the Elementor kit, `wp-content/uploads/elementor/css/post-5.css`). All 44
stylesheets the municipal home page loads were downloaded and searched; the in-repo
institutional logos were histogrammed as a cross-check. Full evidence:
[DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) §12.

| Elementor global | Hex | Semantic role (verified) | Measured usage |
| --- | --- | --- | --- |
| `primary` | **`#0070FF`** | Brand blue — actions, links, active state, header/footer fills | footer 11 · home 7 · header/footer backgrounds · 5 buttons · events accent |
| `secondary` | **`#DE232B`** | Brand red — emphasis/highlights | footer 8 · home 6 · 2 buttons · footer links 7 |
| `accent` | **`#41BCFF`** | Light blue — surfaces/highlight | home 17 · footer 3 · header 2 · 5 buttons |
| `text` | **`#000000`** | Body text | footer 15 · home 11 · header 3 |
| (white) | `#FFFFFF` | Surfaces | home 27 · footer 9 · header 11 |
| kit background | `#F8F8F8` | Page background | kit `background-color` |

**Roles do not follow the brief's order:** in the municipal kit the red `#DE232B` is the global
*secondary* and the light blue `#41BCFF` is the global *accent*.

Other verified properties: fonts **Montserrat** (primary + text) and **Poppins** (secondary);
weights 600/400/500 plus in-page 700/800; container 1200 px (1024 at ≤1024, 767 at ≤767);
radii **30 px** dominant plus 3 px and 10 px; **no shadows** on the templates. Additional
page-level site colours: `#304A83` (header bg), `#0050B8` (dark blue text on the accent
surface), `#707070` (grey text), `#FF7E24`, `#10B981`, `#004D69` — all adopted as above except
the last three, which are not used by the theme.

### 2.6 Not verified as municipal — not adopted

| Colour | Finding |
| --- | --- |
| `#0C1434` | **Absent from every stylesheet and from the home HTML** of the municipal site. Coincides with the escudo navy family (`#002868`) but has no verified municipal source. |
| `#0C0D0E` | Only in Elementor's generic `base-desktop.css` and `widget-divider.min.css` — **Elementor defaults**, not the municipal kit. |
| `#D6D5D5` | Only in Elementor's generic `base-desktop.css` — **Elementor default**, not the municipal kit. |

These must not be adopted as brand tokens without explicit approval. The brief's `#0C1434`
(deep navy) is therefore represented in the theme by the **verified** institutional blue
`#304A83`.

## 3. Typography

- Families: **Montserrat** (400/500/600/700/800) for titles/body and **IBM Plex Mono**
  (400/500/600/700) for figures, both self-hosted woff2 subsets with `font-display:swap`
  (`assets/css/fonts.css`, inventory in `assets/fonts/README.md`). No Google Fonts runtime
  dependency.
- `--font-title` is Montserrat and `--font-mono` is IBM Plex Mono; every font declaration goes
  through them. The historical defect (the committed CSS asked for `'Manrope'`, which is **not
  shipped** — text fell back to `system-ui`) is fixed: no `Manrope` reference remains (grep).
- **Poppins** (the municipal *secondary* family) is **not** declared as a token: the theme does
  not ship it, so a `--font-secondary` would be a broken fallback. Add it only with the font
  files.
- IBM Plex Mono for numeric content is a **temporary** decision inherited from the baseline.

## 4. Components & conventions

- Naming: `otrn-` prefix, BEM-ish blocks (`otrn-act__eyebrow`, `otrn-rail__item`,
  `otrn-burger__line`). Layout container `.wrap` = max-width 1440 px, 32 px padding.
- Chrome: `.otrn-header` (72 px, border-bottom), `.otrn-brand`, `.otrn-nav` (grouped row +
  dropdowns + «Más» + mobile drawer, `assets/css/otrn-menu.css`), `.otrn-cuenta` (person icon
  account control), `.otrn-burger`. The legacy `.otrn-login` button was removed: no markup used
  it.
- **One footer for both page families**: three institutional Arauco logos, each on a white
  "plate" (`--paper` + `--grey` border, no shadow) because two of the PNGs have an opaque light
  grey inner background, plus the copy line. The front page only overrides `margin-top:0`.
- Reusables: page head with `.eyebrow` chip; stat boxes (`.otrn-stats`/`.otrn-stat`, plus an
  `.accent` variant on `--primary-soft`); cards (`.otrn-lin-card`, `.otrn-indexcard`, news
  cards); buttons (`.otrn-btn` — pill, `--primary-dark`; `.ghost` variant; the `.teal` variant
  is now red); row links (`.otrn-rowlink`, solid/ghost); tables (`.otrn-table`, with a
  `tarjetas-movil` mode that turns rows into labelled cards on small screens); filters/fields on
  the archive forms; provisional notice (`.otrn-provisional`, now `--warning`); modality icons
  (`.otrn-stat.accent`, `.otrn-dato-lite__ico` → `--accent`).
- Link colours must beat `.otrn a{color:inherit}` (0,1,1). Button-like links therefore carry an
  explicit higher-specificity rule — `.otrn a.otrn-btn`, `.otrn a.otrn-rowlink`,
  `.otrn a.otrn-more`. This is why the solid row link was invisible-on-blue before the fix
  ([VERIFICATION.md](VERIFICATION.md) §V17).
- Landing scene system: `.otrn-landing`, `.otrn-stage` (shared photographic level),
  `.otrn-act` scenes, `.otrn-rail` (01–05 indicator), `.otrn-reveal` (scroll reveal), burger
  classes for the mobile panel.

## 5. Responsive behaviour

- Breakpoints: 1440 (wrap), 1280, 992, 980, **782** (admin bar), 768, 720, 640, 600, 480, plus
  the measured navigation thresholds **1152 / 910 / 680** (`otrn-menu.css`) and the footer logo
  step at 600. The list is still not fully aligned (992 vs 980; 720 vs 768 vs 679/680) —
  [DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) §3.
- Navigation (F1, implemented `5f17b38`): one grouped row from 1152 px, progressive collapse
  into «Más» at 910 and 680, and an accessible full-height drawer with a burger at ≤679 px.
  **Verified (2026-10-16)**: 7 pages × 9 widths from 375 to 1920 px, **0 px horizontal
  overflow**, burger/panel thresholds identical on the front page and on internal pages, and
  the token pass did not change any of it ([VERIFICATION.md](VERIFICATION.md) §V17).
- ≤600 px: footer logos drop to 40 px.
- ≤768 px: the landing switches to static mode (scenes stack full-bleed) and the rail is
  hidden; grids collapse (data/news grids to 2 columns at 768, 1 at 480); head layouts stack at
  980.
- `prefers-reduced-motion`: animations neutralised, reveals forced visible, nav transition off
  (`otrn-landing.css`).
- Unverified: real-device behaviour and touch/keyboard interaction.

## 6. Scrollytelling architecture

- **Static mode (default)**: no JS / reduced-motion / ≤768 px — scenes stack with photos and
  legible text (`front-page.php`).
- **Immersive mode**: desktop + GSAP/ScrollTrigger; the shared stage is pinned with `scrub:1`,
  three crossfaded scenes, the hero panel expanding to full-bleed, and the rail synced to scroll
  progress (`assets/otrn-landing.js`).
- Reveal system: IntersectionObserver adds `.is-visible`; CSS pre-hides with
  `.otrn-reveal{opacity:0}`. **With JS fully disabled and no reduced-motion preference, revealed
  content stays invisible** (F5, confirmed at runtime).
- **Unverifiable from computed styles**: text over the photographs. The scene veils are now the
  institutional blue (`rgba(48,74,131,…)`) instead of the old dark teal, so the overlay
  contrast changed; it still needs a human look at the rendered page
  ([VERIFICATION.md](VERIFICATION.md) §V17).
- Vendor: GSAP + ScrollTrigger self-hosted (`assets/vendor/`), loaded only on the front page
  (~115 KB noted in code comments).

## 7. Status vs the municipal identity

| Aspect | Before (baseline `5f43933`) | Now (implemented) | Status |
| --- | --- | --- | --- |
| Palette | Teal/terracotta from photographs (`--aru-*`) | Municipal identity verified against `muniarauco.cl` (§2.1, §2.5) | **Applied**; visual sign-off pending (F2) |
| Token layering | Three overlapping systems | One (`style.css :root`) | **Resolved** (F6) |
| Suite of colours | 14 tokens, 3 semantic pairs | 26 tokens incl. 4 status pairs + rhythm + pill radius | Applied |
| Typography | Montserrat + IBM Plex Mono, but Manrope requested | Montserrat + IBM Plex Mono via `--font-title`/`--font-mono` | Resolved (Manrope) |
| Institutional logos | 3 Arauco logos, GORE low-res, RAU absent | Same 3 logos, now on white plates with one height | Source material still pending (F13) |
| Rapa Nui leftovers | Present in copy/tokens/theme name | Tokens cleaned; **copy/theme name untouched** | Migration pending (F7–F10) |

## 8. Before → after token mapping

The full table (token, previous value, new value, role) plus the alias remap, the semantic
remapping of tiers/scales, the literal replacements and the deleted dead CSS is in
[DESIGN_TOKENS_AUDIT.md](DESIGN_TOKENS_AUDIT.md) **§13**. Highlights:

| Was | Is |
| --- | --- |
| `--primary` coral `#DF8056`, `--primary-dark` `#A05C3D` | `#0070FF` / `#0050B8` |
| `--secondary` teal `#2F6F79` | municipal red `#DE232B` |
| — | new `--accent #41BCFF` (decorative) |
| `--dark` `#2B2B2B`, `--text2` `#666666`, `--text3` `#888888` | `#000000`, `#707070`, *removed* |
| single `--error` | `--success`/`--warning`/`--error`/`--neutral`, each with a soft pair |
| `--radius 14px` / `--radius-sm 10px` | `10px` / `6px` + new `--radius-pill 30px` |
| `--aru-*` (2 layers) | deleted; all uses remapped to global tokens |

## 9. Open items for the client

1. **Visual sign-off** of the token pass (and of the F1 navigation) — nothing here is approved.
2. **Non-text contrast** of form-control borders (§2.3) — needs a UX decision.
3. **Poppins**: ship it or drop it from the municipal parity list (§3).
4. Text over the landing photographs: the veil hue changed (teal → institutional blue) and the
   rendered result was not measurable from computed styles (§6).
