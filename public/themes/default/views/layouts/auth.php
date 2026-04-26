<!DOCTYPE html>
<html lang="<?= get_locale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'OOPress') ?></title>
    <link rel="icon" type="image/x-icon" href="<?= theme_asset('images/icon.ico') ?>">
    <link rel="stylesheet" href="<?= theme_asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= theme_asset('css/auth.css') ?>">
    <?= $this->section('seo') ?>
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <?= $this->section('content') ?>
    </div>
    
    <script src="<?= theme_asset('js/script.js') ?>"></script>
</body>
</html>
