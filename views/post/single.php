<?php $this->layout('layouts/app') ?>

<article>
    <h1><?= $this->e($post->title) ?></h1>
    
    <div class="meta">
        Posted on <?= $post->created_at ?>
    </div>
    
    <div class="content">
        <?= $post->content ?>
    </div>
</article>