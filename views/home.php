<?php $this->layout('layouts/app') ?>

<h1><?= $this->e($title) ?></h1>

<?php foreach ($posts as $post): ?>
    <article>
        <h2>
            <a href="/post/<?= $this->e($post->slug) ?>">
                <?= $this->e($post->title) ?>
            </a>
        </h2>
        <p><?= $this->e($post->excerpt ?? '') ?></p>
    </article>
<?php endforeach; ?>