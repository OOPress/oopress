<?php $this->layout('layouts/admin') ?>

<div class="admin-header">
    <h1><?= __('Site Settings') ?></h1>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div class="settings-layout">
    <div class="settings-sidebar">
        <ul class="settings-tabs">
            <li><a href="?group=general" class="<?= $current_group === 'general' ? 'active' : '' ?>"><?= __('General') ?></a></li>
            <li><a href="?group=reading" class="<?= $current_group === 'reading' ? 'active' : '' ?>"><?= __('Reading') ?></a></li>
            <li><a href="?group=comments" class="<?= $current_group === 'comments' ? 'active' : '' ?>"><?= __('Comments') ?></a></li>
            <li><a href="?group=seo" class="<?= $current_group === 'seo' ? 'active' : '' ?>"><?= __('SEO') ?></a></li>
            <li><a href="?group=media" class="<?= $current_group === 'media' ? 'active' : '' ?>"><?= __('Media') ?></a></li>
            <li><a href="?group=cookies" class="<?= $current_group === 'cookies' ? 'active' : '' ?>"><?= __('Cookies') ?></a></li>
            <li><a href="?group=advanced" class="<?= $current_group === 'advanced' ? 'active' : '' ?>"><?= __('Advanced') ?></a></li>
        </ul>
    </div>
    
    <div class="settings-content">
        <form method="POST" action="/admin/settings/save">
            <input type="hidden" name="_group" value="<?= $current_group ?>">
            
            <?php if (isset($settings[$current_group])): ?>
                <?php foreach ($settings[$current_group] as $setting): ?>
                    <div class="setting-field">
                        <label for="<?= $setting['key'] ?>"><?= $setting['label'] ?></label>
                        
                        <?php if ($setting['type'] === 'text'): ?>
                            <input type="text" 
                                   id="<?= $setting['key'] ?>" 
                                   name="<?= $setting['key'] ?>" 
                                   value="<?= $this->e($setting['value']) ?>">
                        
                        <?php elseif ($setting['type'] === 'textarea'): ?>
                            <textarea id="<?= $setting['key'] ?>" 
                                      name="<?= $setting['key'] ?>" 
                                      rows="4"><?= $this->e($setting['value']) ?></textarea>
                        
                        <?php elseif ($setting['type'] === 'checkbox'): ?>
                            <label class="checkbox-label">
                                <input type="hidden" name="<?= $setting['key'] ?>" value="0">
                                <input type="checkbox" 
                                       id="<?= $setting['key'] ?>" 
                                       name="<?= $setting['key'] ?>" 
                                       value="1"
                                       <?= $setting['value'] ? 'checked' : '' ?>>
                                <?= __('Enable') ?>
                            </label>
                        
                        <?php elseif ($setting['type'] === 'select'): ?>
                            <select id="<?= $setting['key'] ?>" name="<?= $setting['key'] ?>">
                                <?php 
                                $options = explode('|', $setting['options']);
                                foreach ($options as $option):
                                    $optionValue = trim($option);
                                ?>
                                    <option value="<?= $optionValue ?>" 
                                            <?= $setting['value'] === $optionValue ? 'selected' : '' ?>>
                                        <?= $optionValue ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <?php if ($current_group === 'cookies'): ?>
                            <div class="setting-field">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="cookie_banner_enabled" value="1" <?= \OOPress\Models\Setting::get('cookie_banner_enabled', true) ? 'checked' : '' ?>>
                                    <?= __('Enable Cookie Banner') ?>
                                </label>
                            </div>
                            
                            <div class="setting-field">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="cookie_analytics_enabled" value="1" <?= \OOPress\Models\Setting::get('cookie_analytics_enabled', false) ? 'checked' : '' ?>>
                                    <?= __('Enable Analytics Cookies') ?>
                                </label>
                            </div>
                            
                            <div class="setting-field">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="cookie_marketing_enabled" value="1" <?= \OOPress\Models\Setting::get('cookie_marketing_enabled', false) ? 'checked' : '' ?>>
                                    <?= __('Enable Marketing Cookies') ?>
                                </label>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($setting['description']): ?>
                            <p class="setting-description"><?= $setting['description'] ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="settings-actions">
                <button type="submit" class="btn btn-primary"><?= __('Save Settings') ?></button>
            </div>
        </form>
    </div>
</div>