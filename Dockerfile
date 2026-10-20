# syntax=docker/dockerfile:1
# Production image for Apache + PHP hosting (any VPS, Fly.io, Railway, Render, ECS...).
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --no-progress

FROM php:8.4-apache
RUN apt-get update \
 && apt-get install -y --no-install-recommends libpq-dev curl \
 && docker-php-ext-install -j"$(nproc)" pdo_pgsql opcache \
 && apt-get purge -y --auto-remove libpq-dev \
 && rm -rf /var/lib/apt/lists/* \
 && a2enmod rewrite headers \
 && sed -ri 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
 && printf 'ServerTokens Prod\nServerSignature Off\n' >> /etc/apache2/conf-available/security.conf \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && printf 'expose_php=0\nopcache.enable=1\nopcache.validate_timestamps=0\nsession.use_strict_mode=1\n' > "$PHP_INI_DIR/conf.d/zz-bellabell.ini"

ENV APP_ENV=production \
    TRUST_PROXY_HTTPS=1 \
    CONTACT_RATE_DIR=/var/lib/bellabell/rate

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
RUN mkdir -p /var/lib/bellabell/rate && chown -R www-data:www-data /var/lib/bellabell

EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -fsS http://127.0.0.1/healthz || exit 1
