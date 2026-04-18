<?php $this->layout('layouts/admin') ?>

<h1><?= __('Themes') ?></h1>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['flash_success'] ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-error"><?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="themes-grid">
    <?php foreach ($themes as $theme): ?>
        <div class="theme-card <?= $theme['active'] ? 'active' : '' ?>">
            <div class="theme-screenshot">
                <?php if (file_exists(__DIR__ . '/../../../themes/' . $theme['name'] . '/screenshot.png')): ?>
                    <img src="/themes/<?= $theme['name'] ?>/screenshot.png" alt="<?= $theme['title'] ?>">
                <?php else: ?>
                    <div class="no-screenshot"><?= __('No screenshot') ?></div>
                <?php endif; ?>
            </div>
            <div class="theme-info">
                <h3><?= $theme['title'] ?></h3>
                <p class="theme-version"><?= __('Version') ?>: <?= $theme['version'] ?></p>
                <p class="theme-author"><?= __('By') ?>: <?= $theme['author'] ?></p>
                <p class="theme-description"><?= $theme['description'] ?></p>
            </div>
            <div class="theme-actions">
                <?php if ($theme['active']): ?>
                    <span class="badge active"><?= __('Active') ?></span>
                <?php else: ?>
                    <form method="POST" action="/admin/themes/activate">
                        <input type="hidden" name="theme" value="<?= $theme['name'] ?>">
                        <button type="submit" class="btn btn-primary"><?= __('Activate') ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.themes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.theme-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    transition: all 0.2s;
}

.theme-card.active {
    border-color: #4299e1;
    box-shadow: 0 0 0 2px rgba(66, 153, 225, 0.2);
}

.theme-screenshot {
    height: 200px;
    background: #f7fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.theme-screenshot img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-screenshot {
    color: #a0aec0;
    font-size: 14px;
}

.theme-info {
    padding: 15px;
}

.theme-info h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
}

.theme-version, .theme-author {
    font-size: 12px;
    color: #718096;
    margin: 5px 0;
}

.theme-description {
    font-size: 14px;
    color: #4a5568;
    margin-top: 10px;
}

.theme-actions {
    padding: 15px;
    border-top: 1px solid #e2e8f0;
    text-align: center;
}

.badge.active {
    display: inline-block;
    padding: 6px 12px;
    background: #c6f6d5;
    color: #22543d;
    border-radius: 4px;
    font-size: 14px;
}
</style>