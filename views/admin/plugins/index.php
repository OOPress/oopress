<?php $this->layout('layouts/admin') ?>

<h1><?= __('Plugins') ?></h1>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['flash_success'] ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-error"><?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="plugins-grid">
    <?php foreach ($plugins as $plugin): ?>
        <div class="plugin-card <?= $plugin['active'] ? 'active' : '' ?>">
            <div class="plugin-info">
                <h3><?= $this->e($plugin['name']) ?></h3>
                <p class="plugin-version"><?= __('Version') ?>: <?= $plugin['version'] ?></p>
                <p class="plugin-author"><?= __('By') ?>: <?= $plugin['author'] ?></p>
                <p class="plugin-description"><?= $this->e($plugin['description']) ?></p>
            </div>
            <div class="plugin-actions">
                <?php if ($plugin['active']): ?>
                    <form method="POST" action="/admin/plugins/deactivate">
                        <input type="hidden" name="plugin" value="<?= $plugin['slug'] ?>">
                        <button type="submit" class="btn btn-secondary"><?= __('Deactivate') ?></button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="/admin/plugins/activate">
                        <input type="hidden" name="plugin" value="<?= $plugin['slug'] ?>">
                        <button type="submit" class="btn btn-primary"><?= __('Activate') ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.plugins-grid {
    margin-top: 20px;
}

.plugin-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 15px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
}

.plugin-card.active {
    border-left: 4px solid #4299e1;
    background: #f7fafc;
}

.plugin-info {
    flex: 1;
}

.plugin-info h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
}

.plugin-version, .plugin-author {
    font-size: 12px;
    color: #718096;
    margin: 3px 0;
}

.plugin-description {
    font-size: 14px;
    color: #4a5568;
    margin-top: 10px;
}

.plugin-actions {
    margin-left: 20px;
}
</style>