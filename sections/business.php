<section class="business-section section-pad container" id="business" data-motion-level="reveal" aria-labelledby="business-title">
    <div class="business-heading"><h2 id="business-title" class="display-title" data-reveal><?= lines('business.title') ?></h2><p class="body-copy"><?= e(t('business.description')) ?></p></div>
    <div class="business-grid">
        <?php foreach (published($company['business']) as $i => $area): $id=$area['id']; ?>
        <a class="business-card business-<?= e($id) ?>" href="<?= e(url($area['href'])) ?>" data-reveal><div class="business-image"><?= picture($area['media']) ?><span class="business-arrow" aria-hidden="true"><?= icon('arrow-up-right') ?></span></div><div class="business-meta"><span class="business-index"><?= str_pad((string)($i+1),2,'0',STR_PAD_LEFT) ?></span><h3><?= e(t('business.' . $id . '.name')) ?></h3><span class="business-english"><?= e(t('business.' . $id . '.english')) ?></span></div><p><?= e(t('business.' . $id . '.description')) ?></p></a>
        <?php endforeach; ?>
    </div>
</section>
