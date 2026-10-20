<?php
declare(strict_types=1);
if (defined('APP_BOOTSTRAPPED')) { return; }
define('APP_BOOTSTRAPPED', true);
define('APP_ROOT', dirname(__DIR__));
if (is_file(APP_ROOT . '/.env')) {
    foreach (file(APP_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) { continue; }
        [$key, $value] = explode('=', $line, 2);
        if (preg_match('/^[A-Z_]+$/', trim($key)) && getenv(trim($key)) === false) {
            putenv(trim($key) . '=' . trim(trim($value), "\"'"));
        }
    }
}
$supportedLanguages = ['ko' => '한국어', 'en' => 'English', 'zh-CN' => '简体中文'];
$page = $page ?? 'home';
$lang = isset($routeLang) && isset($supportedLanguages[$routeLang]) ? $routeLang : 'ko';
if (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) === '/') {
    $preferred = $_COOKIE['bellabell_language'] ?? 'ko';
    $preferred = is_string($preferred) && isset($supportedLanguages[$preferred]) ? $preferred : 'ko';
    header('Location: /' . $preferred . '/', true, 302); exit;
}
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    // Only trust the forwarded protocol when the host explicitly terminates TLS in front of PHP (Vercel, a reverse proxy).
    || (getenv('TRUST_PROXY_HTTPS') === '1' && strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
if (PHP_SAPI !== 'cli' && ($_COOKIE['bellabell_language'] ?? '') !== $lang) {
    setcookie('bellabell_language', $lang, ['expires'=>time()+31536000,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Lax']);
}
$defaultContent = require APP_ROOT . '/data/ko.php';
$content = $lang === 'ko' ? $defaultContent : require APP_ROOT . '/data/' . $lang . '.php';
$company = require APP_ROOT . '/data/company.php';
$media = require APP_ROOT . '/data/media.php';
$navigation = require APP_ROOT . '/data/navigation.php';
$seo = require APP_ROOT . '/data/seo.php';
$appUrl = rtrim(getenv('APP_URL') ?: 'http://127.0.0.1:8080', '/');
$isProduction = getenv('APP_ENV') === 'production';
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; media-src 'self'; connect-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'");
if (!$isProduction) { header('X-Robots-Tag: noindex, nofollow'); }

function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function lookup(array $data, string $key): mixed {
    foreach (explode('.', $key) as $part) {
        if (!is_array($data) || !array_key_exists($part, $data)) { return null; }
        $data = $data[$part];
    }
    return $data;
}
function t(string $key): string {
    global $content, $defaultContent, $lang, $isProduction;
    $value = lookup($content, $key);
    if (!is_string($value)) {
        if (!$isProduction) { error_log("Missing translation [$lang]: $key"); }
        $value = lookup($defaultContent, $key);
    }
    return is_string($value) ? $value : '';
}
function lines(string $key): string { return nl2br(e(t($key)), false); }
function url(string $target = '', ?string $locale = null): string {
    global $lang;
    return '/' . ($locale ?? $lang) . '/' . ltrim($target, '/');
}
function asset(string $path): string { return '/assets/' . ltrim($path, '/'); }
function icon(string $name, string $class = ''): string {
    return '<i data-lucide="' . e($name) . '" class="' . e($class) . '" aria-hidden="true"></i>';
}
function brand_logo(): string {
    return '<img src="' . e(asset('images/bellabelfarm-s.svg')) . '" alt="BELLABELL FARM" width="1445" height="141" decoding="async">';
}
function picture(string $key, string $class = '', bool $eager = false): string {
    global $media;
    $item = $media[$key] ?? $media['hero'];
    $loading = $eager ? 'eager' : 'lazy';
    $priority = $eager ? ' fetchpriority="high"' : '';
    return '<img src="' . e(asset($item['file'])) . '" alt="' . e(t('alt.' . $item['alt'])) . '" class="' . e($class) . '" width="' . (int)$item['width'] . '" height="' . (int)$item['height'] . '" loading="' . $loading . '" decoding="async"' . $priority . '>';
}
function published(array $records): array {
    return array_filter($records, fn(array $record): bool => ($record['status'] ?? 'draft') === 'published');
}
