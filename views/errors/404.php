<?php 
// Ensure $seo is passed from the controller
if (!isset($seo)) {
    $seo = new \OOPress\Core\SEO();
    $seo->set404();
}

// Render SEO in head
$this->section('seo', $seo->render());
?>

<?php $this->layout('layouts/app') ?>

<div class="error-page">
    <h1><?= __('404 - Page Not Found') ?></h1>
    <p><?= __('Sorry, the page you are looking for does not exist.') ?></p>
    <a href="/" class="btn"><?= __('Return to Home') ?></a>
</div>