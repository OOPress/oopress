<?php $this->layout('layouts/admin') ?>

<div class="admin-header">
    <h1><?= __('Manage Pages') ?></h1>
    <a href="/admin/pages/create" class="btn btn-primary"><?= __('Add New Page') ?></a>
</div>

<?php if (empty($pages)): ?>
    <p><?= __('No pages found.') ?></p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th><?= __('Title') ?></th>
                <th><?= __('Slug') ?></th>
                <th><?= __('Status') ?></th>
                <th><?= __('Menu Order') ?></th>
                <th><?= __('In Menu') ?></th>
                <th><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pages as $page): ?>
            <tr>
                <td><?= str_repeat('— ', $page->parent_id > 0 ? 1 : 0) ?><?= $this->e($page->title) ?></td>
                <td><?= $this->e($page->slug) ?></td>
                <td><span class="status status-<?= $page->status ?>"><?= $page->status ?></span></td>
                <td><?= $page->menu_order ?></td>
                <td><?= $page->show_in_menu ? '✓' : '✗' ?></td>
                <td>
                    <a href="/admin/pages/<?= $page->id ?>/edit"><?= __('Edit') ?></a>
                    <a href="/admin/pages/<?= $page->id ?>/delete" onclick="return confirm('<?= __('Delete this page?') ?>')"><?= __('Delete') ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>