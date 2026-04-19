<?php $this->layout('layouts/admin') ?>

<h1><?= __('Cache Management') ?></h1>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['flash_success'] ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="cache-stats">
    <h2><?= __('Cache Statistics') ?></h2>
    <table class="admin-table">
        <tr>
            <th><?= __('Cache Files') ?></th>
            <td><?= $stats['files'] ?></td>
        </tr>
        <tr>
            <th><?= __('Total Size') ?></th>
            <td><?= $stats['size'] ?></td>
        </tr>
        <tr>
            <th><?= __('Cache Path') ?></th>
            <td><code><?= $stats['path'] ?></code></td>
        </tr>
    </table>
</div>

<div class="cache-actions">
    <h2><?= __('Clear Cache') ?></h2>
    
    <div class="cache-buttons">
        <form method="POST" action="/admin/cache/clear" style="display: inline;">
            <input type="hidden" name="type" value="page">
            <button type="submit" class="btn btn-primary"><?= __('Clear Page Cache') ?></button>
        </form>
        
        <form method="POST" action="/admin/cache/clear" style="display: inline;">
            <input type="hidden" name="type" value="query">
            <button type="submit" class="btn btn-primary"><?= __('Clear Query Cache') ?></button>
        </form>
        
        <form method="POST" action="/admin/cache/clear" style="display: inline;">
            <input type="hidden" name="type" value="all">
            <button type="submit" class="btn btn-danger"><?= __('Clear All Cache') ?></button>
        </form>
    </div>
</div>

<div class="cache-settings">
    <h2><?= __('Cache Settings') ?></h2>
    
    <form method="POST" action="/admin/settings/save">
        <input type="hidden" name="_group" value="cache">
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="page_cache_enabled" value="1" <?= $page_cache_enabled ? 'checked' : '' ?>>
                <?= __('Enable Page Cache') ?>
            </label>
            <small><?= __('Cache entire pages for faster loading') ?></small>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="query_cache_enabled" value="1" <?= $query_cache_enabled ? 'checked' : '' ?>>
                <?= __('Enable Query Cache') ?>
            </label>
            <small><?= __('Cache database queries') ?></small>
        </div>
        
        <div class="form-group">
            <label for="cache_ttl"><?= __('Cache TTL (seconds)') ?></label>
            <input type="number" id="cache_ttl" name="cache_ttl" value="<?= $cache_ttl ?>">
            <small><?= __('How long to cache items (default: 3600 seconds = 1 hour)') ?></small>
        </div>
        
        <div class="form-group">
            <label for="page_cache_excluded"><?= __('Excluded Paths') ?></label>
            <textarea id="page_cache_excluded" name="page_cache_excluded" rows="5"><?= \OOPress\Models\Setting::get('page_cache_excluded', "/admin\n/login\n/register\n/dashboard\n/logout") ?></textarea>
            <small><?= __('One path per line. These paths will not be cached.') ?></small>
        </div>
        
        <button type="submit" class="btn btn-primary"><?= __('Save Settings') ?></button>
    </form>
</div>

<style>
.cache-stats, .cache-actions, .cache-settings {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.cache-stats h2, .cache-actions h2, .cache-settings h2 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
}

.cache-buttons {
    display: flex;
    gap: 10px;
}

.btn-danger {
    background: #e53e3e;
    color: white;
}

.btn-danger:hover {
    background: #c53030;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}
</style>