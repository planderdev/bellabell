# BASE UI 디자인 시스템 교체

현재 스타일은 `Downloads/BASE-UI` 1.8.0 기반입니다. 프라이머리는 `#00AF84`, 컨테이너는 최대 `1620px`입니다. 일반 타이포·간격·공통 컴포넌트는 BASE UI를 사용하며 메인 페이지의 대형 디자인 타이포는 기존 크기를 유지합니다. 기존 CSS 덮어쓰기 파일을 제거했으며, 메인과 서브페이지 스타일을 분리했습니다.

[디자인 시스템](docs/design-system.md) · [QA 보고서](docs/qa-report.md)

토큰/공통 UI 재생성: `npm run design-system`. 검사: `node tests/design-system.mjs`.
기존 5개 GSAP 시퀀스의 동작 구조는 유지되며 회귀 검사는 `node tests/motion.mjs`입니다. 이전 모션 구현 기록은 [3차 보고서](docs/motion-upgrade.md)에 보관합니다.

---

# BELLABELL FARM

벨아벨팜 기업소개 웹사이트. PHP 서버 렌더링, 한국어·영어·중국어 간체, GSAP ScrollTrigger, Lenis, Swiper, Lucide로 구현했습니다.

## 로컬 실행

PHP 8.2 이상이 필요합니다. 이 작업 환경에서는 PHP 8.5.5로 검증했습니다. PHP의 OpenSSL 확장을 활성화하세요.

```powershell
npm ci
composer install --no-dev --optimize-autoloader
php -S 127.0.0.1:8080 router.php
```

현재 Windows 환경에서 OpenSSL이 기본 비활성화되어 있으면:

```powershell
C:\php\php.exe -d extension=openssl -d extension=mbstring -S 127.0.0.1:8080 router.php
```

Composer 명령이 없고 이번 작업에서 받은 공식 PHAR를 사용하는 경우:

```powershell
C:\php\php.exe -d extension=openssl -d extension=mbstring tools/composer.phar install --no-dev --optimize-autoloader
```

브라우저에서 <http://127.0.0.1:8080/ko/>를 엽니다. `/en/`, `/zh-CN/`도 제공합니다. FAQ를 포함한 언어별 10개 페이지, 총 30개 페이지가 있습니다. 정적 브라우저 라이브러리는 `assets/vendor/`에 포함되어 있어 기본 화면 실행에 Node 빌드 서버는 필요하지 않습니다.

## 구성

| 경로 | 역할 |
| --- | --- |
| `index.php`, `company.php` 등 | 페이지 진입점 |
| `router.php`, `.htaccess` | 언어 경로 및 서버 라우팅 |
| `includes/` | 공통 레이아웃, 번역 함수, 문의 처리 |
| `sections/` | 홈의 10개 콘텐츠 구간 |
| `data/ko.php`, `en.php`, `zh-CN.php` | 동일한 키 구조의 문구, 오류 메시지, 대체 텍스트 |
| `data/details-*.php` | 번역 배열에 포함되는 개편 상세 페이지 및 FAQ 문구 |
| `data/company.php` | 브랜드, 사업, 원료, 연혁, 소식 및 공개 상태 |
| `data/media.php` | 로컬 미디어 경로와 크기 |
| `assets/css/`, `assets/js/` | 디자인 및 인터랙션 |
| `tests/` | 서버 검증 및 로컬 SMTP 통합 검사 |

문구는 해당 언어 배열에서 수정합니다. 기록은 `status => published`만 표시되며, 증거가 없는 특허 항목은 `draft`로 유지합니다. 레이아웃 파일에 기업 사실을 직접 추가하지 마세요.

현재 디자인은 제공된 SVG 로고, 화이트 배경, BASE UI 토큰과 프라이머리 `#00AF84`를 사용합니다. 다크모드 및 모션 토글 버튼은 제거했습니다. 운영체제의 모션 최소화 설정은 자동으로 존중합니다.

## 문의 메일 설정

`.env.example`을 `.env`로 복사하고 실제 도메인, 발신 주소 및 SMTP 환경을 설정합니다. `.env`는 Git에 포함되지 않습니다.

- `MAIL_TRANSPORT=smtp`, `SMTP_HOST`, `MAIL_FROM`이 필요합니다.
- 수신 주소 기본값: `partner@jejubellabell.com`.
- `SMTP_ENCRYPTION=tls`, 포트 587 또는 서비스에 맞는 SSL/465를 사용합니다.
- SMTP 계정은 발신 도메인에서 발송 권한을 가진 계정이어야 합니다.
- 설정이 없을 때는 전송 버튼을 비활성화하고 공식 이메일로 안내합니다. 성공한 것처럼 처리하지 않습니다.
- IP 해시를 사용하는 요청 제한 파일은 웹 루트 밖에 둡니다. 다중 인스턴스 운영 시 공유 저장소 또는 프록시 수준 제한을 별도로 적용해야 합니다.

로컬 테스트에서는 외부로 전송하지 않고 `127.0.0.1` SMTP 테스트 서버에서 합성 메일 1건을 받았습니다. 실제 SMTP 인증, 외부 수신함 도달 및 SPF/DKIM/DMARC는 운영 환경에서 확인해야 합니다.

## 검사

```powershell
php tests/run.php
npm run check
python tests/http_check.py
python tests/smtp_integration.py
```

HTTP 검사는 8080 로컬 서버가 실행 중이어야 합니다. SMTP 검사는 별도 로컬 PHP/SMTP 프로세스를 시작하고 종료합니다.

## 문서

- [디자인 방향](docs/design-system.md)
- [모션 구현 지도](docs/motion-map.md)
- [콘텐츠 및 사실 근거](docs/content-map.md)
- [미디어 출처](docs/media-sources.md)
- [검수 결과](docs/qa-report.md)
- [운영 배포 안내](docs/deployment.md)

로컬 검수용 결과물입니다. 실제 공개 배포 전에는 운영 도메인과 SMTP 설정, 회사의 개인정보 처리 운영 기준 및 최신 연락처를 반영하세요.
