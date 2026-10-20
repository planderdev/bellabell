<?php
$faqItems=[1=>'products',2=>'products',3=>'partnership',4=>'partnership',5=>'partnership',6=>'visit'];
if($faqPreview) $faqItems=[1=>'products',3=>'partnership',6=>'visit'];
?>
<section class="faq-section container <?= $faqPreview?'faq-preview section-pad':'' ?>">
    <?php if($faqPreview): ?><div class="detail-intro"><h2><?= e(t('detail.faq.label')) ?></h2><p><?= e(t('detail.faq.title')) ?></p></div><?php endif; ?>
    <?php if(!$faqPreview): ?><div class="faq-filters" role="group" aria-label="<?= e(t('detail.faq.label')) ?>"><?php foreach(['all','products','partnership','visit'] as $category): ?><button class="chip filter-button <?= $category==='all'?'is-active':'' ?>" type="button" data-faq-filter="<?= e($category) ?>" aria-pressed="<?= $category==='all'?'true':'false' ?>"><?= e(t('detail.faq.'.$category)) ?></button><?php endforeach; ?></div><?php endif; ?>
    <div class="faq-list"><?php foreach($faqItems as $number=>$category): ?>
    <details class="accordion faq-item" data-faq-category="<?= e($category) ?>"><summary><span class="faq-q" aria-hidden="true">Q</span><span><?= e(t('detail.faq.q'.$number)) ?></span></summary><div class="accordion__body faq-answer"><p><?= e(t('detail.faq.a'.$number)) ?></p></div></details>
    <?php endforeach; ?></div>
    <?php if($faqPreview): ?><a class="text-link faq-more" href="<?= e(url('faq')) ?>"><?= e(t('common.discover')) ?><?= icon('arrow-up-right') ?></a><?php endif; ?>
</section>
