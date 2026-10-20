<header class="site-header <?= $page !== 'home' ? 'is-solid' : '' ?>" id="site-header">
    <a class="wordmark" href="<?= e(url()) ?>" aria-label="<?= e($company['name']) ?>">
        <?= brand_logo() ?>
    </a>
    <?php require APP_ROOT . '/includes/navigation.php'; ?>
    <div class="header-actions">
        <?php require APP_ROOT . '/includes/language-switcher.php'; ?>
        <a class="header-contact button button--weak button--md" href="<?= e(url('contact')) ?>"><?= e(t('common.contact')) ?><?= icon('arrow-up-right') ?></a>
        <button class="icon-button menu-toggle" type="button" aria-label="<?= e(t('common.menu')) ?>" aria-controls="site-menu" aria-expanded="false"><?= icon('menu') ?></button>
    </div>
</header>
<dialog class="site-menu" id="site-menu" aria-label="<?= e(t('common.menu')) ?>" data-lenis-prevent>
    <div class="menu-top"><a class="wordmark" href="<?= e(url()) ?>"><?= brand_logo() ?></a><button class="icon-button menu-close" type="button" aria-label="<?= e(t('common.close')) ?>"><?= icon('x') ?></button></div>
    <nav class="overlay-navigation" aria-label="<?= e(t('common.menu')) ?>">
        <?php foreach (array_merge($navigation, ['contact']) as $i => $navItem): ?>
        <a href="<?= e(url($navItem)) ?>"><span class="menu-index"><?= str_pad((string)($i+1),2,'0',STR_PAD_LEFT) ?></span><?= e(t('nav.' . $navItem)) ?><?= icon('arrow-up-right') ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="menu-bottom"><p><?= e(t('common.brandSignature')) ?></p><?php require APP_ROOT . '/includes/language-switcher.php'; ?></div>
</dialog>
<div class="header-sentinel" aria-hidden="true"></div>
