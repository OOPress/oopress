<?php $this->layout('layouts/app') ?>

<?php 
// Ensure $auth is defined
if (!isset($auth)) {
    $auth = null;
}
?>

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
    
    <!-- Comments Section -->
    <div id="comments" class="comments-section">
        <h3><?= __('Comments') ?> (<?= $post->getCommentCount() ?>)</h3>
        
        <?php
        $comments = $post->getComments();
        if (!empty($comments)):
        ?>
            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment" id="comment-<?= $comment->id ?>">
                        <div class="comment-meta">
                            <strong class="comment-author"><?= $this->e($comment->author_name) ?></strong>
                            <span class="comment-date"><?= date($date_format, strtotime($comment->created_at)) ?></span>
                        </div>
                        <div class="comment-content">
                            <?= $comment->content ?>
                        </div>
                        <button class="reply-btn" data-comment-id="<?= $comment->id ?>">
                            <?= __('Reply') ?>
                        </button>
                        
                        <?php
                        $replies = $comment->replies();
                        if (!empty($replies)):
                        ?>
                            <div class="replies">
                                <?php foreach ($replies as $reply): ?>
                                    <div class="comment reply" id="comment-<?= $reply->id ?>">
                                        <div class="comment-meta">
                                            <strong class="comment-author"><?= $this->e($reply->author_name) ?></strong>
                                            <span class="comment-date"><?= date($date_format, strtotime($reply->created_at)) ?></span>
                                        </div>
                                        <div class="comment-content">
                                            <?= $reply->content ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-comments"><?= __('No comments yet. Be the first to comment!') ?></p>
        <?php endif; ?>
        
        <!-- Comment Form -->
        <div id="comment-form" class="comment-form">
            <h4><?= __('Leave a Comment') ?></h4>
            
            <?php if (isset($_SESSION['comment_success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['comment_success'] ?></div>
                <?php unset($_SESSION['comment_success']); ?>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['comment_errors'])): ?>
                <div class="alert alert-error">
                    <?php foreach ($_SESSION['comment_errors'] as $error): ?>
                        <div><?= $error ?></div>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['comment_errors']); ?>
            <?php endif; ?>
            
            <form method="POST" action="/comment/submit">
                <input type="hidden" name="post_id" value="<?= $post->id ?>">
                <input type="hidden" name="parent_id" id="parent_id" value="0">
                
                <?php if (!$auth || !$auth->check()): ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="author_name"><?= __('Name') ?> *</label>
                            <input type="text" id="author_name" name="author_name" 
                                value="<?= $this->e($_SESSION['comment_data']['author_name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="author_email"><?= __('Email') ?> *</label>
                            <input type="email" id="author_email" name="author_email" 
                                value="<?= $this->e($_SESSION['comment_data']['author_email'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="author_url"><?= __('Website') ?></label>
                        <input type="url" id="author_url" name="author_url" 
                            value="<?= $this->e($_SESSION['comment_data']['author_url'] ?? '') ?>">
                    </div>
                <?php else: ?>
                    <p class="logged-in-as">
                        <?= __('Logged in as') ?> <strong><?= $this->e($auth->user()->display_name ?? $auth->user()->username) ?></strong>
                    </p>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="content"><?= __('Comment') ?> *</label>
                    <textarea id="content" name="content" rows="6" required><?= $this->e($_SESSION['comment_data']['content'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary"><?= __('Submit Comment') ?></button>
            </form>
        </div>
    </div>
    
    <div class="post-footer">
        <a href="/" class="back-to-home">← <?= __('Back to Home') ?></a>
    </div>
</article>

<script>
// Reply functionality
document.querySelectorAll('.reply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const commentId = this.dataset.commentId;
        document.getElementById('parent_id').value = commentId;
        document.getElementById('comment-form').scrollIntoView({ behavior: 'smooth' });
        document.getElementById('comment-form').style.background = '#f7fafc';
        document.getElementById('comment-form').style.padding = '20px';
        document.getElementById('comment-form').style.borderRadius = '8px';
    });
});
</script>