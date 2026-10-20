# Motion map

## Current: mobile cinematic update

The same five scene timelines now run on desktop, portrait mobile and compact viewports at least 600px tall. Compact layouts use native scrolling, stable `100svh` stages and a shorter `.35` scrub. Desktop distances and choreography remain unchanged.

| Pin ID | Desktop end | Compact end | start | pin / pinSpacing |
| --- | --- | --- | --- | --- |
| hero-journey | H × 1.65, combined hero/intro | H × .9, hero only | top top | true / true |
| philosophy | H × 3.2 | H × 2.1 | top top | true / true |
| ingredients | max(H × 1.4, travel × 1.05) | max(H × 1.25, travel × 1.2) | top top | true / true |
| technology | H × 2.6 | H × 1.8 | top top | true / true |
| brands | H × 1.8 | H × 1.25 | top top | true / true |

H is the measured stage height; travel is the gallery's actual overflow width. Pins never nest. Mobile hero image scale/masking and opposite brand-line travel lead into an unpinned, readable introduction with synchronized text/photo parallax. Philosophy keeps all three masked scenes. The ingredient gallery translates horizontally from vertical touch scrolling; research and brands stack their media and copy vertically. Brand buttons seek the same timeline on both layouts.

Mobile intro, sustainability and the final partnership section also retain scroll-linked motion. Native touch scrolling is preserved: no touch interception, forced snapping or mobile Lenis. `ignoreMobileResize` avoids refresh jumps from browser chrome; width/orientation changes rebuild scenes via GSAP matchMedia. Portrait eligibility does not depend on a height threshold, so browser chrome cannot toggle pins off. Only compact landscape viewports below 600px tall return to readable flow and a brand Swiper. Reduced motion removes pins and restores every panel regardless of width.

Lenis remains desktop-only on a single GSAP ticker. Fonts/load refresh layout and chapter semantics. The [3차 보고서](motion-upgrade.md) records the earlier desktop implementation.

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
