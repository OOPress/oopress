<?php $this->layout('layouts/admin') ?>

<?php $this->insert('partials/tinymce') ?>

<h1><?= __('Edit Post') ?>: <?= $this->e($post->title) ?></h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<!-- Content Format Selector -->
<div class="meta-box">
    <h3><?= __('Content Format') ?></h3>
    <div class="meta-box-content">
        <div class="format-selector">
            <?php 
            $formats = [
                'tinymce' => ['label' => 'TinyMCE', 'icon' => '📝', 'desc' => 'Rich text editor'],
                'html' => ['label' => 'HTML', 'icon' => '🔧', 'desc' => 'Raw HTML code'],
                'markdown' => ['label' => 'Markdown', 'icon' => '📄', 'desc' => 'Markdown syntax'],
                'php' => ['label' => 'PHP', 'icon' => '⚙️', 'desc' => 'PHP code (restricted)']
            ];
            
            foreach ($formats as $key => $format):
                $checked = ($post->content_format ?? 'tinymce') === $key ? 'checked' : '';
            ?>
                <label class="format-option <?= $key ?>">
                    <input type="radio" name="content_format" value="<?= $key ?>" <?= $checked ?> data-format="<?= $key ?>">
                    <span class="format-icon"><?= $format['icon'] ?></span>
                    <span class="format-name"><?= $format['label'] ?></span>
                    <span class="format-desc"><?= $format['desc'] ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Content Editor -->
<div class="form-group">
    <label for="content"><?= __('Content') ?></label>
    
    <!-- TinyMCE Editor (default) -->
    <textarea id="content-tinymce" name="content" style="display:none;"><?= $this->e($post->content) ?></textarea>
    
    <!-- HTML Editor (CodeMirror) -->
    <textarea id="content-html" name="content_html" style="display:none;" class="code-editor"><?= $this->e($post->content) ?></textarea>
    
    <!-- Markdown Editor -->
    <textarea id="content-markdown" name="content_markdown" style="display:none;" class="code-editor"><?= $this->e($post->content) ?></textarea>
    
    <!-- PHP Editor -->
    <textarea id="content-php" name="content_php" style="display:none;" class="code-editor php-editor"><?= $this->e($post->content) ?></textarea>
</div>

