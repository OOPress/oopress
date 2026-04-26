<?php $this->layout('layouts/app') ?>

<div class="page-content">
    <h1><?= __('About OOPress') ?></h1>
    
    <div class="about-content">
        <div class="about-header">
            <img src="<?= theme_asset('images/logo-light.svg') ?>" alt="OOPress" class="about-logo">
            <h2><?= oopress_name() ?> v<?= oopress_version() ?></h2>
            <p class="about-description">Lean, modern PHP CMS with clean OOP architecture</p>
        </div>
        
        <div class="about-grid">
            <div class="about-section">
                <h3><?= __('Features') ?></h3>
                <ul>
                    <li><?= __('Clean Object-Oriented Architecture') ?></li>
                    <li><?= __('Modern PHP 8.2+ Requirements') ?></li>
                    <li><?= __('Flexible Theme System') ?></li>
                    <li><?= __('Plugin Architecture') ?></li>
                    <li><?= __('RESTful Routing') ?></li>
                    <li><?= __('Database Migrations') ?></li>
                    <li><?= __('User Authentication & Authorization') ?></li>
                    <li><?= __('Admin Dashboard') ?></li>
                </ul>
            </div>
            
            <div class="about-section">
                <h3><?= __('Technical Details') ?></h3>
                <ul>
                    <li><strong><?= __('Version') ?>:</strong> <?= oopress_version() ?></li>
                    <li><strong><?= __('License') ?>:</strong> Apache-2.0</li>
                    <li><strong><?= __('Author') ?>:</strong> OOPress Team</li>
                    <li><strong><?= __('PHP Version') ?>:</strong> <?= PHP_VERSION ?></li>
                    <li><strong><?= __('Database') ?>:</strong> MySQL/MariaDB</li>
                    <li><strong><?= __('Homepage') ?>:</strong> <a href="https://oopress.org" target="_blank">https://oopress.org</a></li>
                </ul>
            </div>
        </div>
        
        <div class="about-section">
            <h3><?= __('License') ?></h3>
            <p><?= oopress_name() ?> is released under the Apache-2.0 license. You are free to use, modify, and distribute this software according to the terms of the license.</p>
        </div>
        
        <div class="about-section">
            <h3><?= __('Support') ?></h3>
            <p><?= __('For support, documentation, and contributions, please visit our homepage at') ?> <a href="https://oopress.org" target="_blank">https://oopress.org</a>.</p>
        </div>
    </div>
</div>

<style>
.about-content {
    max-width: 800px;
    margin: 0 auto;
}

.about-header {
    text-align: center;
    margin-bottom: 3rem;
}

.about-logo {
    height: 80px;
    margin-bottom: 1rem;
}

.about-header h2 {
    color: var(--oopress-orange);
    margin-bottom: 0.5rem;
}

.about-description {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 2rem;
}

.about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.about-section {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.about-section h3 {
    color: var(--oopress-orange);
    margin-bottom: 1rem;
    border-bottom: 2px solid var(--oopress-orange);
    padding-bottom: 0.5rem;
}

.about-section ul {
    list-style: none;
    padding: 0;
}

.about-section li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.about-section li:last-child {
    border-bottom: none;
}

.about-section a {
    color: var(--oopress-orange);
    text-decoration: none;
}

.about-section a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .about-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .about-section {
        padding: 1.5rem;
    }
}
</style>
