> 2026-10-20 BASE UI 교체 이후의 현재 시각 규격·파일 구조는 [디자인 시스템](design-system.md)을 따릅니다. 아래는 3차 모션 구현 당시의 기록입니다. `home-motion.css`는 이후 `home.css`로 재작성되었습니다.

# 메인 모션 고도화 보고서 — 3차 개편

2026-10-20. 기존 PHP 사이트를 수정했습니다. 새 사이트나 새 라우팅을 만들지 않았습니다.

## 구현 전후

| 구간 | 이전 | 변경 후 |
| --- | --- | --- |
| 히어로·인트로 | 이미지와 카피가 별도 트리거로 이동한 뒤 일반 스크롤 | 제주 사진 확대 → 사진이 왼쪽 프레임으로 축소 → 오른쪽 기업 메시지와 작은 반려동물 사진 등장. 하나의 고정 구간 안에서 구성 변화 |
| 브랜드 철학 | 장면 전체 페이드, 별도의 진행 표시 트리거 | 이전 풍경을 유지하면서 다음 이미지의 작은 마스크가 전체 화면으로 확장. 타이포·본문·이미지·진행 표시를 단일 타임라인으로 제어 |
| 제주 원물 | 작은 카드 트랙 이동 | 큰 이미지, 초대형 번호, 분류·설명·링크를 가진 시네마틱 트랙. 내부 이미지와 번호에 반대 방향의 움직임 적용 |
| 연구·기술 | 이미지와 카피가 한 카드로 페이드 교체 | 왼쪽 제목·단계 설명, 오른쪽 고정 미디어 프레임. 이미지 마스크·scale·Y와 카피의 clip·Y 동기화 |
| 브랜드 | 두 배 너비 트랙을 좌우 이동 | FROM HALLA 이미지 확대와 제목 퇴장 → AOUAOU 이미지 창 확장 → 배경·제목·카피 전환. 버튼도 같은 타임라인 탐색 |
| 보조 구간 | 반복되는 fade-up | 지속가능경영의 사진·메시지 연결, 연혁의 진행선·제목, 파트너십의 배경·전경 깊이, 카드 이미지 마스크, 타이포 wipe로 분리 |

히어로는 기존 2400×1351 제주 고해상도 사진을 사용합니다. 제주 촬영으로 확인되지 않은 기존 일반 바다 영상은 히어로에서 제외하고 파일만 보존했습니다. 로고 SVG, 화이트 바탕, 프라이머리 `#007572`, 다국어 문구와 출처는 유지했습니다.

## 실제 GSAP 구성

아래 다섯 타임라인은 모두 데스크톱 `min-width:1024px`에서만 생성됩니다. 공통 설정은 `start:'top top'`, `pin:true`, `pinSpacing:true`, `anticipatePin:1`, `invalidateOnRefresh:true`입니다. 서로 형제 요소이며 중첩 핀이 없습니다. H는 짧은 화면의 최소 높이까지 포함한 해당 요소의 실제 `offsetHeight`입니다.

| ID | end | scrub | 같은 타임라인에 연결한 요소 |
| --- | --- | --- | --- |
| `hero-journey` | H × 1.65 | .55 | 제주 사진 scale 1→1.17, 브랜드 두 줄의 서로 다른 이동, 제목 wipe, 미디어 inset, 인트로 문장·작은 사진 |
| `philosophy` | H × 3.2 | .60 | JEJU 배경 → t=.6 연구 마스크 → t=2.55 웰니스 마스크 → t=4.15 최종 장면. 타이포·본문·카운터·진행선 포함, 전체 4.75 단위 |
| `ingredients` | max(H × 1.4, 실제 가로 이동량 × 1.05) | .60 | 트랙 X, 이미지 scale 1.17→1.08 및 X −3%→3%, 번호의 반대 이동, 진행선. 전체 3 단위 |
| `technology` | H × 2.6 | .55 | t=.65 / 2.35 미디어 wipe, 이미지 scale·Y, 이전 이미지 이동, 왼쪽 카피 clip·Y, 단계 번호. 전체 4.1 단위 |
| `brands` | H × 1.8 | .60 | 사진 확대, 제목·본문 퇴장, 두 번째 브랜드 마스크와 배경 확장, 새 제목·본문, 진행선. 전체 2.65 단위 |

히어로 진입 효과는 내부 글자 span에만 적용합니다. 스크롤은 외부 래퍼를 제어하므로 빠른 첫 스크롤에서도 같은 속성을 덮어쓰지 않습니다.

`chapterAt()`은 방향 이벤트가 아닌 현재 재생 시간으로 활성 장면을 계산합니다. 철학 경계는 1.28 / 3.23, 연구는 1.1 / 2.8, 브랜드는 1.3입니다. 비활성 장면의 `inert`·`aria-hidden`은 상태 변경 시에만 갱신합니다. GSAP refresh가 콜백 없이 렌더링한 뒤에도 별도 refresh 리스너가 번호와 접근성 상태를 맞춥니다.

## 네 가지 모션 계층

