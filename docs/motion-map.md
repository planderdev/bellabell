# Motion map

## Current: revision 3

Current configuration is in [메인 모션 고도화 보고서](motion-upgrade.md).

| Pin ID | start | end | scrub | pin / pinSpacing |
| --- | --- | --- | --- | --- |
| hero-journey | top top | measured section height × 1.65 | .55 | true / true |
| philosophy | top top | measured section height × 3.2 | .60 | true / true |
| ingredients | top top | max(height × 1.4, horizontal travel × 1.05) | .60 | true / true |
| technology | top top | measured section height × 2.6 | .55 | true / true |
| brands | top top | measured section height × 1.8 | .60 | true / true |

Five sibling timelines; zero nested pins. Each controls multiple visual elements, chapter state and progress where applicable. Desktop only (≥1024px); mobile and reduced motion use source flow. Lenis shares one GSAP ticker; refresh synchronizes Lenis size and chapter semantics.

## Mobile motion layer (<1024px) — revision 4

`assets/js/mobile-motion.js` runs inside `gsap.matchMedia('(max-width:1023px)')` and is the complement of the desktop pin breakpoint. No pins, no Lenis, no horizontal travel: every effect is transform, opacity or clip-path, so content stays in source order and scroll remains native. Reduced motion skips the whole layer, as on desktop.

| Section | Trigger | Motion |
| --- | --- | --- |
| Hero | `.hero` top top → bottom top, scrub .4 | poster scale 1.12 drifts +7% Y; content lifts 48px and dims; scroll hint fades |
| Introduction | `.introduction` top 78%, once | label/title/lines/copy/link rise 40px with .1s stagger; floating photo rises and settles at −4° |
| Philosophy | each `.philosophy-scene` top 75%, once | image opens from the bottom (clip inset), img scale 1.18→1.08, word then title/copy rise; img parallax −4%→+4% over the scene; sticky chapter counter 01→03 switches at scene middle; progress bar scrubs over the stage |
| Ingredients | each `.ingredient-card` top 82%, once | image opens from the bottom, img 1.15→1.06, number slides from +24px, category and copy stagger in |
| Research | each `.research-scene` top 80%, once | image opens upward, img settles from +10% Y, copy rises; step track fills per stage (reverses on leave-back) |
| Brands | `.brands-section` top 70%, once | each panel's artwork opens from a rounded aperture, image settles, copy staggers; Swiper navigation unchanged |
| Sustainability | `.sustainability-image` top 85% → top 30%, scrub .5 | rounded 6% inset opens to full bleed, img 1.15→1; copy rises once at top 55% |
| Global | `.global-section` top bottom → bottom top, scrub .6 | background −8%→+8% Y at scale 1.12; content drifts upward |

Detail pages share the same layer (the module runs on every page and skips sections that are absent):

| Page / element | Trigger | Motion |
| --- | --- | --- |
| Company photo fan | `.company-fan` top 90% → bottom 30%, scrub .6 | three photos rise from +40px and spread: rotation ×1.6 from the CSS base, outer photos drift ∓20px, centre lifts 16px |
| Story cards (company, technology) | each `.detail-story-card` top 85%, once | image opens from the bottom, img 1.15→1.05, label/title/copy stagger; img parallax −3%→+3% across the card |
| Portfolio / ingredient index / impact value media | each box top 85%, once | aperture from the bottom with img 1.15→1.05, on top of the existing card fade; portfolio arrow pops in (back.out) |
| Sustainability cover | `.impact-image` top 90% → top 30%, scrub .5 | rounded 6% inset opens to the box edge, img 1.15→1 |
| Partners, news items, FAQ items, CTA, contact | each list/item top 85–92%, once | rise 24px with stagger |

CSS support lives at the end of `assets/css/home.css`: the chapter counter becomes `position:sticky` under the philosophy stage and the research step track is displayed in flow. `tests/motion.mjs` covers the reduced-motion no-op and the breakpoint string.

The earlier fade/card-slide/video settings below are retained only as the **pre-upgrade comparison**. They are superseded by revision 3.

---

The settings below describe this original implementation.

| Section | Trigger / start → end | Pin | Scrub / easing | Motion |
| --- | --- | --- | --- | --- |
| Hero entrance | DOM ready | No | 1s, stagger .13s, power3.out | HTML text and CTA translate 32px and fade in |
| Hero departure | top top → bottom top | No | scrub 1 / none | media scale 1→1.05 and +14% Y; content Y -100 and opacity .15 |
| Intro words | each line top 80% → top 50% | No | .5 / none | line opacity .2→1 |
| Intro flanking images | top bottom → bottom top | No | 1 / none | opposite ±80px / ±60px movement on desktop |
| Philosophy | top top → +240vh | Yes | .8 | 3 overlapping scenes; .85-unit clip inset, opacity, scale 1.12→1, text +36→0; reversible timeline |
| Ingredients | top top → max(90vh, travel×1.4) | Yes | .8 / none | X translation equals track.scrollWidth − viewport width; progress line |
| Research | top top → +180vh | Yes | .65 / power2.out | 3 scenes, image and copy fade/translate, active number |
| Brands | top top → +125vh | Yes | .75 / none | two wide panels, measured viewport X translation; previous/next buttons seek the chapter |
| Sustainability | top 70% → top 5% | No | 1 / none | photo clip inset 6%→0; content remains actual HTML |
| History | top 65% → bottom 35% | No | immediate active state | active year updates both forward/backward |
| Reveal | top 91% once | No | .9s / power3.out | opacity and Y, then clear inline properties |
| Detail company photo fan | top 78% → bottom 20% | No | 1 / none | outer photo rotation ±9→±16°, outward translation; center rises 24px |
| Detail company/research story | top top → max(160vh, horizontal travel×1.6) | Yes | .7 / none | photo/copy cards move horizontally; chapter count and progress line follow timeline |

## Responsive and accessibility

- ≥1024px: four distinct GSAP timelines with ScrollTrigger `pin`, `scrub` and dynamic end distances.
- <1024px: no pinned scenes; ordered content flow; brands use Swiper with swipe, keyboard and previous/next controls.
- Reduced motion: operating system preference removes timeline/reveal/parallax/smooth-scrolling effects; all story panels remain available in source order; video pauses. The manual footer button and its stored preference were removed in revision 2 as requested.
- Inactive pinned panels are `inert` and `aria-hidden`; cleanup restores them after breakpoint/motion changes.
- Video is muted, looping and inline; pause control; offscreen and hidden-tab pause. Mobile/data-saving starts with the still poster.
- Lenis uses GSAP's ticker only, converting seconds to milliseconds. No duplicate RAF loops. Lenis integration follows https://github.com/darkroomengineering/lenis.
- Fonts and window load trigger ScrollTrigger refresh. Images reserve their dimensions; lazy image loads do not interrupt active timelines. GSAP matchMedia reverts desktop timelines and styles.
- No scroll-position state in a rendering framework; no raw scroll event animation loop.
- Detail FAQ uses native `details`/`summary`; category filters update visible items and close hidden answers. Toggle/filter changes refresh layout measurements. Mobile detail story cards stack vertically and do not pin.
