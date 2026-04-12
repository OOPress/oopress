<?php $this->layout('layouts/app') ?>

<div class="archive-header">
    <h1><?= $this->e($title) ?></h1>
    <?php if ($category->description): ?>
        <p class="archive-description"><?= $this->e($category->description) ?></p>
    <?php endif; ?>
</div>

<?php if (empty($posts)): ?>
    <p><?= __('No posts found in this category.') ?></p>
<?php else: ?>
    <div class="posts-list">
        <?php foreach ($posts as $post): ?>
            <article class="post-preview">
                <h2>
                    <a href="/post/<?= $post->slug ?>">
                        <?= $this->e($post->title) ?>
                    </a>
                </h2>
                <div class="post-meta">
                    <?= date($date_format, strtotime($post->published_at ?? $post->created_at)) ?>
                </div>
                <div class="post-excerpt">
                    <?= strlen($post->excerpt) > 100 ? substr($post->excerpt, 0, 100) . '...' : $post->excerpt ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>