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
            <li><a href="?group=contact" class="<?= $current_group === 'contact' ? 'active' : '' ?>"><?= __('Contact') ?></a></li>
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
                        
                        <?php if ($current_group === 'contact'): ?>
                            <div class="setting-field">
                                <label for="contact_email"><?= __('Contact Email') ?></label>
                                <input type="email" id="contact_email" name="contact_email" value="<?= $this->e($settings['contact']['contact_email']['value'] ?? '') ?>">
                                <small><?= __('Email address where contact form submissions are sent') ?></small>
                            </div>
                            
                            <h3><?= __('SMTP Settings') ?></h3>
                            <small><?= __('Leave empty to use PHP mail() function') ?></small>
                            
                            <div class="setting-field">
                                <label for="smtp_host"><?= __('SMTP Host') ?></label>
                                <input type="text" id="smtp_host" name="smtp_host" value="<?= $this->e($settings['contact']['smtp_host']['value'] ?? '') ?>">
                            </div>
                            
                            <div class="setting-field">
                                <label for="smtp_port"><?= __('SMTP Port') ?></label>
                                <input type="text" id="smtp_port" name="smtp_port" value="<?= $this->e($settings['contact']['smtp_port']['value'] ?? '2525') ?>">
                            </div>
                            
                            <div class="setting-field">
                                <label for="smtp_username"><?= __('SMTP Username') ?></label>
                                <input type="text" id="smtp_username" name="smtp_username" value="<?= $this->e($settings['contact']['smtp_username']['value'] ?? '') ?>">
                            </div>
                            
                            <div class="setting-field">
                                <label for="smtp_password"><?= __('SMTP Password') ?></label>
                                <input type="password" id="smtp_password" name="smtp_password" value="<?= $this->e($settings['contact']['smtp_password']['value'] ?? '') ?>">
                            </div>
                            
                            <div class="setting-field">
                                <label for="smtp_encryption"><?= __('SMTP Encryption') ?></label>
                                <select id="smtp_encryption" name="smtp_encryption">
                                    <option value="tls" <?= ($settings['contact']['smtp_encryption']['value'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= ($settings['contact']['smtp_encryption']['value'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="none" <?= ($settings['contact']['smtp_encryption']['value'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
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