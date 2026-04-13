<?php $this->layout('layouts/admin') ?>

<?php $this->insert('partials/tinymce') ?>

<h1><?= __('Create New Post') ?></h1>

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
                $checked = $key === 'tinymce' ? 'checked' : '';
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

<form method="POST" action="/admin/posts/create">
    <div class="form-group">
        <label for="title"><?= __('Title') ?> *</label>
        <input type="text" id="title" name="title" required autofocus>
    </div>
    
    <!-- Content Editor -->
    <div class="form-group">
        <label for="content"><?= __('Content') ?></label>
        
        <!-- TinyMCE Editor -->
        <textarea id="content-tinymce" name="content" style="display:block;"><?= $this->e($post->content ?? '') ?></textarea>
        
        <!-- HTML Editor -->
        <textarea id="content-html" name="content_html" style="display:none;" class="code-editor"><?= $this->e($post->content ?? '') ?></textarea>
        
        <!-- Markdown Editor -->
        <textarea id="content-markdown" name="content_markdown" style="display:none;" class="code-editor"><?= $this->e($post->content ?? '') ?></textarea>
        
        <!-- PHP Editor -->
        <textarea id="content-php" name="content_php" style="display:none;" class="code-editor php-editor"><?= $this->e($post->content ?? '') ?></textarea>
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
    
    <!-- Categories -->
    <div class="meta-box">
        <h3><?= __('Categories') ?></h3>
        <div class="meta-box-content">
            <?php
            $categoryTaxonomy = OOPress\Models\Taxonomy::firstWhere(['slug' => 'category']);
            $allCategories = OOPress\Models\Term::where(['taxonomy_id' => $categoryTaxonomy->id ?? 0]);
            ?>
            
            <?php if (empty($allCategories)): ?>
                <p><?= __('No categories yet.') ?> <a href="/admin/categories"><?= __('Create one') ?></a></p>
            <?php else: ?>
                <ul class="taxonomy-checkbox-list">
                    <?php foreach ($allCategories as $category): ?>
                        <li>
                            <label>
                                <input type="checkbox" name="categories[]" value="<?= $category->id ?>">
                                <?= $this->e($category->name) ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tags -->
    <div class="meta-box">
        <h3><?= __('Tags') ?></h3>
        <div class="meta-box-content">
            <?php
            $tagTaxonomy = OOPress\Models\Taxonomy::firstWhere(['slug' => 'tag']);
            $allTags = OOPress\Models\Term::where(['taxonomy_id' => $tagTaxonomy->id ?? 0]);
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
                    
                    <div id="selected-tags" class="selected-tags"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- SEO Settings -->
    <div class="meta-box">
        <h3><?= __('SEO Settings') ?></h3>
        <div class="meta-box-content">
            <div class="form-group">
                <label for="meta_title"><?= __('Meta Title') ?></label>
                <input type="text" id="meta_title" name="meta_title">
                <small><?= __('Leave blank to use post title. Recommended: 50-60 characters.') ?></small>
            </div>
            
            <div class="form-group">
                <label for="meta_description"><?= __('Meta Description') ?></label>
                <textarea id="meta_description" name="meta_description" rows="3"></textarea>
                <small><?= __('Recommended: 150-160 characters.') ?></small>
            </div>
            
            <div class="form-group">
                <label for="meta_keywords"><?= __('Meta Keywords') ?></label>
                <input type="text" id="meta_keywords" name="meta_keywords">
                <small><?= __('Comma-separated keywords') ?></small>
            </div>
            
            <div class="form-group">
                <label for="canonical_url"><?= __('Canonical URL') ?></label>
                <input type="url" id="canonical_url" name="canonical_url">
            </div>
            
            <h4><?= __('Open Graph (Social Media)') ?></h4>
            
            <div class="form-group">
                <label for="og_title"><?= __('OG Title') ?></label>
                <input type="text" id="og_title" name="og_title">
            </div>
            
            <div class="form-group">
                <label for="og_description"><?= __('OG Description') ?></label>
                <textarea id="og_description" name="og_description" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label for="og_image"><?= __('OG Image URL') ?></label>
                <input type="url" id="og_image" name="og_image">
            </div>
            
            <div class="form-group">
                <label for="schema_type"><?= __('Schema Type') ?></label>
                <select id="schema_type" name="schema_type">
                    <option value="Article">Article</option>
                    <option value="BlogPosting">BlogPosting</option>
                    <option value="NewsArticle">NewsArticle</option>
                    <option value="Review">Review</option>
                </select>
            </div>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary"><?= __('Create Post') ?></button>
    <a href="/admin/posts" class="btn btn-secondary"><?= __('Cancel') ?></a>
</form>

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
</style>

<script>
// Format selector logic
document.querySelectorAll('.format-option input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.format-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        this.closest('.format-option').classList.add('selected');
        
        const format = this.value;
        document.querySelectorAll('[id^="content-"]').forEach(editor => {
            editor.style.display = 'none';
        });
        
        if (format === 'tinymce') {
            document.getElementById('content-tinymce').style.display = 'block';
            if (typeof tinymce !== 'undefined' && !tinymce.get('content-tinymce')) {
                tinymce.init({
                    selector: '#content-tinymce',
                    height: 500,
                    menubar: true,
                    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                    toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image media | help'
                });
            }
        } else {
            document.getElementById(`content-${format}`).style.display = 'block';
            if (typeof tinymce !== 'undefined' && tinymce.get('content-tinymce')) {
                tinymce.get('content-tinymce').remove();
            }
        }
    });
});

// Trigger initial format selection
document.querySelector('.format-option input:checked').dispatchEvent(new Event('change'));

// Tags functionality
const tagSelect = document.getElementById('tag-select');
const selectedTags = document.getElementById('selected-tags');

if (tagSelect) {
    tagSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            if (!selectedTags.querySelector(`.tag[data-id="${option.value}"]`)) {
                const tagSpan = document.createElement('span');
                tagSpan.className = 'tag';
                tagSpan.setAttribute('data-id', option.value);
                tagSpan.innerHTML = option.text + 
                    '<button type="button" class="remove-tag">&times;</button>' +
                    '<input type="hidden" name="tags[]" value="' + option.value + '">';
                selectedTags.appendChild(tagSpan);
                
                tagSpan.querySelector('.remove-tag').addEventListener('click', function() {
                    tagSpan.remove();
                });
            }
            this.value = '';
        }
    });
}
</script>