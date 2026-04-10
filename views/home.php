<?php $this->layout('layouts/app') ?>

<h1><?= $this->e($title) ?></h1>

<?php if (empty($posts)): ?>
    <p><?= __('No posts found.') ?></p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article class="post-preview">
            <h2>
                <a href="/post/<?= $post->slug ?>">
                    <?= $this->e($post->title) ?>
                </a>
            </h2>
            <div class="post-meta">
                <?= __('Posted on') ?> <?= $post->published_at ?>
            </div>
            <div class="post-excerpt">
                <?= $post->excerpt ?>
            </div>
            <a href="/post/<?= $post->slug ?>" class="read-more">
                <?= __('Read more') ?> →
            </a>
        </article>
    <?php endforeach; ?>
<?php endif; ?>