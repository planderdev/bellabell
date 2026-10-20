<section class="brands-section section-pad" id="brands" data-motion-level="cinematic" aria-labelledby="brands-title">
    <div class="container brands-heading"><p class="eyebrow"><?= e(t('brands.label')) ?></p><h2 id="brands-title" class="display-title" data-reveal><?= lines('brands.title') ?></h2></div>
    <div class="brand-viewport swiper"><div class="brand-track swiper-wrapper">
        <?php foreach (published($company['brands']) as $i => $brand): $id=$brand['id']; ?>
        <article class="brand-panel swiper-slide brand-<?= e($id) ?>" id="brand-<?= e($id) ?>" aria-label="<?= e($brand['name']) ?>">
            <span class="brand-display" aria-hidden="true"><?= e($brand['name']) ?></span>
            <div class="brand-art"><?= picture($brand['media']) ?></div>
            <div class="brand-copy"><?php if ($brand['logo']): ?><img class="brand-logo" src="<?= e(asset($brand['logo'])) ?>" width="240" height="64" alt="<?= e($brand['name']) ?>" loading="lazy"><?php else: ?><span class="brand-name"><?= e($brand['name']) ?></span><?php endif; ?>
            <h3><?= lines('brands.' . $id . '.title') ?></h3><p><?= e(t('brands.' . $id . '.description')) ?></p><a class="button button--outline" href="<?= e($brand['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('brands.' . $id . '.cta')) ?><?= icon('arrow-up-right') ?><span class="sr-only"><?= e(t('common.external')) ?></span></a></div>
        </article>
        <?php endforeach; ?>
    </div></div>
    <div class="container brand-controls"><span class="brand-count" aria-hidden="true"><span class="brand-current">01</span> / 02</span><span class="brand-progress" aria-hidden="true"><span></span></span><div class="brand-navigation"><button type="button" class="icon-button brand-prev" aria-label="<?= e(t('common.previous')) ?>"><?= icon('arrow-left') ?></button><button type="button" class="icon-button brand-next" aria-label="<?= e(t('common.next')) ?>"><?= icon('arrow-right') ?></button></div></div>
</section>
