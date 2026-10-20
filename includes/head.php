<?php
$title = t('pages.' . $page . '.title');
$description = t('pages.' . $page . '.description');
$canonicalPath = url($page === 'home' ? '' : $page);
?>
<!doctype html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#00AF84">
    <title><?= e($title) ?><?= $page !== 'home' ? ' | ' . e($company['name']) : '' ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <link rel="canonical" href="<?= e($appUrl . $canonicalPath) ?>">
    <?php foreach ($supportedLanguages as $locale => $name): ?>
    <link rel="alternate" hreflang="<?= e($locale) ?>" href="<?= e($appUrl . url($page === 'home' ? '' : $page, $locale)) ?>">
    <?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= e($appUrl . url($page === 'home' ? '' : $page, 'ko')) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e($appUrl . $canonicalPath) ?>">
    <meta property="og:site_name" content="<?= e($company['name']) ?>">
    <meta property="og:locale" content="<?= e($seo['og_locale'][$lang]) ?>">
    <meta property="og:image" content="<?= e($appUrl . asset($media['hero']['file'])) ?>">
    <?php if ($page === 'home'): ?><link rel="preload" as="image" href="<?= e(asset($media['hero']['file'])) ?>"><?php endif; ?>
    <link rel="preload" href="<?= e(asset('fonts/PretendardVariable.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/Raleway-Latin-Variable.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('vendor/lenis.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('vendor/swiper-bundle.min.css')) ?>">
    <?php foreach (['tokens','ui','base','layout',$page === 'home' ? 'home' : 'details'] as $css): ?>
    <link rel="stylesheet" href="<?= e(asset('css/' . $css . '.css')) ?>">
    <?php endforeach; ?>
</head>
<body class="root page-<?= e($page) ?>">
<a href="#main" class="skip-link"><?= e(t('common.skip')) ?></a>
