<?php $this->layout('layouts/admin') ?>

<h1><?= __('Edit Post') ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="/admin/posts/<?= $post->id ?>/edit">
    <div class="form-group">
        <label for="title"><?= __('Title') ?> *</label>
        <input type="text" id="title" name="title" value="<?= $this->e($post->title) ?>" required>
    </div>
    
    <div class="form-group">
        <label for="content"><?= __('Content') ?></label>
        <textarea id="content" name="content" rows="15"><?= $this->e($post->content) ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="excerpt"><?= __('Excerpt') ?></label>
        <textarea id="excerpt" name="excerpt" rows="3"><?= $this->e($post->excerpt) ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="status"><?= __('Status') ?></label>
        <select id="status" name="status">
            <option value="draft" <?= $post->status === 'draft' ? 'selected' : '' ?>><?= __('Draft') ?></option>
            <option value="published" <?= $post->status === 'published' ? 'selected' : '' ?>><?= __('Published') ?></option>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary"><?= __('Update Post') ?></button>
    <a href="/admin/posts" class="btn btn-secondary"><?= __('Cancel') ?></a>
</form>