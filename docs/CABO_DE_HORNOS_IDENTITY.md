# Cabo de Hornos Identity Study — Evidence & Token Proposal

**Date:** 2026-11-08 · **Status:** documentation only — **the proposed design direction is NOT implemented and NOT approved.**

Goal: ground the `otrn` design of this clone in the official visual identity of the
**Municipalidad de Cabo de Hornos y Antártica**, verified from the client's live sites,
before any CSS change. This mirrors the Arauco workflow (audit → client sign-off → implement).

## Sources (verified live, 2026-11-08)

| Source | What was verified |
| --- | --- |
| `https://imcabodehornos.cl/` (municipal portal) | Full inline CSS read; desktop screenshot + mobile confirmation (brand, ribbon, slider) |
| `https://visitacabodehornos.cl/` (Oficina de Turismo portal — direct sibling of the observatory) | Full inline CSS read (head), structure of sections |
| Both sites read with full inline CSS; both are WordPress block themes over Bootstrap 5.3.2 (Owl/bx sliders, Formidable forms) — same builder family as the `otrn` parent context | — |

## 1. Verified identity elements (municipal site)

### 1.1 Palette

| Role | Value | Evidence |
| --- | --- | --- |
| Corporate blue (primary) | `#0071BC` | Titles, buttons (`.btn-blue`, `.bg-blue`, `txt-blue`), links, pagination current |
| Deep blue (hover/dark) | `#00518A` | Button hover, banner gradient stops `rgba(0,81,138,.90)` → `rgba(0,113,188,.58)` |
| Mid blue | `#005C98` | Gradient middle stop `rgba(0,92,152,.72)` |
| Amber accent | `#FFB800` | Menu hover underline (3px→70% width), eyebrow labels, badges (`.cdh-diario-ed`, text `#3a2c00` on it), dots `#ffcb46` |
| Green accent (hover) | `#5A9E22` | Menu link hover text |
| Light-blue tint (bg) | `#EAF2F9` | Category chips, pagination hover |
| Light-blue tint (border) | `#D6E3EE`, `#E7ECF1` | Pagination border, card border |
| Muted text | `#5A6B7B`, `#1D2733` | Subtitles, card titles |
| Base/canvas | `#F9F9F9` body, `#FFFFFF` cards | — |
| **Multicolor ribbon (signature band)** | `linear-gradient(90deg, #A9C33A 0–25%, #EC008C 25–50%, #27AAE1 50–75%, #F15A29 75–100%)`, 6px high | Under the header and on top of the slider of every page + each interior banner (`.banner::after`); screenshot-confirmed under the header |

The ribbon (green·magenta·cyan·orange) reads as the comuna's brand signature; it is the one
element repeated on every page of the municipal site.

### 1.2 Typography

- **Gotham Narrow** (Light 300/400, Bold 700, Ultra 900), self-hosted OTF in the client's uploads:
  `/wp-content/uploads/2024/03/GothamNarrow-{Light,Bold,Ultra}.otf`. Forced via `body,h1…a`
  in both sites (with `!important`).
- Fallback/secondary: **Roboto** (Google Fonts, municipal site), **Prompt** (Google Fonts,
  tourism site — used for the big display headings up to ~100px).
- ⚠️ **Licensing caveat:** Gotham is a commercial typeface (Monotype/Hoefler). We must NOT copy
  the OTF files into our repo without the client's licence/consent. Free substitutes to propose:
  **Montserrat** (already self-hosted in `otrn/assets/fonts`, closest geometric match) as body,
  and **Prompt** (OFL) for display, matching the tourism site's display font.

### 1.3 Components (municipal site)

| Component | Verified spec |
| --- | --- |
| Buttons | Pill, 50px radius; `#0071BC` → hover `#00518A`; ghost variant white-border → hover fills blue; height ~40px |
| Cards | Radius 14px, white, border `#E7ECF1`, shadows `0 10px 26px rgba(0,81,138,.14)`, hover lift `translateY(-6px)` + `0 18px 42px rgba(0,81,138,.22)` |
| Card titles | `#0071BC` bold |
| Interior banner (page hero) | Photo background + blue linear gradient L→R `rgba(0,81,138,.90) → rgba(0,113,188,.58)` + 6px multicolor ribbon on top; `min-height:110px` mobile |
| Section headings | Regular weight + highlighted word bold in `#0071BC` (`.fw-bold`) |
| Pagination | 42px pill numbers, border `#D6E3EE`, hover bg `#EAF2F9`, current bg `#0071BC` white text |
| Forms (Formidable) | Focus border `#0071BC` + ring `rgba(0,113,188,.18)`; submit = blue pill, hover `#00518A` |
| Header | White; brand logo left (`Logo_UP_Cabo-de-Hornos_5-06-1-10.png`, multicolor "Crece" script + mountain + bird); nav links `#0071BC` w/ amber animated underline; utility strip links (PortalTransparencia, Ley Lobby) + date |
| Mobile menu | White fixed drawer from the right (70% width ≤500px, 40–60% tablet), opened by custom icon; links left-aligned `#0071BC` |
| Home hero slider | Full-bleed photo slider (bxSlider) capped at 1500px, white slider text with subtle text-shadow, ribbon on top |
| Special section (Diario Digital) | Blue gradient panel `135deg #0071BC → #00518A`, white text, amber eyebrow + badge, ghost CTA |
| Video cards (YouTube grid) | White rounded cards 16/9 thumb, blue play circle `rgba(0,81,138,.88)` → amber `#FFB800` on hover |
| Footer | Menu columns ("Menú", "Enlaces de interés"), contact info block (`municipalidad@imcabodehornos.cl`, `225881011`, "O'Higgins #189, Puerto Williams"), social icons (Facebook/Instagram/YouTube), partner logo row, `Copyright © Cabo de Hornos 2026` |

