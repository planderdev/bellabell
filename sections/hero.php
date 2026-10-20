<section class="hero" aria-labelledby="hero-title">
    <div class="hero-media">
        <?= picture('hero', 'hero-poster', true) ?>
        <div class="hero-shade"></div>
    </div>
    <div class="hero-content container">
        <div class="hero-brand" aria-hidden="true"><span class="hero-brand-line"><span>BELLABELL</span></span><span class="hero-brand-line"><span>FARM</span></span></div>
        <h1 id="hero-title"><?php foreach (explode("\n", t('hero.title')) as $line): ?><span class="hero-title-line"><span><?= e($line) ?></span></span><?php endforeach; ?></h1>
        <p class="hero-description hero-reveal"><?= lines('hero.description') ?></p>
        <a class="button button--light hero-reveal" href="<?= e(url('company')) ?>"><?= e(t('hero.cta')) ?><span class="button-icon"><?= icon('arrow-up-right') ?></span></a>
    </div>
    <div class="hero-bottom container">
        <a class="hero-scroll" href="#introduction"><?= icon('arrow-down') ?><span><?= e(t('common.scroll')) ?></span></a>
        <span class="hero-scroll-progress" aria-hidden="true"><span></span></span>
    </div>
</section>
