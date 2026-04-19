<!DOCTYPE html>
<html lang="<?= get_locale() ?>" <?= $this->section('html-attributes') ?>>
<head>
    <?= $this->section('seo') ?>
    <link rel="stylesheet" href="/assets/css/style.css">
    <?= $this->section('head') ?>
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
        </nav>
    </header>
    
    <main>
        <?= $this->section('content') ?>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> OOPress</p>
    </footer>
</body>
</html>