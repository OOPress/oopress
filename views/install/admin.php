<?php $this->layout('install/layout', ['step' => 3]) ?>

<h2>Admin Account</h2>
<p>Create your administrator account.</p>

<?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="/install/admin">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
    </div>
    
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <small>At least 6 characters</small>
    </div>
    
    <div class="form-group">
        <label for="password_confirm">Confirm Password</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
    </div>
    
    <button type="submit" class="btn btn-primary">Continue →</button>
    <a href="/install/database" class="btn btn-secondary">← Back</a>
</form>