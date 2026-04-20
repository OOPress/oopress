<?php $this->layout('install/layout', ['step' => 4]) ?>

<h2>Site Settings</h2>
<p>Configure your website.</p>

<form method="POST" action="/install/site">
    <div class="form-group">
        <label for="site_title">Site Title</label>
        <input type="text" id="site_title" name="site_title" value="OOPress" required>
    </div>
    
    <div class="form-group">
        <label for="site_tagline">Tagline</label>
        <input type="text" id="site_tagline" name="site_tagline" value="A modern PHP CMS">
    </div>
    
    <div class="form-group">
        <label for="timezone">Timezone</label>
        <select id="timezone" name="timezone">
            <?php foreach ($timezones as $value => $label): ?>
                <option value="<?= $value ?>" <?= $value === 'UTC' ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary">Install Now →</button>
    <a href="/install/admin" class="btn btn-secondary">← Back</a>
</form>