<?php $this->layout('layouts/admin') ?>

<div class="admin-header">
    <h1><?= __('Manage Tags') ?></h1>
    <button id="add-tag-btn" class="btn btn-primary"><?= __('Add New Tag') ?></button>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<div id="add-tag-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.2); z-index:1000; min-width:400px;">
    <h3><?= __('Add New Tag') ?></h3>
    <form method="POST" action="/admin/tags/create">
        <div class="form-group">
            <label for="name"><?= __('Name') ?> *</label>
            <input type="text" id="name" name="name" required autofocus>
        </div>
        <div class="form-group">
            <label for="description"><?= __('Description') ?></label>
            <textarea id="description" name="description" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?= __('Create Tag') ?></button>
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-tag-modal').style.display='none'"><?= __('Cancel') ?></button>
    </form>
</div>

<?php if (empty($tags)): ?>
    <p><?= __('No tags found. Click "Add New Tag" to create one.') ?></p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th><?= __('ID') ?></th>
                <th><?= __('Name') ?></th>
                <th><?= __('Slug') ?></th>
                <th><?= __('Description') ?></th>
                <th><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tags as $tag): ?>
            <tr>
                <td><?= $tag->id ?></td>
                <td><?= $this->e($tag->name) ?></td>
                <td><?= $this->e($tag->slug) ?></td>
                <td><?= $this->e($tag->description) ?></td>
                <td>
                    <a href="/admin/tags/<?= $tag->id ?>/edit"><?= __('Edit') ?></a>
                    <a href="/admin/tags/<?= $tag->id ?>/delete" onclick="return confirm('<?= __('Delete this tag?') ?>')"><?= __('Delete') ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
document.getElementById('add-tag-btn').onclick = function() {
    document.getElementById('add-tag-modal').style.display = 'block';
};
</script>