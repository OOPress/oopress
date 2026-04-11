<!DOCTYPE html>
<html lang="<?= get_locale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'Admin') ?> - OOPress Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <nav class="admin-nav">
            <div class="admin-nav-brand">
                <a href="/admin">OOPress Admin</a>
            </div>
            <ul class="admin-nav-menu">
                <li><a href="/admin"><?= __('Dashboard') ?></a></li>
                <li><a href="/admin/posts"><?= __('Posts') ?></a></li>
                <li><a href="/admin/users"><?= __('Users') ?></a></li>
                <li><a href="/"><?= __('View Site') ?></a></li>
                <li><a href="/logout"><?= __('Logout') ?></a></li>
            </ul>
        </nav>
        
        <div class="admin-container">
            <main class="admin-main">
                <?= $this->section('content') ?>
            </main>
        </div>
    </div>
</body>
</html>