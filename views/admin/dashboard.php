<?php $this->layout('layouts/admin') ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><?= __('Dashboard') ?></h1>
        <p class="welcome-text"><?= __('Welcome back,') ?> <strong><?= $_SESSION['user_display_name'] ?? $_SESSION['user_username'] ?? 'Admin' ?></strong>!</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3><?= __('Total Posts') ?></h3>
                <p class="stat-number"><?= $stats['total_posts'] ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3><?= __('Published') ?></h3>
                <p class="stat-number"><?= $stats['published_posts'] ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📄</div>
            <div class="stat-content">
                <h3><?= __('Drafts') ?></h3>
                <p class="stat-number"><?= $stats['draft_posts'] ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?= __('Users') ?></h3>
                <p class="stat-number"><?= $stats['total_users'] ?></p>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="dashboard-grid">
        <!-- Recent Posts -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><?= __('Recent Posts') ?></h3>
                <a href="/admin/posts" class="card-link"><?= __('View All') ?> →</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_posts)): ?>
                    <p class="empty-message"><?= __('No posts yet.') ?> <a href="/admin/posts/create"><?= __('Create one') ?></a></p>
                <?php else: ?>
                    <table class="dashboard-table">
                        <tbody>
                            <?php foreach ($recent_posts as $post): ?>
                                <tr>
                                    <td class="post-title">
                                        <a href="/admin/posts/<?= $post->id ?>/edit"><?= $this->e($post->title) ?></a>
                                    </td>
                                    <td class="post-status">
                                        <span class="status-badge status-<?= $post->status ?>"><?= $post->status ?></span>
                                    </td>
                                    <td class="post-date"><?= date('M j, Y', strtotime($post->created_at)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><?= __('Recent Users') ?></h3>
                <a href="/admin/users" class="card-link"><?= __('View All') ?> →</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_users)): ?>
                    <p class="empty-message"><?= __('No users yet.') ?></p>
                <?php else: ?>
                    <table class="dashboard-table">
                        <tbody>
                            <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td class="user-info">
                                        <div class="user-avatar"><?= strtoupper(substr($user->username, 0, 1)) ?></div>
                                        <div class="user-details">
                                            <a href="/admin/users/<?= $user->id ?>/edit"><?= $this->e($user->username) ?></a>
                                            <small><?= $this->e($user->email) ?></small>
                                        </div>
                                    </td>
                                    <td class="user-role">
                                        <span class="role-badge role-<?= $user->role ?>"><?= $user->role ?></span>
                                    </td>
                                    <td class="user-date"><?= date('M j, Y', strtotime($user->created_at)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3><?= __('Quick Actions') ?></h3>
        <div class="action-buttons">
            <a href="/admin/posts/create" class="action-btn">
                <span class="action-icon">✏️</span>
                <span><?= __('New Post') ?></span>
            </a>
            <a href="/admin/pages/create" class="action-btn">
                <span class="action-icon">📄</span>
                <span><?= __('New Page') ?></span>
            </a>
            <a href="/admin/media" class="action-btn">
                <span class="action-icon">🖼️</span>
                <span><?= __('Upload Media') ?></span>
            </a>
            <a href="/admin/users/create" class="action-btn">
                <span class="action-icon">👤</span>
                <span><?= __('Add User') ?></span>
            </a>
        </div>
    </div>
</div>