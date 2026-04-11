<?php $this->layout('layouts/admin') ?>

<div class="admin-header">
    <h1><?= __('Media Library') ?></h1>
    <button id="upload-btn" class="btn btn-primary"><?= __('Upload File') ?></button>
</div>

<?php if ($error ?? false): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<div id="upload-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.2); z-index:1000;">
    <h3><?= __('Upload File') ?></h3>
    <form id="upload-form" method="POST" action="/admin/media/upload" enctype="multipart/form-data">
        <input type="file" name="file" accept="image/*,application/pdf,.txt,.doc,.docx" required>
        <button type="submit"><?= __('Upload') ?></button>
        <button type="button" onclick="document.getElementById('upload-modal').style.display='none'"><?= __('Cancel') ?></button>
    </form>
</div>

<?php if (empty($media)): ?>
    <p><?= __('No media files found. Click "Upload File" to add some.') ?></p>
<?php else: ?>
    <div class="media-grid">
        <?php foreach ($media as $item): ?>
            <div class="media-item">
                <?php if (strpos($item->mime_type, 'image/') === 0): ?>
                    <img src="<?= $item->url ?>" alt="<?= $this->e($item->original_name) ?>">
                <?php else: ?>
                    <div class="media-icon">
                        📄 <?= strtoupper(pathinfo($item->original_name, PATHINFO_EXTENSION)) ?>
                    </div>
                <?php endif; ?>
                <div class="media-info">
                    <div class="media-name" title="<?= $this->e($item->original_name) ?>">
                        <?php 
                        $name = $item->original_name;
                        echo strlen($name) > 30 ? substr($name, 0, 30) . '...' : $name;
                        ?>
                    </div>
                    <div class="media-meta">
                        <?= $item->getFormattedSize() ?>
                    </div>
                    <div class="media-actions">
                        <button class="copy-url-btn" data-url="<?= $item->url ?>"><?= __('Copy URL') ?></button>
                        <a href="/admin/media/<?= $item->id ?>/delete" onclick="return confirm('<?= __('Delete this file?') ?>')"><?= __('Delete') ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
document.getElementById('upload-btn').onclick = function() {
    document.getElementById('upload-modal').style.display = 'block';
};

// Copy URL functionality
document.querySelectorAll('.copy-url-btn').forEach(btn => {
    btn.onclick = function() {
        const url = this.dataset.url;
        navigator.clipboard.writeText(url);
        this.textContent = '✓ Copied!';
        setTimeout(() => {
            this.textContent = 'Copy URL';
        }, 2000);
    };
});
</script>

<style>
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.media-item {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background: white;
}

.media-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.media-icon {
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    background: #f7fafc;
}

.media-info {
    padding: 10px;
}

.media-name {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.media-meta {
    font-size: 12px;
    color: #718096;
    margin-bottom: 10px;
}

.media-actions {
    display: flex;
    gap: 10px;
}

.media-actions button,
.media-actions a {
    font-size: 12px;
    padding: 4px 8px;
    background: #e2e8f0;
    color: #4a5568;
    text-decoration: none;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
</style>