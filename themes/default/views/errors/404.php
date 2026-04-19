<?php $this->layout('layouts/app') ?>

<div class="error-page">
    <h1><?= __('404 - Page Not Found') ?></h1>
    <p><?= __('Sorry, the page you are looking for does not exist.') ?></p>
    <a href="/" class="btn"><?= __('Return to Home') ?></a>
</div>

<style>
.error-page {
    text-align: center;
    padding: 60px 20px;
}

.error-page h1 {
    font-size: 48px;
    color: #e53e3e;
    margin-bottom: 20px;
}

.error-page .btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: #4299e1;
    color: white;
    text-decoration: none;
    border-radius: 4px;
}
</style>