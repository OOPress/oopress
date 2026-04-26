<?php $this->layout('layouts/app') ?>

<div class="hero">
    <h1><?= $this->e($title) ?></h1>
    <?php if ($tagline): ?>
        <p class="tagline"><?= $this->e($tagline) ?></p>
    <?php endif; ?>
</div>

<?php if (empty($posts)): ?>
    <p><?= __('No posts found.') ?></p>
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
                    <span class="post-date">
                        <?= date($date_format, strtotime($post->published_at ?? $post->created_at)) ?>
                    </span>
                    
                    <?php 
                    $categories = $post->getCategories();
                    if (!empty($categories)): 
                    ?>
                        <span class="post-categories">
                            <?= __('in') ?>
                            <?php foreach ($categories as $i => $cat): ?>
                                <a href="/category/<?= $cat->slug ?>"><?= $this->e($cat->name) ?></a><?= $i < count($categories)-1 ? ', ' : '' ?>
                            <?php endforeach; ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if ($show_excerpt): ?>
                    <div class="post-excerpt">
                        <?php 
                        $excerpt = $post->excerpt ?: strip_tags($post->content);
                        echo strlen($excerpt) > $excerpt_length ? substr($excerpt, 0, $excerpt_length) . '...' : $excerpt;
                        ?>
                    </div>
                    <a href="/post/<?= $post->slug ?>" class="read-more">
                        <?= __('Read more') ?> →
                    </a>
                <?php else: ?>
                    <div class="post-content">
                        <?= $post->content ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?= $current_page - 1 ?>" class="prev">&laquo; <?= __('Previous') ?></a>
            <?php endif; ?>
            
            <span class="current-page"><?= __('Page') ?> <?= $current_page ?> <?= __('of') ?> <?= $total_pages ?></span>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?= $current_page + 1 ?>" class="next"><?= __('Next') ?> &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>