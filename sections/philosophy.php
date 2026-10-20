<section class="philosophy-section" id="philosophy" data-motion-level="cinematic" aria-label="<?= e(t('philosophy.title')) ?>">
    <div class="philosophy-stage">
        <?php foreach (['jeju'=>'hero','research'=>'concept','wellness'=>'dog'] as $id => $image): ?>
        <article class="philosophy-scene" data-scene="<?= e($id) ?>">
            <div class="philosophy-image"><?= picture($image) ?><div class="philosophy-shade"></div></div>
            <div class="philosophy-copy container"><span class="philosophy-word"><?= e(t('philosophy.' . $id . '.word')) ?></span><div class="philosophy-text"><h2><?= lines('philosophy.' . $id . '.title') ?></h2><p><?= e(t('philosophy.' . $id . '.description')) ?></p></div></div>
        </article>
        <?php endforeach; ?>
    </div>
    <div class="chapter-progress container" aria-hidden="true"><span class="chapter-current">01</span><div class="chapter-track"><span></span></div><span>03</span><span class="chapter-caption"><?= e(t('philosophy.label')) ?></span></div>
</section>
