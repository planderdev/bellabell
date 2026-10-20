<?php
require __DIR__ . '/includes/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
echo $isProduction ? "User-agent: *\nAllow: /\nSitemap: " . $appUrl . "/sitemap.xml\n" : "User-agent: *\nDisallow: /\n";
