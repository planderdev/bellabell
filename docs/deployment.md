# 운영 배포

## 서버

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
    location ~ ^/(includes|sections|data|docs|tests|tools|vendor|node_modules|\.runtime)(/|$) {
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
5. `CONTACT_RATE_DIR`을 웹 루트 밖의 쓰기 가능한 전용 디렉터리로 설정. PHP 프로세스만 접근하도록 권한 설정.
6. HTTPS 리다이렉트와 HTTPS 서버 변수 처리. 신뢰하지 않는 전달 헤더를 그대로 HTTPS 판정에 사용하지 않음.
7. 3개 언어 경로, 정적 미디어, 404, canonical/hreflang, robots/sitemap, 문의 메일 실제 수신을 확인.

PHP-FPM의 `clear_env` 설정으로 환경변수가 차단될 수 있습니다. 서버 풀에 필요한 변수만 명시하거나 웹에서 차단되는 루트 `.env`를 사용하세요. 인증정보를 저장소에 커밋하지 마세요.

## 운영 확인 항목

- 개인정보 안내의 담당자와 실제 보유·삭제 기준 확정. 이메일함에 전달된 문의는 담당자가 처리합니다.
- 외부 SMTP 성공 응답은 최종 수신함 도착을 보장하지 않습니다. 서비스 발송 정책과 SPF/DKIM/DMARC 및 스팸함을 확인합니다.
- 요청 제한은 인스턴스의 파일 저장소에 기반합니다. 여러 인스턴스라면 공유 제한 또는 게이트웨이 제한으로 보완합니다.
- 기업 연락처·영문 표기·브랜드 미디어의 최신 버전을 최종 대조합니다.
- PHP 업데이트와 npm/Composer 의존성 업데이트 후 기능 검사를 실행합니다.
- 접근 로그 보유 정책은 웹 서버에서 설정합니다. 애플리케이션은 문의 내용을 로그에 쓰지 않습니다.

공개 배포, DNS 변경, 외부 메일 발송은 이번 작업에서 실행하지 않았습니다.
