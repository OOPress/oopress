<?php $this->layout('layouts/admin') ?>

<h1><?= __('Edit Post') ?>: <?= $this->e($post->title) ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<div class="post-editor-layout">
    <div class="post-editor-main">
        <form method="POST" action="/admin/posts/<?= $post->id ?>/edit">
            <div class="form-group">
                <label for="title"><?= __('Title') ?> *</label>
                <input type="text" id="title" name="title" value="<?= $this->e($post->title) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="content"><?= __('Content') ?></label>
                <textarea id="content" name="content" rows="15"><?= $this->e($post->content) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="excerpt"><?= __('Excerpt') ?></label>
                <textarea id="excerpt" name="excerpt" rows="3"><?= $this->e($post->excerpt) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="status"><?= __('Status') ?></label>
                <select id="status" name="status">
                    <option value="draft" <?= $post->status === 'draft' ? 'selected' : '' ?>><?= __('Draft') ?></option>
                    <option value="published" <?= $post->status === 'published' ? 'selected' : '' ?>><?= __('Published') ?></option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary"><?= __('Update Post') ?></button>
            <a href="/admin/posts" class="btn btn-secondary"><?= __('Cancel') ?></a>
        </form>
    </div>
    
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
</div>

<script>
// Tags functionality
const tagSelect = document.getElementById('tag-select');
const selectedTags = document.getElementById('selected-tags');

if (tagSelect) {
    tagSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            // Check if tag already added
            if (!selectedTags.querySelector(`.tag[data-id="${option.value}"]`)) {
                const tagSpan = document.createElement('span');
                tagSpan.className = 'tag';
                tagSpan.setAttribute('data-id', option.value);
                tagSpan.innerHTML = option.text + 
                    '<button type="button" class="remove-tag">&times;</button>' +
                    '<input type="hidden" name="tags[]" value="' + option.value + '">';
                selectedTags.appendChild(tagSpan);
                
                // Add remove functionality
                tagSpan.querySelector('.remove-tag').addEventListener('click', function() {
                    tagSpan.remove();
                });
            }
            this.value = '';
        }
    });
}

// Remove tag buttons
document.querySelectorAll('.remove-tag').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.tag').remove();
    });
});
</script>

<style>
.post-editor-layout {
    display: flex;
    gap: 20px;
}

.post-editor-main {
    flex: 2;
}

.post-editor-sidebar {
    flex: 1;
}

.meta-box {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 20px;
}

.meta-box h3 {
    margin: 0;
    padding: 12px 15px;
    background: #edf2f7;
    border-bottom: 1px solid #e2e8f0;
    font-size: 16px;
}

.meta-box-content {
    padding: 15px;
}

.taxonomy-checkbox-list {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 200px;
    overflow-y: auto;
}

.taxonomy-checkbox-list li {
    margin-bottom: 8px;
}

.taxonomy-checkbox-list label {
    cursor: pointer;
}

.tag-selector select {
    padding: 8px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
}

.selected-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #4299e1;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.tag .remove-tag {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 14px;
    padding: 0 4px;
}

.tag .remove-tag:hover {
    color: #fed7d7;
}
</style>