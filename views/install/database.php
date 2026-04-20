<?php $this->layout('install/layout', ['step' => 2]) ?>

<h2>Database Configuration</h2>
<p>Enter your database connection details.</p>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="/install/database">
    <div class="form-group">
        <label for="db_host">Database Host</label>
        <input type="text" id="db_host" name="db_host" value="localhost" required>
    </div>
    
    <div class="form-group">
        <label for="db_name">Database Name</label>
        <input type="text" id="db_name" name="db_name" required>
    </div>
    
    <div class="form-group">
        <label for="db_user">Database User</label>
        <input type="text" id="db_user" name="db_user" required>
    </div>
    
    <div class="form-group">
        <label for="db_pass">Database Password</label>
        <input type="password" id="db_pass" name="db_pass">
    </div>
    
    <button type="submit" class="btn btn-primary">Test Connection →</button>
    <a href="/install/welcome" class="btn btn-secondary">← Back</a>
</form>