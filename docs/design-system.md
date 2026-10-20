# BELLABELL FARM — BASE UI 1.8.0

2026-10-20 교체 기준. 이 문서가 현재 디자인 시스템의 기준이며, 이전 개편 보고서의 팔레트·타이포·CSS 파일 목록보다 우선합니다.

## 원본과 생성

사용자 지정 원본: `C:/Users/pc/Downloads/BASE-UI` (1.8.0).

- `design-system/base-ui/tokens.json`: 원본 파운데이션·시맨틱·타이포·반응형 데이터.
- `design-system/base-ui/components/`: 실제 사용하는 9개 원본 모듈. 기본 리셋, 버튼, 칩/필터, 카드/미디어, 아코디언, 입력, 선택 컨트롤, 모션, 접근성.
- `design-system/base-ui/brand-source.js`: BASE UI의 원본 브랜드 계산 함수.
- `tools/build-design-system.mjs`: 위 소스를 이용해 `assets/css/tokens.css`와 `assets/css/ui.css` 생성.
- 실행: `npm run design-system`. 생성물 일치 검사: `npm run design-system:check`.
- 원본 Downloads 폴더는 수정하지 않음. 라이선스는 NOTICE와 폰트·아이콘 고지에 보존.

## 적용 토큰

라이트 테마 494개 토큰을 단일 `:root`에 선언합니다. `brand.css`, 추가 `:root` 패치, 런타임 스타일 주입은 사용하지 않습니다. 브랜드 관련 9개 값은 원본 계산 함수에 `#00AF84`를 넣어 생성하며, 나머지 토큰은 원본 값과 같습니다.

| 항목 | 현재 값 |
| --- | --- |
| Primary | `#00AF84` |
| Primary 버튼 글자 | `#000000` — BASE UI 대비 계산 결과 |
| 강조 글자 | `#008766` — BASE UI의 라이트 배경용 잉크 |
| 본문 | `#191F28` |
| 보조 글자 | `#6B7684` |
| 기본 배경 / 보조 배경 | `#FFFFFF` / `#F2F4F6` |
| 컨테이너 | 최대 1100px, 내부 여백 PC 24px / 모바일 16px |
| 제목 display-1 | PC 56/72px → 모바일 32/42px |
| 본문 | 16/24px, 읽기용 16/26px |
| 모서리 | xs 4, sm 8, md 12, lg 16, xl 24px |
| 컨트롤 | 기본 버튼 48px, 입력 52px, 모바일 칩 최소 44px |
| UI 전환 | 120 / 200 / 320ms, BASE UI 표준 easing |

상태색(성공·주의·오류)과 원자 팔레트는 BASE UI 값을 사용합니다. 원본 SVG 로고와 사진은 브랜드 자산이며 UI 토큰과 구분합니다.

## CSS 책임

공통: `tokens.css` → `ui.css` → `base.css` → `layout.css`.

메인에서 `home.css`, 서브페이지에서 `details.css` 하나만 추가합니다. 외부 Lenis/Swiper CSS는 공통 스타일보다 먼저 로드합니다.

- `base.css`: 문서 의미, 공통 타이포, 컨테이너, 텍스트 링크.
- `layout.css`: 헤더, 메뉴, 언어 선택, 푸터, 공용 연혁.
- `home.css`: 메인 구성·반응형·핀 활성 상태.
- `details.css`: 서브페이지 구성·반응형.

이전 `components.css`, `responsive.css`, `animations.css`, `home-motion.css`는 삭제했습니다. 기존 토큰 이름을 새 토큰으로 우회 연결하는 호환 별칭도 추가하지 않았습니다. 페이지 CSS에는 직접 작성한 HEX/RGB 색상이 없습니다.

## 기능 및 접근성

기존 PHP 라우팅, 3개 언어, 폼 필드/서버 검증, FAQ/소식 필터와 GSAP 5개 타임라인을 사용합니다. 폼·카드·칩·아코디언은 BASE UI 클래스 구조로 교체했습니다. UI 전체를 다시 초기화하는 BASE UI JS는 추가하지 않았습니다. 기존 기능의 이중 이벤트 등록을 피하기 위해 네이티브 컨트롤과 현재 JS가 동작을 담당합니다.

BASE UI 기본 소스의 포커스 제거 규칙을 프로젝트 소스에서 수정해 키보드 포커스 2px 링을 유지했습니다. 소스의 입력 `outline: none !important`도 해제했습니다. 모션 마스크 모서리는 토큰을 읽고, 모바일 간격보다 컸던 가로 진입 이동은 `space-12`로 변경했습니다.

타이포·간격은 BASE UI의 768–992px 유동 규칙을 사용합니다. 긴 GSAP 핀의 기능 분기점은 기존 1024px로 유지합니다. 운영체제 reduced-motion 설정을 사용하며 별도 테마·모션 버튼은 없습니다.

## 검증

`node tests/design-system.mjs`: 토큰 원본 일치, 단일 선언, 기본 대비, 이전 CSS 제거 검사 502개.
전체 브라우저 및 기능 검수 결과는 `docs/qa-report.md`의 4차 개편 항목을 참고하세요.
