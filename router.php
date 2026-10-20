<?php
declare(strict_types=1);
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if (str_contains($path, "\0") || str_contains($path, '\\') || preg_match('~(?:^|/)\.\.(?:/|$)~', $path)) {
    http_response_code(400); exit('Bad request');
}
if (preg_match('~^/assets/[a-zA-Z0-9_./-]+\.(?:css|js|svg|png|jpg|jpeg|webp|avif|mp4|webm|woff2?)$~', $path)) {
    $file = realpath(__DIR__ . $path);
    $assetRoot = realpath(__DIR__ . '/assets');
    if ($file && $assetRoot && str_starts_with($file, $assetRoot . DIRECTORY_SEPARATOR) && is_file($file)) {
        return false;
    }
    http_response_code(404); exit;
}
if ($path === '/healthz') { require __DIR__ . '/healthz.php'; exit; }
if ($path === '/robots.txt') { require __DIR__ . '/robots.php'; exit; }
if ($path === '/sitemap.xml') { require __DIR__ . '/sitemap.php'; exit; }
$pages = ['company', 'business', 'technology', 'sustainability', 'brands', 'news', 'contact', 'privacy', 'faq'];
if ($path === '/') { require __DIR__ . '/index.php'; exit; }
if (preg_match('~^/(ko|en|zh-CN)(?:/([a-z-]+))?/?$~', $path, $match)) {
    $routeLang = $match[1];
    $page = $match[2] ?? 'home';
    if ($page === 'home') { require __DIR__ . '/index.php'; exit; }
    if (in_array($page, $pages, true)) { require __DIR__ . '/' . $page . '.php'; exit; }
}
if (preg_match('~^/(index|' . implode('|', $pages) . ')\.php$~', $path, $match)) {
    header('Location: /ko/' . ($match[1] === 'index' ? '' : $match[1]), true, 301); exit;
}
$page = '404';
$routeLang = preg_match('~^/(en|zh-CN)/~', $path, $m) ? $m[1] : 'ko';
http_response_code(404);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>
<main id="main" class="error-page container"><p class="eyebrow">404</p><h1><?= e(t('errors.notFound')) ?></h1><a class="button" href="<?= e(url()) ?>"><?= e(t('common.home')) ?><?= icon('arrow-up-right') ?></a></main>
<?php require __DIR__ . '/includes/footer.php'; ?>
