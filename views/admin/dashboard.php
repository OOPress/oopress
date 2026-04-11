<?php $this->layout('layouts/admin') ?>

<h1><?= __('Admin Dashboard') ?></h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?= __('Total Posts') ?></h3>
        <p class="stat-number"><?= $stats['total_posts'] ?></p>
    </div>
    
    <div class="stat-card">
        <h3><?= __('Published') ?></h3>
        <p class="stat-number"><?= $stats['published_posts'] ?></p>
    </div>
    
    <div class="stat-card">
        <h3><?= __('Drafts') ?></h3>
        <p class="stat-number"><?= $stats['draft_posts'] ?></p>
    </div>
    
    <div class="stat-card">
        <h3><?= __('Users') ?></h3>
        <p class="stat-number"><?= $stats['total_users'] ?></p>
    </div>
</div>

<div class="admin-sections">
    <div class="section">
        <h2><?= __('Recent Posts') ?></h2>
        <?php if (empty($recent_posts)): ?>
            <p><?= __('No posts yet.') ?></p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= __('Title') ?></th>
                        <th><?= __('Status') ?></th>
                        <th><?= __('Date') ?></th>
                        <th><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_posts as $post): ?>
                    <tr>
                        <td><?= $this->e($post->title) ?></td>
                        <td><span class="status status-<?= $post->status ?>"><?= $post->status ?></span></td>
                        <td><?= $post->created_at ?></td>
                        <td>
                            <a href="/admin/posts/<?= $post->id ?>/edit"><?= __('Edit') ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="/admin/posts" class="btn btn-secondary"><?= __('View All Posts') ?></a>
    </div>
    
    <div class="section">
        <h2><?= __('Recent Users') ?></h2>
        <?php if (empty($recent_users)): ?>
            <p><?= __('No users yet.') ?></p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= __('Username') ?></th>
                        <th><?= __('Email') ?></th>
                        <th><?= __('Role') ?></th>
                        <th><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $user): ?>
                    <tr>
                        <td><?= $this->e($user->username) ?></td>
                        <td><?= $this->e($user->email) ?></td>
                        <td><?= $user->role ?></td>
                        <td>
                            <a href="/admin/users/<?= $user->id ?>/edit"><?= __('Edit') ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="/admin/users" class="btn btn-secondary"><?= __('View All Users') ?></a>
    </div>
</div>