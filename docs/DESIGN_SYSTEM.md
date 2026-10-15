# Design System

Description of the **implemented** visual system as of commit `5f43933`, plus the
**uncommitted** working-tree deltas (clearly marked). The proposed municipal identity is
described only to compare against; **it has not been approved and is not implemented**.
Runtime pass 2026-10-15: the F6 token divergence and the F1/F5 defects are confirmed
([VERIFICATION.md](VERIFICATION.md)); the full visual review is still pending.

## 1. Sources of truth — and their gaps

- Code comments reference `docs/design-tokens.md` (`style.css:11`) but that file does not
  exist anywhere in the repository (F15).
- Three token layers currently coexist (F6):
  1. `style.css:16-25` — global `:root` tokens (committed values; see §2).
  2. `assets/css/otrn-arauco.css:17-46` — `--aru-*` tokens scoped to `.otrn.home`,
     loaded **only on the front page**; its own header comment states it is an
     *interpretation* of the approved photographs and **pending client confirmation**
     (`otrn-arauco.css:1-17`).
  3. `assets/css/otrn-landing.css:25-31` — an **uncommitted** unscoped `:root` fallback
     duplicating `--aru-*` names with *different values* while claiming to be "idénticos
     al sistema" (see §2.3).

## 2. Actual tokens

### 2.1 Global tokens — `style.css:16-25` *(working tree)*

| Token | Value | Notes |
| --- | --- | --- |
| `--primary` / `--primary-dark` / `--primary-soft` / `--primary-tint` | `#DF8056` / `#A05C3D` / `#FAEEE9` / `#F0C5B2` | Terracotta family |
| `--secondary` / `--secondary-soft` | `#2F6F79` / `#E3ECED` | Teal |
| `--blue-dark` / `--blue-dark-soft` | `#0E506D` / `#DFE8EC` | Deep blue |
| `--dark` | `#2B2B2B` | **Uncommitted fix**; committed value was `#333333` |
| `--text2` / `--text3` | `#666666` / `#888888` | Secondary text |
| `--grey` / `--cream` / `--lgrey` | `#E8EEE6` / `#F6F3E0` / `#F0F0F0` | Surfaces/borders |
| `--error` | `#EA5252` | Errors |
| `--paper` | `#FFFFFF` | Background |
| `--radius` / `--radius-sm` | `14px` / `10px` | Radii |
| `--font-title` | `'Montserrat',system-ui,sans-serif` | **Uncommitted addition**; see §3 |

### 2.2 Landing tokens — `otrn-arauco.css:17-46` (committed)

Scoped to `.otrn.home`, loaded only on the front page:

- Ocean: `--aru-teal:#2F6F79`, `--aru-teal-deep:#145E6B`, `--aru-teal-soft:#E3ECED`,
  `--aru-ink:#0E506D`, `--aru-ink-deep:#0A2E3C`.
- Earth: `--aru-terra:#DF8056`, `--aru-terra-deep:#A05C3D`, `--aru-terra-soft:#FAEEE9`,
  `--aru-terra-tint:#F0C5B2`.
- Sand/neutrals: `--aru-arena:#F6F3E0`, `--aru-papel:#FFFFFF`, `--aru-texto:#2B2B2B`,
  `--aru-texto-2:#5A5A5A`, `--aru-texto-3:#8A8A8A`.
- Footer neutral: `--aru-footer:#F7F7F7` (client decision, per comment).
- Rhythm/radii: `--aru-gap:clamp(48px,8vw,120px)`, `--aru-radio:14px`, `--aru-radio-sm:10px`.

### 2.3 Uncommitted landing fallback — `otrn-landing.css:25-31`

Unscoped `:root` fallback with **different values** from §2.2 — e.g. `--aru-terra:#C0392B`
(vs `#DF8056`), `--aru-teal:#1A6B77` (vs `#2F6F79`), `--aru-ink:#0A2E3C` (vs `#0E506D`),
`--aru-texto-2:#5A6B70` (vs `#5A5A5A`), plus extra tokens `--aru-papel-2`, `--aru-linea`
and smaller radii (`10px`/`6px`). For elements inside `.otrn.home` the scoped §2.2 values
win; for anything outside that subtree the fallback applies. The comment claims the values
are identical to the system, which is not the case — this is issue F6.
**Runtime (2026-10-15)**: on `/`, `:root` resolves `--aru-terra:#C0392B` while `.otrn.home`
resolves `#DF8056` — both live simultaneously (confirmed).

### 2.4 Proposed municipal palette — **not implemented**

The brief's proposed colors `#0070FF`, `#41BCFF`, `#DE232B`, `#0C1434`, `#0C0D0E`,
`#D6D5D5` do not appear anywhere in the theme (grep). Only `#F7F7F7` survives, as the
landing footer neutral (§2.2). No evidence of formal approval exists in the repository;
treat this palette as **proposed only** (F2).

## 3. Typography

- Families: **Montserrat** (400/500/600/700/800) for titles/body and **IBM Plex Mono**
  (400/500/600/700) for figures, both self-hosted woff2 subsets with `font-display:swap`
  (`assets/css/fonts.css`, inventory in `assets/fonts/README.md`). No Google Fonts runtime
  dependency.
