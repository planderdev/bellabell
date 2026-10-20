<?php
require APP_ROOT . '/includes/head.php';
require APP_ROOT . '/includes/header.php';
?>
<main id="main">
<?php require APP_ROOT . '/includes/page-hero.php'; ?>
<?php if ($page === 'company'): ?>
    <div class="company-fan container" aria-hidden="true">
        <div class="fan-image fan-left"><?= picture('hero','',true) ?></div>
        <div class="fan-image fan-center"><?= picture('products','',true) ?></div>
        <div class="fan-image fan-right"><?= picture('dog','',true) ?></div>
    </div>
    <section class="company-intro container section-pad">
        <p class="section-label"><?= e(t('pages.company.label')) ?></p>
        <h2 class="display-title" data-reveal><?= lines('intro.title') ?></h2>
        <p class="body-copy" data-reveal><?= e(t('intro.description')) ?></p>
    </section>
    <?php
    $storyTitle='detail.company.story';$storyLead='detail.company.storyLead';
    $storyPanels=[
        ['media'=>'hero','title'=>'philosophy.jeju.title','text'=>'philosophy.jeju.description','label'=>t('philosophy.jeju.word')],
        ['media'=>'concept','title'=>'philosophy.research.title','text'=>'philosophy.research.description','label'=>t('philosophy.research.word')],
        ['media'=>'dog','title'=>'philosophy.wellness.title','text'=>'philosophy.wellness.description','label'=>t('philosophy.wellness.word')],
    ];
    require APP_ROOT . '/includes/detail-story.php';
    require APP_ROOT . '/sections/history.php';
    $faqPreview=true;require APP_ROOT . '/includes/detail-faq.php';
    ?>
<?php elseif ($page === 'business'): ?>
    <section class="portfolio-section container">
        <div class="detail-intro"><h2 data-reveal><?= lines('detail.business.intro') ?></h2><p><?= e(t('detail.business.lead')) ?></p></div>
        <div class="portfolio-grid business-portfolio">
        <?php foreach (published($company['business']) as $business): $id=$business['id']; ?>
            <a class="card portfolio-card portfolio-<?= e($id) ?>" href="<?= e(url($business['href'])) ?>" data-reveal>
                <div class="portfolio-media"><?= picture($business['media']) ?><span class="portfolio-arrow"><?= icon('arrow-up-right') ?></span></div>
                <div class="card__body portfolio-copy"><h3><?= e(t('business.'.$id.'.name')) ?></h3><p><?= e(t('business.'.$id.'.description')) ?></p></div>
            </a>
        <?php endforeach; ?>
        </div>
    </section>
    <section class="ingredient-index container section-pad" id="ingredients"><h2 class="display-title"><?= e(t('detail.business.ingredients')) ?></h2>
        <div class="ingredient-index-grid"><?php foreach (published($company['ingredients']) as $ingredient):$id=$ingredient['id']; ?>
        <article data-reveal><div class="ingredient-index-image"><?= picture($ingredient['media']) ?></div><h3><?= e(t('ingredients.'.$id.'.name')) ?></h3><p><?= e(t('ingredients.'.$id.'.description')) ?></p></article>
        <?php endforeach; ?></div>
    </section>
<?php elseif ($page === 'technology'): ?>
    <?php
    $storyTitle='detail.technology.intro';$storyLead='detail.technology.lead';$storyPanels=[];
    foreach(published($company['research']) as $i=>$stage) $storyPanels[]=['media'=>$stage['media'],'title'=>'technology.'.$stage['id'].'.title','text'=>'technology.'.$stage['id'].'.description','label'=>str_pad((string)($i+1),2,'0',STR_PAD_LEFT)];
    require APP_ROOT . '/includes/detail-story.php';
    ?>
    <section class="detail-partners container section-pad"><h2 class="display-title"><?= e(t('technology.partners')) ?></h2><div class="partners-list"><?php foreach (['partner1','partner2','partner3'] as $partner): ?><p><?= e(t('technology.' . $partner)) ?></p><?php endforeach; ?></div></section>
