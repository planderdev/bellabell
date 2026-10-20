<?php
declare(strict_types=1);
// Liveness/readiness probe for load balancers and deploy checks. No cookies, no secrets.
define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/includes/contact-handler.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Robots-Tag: noindex, nofollow');
echo json_encode([
    'status' => 'ok',
    'php' => PHP_VERSION,
    'env' => getenv('APP_ENV') ?: 'local',
    'checks' => [
        'translations' => is_file(APP_ROOT . '/data/ko.php') && is_file(APP_ROOT . '/data/en.php') && is_file(APP_ROOT . '/data/zh-CN.php'),
        'composer' => is_file(APP_ROOT . '/vendor/autoload.php'),
        'openssl' => extension_loaded('openssl'),
        'pdo_pgsql' => extension_loaded('pdo_pgsql'),
        'contact_secret' => contact_secret() !== '',
        'mail' => mail_ready(),
        'database' => db_ready(),
        'contact_form' => contact_ready(),
    ],
], JSON_UNESCAPED_SLASHES);