## 2. Tourism sibling (`visitacabodehornos.cl`) — the closer reference for the observatory

- Same theme family, same **Gotham Narrow**, same corporate blue `#0071BC` (`bg-blue`, `btn-blue`,
  `txt-blue`, `btn-blanco` text/border).
- **Accent is orange `#F98319`** (nav hover underline 4px full-width, `.cline` separator,
  `btn-naranja`, carousel arrows/dots, pagination current) — instead of the municipal amber.
- Hero/banner treatment differs: **dark photo overlay** (`linear-gradient(144.18deg,#000 20.96%,transparent)`)
  instead of the municipal blue gradient; display headings use **Prompt** up to 100px.
- Deep navy `#014A94` also present (`.btn-act`).
- Photo-led sections (480–842px), white pill contact button over transparent nav, language switcher pill.

**Design consequence for the observatory:** the site visually belongs to the *Oficina de Turismo*
family (orange accent, dark photo heroes) while remaining inside the municipal system (blue
primary, Gotham/Arena typography). The final accent direction (amber `#FFB800` vs orange
`#F98319`, and whether the multicolor ribbon is used) **is a client decision** — recorded as pending.

## 3. Proposed token mapping (`style.css :root`, current values → proposal)

Current as of commit `545d8e3`. **Proposal only — do not apply without approval.**

| Token | Current (Arauco) | Proposal (Cabo de Hornos) | Basis |
| --- | --- | --- | --- |
| `--primary` | `#0070FF` | `#0071BC` | Municipal corporate blue (verified) |
| `--primary-dark` | `#0050B8` | `#00518A` | Verified hover/dark |
| `--primary-soft` | `#E7F1FF` | `#EAF2F9` | Verified tint |
| `--primary-tint` | `#BBD8FF` | `#D6E3EE` | Verified border tint |
| `--secondary` | `#DE232B` (Arauco red) | `#F98319` (or `#FFB800`) | Tourism orange (verified) / municipal amber; client choice |
| `--accent` | `#41BCFF` | `#27AAE1` | Ribbon cyan (verified) |
| `--blue-dark` | `#304A83` | `#014A94` | Verified navy (tourism) |
| `--text2` | `#707070` | `#5A6B7B` | Verified muted |
| `--grey` (borders) | `#E0E0E0` | `#E7ECF1` | Verified card border |
| `--cream` / canvas | `#F8F8F8` | `#F9F9F9` | Verified body base |
| `--paper` | `#FFFFFF` | unchanged | — |
| `--radius` | `10px` | `14px` | Verified cards |
| `--radius-sm` | `6px` | `8px` (chips 12px img) | Verified images 12px |
| `--radius-pill` | `30px` | `50px` | Verified buttons/pages |
| Shadows | (Arauco set) | `0 10px 26px rgba(0,81,138,.14)` / hover `0 18px 42px rgba(0,81,138,.22)` | Verified cards |
| Title font | Montserrat (self-hosted) | Gotham Narrow ⚠️ licence / Montserrat provisional | Verified sites |
| Display font | (none dedicated) | Prompt (OFL, verified in use) or Montserrat Bold | Verified tourism hero |
| New: `--ribbon` | — | `linear-gradient(90deg,#A9C33A 0 25%,#EC008C 25% 50%,#27AAE1 50% 75%,#F15A29 75% 100%)` | Verified signature band |
| Status (`success/warning/error`) | unchanged | unchanged | Semantic, not brand |

## 4. Open items for the client (decisions, not design work)

1. **Direction**: municipal (amber + ribbon) vs tourism (orange + dark photo headers) vs hybrid.
2. **Typeface**: provide Gotham Narrow licence/files, or approve free substitutes (Montserrat + Prompt).
3. **Logos**: originals of the "Crece Ilustre Municipalidad" brand (header), the tourism-logo
   variant (`0-13.png` on the tourism site), and footer assets (UN Tourism, ZOIT, etc. if used).
   Never fabricated here.
4. **Ribbon**: confirm its use on the observatory (under header / atop hero) or reserve it for municipal portals.
5. Photography of the territory (Puerto Williams / Isla Navarino) to replace inherited Arauco images.

## 5. Suggested implementation plan (after approval)

- **Phase A — tokens only:** replace `:root` values per §3 (fonts pending §4.2); no component changes.
- **Phase B — chrome:** header (logo swap + nav affordances), footer (contact block, partner row),
  add ribbon element, onPage banners per photo-treatment decision.
- **Phase C — surfaces:** cards/buttons/pagination/forms to the verified specs; landing hero per tourism treatment.
- Per acceptance criteria: contrast checks (blue on white ok; verify white-on-#FFB800 text, etc.
  as in the Arauco audit), keyboard/CLS pass, then update VERIFICATION.md in this repo.
