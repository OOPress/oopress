<?php $this->layout('layouts/admin') ?>

<h1><?= __('Create New Post') ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="/admin/posts/create">
    <div class="form-group">
        <label for="title"><?= __('Title') ?> *</label>
        <input type="text" id="title" name="title" required autofocus>
    </div>
    
    <div class="form-group">
        <label for="content"><?= __('Content') ?></label>
        <textarea id="content" name="content" rows="15"></textarea>
    </div>
    
    <div class="form-group">
        <label for="excerpt"><?= __('Excerpt') ?></label>
        <textarea id="excerpt" name="excerpt" rows="3"></textarea>
    </div>
    
    <div class="form-group">
        <label for="status"><?= __('Status') ?></label>
        <select id="status" name="status">
            <option value="draft"><?= __('Draft') ?></option>
            <option value="published"><?= __('Published') ?></option>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary"><?= __('Create Post') ?></button>
    <a href="/admin/posts" class="btn btn-secondary"><?= __('Cancel') ?></a>
</form>