<?php $this->layout('layouts/app') ?>

<div class="user-dashboard">
    <h1><?= __('User Dashboard') ?></h1>
    
    <div class="dashboard-welcome">
        <p><?= __('Welcome back,') ?> <strong><?= $this->e($user->display_name ?? $user->username) ?></strong>!</p>
    </div>
    
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3><?= __('Your Information') ?></h3>
            <div class="card-content">
                <p><strong><?= __('Username') ?>:</strong> <?= $this->e($user->username) ?></p>
                <p><strong><?= __('Email') ?>:</strong> <?= $this->e($user->email) ?></p>
                <p><strong><?= __('Role') ?>:</strong> <?= $user->role ?></p>
                <p><strong><?= __('Member since') ?>:</strong> <?= date('F j, Y', strtotime($user->created_at)) ?></p>
                <?php if ($user->last_login): ?>
                    <p><strong><?= __('Last login') ?>:</strong> <?= date('F j, Y g:i a', strtotime($user->last_login)) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($user->role === 'admin' || $user->role === 'editor'): ?>
        <div class="dashboard-card">
            <h3><?= __('Quick Stats') ?></h3>
            <div class="card-content">
                <p><strong><?= __('Total Posts') ?>:</strong> <?= $stats['total_posts'] ?? 0 ?></p>
                <p><strong><?= __('Published') ?>:</strong> <?= $stats['published_posts'] ?? 0 ?></p>
                <p><strong><?= __('Comments') ?>:</strong> <?= $stats['comments'] ?? 0 ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="dashboard-card">
            <h3><?= __('Quick Links') ?></h3>
            <div class="card-content">
                <ul class="dashboard-links">
                    <?php if ($user->role === 'admin' || $user->role === 'editor'): ?>
                        <li><a href="/admin/posts">📝 <?= __('Manage Posts') ?></a></li>
                        <li><a href="/admin/pages">📄 <?= __('Manage Pages') ?></a></li>
                    <?php endif; ?>
                    <?php if ($user->role === 'admin'): ?>
                        <li><a href="/admin/users">👥 <?= __('Manage Users') ?></a></li>
                        <li><a href="/admin/settings">⚙️ <?= __('Site Settings') ?></a></li>
                    <?php endif; ?>
                    <li><a href="/admin">🔧 <?= __('Admin Panel') ?></a></li>
                    <li><a href="/logout">🚪 <?= __('Logout') ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>