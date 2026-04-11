<?php $this->layout('layouts/admin') ?>

<div class="admin-header">
    <h1><?= __('Manage Users') ?></h1>
</div>

<?php if (empty($users)): ?>
    <p><?= __('No users found.') ?></p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th><?= __('ID') ?></th>
                <th><?= __('Username') ?></th>
                <th><?= __('Email') ?></th>
                <th><?= __('Role') ?></th>
                <th><?= __('Status') ?></th>
                <th><?= __('Registered') ?></th>
                <th><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user->id ?></td>
                <td><?= $this->e($user->username) ?></td>
                <td><?= $this->e($user->email) ?></td>
                <td><?= $user->role ?></td>
                <td><?= $user->status ?></td>
                <td><?= $user->created_at ?></td>
                <td>
                    <a href="/admin/users/<?= $user->id ?>/edit"><?= __('Edit') ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p><a href="/admin" class="btn btn-secondary"><?= __('Back to Dashboard') ?></a></p>