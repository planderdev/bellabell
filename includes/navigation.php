<nav class="desktop-navigation" aria-label="<?= e(t('common.menu')) ?>">
    <?php foreach ($navigation as $navItem): ?>
    <a href="<?= e(url($navItem)) ?>" <?= $page === $navItem ? 'aria-current="page"' : '' ?>><?= e(t('nav.' . $navItem)) ?></a>
    <?php endforeach; ?>
</nav>
