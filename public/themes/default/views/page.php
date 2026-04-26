<?php $this->layout('layouts/app') ?>

<article class="page">
    <h1><?= $this->e($page->title) ?></h1>
    
    <div class="page-content">
        <?= $page->content ?>
    </div>
    
    <?php if (!empty($children)): ?>
        <div class="page-children">
            <h2><?= __('Subpages') ?></h2>
            <ul>
                <?php foreach ($children as $child): ?>
                    <li>
                        <a href="<?= $child->getUrl() ?>"><?= $this->e($child->title) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</article>

<style>
.page {
    max-width: 800px;
    margin: 0 auto;
}

.page h1 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
}

.page-content {
    font-size: 1.1rem;
    line-height: 1.8;
}

.page-content img {
    max-width: 100%;
    height: auto;
}

.page-children {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid #e2e8f0;
}

.page-children h2 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.page-children ul {
    list-style: none;
    padding: 0;
}

.page-children li {
    margin-bottom: 0.5rem;
}

.page-children a {
    color: #4299e1;
    text-decoration: none;
}

.page-children a:hover {
    text-decoration: underline;
}
</style>