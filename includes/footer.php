<footer class="site-footer">
    <div class="footer-top container">
        <div class="footer-story"><a class="wordmark" href="<?= e(url()) ?>"><?= brand_logo() ?></a><p><?= lines('footer.tagline') ?></p></div>
        <div class="footer-column"><p class="footer-label"><?= e(t('footer.companyLinks')) ?></p><?php foreach (['company','technology','sustainability','news'] as $item): ?><a href="<?= e(url($item)) ?>"><?= e(t('nav.' . $item)) ?></a><?php endforeach; ?></div>
        <div class="footer-column"><p class="footer-label"><?= e(t('footer.brandLinks')) ?></p><?php foreach (published($company['brands']) as $brand): ?><a href="<?= e($brand['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($brand['name']) ?><?= icon('arrow-up-right') ?><span class="sr-only"><?= e(t('common.external')) ?></span></a><?php endforeach; ?></div>
        <div class="footer-connect"><p class="footer-label"><?= e(t('footer.email')) ?></p><a class="email-link" href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?><?= icon('arrow-up-right') ?></a><a class="text-link" href="<?= e(url('contact')) ?>"><?= e(t('common.contact')) ?><?= icon('arrow-right') ?></a></div>
    </div>
    <div class="footer-company container"><p><?= e(t('footer.companyName')) ?> <span><?= e(t('footer.ceo')) ?></span></p><p><?= e(t('footer.address')) ?></p><p><?= e(t('footer.registration')) ?> <?= e($company['registration']) ?></p></div>
    <div class="footer-bottom container"><small><?= e(t('common.copyright')) ?></small><div class="footer-utilities"><a href="<?= e(url('faq')) ?>"><?= e(t('detail.faq.label')) ?></a><a href="<?= e(url('privacy')) ?>"><?= e(t('footer.privacy')) ?></a><?php require APP_ROOT . '/includes/language-switcher.php'; ?><a href="#main" class="icon-button back-top" aria-label="<?= e(t('common.top')) ?>"><?= icon('arrow-up') ?></a></div></div>
    <div class="footer-signature" aria-hidden="true"><?= brand_logo() ?></div>
</footer>
<?php foreach (['gsap.min','ScrollTrigger.min','lenis.min','swiper-bundle.min','lucide.min'] as $script): ?>
<script defer src="<?= e(asset('vendor/' . $script . '.js')) ?>"></script>
<?php endforeach; ?>
<script type="module" src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
