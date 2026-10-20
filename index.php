<?php
$page = 'home';
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/head.php';
require APP_ROOT . '/includes/header.php';
?>
<main id="main">
<div class="hero-journey" data-motion-level="cinematic">
<?php require APP_ROOT . '/sections/hero.php'; require APP_ROOT . '/sections/introduction.php'; ?>
</div>
<?php foreach (['philosophy','ingredients','technology','business','brands','sustainability','history','global'] as $section) { require APP_ROOT . '/sections/' . $section . '.php'; } ?>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
