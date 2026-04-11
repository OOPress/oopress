<?php $this->layout('layouts/admin') ?>

<div class="admin-header">
    <h1><?= __('Manage Posts') ?></h1>
    <a href="/admin/posts/create" class="btn btn-primary"><?= __('Add New Post') ?></a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th><?= __('Title') ?></th>
            <th><?= __('Slug') ?></th>
            <th><?= __('Status') ?></th>
            <th><?= __('Author') ?></th>
            <th><?= __('Date') ?></th>
            <th><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($posts as $post): ?>
        <tr>
            <td><?= $this->e($post->title) ?></td>
            <td><?= $this->e($post->slug) ?></td>
            <td><span class="status status-<?= $post->status ?>"><?= $post->status ?></span></td>
            <td><?= $post->author_id ?></td>
            <td><?= $post->created_at ?></td>
            <td>
                <a href="/admin/posts/<?= $post->id ?>/edit"><?= __('Edit') ?></a>
                <a href="/admin/posts/<?= $post->id ?>/delete" onclick="return confirm('<?= __('Delete this post?') ?>')"><?= __('Delete') ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>