1. **Cinematic:** 히어로, 철학, 원물, 연구, 브랜드. 고정·스크럽·마스크·미디어 변형.
2. **Narrative:** 인트로는 히어로와 연결. 지속가능경영은 마스크·내부 확대·메시지 이동, 연혁은 진행선·제목 정렬, 글로벌 제휴는 배경·전경의 반대 이동.
3. **Reveal:** 보조 제목은 타이포 wipe, 사업 카드는 이미지 마스크·내부 확대·메타정보 이동. 같은 fade-up을 반복하지 않음.
4. **Micro:** 버튼 2px 이동, 화살표 회전·그린 배경, 메뉴 강조, 기존 키보드 포커스 표시. hover 가능한 환경에서만 적용.

## 수명주기·탐색·성능

- Lenis는 `autoRaf:false`와 GSAP ticker 한 개로 동작합니다. 초→밀리초 변환, ScrollTrigger update 구독 한 개를 유지하며 refresh 시 Lenis 크기도 갱신합니다. destroy 시 모두 해제합니다.
- 핀은 문서 순서대로 생성하고 이후 보조 효과를 초기화합니다. GSAP matchMedia가 크기 변경 시 되돌림을 담당합니다.
- 폰트 로드, window load, bfcache pageshow에서 필요한 refresh를 실행합니다. 고정 미디어 박스가 높이를 확보하므로 각 lazy image마다 무조건 refresh하지 않습니다.
- 초기 앵커·hashchange를 측정된 핀 좌표로 변환합니다. 인트로 앵커는 첫 타임라인 끝, 브랜드 앵커는 해당 장면 시작/끝으로 이동합니다.
- 같은 페이지 링크의 키보드 포커스는 Lenis 이동 완료 후 전달합니다. 뒤로 가기에서는 초기 hash보다 브라우저의 이전 위치 복원을 우선합니다.
- 모바일·태블릿에서 핀은 0개입니다. 원물은 태블릿에서 기본 가로 탐색, 768px 미만에서는 세로 흐름입니다. 브랜드는 기존 Swiper의 스와이프·키보드·이전/다음 버튼을 사용하며 자동재생하지 않습니다.
- OS `prefers-reduced-motion`에서는 GSAP·Lenis·Swiper를 생략하고 모든 콘텐츠를 원래 순서로 제공합니다. 수동 모션·다크모드 버튼은 추가하지 않았습니다.
- 큰 이미지의 `will-change:transform`은 현재 활성 핀에만 적용합니다. 큰 blur/filter, 레이아웃 크기 애니메이션, 별도 상시 RAF는 사용하지 않습니다.

## 검수 중 수정한 문제

- 히어로 프레임이 아직 넓을 때 인트로 글자가 겹치는 문제: 글자 진입 시점을 늦춤.
- 브랜드 이전 제목이 다음 배경 위에 남는 문제: 패널의 쌓임 순서를 명시하고 이전 제목의 퇴장을 타임라인에 포함.
- 이미지 parallax 끝에서 가장자리가 드러날 가능성: 최소 scale을 1.08로 유지.
- 직접 브랜드 앵커로 초기 진입할 때 ScrollTrigger가 이미 지난 `once` 트리거를 측정 도중 삭제하여 초기화가 중단되는 문제: `toggleActions:'play none none none'`으로 한 번 재생하되 측정 목록에서는 유지. 수정 후 18개 언어·뷰포트 조합의 직접 링크 재검사 통과.
- 짧은 1024px 영문 화면에서 브랜드 제목과 상단 설명의 간격: 타이틀 위치 조정.

## 검증 결과와 한계

실제 결과와 미검증 항목은 [QA 보고서](qa-report.md)에 정리했습니다. 주요 수치는 6개 너비 × 3개 언어의 홈 18조건, 연구 레이아웃 18조건, 브랜드 직접 링크 18조건입니다. 이는 모든 프레임·기기의 성능 보증을 의미하지 않습니다.

## 변경 파일 전체

| 역할 | 파일 |
| --- | --- |
| 메인 조합·스타일 로딩 | `index.php`, `includes/head.php` |
| 메인 템플릿 | `sections/hero.php`, `sections/introduction.php`, `sections/philosophy.php`, `sections/ingredients.php`, `sections/technology.php`, `sections/business.php`, `sections/brands.php`, `sections/sustainability.php`, `sections/history.php`, `sections/global.php` |
| 모션·스크롤·탐색 | `assets/js/animations.js`, `assets/js/pin-scroll.js`, `assets/js/smooth-scroll.js`, `assets/js/navigation.js`, `assets/js/main.js`, 신규 `assets/js/scroll-target.js` |
| 스타일 | `assets/css/animations.css`, 신규 `assets/css/home-motion.css` |
| 회귀 검사 | 신규 `tests/motion.mjs` |
| 문서 | `README.md`, `docs/motion-upgrade.md`, `docs/motion-map.md`, `docs/design-system.md`, `docs/media-sources.md`, `docs/qa-report.md` |

`data/*.php` 번역·회사 정보, PHP 라우터, 문의 메일 수신 주소 및 SMTP 설정은 변경하지 않았습니다.
