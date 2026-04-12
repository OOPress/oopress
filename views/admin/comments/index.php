<?php $this->layout('layouts/admin') ?>

<div class="admin-header">
    <h1><?= __('Manage Comments') ?></h1>
</div>

<div class="comment-tabs">
    <a href="?status=pending" class="tab <?= $current_status === 'pending' ? 'active' : '' ?>">
        <?= __('Pending') ?> (<?= $pending_count ?>)
    </a>
    <a href="?status=approved" class="tab <?= $current_status === 'approved' ? 'active' : '' ?>">
        <?= __('Approved') ?>
    </a>
    <a href="?status=spam" class="tab <?= $current_status === 'spam' ? 'active' : '' ?>">
        <?= __('Spam') ?>
    </a>
    <a href="?status=trash" class="tab <?= $current_status === 'trash' ? 'active' : '' ?>">
        <?= __('Trash') ?>
    </a>
</div>

<?php if (empty($comments)): ?>
    <p><?= __('No comments found.') ?></p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th><?= __('Author') ?></th>
                <th><?= __('Comment') ?></th>
                <th><?= __('Post') ?></th>
                <th><?= __('Date') ?></th>
                <th><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $comment): ?>
                <tr>
                    <td>
                        <strong><?= $this->e($comment->author_name) ?></strong><br>
                        <small><?= $this->e($comment->author_email) ?></small>
                    </td>
                    <td>
                        <?= strlen($comment->content) > 100 ? substr($comment->content, 0, 100) . '...' : $comment->content ?>
                    </td>
                    <td>
                        <a href="/post/<?= $comment->post()->slug ?>#comment-<?= $comment->id ?>" target="_blank">
                            <?= $this->e($comment->post()->title) ?>
                        </a>
                    </td>
                    <td><?= $comment->created_at ?></td>
                    <td>
                        <?php if ($current_status === 'pending'): ?>
                            <a href="/admin/comments/<?= $comment->id ?>/approve"><?= __('Approve') ?></a>
                            <a href="/admin/comments/<?= $comment->id ?>/spam"><?= __('Spam') ?></a>
                        <?php endif; ?>
                        <?php if ($current_status === 'approved'): ?>
                            <a href="/admin/comments/<?= $comment->id ?>/trash"><?= __('Trash') ?></a>
                        <?php endif; ?>
                        <?php if ($current_status === 'spam' || $current_status === 'trash'): ?>
                            <a href="/admin/comments/<?= $comment->id ?>/delete" onclick="return confirm('<?= __('Delete permanently?') ?>')"><?= __('Delete') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>