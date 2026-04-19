<!DOCTYPE html>
<html lang="<?= get_locale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'OOPress') ?></title>
    <link rel="stylesheet" href="<?= $this->e($theme_asset_url ?? '/themes/default/assets/') ?>css/style.css">
    <?= $this->section('seo') ?>
</head>
<body>
    <header>
        <nav>
            <a href="/"><?= __('Home') ?></a>
            
            <?php foreach (oop_menu() as $menuPage): ?>
                <a href="<?= $menuPage->getUrl() ?>"><?= $this->e($menuPage->title) ?></a>
            <?php endforeach; ?>
            
            <?php if (auth() && auth()->check()): ?>
                <a href="/dashboard"><?= __('Dashboard') ?></a>
                <a href="/logout"><?= __('Logout') ?></a>
            <?php else: ?>
                <a href="/login"><?= __('Login') ?></a>
            <?php endif; ?>
        </nav>
    </header>
    
    <main>
        <?= $this->section('content') ?>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> <?= $this->e($site_title ?? 'OOPress') ?></p>
    </footer>
    
    <script src="<?= $this->e($theme_asset_url ?? '/themes/default/assets/') ?>js/script.js"></script>

    <!-- Cookie Banner -->
    <?php
    $cookieConsent = new \OOPress\Core\CookieConsent();
    echo $cookieConsent->renderBanner();
    ?>
</body>
</html>