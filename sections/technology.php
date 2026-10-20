<section class="technology-section section-pad" id="technology" data-motion-level="cinematic" aria-labelledby="technology-title">
    <div class="container technology-layout">
        <div class="technology-intro"><p class="eyebrow"><?= e(t('technology.label')) ?></p><h2 class="display-title" id="technology-title"><?= lines('technology.title') ?></h2><p class="body-copy"><?= e(t('technology.description')) ?></p><a class="text-link" href="<?= e(url('technology')) ?>"><?= e(t('technology.link')) ?><?= icon('arrow-up-right') ?></a><div class="research-progress" aria-hidden="true"><span class="research-current">01</span><span>/ 03</span></div></div>
        <div class="research-step-track" aria-hidden="true"><span class="research-step"></span><span class="research-step"></span><span class="research-step"></span></div>
        <div class="research-scenes">
            <?php foreach (published($company['research']) as $i => $stage): $id=$stage['id']; ?>
            <article class="research-scene" data-research="<?= e($id) ?>"><div class="research-image"><?= picture($stage['media']) ?></div><div class="research-copy"><span class="research-number"><?= str_pad((string)($i+1),2,'0',STR_PAD_LEFT) ?></span><div><h3><?= e(t('technology.' . $id . '.title')) ?></h3><p><?= e(t('technology.' . $id . '.description')) ?></p></div></div></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
