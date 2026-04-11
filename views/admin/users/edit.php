<?php $this->layout('layouts/admin') ?>

<h1><?= __('Edit User') ?>: <?= $this->e($user->username) ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="/admin/users/<?= $user->id ?>/edit">
    <div class="form-group">
        <label for="username"><?= __('Username') ?></label>
        <input type="text" id="username" value="<?= $this->e($user->username) ?>" disabled>
        <small><?= __('Username cannot be changed') ?></small>
    </div>
    
    <div class="form-group">
        <label for="display_name"><?= __('Display Name') ?></label>
        <input type="text" id="display_name" name="display_name" value="<?= $this->e($user->display_name ?? $user->username) ?>">
    </div>
    
    <div class="form-group">
        <label for="email"><?= __('Email') ?></label>
        <input type="email" id="email" name="email" value="<?= $this->e($user->email) ?>" required>
    </div>
    
    <div class="form-group">
        <label for="role"><?= __('Role') ?></label>
        <select id="role" name="role">
            <option value="admin" <?= $user->role === 'admin' ? 'selected' : '' ?>><?= __('Admin') ?></option>
            <option value="editor" <?= $user->role === 'editor' ? 'selected' : '' ?>><?= __('Editor') ?></option>
            <option value="author" <?= $user->role === 'author' ? 'selected' : '' ?>><?= __('Author') ?></option>
            <option value="subscriber" <?= $user->role === 'subscriber' ? 'selected' : '' ?>><?= __('Subscriber') ?></option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="status"><?= __('Status') ?></label>
        <select id="status" name="status">
            <option value="active" <?= $user->status === 'active' ? 'selected' : '' ?>><?= __('Active') ?></option>
            <option value="inactive" <?= $user->status === 'inactive' ? 'selected' : '' ?>><?= __('Inactive') ?></option>
            <option value="banned" <?= $user->status === 'banned' ? 'selected' : '' ?>><?= __('Banned') ?></option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="password"><?= __('New Password') ?></label>
        <input type="password" id="password" name="password">
        <small><?= __('Leave blank to keep current password') ?></small>
    </div>
    
    <button type="submit" class="btn btn-primary"><?= __('Update User') ?></button>
    <a href="/admin/users" class="btn btn-secondary"><?= __('Cancel') ?></a>
</form>