<details class="language-switcher">
    <summary aria-label="<?= e(t('common.language')) ?>"><?= icon('globe-2') ?><span><?= e(['ko'=>'KO','en'=>'EN','zh-CN'=>'ZH'][$lang]) ?></span><?= icon('chevron-down') ?></summary>
    <div class="language-options">
        <?php foreach ($supportedLanguages as $locale => $languageName): ?>
        <a href="<?= e(url($page === 'home' || $page === '404' ? '' : $page, $locale)) ?>" lang="<?= e($locale) ?>" hreflang="<?= e($locale) ?>" <?= $lang === $locale ? 'aria-current="true"' : '' ?>><?= e($languageName) ?><?= $lang === $locale ? icon('check') : '' ?></a>
        <?php endforeach; ?>
    </div>
</details>