<style>
.format-selector {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.format-option {
    flex: 1;
    min-width: 120px;
    padding: 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s;
}

.format-option:hover {
    border-color: #4299e1;
    background: #ebf8ff;
}

.format-option input {
    display: none;
}

.format-option.selected {
    border-color: #4299e1;
    background: #ebf8ff;
}

.format-icon {
    font-size: 24px;
    display: block;
    margin-bottom: 8px;
}

.format-name {
    font-weight: 600;
    display: block;
    margin-bottom: 4px;
}

.format-desc {
    font-size: 11px;
    color: #718096;
}

.code-editor {
    font-family: monospace;
    font-size: 14px;
    line-height: 1.5;
    background: #1a202c;
    color: #e2e8f0;
    padding: 15px;
    border-radius: 8px;
    width: 100%;
    min-height: 500px;
}
</style>

<script>
// Format selector logic
document.querySelectorAll('.format-option input').forEach(radio => {
    radio.addEventListener('change', function() {
        // Update selected class
        document.querySelectorAll('.format-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        this.closest('.format-option').classList.add('selected');
        
        // Show/hide appropriate editor
        const format = this.value;
        document.querySelectorAll('[id^="content-"]').forEach(editor => {
            editor.style.display = 'none';
        });
        
        if (format === 'tinymce') {
            document.getElementById('content-tinymce').style.display = 'block';
            // Initialize TinyMCE if not already
            if (typeof tinymce !== 'undefined' && !tinymce.get('content-tinymce')) {
                tinymce.init({
                    selector: '#content-tinymce',
                    license_key: 'gpl', // Required for TinyMCE 8 - confirms open-source use
                    height: 500,
                    menubar: true,
                    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                    toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image media | help',
                    image_title: true,
                    automatic_uploads: true
                });
            }
        } else {
            document.getElementById(`content-${format}`).style.display = 'block';
            // Destroy TinyMCE if exists
            if (typeof tinymce !== 'undefined' && tinymce.get('content-tinymce')) {
                tinymce.get('content-tinymce').remove();
            }
        }
    });
});

// Trigger initial format selection
document.querySelector('.format-option input:checked').dispatchEvent(new Event('change'));
</script>

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

    <!-- Add after the content field -->
    <div class="meta-box">
        <h3><?= __('SEO Settings') ?></h3>
        <div class="meta-box-content">
            <div class="form-group">
                <label for="meta_title"><?= __('Meta Title') ?></label>
                <input type="text" id="meta_title" name="meta_title" value="<?= $this->e($post->meta_title) ?>">
                <small><?= __('Leave blank to use post title. Recommended: 50-60 characters.') ?></small>
                <div class="character-count">0/60</div>
            </div>
            
            <div class="form-group">
                <label for="meta_description"><?= __('Meta Description') ?></label>
                <textarea id="meta_description" name="meta_description" rows="3"><?= $this->e($post->meta_description) ?></textarea>
                <small><?= __('Recommended: 150-160 characters.') ?></small>
                <div class="character-count">0/160</div>
            </div>
            
            <div class="form-group">
                <label for="meta_keywords"><?= __('Meta Keywords') ?></label>
                <input type="text" id="meta_keywords" name="meta_keywords" value="<?= $this->e($post->meta_keywords) ?>">
                <small><?= __('Comma-separated keywords') ?></small>
            </div>
            
            <div class="form-group">
                <label for="canonical_url"><?= __('Canonical URL') ?></label>
                <input type="url" id="canonical_url" name="canonical_url" value="<?= $this->e($post->canonical_url) ?>">
                <small><?= __('Override the canonical URL if needed.') ?></small>
            </div>
            
            <h4><?= __('Open Graph (Social Media)') ?></h4>
            
            <div class="form-group">
                <label for="og_title"><?= __('OG Title') ?></label>
                <input type="text" id="og_title" name="og_title" value="<?= $this->e($post->og_title) ?>">
            </div>
            
            <div class="form-group">
                <label for="og_description"><?= __('OG Description') ?></label>
                <textarea id="og_description" name="og_description" rows="2"><?= $this->e($post->og_description) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="og_image"><?= __('OG Image URL') ?></label>
                <input type="url" id="og_image" name="og_image" value="<?= $this->e($post->og_image) ?>">
                <button type="button" id="select-image-btn" class="btn btn-secondary"><?= __('Select from Media') ?></button>
            </div>
            
            <div class="form-group">
                <label for="schema_type"><?= __('Schema Type') ?></label>
                <select id="schema_type" name="schema_type">
                    <option value="Article" <?= $post->schema_type === 'Article' ? 'selected' : '' ?>>Article</option>
                    <option value="BlogPosting" <?= $post->schema_type === 'BlogPosting' ? 'selected' : '' ?>>BlogPosting</option>
                    <option value="NewsArticle" <?= $post->schema_type === 'NewsArticle' ? 'selected' : '' ?>>NewsArticle</option>
                    <option value="Review" <?= $post->schema_type === 'Review' ? 'selected' : '' ?>>Review</option>
                </select>
            </div>
        </div>
    </div>

    <script>
    // Character counters
    document.getElementById('meta_title')?.addEventListener('input', function() {
        let count = this.value.length;
        this.parentElement.querySelector('.character-count').textContent = count + '/60';
        if (count > 60) this.style.borderColor = 'red';
        else this.style.borderColor = '';
    });

    document.getElementById('meta_description')?.addEventListener('input', function() {
        let count = this.value.length;
        this.parentElement.querySelector('.character-count').textContent = count + '/160';
        if (count > 160) this.style.borderColor = 'red';
        else this.style.borderColor = '';
    });

    // Media selector (simplified - you can enhance with your media library)
    document.getElementById('select-image-btn')?.addEventListener('click', function() {
        let url = prompt('Enter image URL from media library:');
        if (url) {
            document.getElementById('og_image').value = url;
        }
    });
    </script>
    
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