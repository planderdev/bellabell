<?php
require __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><?php foreach ($supportedLanguages as $locale => $name): foreach ($seo['pages'] as $seoPage): ?><url><loc><?= e($appUrl . url($seoPage === 'home' ? '' : $seoPage, $locale)) ?></loc></url><?php endforeach; endforeach; ?></urlset>
