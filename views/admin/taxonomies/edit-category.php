<?php $this->layout('layouts/admin') ?>

<h1><?= __('Edit Category') ?>: <?= $this->e($category->name) ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="/admin/categories/<?= $category->id ?>/edit">
    <div class="form-group">
        <label for="name"><?= __('Name') ?> *</label>
        <input type="text" id="name" name="name" value="<?= $this->e($category->name) ?>" required>
    </div>
    
    <div class="form-group">
        <label for="description"><?= __('Description') ?></label>
        <textarea id="description" name="description" rows="3"><?= $this->e($category->description) ?></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary"><?= __('Update Category') ?></button>
    <a href="/admin/categories" class="btn btn-secondary"><?= __('Cancel') ?></a>
</form>