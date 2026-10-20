# 운영 배포

세 가지 배포 경로를 지원합니다. 어느 경로든 `router.php` 허용 목록이 모든 페이지 요청을 처리하고, `/healthz`가 상태 JSON을 반환합니다.

## Vercel (기본 경로)

`vercel.json`이 `api/index.php`를 `vercel-php@0.8.0`(PHP 8.4) 함수로 선언하고 `/assets/*`를 제외한 모든 경로를 그 함수로 보냅니다. 리전은 서울(`icn1`)입니다. 소스 파일(`includes/`, `data/` 등)은 정적 파일로 노출되지 않고 라우터에서 404로 처리됩니다.

```bash
vercel link            # 최초 1회, 팀/프로젝트 연결
vercel                 # 프리뷰 배포
vercel --prod          # 운영 배포
```

프로젝트 환경변수(Settings → Environment Variables):

| 변수 | 값 |
| --- | --- |
| `APP_ENV` | `production` (운영만. 프리뷰는 비워 두면 `noindex`) |
| `APP_URL` | 운영 도메인 `https://...` |
| `APP_SECRET` | `openssl rand -hex 32` 결과 |
| `TRUST_PROXY_HTTPS` | `1` (Vercel이 TLS를 종료하므로 필수) |
| `MAIL_*`, `SMTP_*` | 문의 메일 설정 |
| `DATABASE_URL` | PostgreSQL 연결 문자열 (선택, 권장) |

서버리스 특성상 PHP 세션과 로컬 파일은 인스턴스 간에 공유되지 않습니다. 문의 폼은 세션 없는 HMAC 토큰을 쓰며, 요청 제한과 토큰 1회성 검사는 `DATABASE_URL`이 있을 때 DB에서 공유됩니다. DB가 없으면 인스턴스별 `/tmp` 파일 제한만 동작하므로 운영에서는 DB 설정을 권장합니다.

GitHub 저장소를 Vercel 프로젝트에 연결하면 `main` 푸시가 운영 배포, PR이 프리뷰 배포가 됩니다.

## Docker

`Dockerfile`은 Apache + PHP 8.4(`pdo_pgsql`, `opcache`) 이미지를 만들고 Composer 의존성을 빌드 단계에서 설치합니다. 포트 80, `HEALTHCHECK`는 `/healthz`를 호출합니다. 환경변수는 `.env.example`과 같습니다. 파일 요청 제한 디렉터리는 `/var/lib/bellabell/rate`이며 볼륨으로 유지합니다.

```bash
docker build -t bellabell .
docker run -p 8080:80 -e APP_ENV=production -e APP_URL=https://example.com -e APP_SECRET=... bellabell
```

Fly.io, Railway, Render, AWS ECS 등 Dockerfile을 받는 플랫폼에 그대로 올릴 수 있습니다. 프록시 뒤에서는 `TRUST_PROXY_HTTPS=1`(이미지 기본값)을 유지합니다.

## 전통 서버 (Apache / Nginx + PHP-FPM)


PHP 8.2 이상과 OpenSSL, Composer 의존성이 필요합니다. 이 작업은 PHP 8.5.5에서 실행했으며 PHP 8.2 자체 런타임 검사는 수행하지 않았습니다. Windows 기본 개발 서버는 로컬 검수용입니다. 운영에는 Apache 또는 Nginx와 PHP-FPM을 사용합니다.

Apache에서는 프로젝트 루트에 포함된 `.htaccess`의 rewrite와 접근 차단이 활성화되어야 합니다. `AllowOverride` 및 `mod_rewrite` 설정을 확인하세요.

Nginx 예시는 실제 서버 경로와 PHP 소켓으로 바꿔 사용합니다.

```nginx
server {
    listen 443 ssl;
    server_name your-company-domain.example;
    root /srv/bellabell-farm;
    # ssl_certificate / ssl_certificate_key: 운영 인증서 설정
    client_max_body_size 64k;

    location ~ (^|/)\. { deny all; }
    location ~ ^/(includes|sections|data|design-system|docs|tests|tools|vendor|node_modules|db|api|\.runtime)(/|$) {
        deny all;
    }
    location ~ ^/assets/[a-zA-Z0-9_./-]+\.(css|js|svg|png|jpg|jpeg|webp|avif|mp4|webm|woff2?)$ {
        try_files $uri =404;
        expires 7d;
        add_header X-Content-Type-Options nosniff;
    }
    location / {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/router.php;
        fastcgi_param SCRIPT_NAME /router.php;
        fastcgi_param HTTPS on;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }
}
```

모든 페이지 요청을 허용 목록이 있는 라우터로 보내는 구성입니다. 임의의 PHP 파일을 직접 실행하는 범용 `location ~ \.php$` 설정은 추가하지 마세요.

## 환경 설정

1. `composer install --no-dev --optimize-autoloader`, `npm ci` 실행.
2. `.env.example`을 `.env`로 복사하거나 서버 환경변수로 설정.
3. `APP_ENV=production`, 실제 HTTPS `APP_URL` 설정. 로컬 기본값은 검색엔진 `noindex`.
4. SMTP 발신 도메인·인증·TLS·수신 주소 설정. 발신 주소를 폼 입력값으로 바꾸지 않음.
5. `APP_SECRET`을 설정하고, `DATABASE_URL`(권장) 또는 `CONTACT_RATE_DIR`(웹 루트 밖, PHP 프로세스만 접근)을 설정.
6. HTTPS 리다이렉트와 HTTPS 서버 변수 처리. 신뢰하지 않는 전달 헤더를 그대로 HTTPS 판정에 사용하지 않음.
7. 3개 언어 경로, 정적 미디어, 404, canonical/hreflang, robots/sitemap, 문의 메일 실제 수신을 확인.

PHP-FPM의 `clear_env` 설정으로 환경변수가 차단될 수 있습니다. 서버 풀에 필요한 변수만 명시하거나 웹에서 차단되는 루트 `.env`를 사용하세요. 인증정보를 저장소에 커밋하지 마세요.

## 운영 확인 항목

- 개인정보 안내의 담당자와 실제 보유·삭제 기준 확정. 이메일함에 전달된 문의는 담당자가 처리합니다.
- 외부 SMTP 성공 응답은 최종 수신함 도착을 보장하지 않습니다. 서비스 발송 정책과 SPF/DKIM/DMARC 및 스팸함을 확인합니다.
- `DATABASE_URL`이 없으면 요청 제한은 인스턴스의 파일 저장소에 기반합니다. 여러 인스턴스라면 DB를 설정하거나 게이트웨이 제한으로 보완합니다.
- 기업 연락처·영문 표기·브랜드 미디어의 최신 버전을 최종 대조합니다.
- PHP 업데이트와 npm/Composer 의존성 업데이트 후 기능 검사를 실행합니다.
- 접근 로그 보유 정책은 웹 서버에서 설정합니다. 애플리케이션은 문의 내용을 로그에 쓰지 않습니다.

공개 배포, DNS 변경, 외부 메일 발송은 이번 작업에서 실행하지 않았습니다.
