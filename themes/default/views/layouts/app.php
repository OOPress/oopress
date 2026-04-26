<!DOCTYPE html>
<html lang="<?= get_locale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'OOPress') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= theme_asset('images/icon.svg') ?>">
    <link rel="stylesheet" href="<?= theme_asset('css/style.css') ?>">
    <?= $this->section('seo') ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="site-logo">
                <a href="/">
                    <img src="<?= theme_asset('images/logo-light.svg') ?>" alt="OOPress">
                </a>
            </div>
            <nav class="site-nav">
                <a href="/"><?= __('Home') ?></a>
                <?php foreach (oop_menu() as $menuPage): ?>
                    <a href="<?= $menuPage->getUrl() ?>"><?= $this->e($menuPage->title) ?></a>
                <?php endforeach; ?>
                <a href="/contact"><?= __('Contact') ?></a>
                <?php if (auth() && auth()->check()): ?>
                    <a href="/admin"><?= __('Dashboard') ?></a>
                    <a href="/logout"><?= __('Logout') ?></a>
                <?php else: ?>
                    <a href="/login"><?= __('Login') ?></a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main class="site-main">
        <div class="container">
            <?= $this->section('content') ?>
        </div>
    </main>
    
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <span class="oo">OO</span><span class="press">Press</span></p>
            <div class="footer-links">
                <a href="/privacy-policy"><?= __('Privacy Policy') ?></a>
                <a href="/imprint"><?= __('Imprint') ?></a>
                <a href="/contact"><?= __('Contact') ?></a>
            </div>
        </div>
    </footer>
    
    <script src="<?= theme_asset('js/script.js') ?>"></script>
</body>
</html>