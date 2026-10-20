<section class="history-section section-pad container" id="history" data-motion-level="narrative" aria-labelledby="history-title">
    <div class="history-heading"><h2 class="display-title" id="history-title" data-reveal><?= lines('history.title') ?></h2><div class="history-year" aria-hidden="true"><?= e($company['history'][0]['year']) ?></div></div>
    <div class="history-list">
        <?php foreach (published($company['history']) as $event): ?>
        <article class="history-event" data-year="<?= e($event['year']) ?>"><span class="event-year"><?= e($event['year']) ?></span><h3><?= e(t('history.' . $event['id'] . '.title')) ?></h3><p><?= e(t('history.' . $event['id'] . '.description')) ?></p><a href="<?= e($event['source']) ?>" class="text-link" target="_blank" rel="noopener noreferrer"><?= e(t('common.source')) ?><?= icon('arrow-up-right') ?><span class="sr-only"><?= e(t('common.external')) ?></span></a></article>
        <?php endforeach; ?>
    </div>
</section>
