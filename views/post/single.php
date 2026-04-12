<?php $this->layout('layouts/app') ?>

<article class="post-single">
    <h1><?= $this->e($post->title) ?></h1>
    
    <div class="post-meta">
        <span class="post-date">
            <?= __('Posted on') ?> <?= date($date_format, strtotime($post->published_at ?? $post->created_at)) ?>
        </span>
        
        <?php if (!empty($categories)): ?>
            <span class="post-categories">
                <?= __('in') ?>
                <?php foreach ($categories as $i => $cat): ?>
                    <a href="/category/<?= $cat->slug ?>"><?= $this->e($cat->name) ?></a><?= $i < count($categories)-1 ? ', ' : '' ?>
                <?php endforeach; ?>
            </span>
        <?php endif; ?>
    </div>
    
    <?php if ($post->featured_image): ?>
        <div class="post-featured-image">
            <img src="<?= $post->featured_image ?>" alt="<?= $this->e($post->title) ?>">
        </div>
    <?php endif; ?>
    
    <div class="post-content">
        <?= $post->content ?>
    </div>
    
    <?php if (!empty($tags)): ?>
        <div class="post-tags">
            <strong><?= __('Tags') ?>:</strong>
            <?php foreach ($tags as $i => $tag): ?>
                <a href="/tag/<?= $tag->slug ?>" class="tag"><?= $this->e($tag->name) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="post-footer">
        <a href="/" class="back-to-home">← <?= __('Back to Home') ?></a>
    </div>
</article>