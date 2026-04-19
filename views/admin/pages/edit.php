<?php $this->layout('layouts/admin') ?>

<?php $this->insert('partials/tinymce') ?>

<h1><?= __('Edit Page') ?>: <?= $this->e($page->title) ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="/admin/pages/<?= $page->id ?>/edit">
    <div class="form-group">
        <label for="title"><?= __('Title') ?> *</label>
        <input type="text" id="title" name="title" value="<?= $this->e($page->title) ?>" required>
    </div>

    <!-- Page Settings -->
    <div class="meta-box">
        <h3><?= __('Page Settings') ?></h3>
        <div class="meta-box-content">
            <div class="form-group">
                <label for="parent_id"><?= __('Parent Page') ?></label>
                <select id="parent_id" name="parent_id">
                    <option value="0"><?= __('No Parent') ?></option>
                    <?php foreach ($pages as $p): ?>
                        <?php if ($p->id !== $page->id): ?>
                            <option value="<?= $p->id ?>" <?= $p->id === $page->parent_id ? 'selected' : '' ?>>
                                <?= $this->e($p->title) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="page_template"><?= __('Template') ?></label>
                <select id="page_template" name="page_template">
                    <?php foreach ($templates as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $page->page_template === $key ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="show_in_menu" value="1" <?= $page->show_in_menu ? 'checked' : '' ?>>
                    <?= __('Show in Menu') ?>
                </label>
            </div>
            
            <div class="form-group">
                <label for="menu_order"><?= __('Menu Order') ?></label>
                <input type="number" id="menu_order" name="menu_order" value="<?= $page->menu_order ?>">
            </div>
        </div>
    </div>

    <!-- Content Editor -->
    <div class="form-group">
        <label for="content-tinymce"><?= __('Content') ?></label>
        <textarea id="content-tinymce" name="content"><?= $this->e($page->content) ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="excerpt"><?= __('Excerpt') ?></label>
        <textarea id="excerpt" name="excerpt" rows="3"><?= $this->e($page->excerpt) ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="status"><?= __('Status') ?></label>
        <select id="status" name="status">
            <option value="draft" <?= $page->status === 'draft' ? 'selected' : '' ?>><?= __('Draft') ?></option>
            <option value="published" <?= $page->status === 'published' ? 'selected' : '' ?>><?= __('Published') ?></option>
        </select>
    </div>
    
    <!-- SEO Settings -->
    <div class="meta-box">
        <h3><?= __('SEO Settings') ?></h3>
        <div class="meta-box-content">
            <div class="form-group">
                <label for="meta_title"><?= __('Meta Title') ?></label>
                <input type="text" id="meta_title" name="meta_title" value="<?= $this->e($page->meta_title) ?>">
            </div>
            
            <div class="form-group">
                <label for="meta_description"><?= __('Meta Description') ?></label>
                <textarea id="meta_description" name="meta_description" rows="3"><?= $this->e($page->meta_description) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="meta_keywords"><?= __('Meta Keywords') ?></label>
                <input type="text" id="meta_keywords" name="meta_keywords" value="<?= $this->e($page->meta_keywords) ?>">
            </div>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary"><?= __('Update Page') ?></button>
    <a href="/admin/pages" class="btn btn-secondary"><?= __('Cancel') ?></a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined' && !tinymce.get('content-tinymce')) {
        tinymce.init({
            selector: '#content-tinymce',
            license_key: 'gpl',
            height: 500,
            menubar: true,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image | help'
        });
    }
});
</script>

<style>
.meta-box {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 20px;
}

.meta-box h3 {
    margin: 0;
    padding: 12px 15px;
    background: #edf2f7;
    border-bottom: 1px solid #e2e8f0;
    font-size: 16px;
}

.meta-box-content {
    padding: 15px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
}

.btn {
    display: inline-block;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
}

.btn-primary {
    background: #4299e1;
    color: white;
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}
</style>