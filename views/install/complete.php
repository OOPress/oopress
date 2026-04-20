<?php $this->layout('install/layout', ['step' => 5]) ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <h3>✓ Installation Complete!</h3>
        <p>OOPress has been successfully installed on your server.</p>
    </div>
    
    <div class="form-group">
        <p><strong>Admin Login:</strong></p>
        <p>Username: <?= htmlspecialchars($_SESSION['install']['admin_username'] ?? 'admin') ?></p>
        <p>Password: (the password you created)</p>
    </div>
    
    <a href="/login" class="btn btn-primary">Login to Admin →</a>
    <a href="/" class="btn btn-secondary">View Site →</a>
<?php else: ?>
    <div class="alert alert-error">
        <h3>✗ Installation Failed</h3>
        <p><?= $error ?></p>
    </div>
    <a href="/install/welcome" class="btn btn-primary">Start Over</a>
<?php endif; ?>