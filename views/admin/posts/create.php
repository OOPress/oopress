<?php $this->layout('layouts/admin') ?>

<h1><?= __('Create New Post') ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="/admin/posts/create">
    <div class="form-group">
        <label for="title"><?= __('Title') ?> *</label>
        <input type="text" id="title" name="title" required autofocus>
    </div>
    
    <div class="form-group">
        <label for="content"><?= __('Content') ?></label>
        <textarea id="content" name="content" rows="15"></textarea>
    </div>
    
    <div class="form-group">
        <label for="excerpt"><?= __('Excerpt') ?></label>
        <textarea id="excerpt" name="excerpt" rows="3"></textarea>
    </div>
    
    <div class="form-group">
        <label for="status"><?= __('Status') ?></label>
        <select id="status" name="status">
            <option value="draft"><?= __('Draft') ?></option>
            <option value="published"><?= __('Published') ?></option>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary"><?= __('Create Post') ?></button>
    <a href="/admin/posts" class="btn btn-secondary"><?= __('Cancel') ?></a>
</form>

    <div class="post-editor-sidebar">
        <!-- Categories Box -->
        <div class="meta-box">
            <h3><?= __('Categories') ?></h3>
            <div class="meta-box-content">
                <?php
                $categoryTaxonomy = OOPress\Models\Taxonomy::firstWhere(['slug' => 'category']);
                $allCategories = OOPress\Models\Term::where(['taxonomy_id' => $categoryTaxonomy->id ?? 0]);
                $postCategories = $post->getCategories();
                $postCategoryIds = array_map(function($cat) { return $cat->id; }, $postCategories);
                ?>
                
                <?php if (empty($allCategories)): ?>
                    <p><?= __('No categories yet.') ?> <a href="/admin/categories"><?= __('Create one') ?></a></p>
                <?php else: ?>
                    <ul class="taxonomy-checkbox-list">
                        <?php foreach ($allCategories as $category): ?>
                            <li>
                                <label>
                                    <input type="checkbox" name="categories[]" value="<?= $category->id ?>"
                                        <?= in_array($category->id, $postCategoryIds) ? 'checked' : '' ?>>
                                    <?= $this->e($category->name) ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tags Box -->
        <div class="meta-box">
            <h3><?= __('Tags') ?></h3>
            <div class="meta-box-content">
                <?php
                $tagTaxonomy = OOPress\Models\Taxonomy::firstWhere(['slug' => 'tag']);
                $allTags = OOPress\Models\Term::where(['taxonomy_id' => $tagTaxonomy->id ?? 0]);
                $postTags = $post->getTags();
                $postTagIds = array_map(function($tag) { return $tag->id; }, $postTags);
                ?>
                
                <?php if (empty($allTags)): ?>
                    <p><?= __('No tags yet.') ?> <a href="/admin/tags"><?= __('Create one') ?></a></p>
                <?php else: ?>
                    <div class="tag-selector">
                        <select id="tag-select" style="width: 100%; margin-bottom: 10px;">
                            <option value=""><?= __('Add a tag...') ?></option>
                            <?php foreach ($allTags as $tag): ?>
                                <option value="<?= $tag->id ?>" data-name="<?= $this->e($tag->name) ?>">
                                    <?= $this->e($tag->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <div id="selected-tags" class="selected-tags">
                            <?php foreach ($postTags as $tag): ?>
                                <span class="tag" data-id="<?= $tag->id ?>">
                                    <?= $this->e($tag->name) ?>
                                    <button type="button" class="remove-tag">&times;</button>
                                    <input type="hidden" name="tags[]" value="<?= $tag->id ?>">
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>