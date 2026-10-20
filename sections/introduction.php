<section class="introduction section-pad" id="introduction" aria-labelledby="intro-title">
    <div class="intro-floating intro-left"><?= picture('sea') ?></div>
    <div class="container intro-content">
        <p class="section-label"><?= e(t('intro.label')) ?></p>
        <h2 id="intro-title" class="display-title"><?= lines('intro.title') ?></h2>
        <div class="intro-lines">
            <?php foreach (['line1','line2','line3'] as $line): ?><p><span class="reveal-line"><?= e(t('intro.' . $line)) ?></span></p><?php endforeach; ?>
        </div>
        <p class="body-copy"><?= e(t('intro.description')) ?></p>
        <a class="text-link" href="<?= e(url('company')) ?>"><?= e(t('intro.link')) ?><?= icon('arrow-up-right') ?></a>
    </div>
    <div class="intro-floating intro-right"><?= picture('dog') ?></div>
</section>
