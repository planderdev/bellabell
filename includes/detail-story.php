<section class="detail-story">
    <div class="detail-story-inner container">
        <div class="detail-story-heading"><h2><?= lines($storyTitle) ?></h2><p><?= e(t($storyLead)) ?></p><div class="detail-story-progress" aria-hidden="true"><span class="detail-story-current">01</span><span>/ <?= str_pad((string)count($storyPanels),2,'0',STR_PAD_LEFT) ?></span><span class="detail-story-line"><i></i></span></div></div>
        <div class="detail-story-viewport"><div class="detail-story-track">
            <?php foreach($storyPanels as $i=>$panel): ?>
            <article class="card detail-story-card <?= in_array($panel['media'],['products','research'],true)?'contain-media':'' ?>"><div class="detail-story-image"><?= picture($panel['media']) ?></div><div class="card__body detail-story-copy"><span><?= e($panel['label']) ?></span><h3><?= lines($panel['title']) ?></h3><p><?= e(t($panel['text'])) ?></p></div></article>
            <?php endforeach; ?>
        </div></div>
    </div>
</section>