- Usage: `.otrn .mono` and stat/table figures use IBM Plex Mono; headings use
  `var(--font-title)` (heading weight 800, tight letter-spacing).
- **Historical defect (being fixed in the working tree)**: committed `style.css` declared
  `font-family:'Manrope',…` in several places while Manrope is **not shipped** — text fell
  back to `system-ui`. The uncommitted delta replaces Manrope with `var(--font-title)`
  (Montserrat) (`style.css:29-30, 98, 501`, `otrn-landing.css` title rule).

## 4. Components & conventions

- Naming: `otrn-` prefix, BEM-ish blocks (`otrn-act__eyebrow`, `otrn-rail__item`,
  `otrn-burger__line`). Layout container `.wrap` = max-width 1440 px, 32 px padding.
- Chrome: `.otrn-header` (72 px, border-bottom), `.otrn-brand`, `.otrn-nav`, `.otrn-login`,
  `.otrn-burger`; footer = logo strip (`--cream`) + copy line.
- Reusables: page head with `.eyebrow` chip; stat boxes (`.otrn-stats`/`.otrn-stat`);
  cards (`.otrn-lin-card`, `.otrn-indexcard`, news cards); buttons (`.otrn-btn`, `.ghost`
  variant on the eval form); tables (`.otrn-table`, with a `tarjetas-movil` mode that turns
  rows into labeled cards on small screens); provisional notice (`.otrn-provisional`);
  form controls on `/evaluar/`.
- Landing scene system: `.otrn-landing`, `.otrn-stage` (shared photographic level),
  `.otrn-act` scenes, `.otrn-rail` (01–05 indicator), `.otrn-reveal` (scroll reveal),
  `.otrn-home-menu`, burger classes for the mobile panel.

## 5. Responsive behavior (static analysis)

Breakpoints present in the CSS: 1440 (wrap), 1280, 980, 768, 600, 480, plus a
`prefers-reduced-motion` block (`otrn-landing.css:205-248`).

- **≤600 px**: `.otrn-nav{display:none}` (`style.css:508-509`). The intended replacement
  (burger + sliding panel) is defined **only** in `otrn-landing.css` (`:194, :213-224`),
  which loads only on the front page → internal pages lose navigation on phones (F1).
  **Runtime (2026-10-15)**: confirmed on internal *and* front pages (nav 0×0, `display:none`;
  the burger toggles state only).
- **≤768 px**: landing switches to static mode (scenes stack full-bleed); rail hidden.
- Grids collapse (data/news grids to 2 columns at 768, 1 at 480); head layouts stack at 980.
- `prefers-reduced-motion`: animations neutralized, reveals forced visible, nav transition
  off (`otrn-landing.css:241-248`).
- Unverified: real-device behavior and touch/keyboard interaction; the 601–768 px internal
  range was measured as visible-but-cramped (runtime 2026-10-15).

## 6. Scrollytelling architecture

- **Static mode (default)**: no JS / reduced-motion / ≤768 px — scenes stack with photos
  and legible text (`front-page.php:1-25`).
- **Immersive mode**: desktop + GSAP/ScrollTrigger; shared stage pinned with `scrub:1`,
  three crossfaded scenes, hero panel expanding to full-bleed, rail synced to scroll
  progress (`assets/otrn-landing.js:75-160`).
- Reveal system: IntersectionObserver adds `.is-visible`; CSS pre-hides with
  `.otrn-reveal{opacity:0}` (`otrn-landing.css:187-188`). **With JS fully disabled and no
  reduced-motion preference, revealed content stays invisible** (F5). Runtime (2026-10-15):
  confirmed — 9/9 hidden with scripts blocked; 3/9 reveal with JS in the control.
- Vendor: GSAP + ScrollTrigger self-hosted (`assets/vendor/`), loaded only on the front
  page (~115 KB noted in code comments); version fallback strings "3.12.5".

## 7. Discrepancies vs the proposed Arauco identity

| Aspect | Implemented | Proposed | Status |
| --- | --- | --- | --- |
| Palette | Teal/terracotta derived from photos (`--aru-*`, §2.2), explicitly "pending confirmation" | Blue/red municipal palette (§2.4) | Not implemented; approval unknown (F2) |
| Token layering | Three overlapping systems (§2.1–2.3) | One system | Conflict (F6) |
| Typography | Montserrat + IBM Plex Mono (matches brief) | Montserrat + IBM Plex Mono | Aligned |
| Institutional logos | 3 Arauco logos in footer; GORE low-res; RAU absent | Full quality set | Sourcing pending (F13) |
| Rapa Nui leftovers | Still present in copy/tokens/theme name | Arauco only | Migration pending (F7–F10, §ARAUCO_MIGRATION) |

## 8. Working-tree deltas relevant to design (uncommitted, do not lose)

- `style.css`: `--dark` `#333333`→`#2B2B2B`; Manrope→`var(--font-title)`; new `--font-title`.
- `otrn-landing.css`: adds the §2.3 fallback block; title rule now uses
  `var(--font-title,'Montserrat',…)`.
- `99-studio-loader.php`: machine-specific Studio temp path — unrelated to design.

See [HANDOFF.md](HANDOFF.md) for the exact diff summary.