<?php elseif ($page === 'sustainability'): ?>
    <section class="impact-cover container"><div class="impact-image"><?= picture('hero','',true) ?></div><div class="impact-statement" data-reveal><h2><?= lines('detail.sustainability.intro') ?></h2><p><?= e(t('sustainability.description')) ?></p></div></section>
    <section class="impact-values container section-pad">
        <?php foreach (['local'=>'bamboo','animals'=>'dog','community'=>'aouaou'] as $value=>$image): ?>
        <article class="impact-value" data-reveal><h2><?= e(t('sustainability.'.$value.'.title')) ?></h2><p><?= e(t('sustainability.'.$value.'.description')) ?></p><div class="impact-value-image impact-<?= e($value) ?>"><?= picture($image) ?></div></article>
        <?php endforeach; ?>
    </section>
<?php elseif ($page === 'brands'): ?>
    <section class="portfolio-section container">
        <div class="detail-intro"><h2 data-reveal><?= lines('detail.brands.intro') ?></h2><p><?= e(t('detail.brands.lead')) ?></p></div>
        <div class="portfolio-grid brand-portfolio">
        <?php foreach(published($company['brands']) as $brand):$id=$brand['id']; ?>
        <a class="card portfolio-card portfolio-<?= e($id) ?>" id="brand-<?= e($id) ?>" href="<?= e($brand['url']) ?>" target="_blank" rel="noopener noreferrer" data-reveal>
            <div class="portfolio-media"><?= picture($brand['media']) ?><span class="portfolio-arrow"><?= icon('arrow-up-right') ?></span></div>
            <div class="card__body portfolio-copy"><div class="portfolio-brand"><?php if($brand['logo']): ?><img src="<?= e(asset($brand['logo'])) ?>" alt="<?= e($brand['name']) ?>" width="220" height="60"><?php else: ?><span><?= e($brand['name']) ?></span><?php endif; ?></div><h3><?= lines('brands.'.$id.'.title') ?></h3><p><?= e(t('brands.'.$id.'.description')) ?></p><span class="text-link"><?= e(t('brands.'.$id.'.cta')) ?><?= icon('arrow-up-right') ?></span><span class="sr-only"><?= e(t('common.external')) ?></span></div>
        </a>
        <?php endforeach; ?>
        </div>
    </section>
<?php elseif ($page === 'news'): ?>
    <section class="news-section container">
        <div class="news-filters" role="group" aria-label="<?= e(t('nav.news')) ?>"><?php foreach (['all','global','company','impact'] as $filter): ?><button class="chip filter-button <?= $filter==='all'?'is-active':'' ?>" type="button" data-filter="<?= e($filter) ?>" aria-pressed="<?= $filter==='all'?'true':'false' ?>"><?= e(t('news.'.$filter)) ?></button><?php endforeach; ?></div>
        <div class="news-list"><?php foreach(published($company['news']) as $story): ?>
        <article class="card card--interactive news-item" data-category="<?= e($story['category']) ?>">
            <a href="<?= e($story['url']) ?>" target="_blank" rel="noopener noreferrer"><div class="news-copy"><span class="news-category"><?= e(t('news.'.$story['category'])) ?></span><h2><?= e(t('news.'.$story['id'].'.title')) ?></h2><p><?= e(t('news.'.$story['id'].'.description')) ?></p></div><time><?= e($story['date']) ?></time><?= icon('arrow-up-right') ?><span class="sr-only"><?= e(t('common.external')) ?></span></a>
        </article><?php endforeach; ?></div>
        <p class="news-empty" hidden role="status"><?= e(t('news.empty')) ?></p>
    </section>
<?php elseif ($page === 'faq'): ?>
    <?php $faqPreview=false;require APP_ROOT . '/includes/detail-faq.php'; ?>
<?php elseif ($page === 'privacy'): ?>
    <article class="privacy-content container"><p><?= e(t('privacy.intro')) ?></p><?php foreach (['items','purpose','rights','storage','owner'] as $part): ?><section><h2><?= e(t('privacy.'.$part.'.title')) ?></h2><p><?= e(t('privacy.'.$part.'.body')) ?></p></section><?php endforeach; ?></article>
<?php endif; ?>
<?php if ($page!=='privacy') require APP_ROOT . '/includes/detail-cta.php'; ?>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
