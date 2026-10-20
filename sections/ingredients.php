<section class="ingredients-section section-pad" id="ingredients" data-motion-level="cinematic" aria-labelledby="ingredients-title">
    <div class="container ingredients-heading"><h2 class="display-title" id="ingredients-title" data-reveal><?= lines('ingredients.title') ?></h2><p class="body-copy"><?= e(t('ingredients.description')) ?></p></div>
    <div class="ingredients-viewport"><div class="ingredients-track">
        <?php foreach (published($company['ingredients']) as $i => $ingredient): $id = $ingredient['id']; ?>
        <article class="ingredient-card"><div class="ingredient-image"><?= picture($ingredient['media']) ?><span class="ingredient-number" aria-hidden="true"><?= str_pad((string)($i+1),2,'0',STR_PAD_LEFT) ?></span><span class="ingredient-category"><?= e(t('ingredients.' . $id . '.category')) ?></span></div><div class="ingredient-copy"><div><h3><?= e(t('ingredients.' . $id . '.name')) ?></h3><p><?= e(t('ingredients.' . $id . '.description')) ?></p></div><a href="<?= e(url('business#ingredients')) ?>" class="icon-button" aria-label="<?= e(t('ingredients.' . $id . '.name') . ' · ' . t('common.discover')) ?>"><?= icon('arrow-up-right') ?></a></div></article>
        <?php endforeach; ?>
    </div></div>
    <div class="container ingredients-bottom"><a href="<?= e(url('business')) ?>" class="text-link"><?= e(t('ingredients.link')) ?><?= icon('arrow-right') ?></a><div class="gallery-track" aria-hidden="true"><span></span></div></div>
</section>
