<?php $this->layout('layouts/admin') ?>

<h1><?= __('Edit Tag') ?>: <?= $this->e($tag->name) ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="/admin/tags/<?= $tag->id ?>/edit">
    <div class="form-group">
        <label for="name"><?= __('Name') ?> *</label>
        <input type="text" id="name" name="name" value="<?= $this->e($tag->name) ?>" required>
    </div>
    
    <div class="form-group">
        <label for="description"><?= __('Description') ?></label>
        <textarea id="description" name="description" rows="3"><?= $this->e($tag->description) ?></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary"><?= __('Update Tag') ?></button>
    <a href="/admin/tags" class="btn btn-secondary"><?= __('Cancel') ?></a>
</form>