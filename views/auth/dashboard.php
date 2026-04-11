<?php $this->layout('layouts/app') ?>

<h1><?= __('Dashboard') ?></h1>

<div class="dashboard-welcome">
    <p><?= __('Welcome') ?>, <strong><?= $this->e($user->display_name ?? $user->username) ?></strong>!</p>
    
    <div class="user-info">
        <h3><?= __('Your Information') ?></h3>
        <p><strong><?= __('Email') ?>:</strong> <?= $this->e($user->email) ?></p>
        <p><strong><?= __('Role') ?>:</strong> <?= $this->e($user->role) ?></p>
        <p><strong><?= __('Member since') ?>:</strong> <?= $user->created_at ?></p>
        <?php if ($user->last_login): ?>
            <p><strong><?= __('Last login') ?>:</strong> <?= $user->last_login ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="dashboard-actions">
    <a href="/" class="btn"><?= __('View Site') ?></a>
    <a href="/logout" class="btn btn-secondary"><?= __('Logout') ?></a>
</div>