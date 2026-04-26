<?php $this->layout('layouts/app') ?>

<div class="auth-container">
    <h1><?= __('Register') ?></h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <form method="POST" action="/register">
        <div class="form-group">
            <label for="username"><?= __('Username') ?> *</label>
            <input type="text" id="username" name="username" 
                   value="<?= $this->e($old['username'] ?? '') ?>" 
                   required autofocus>
            <small><?= __('Letters, numbers, and underscores only. 3+ characters.') ?></small>
        </div>
        
        <div class="form-group">
            <label for="email"><?= __('Email') ?> *</label>
            <input type="email" id="email" name="email" 
                   value="<?= $this->e($old['email'] ?? '') ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="password"><?= __('Password') ?> *</label>
            <input type="password" id="password" name="password" required>
            <small><?= __('At least 6 characters') ?></small>
        </div>
        
        <div class="form-group">
            <label for="password_confirm"><?= __('Confirm Password') ?> *</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        
        <button type="submit" class="btn btn-primary"><?= __('Register') ?></button>
        
        <p class="auth-links">
            <?= __('Already have an account?') ?> 
            <a href="/login"><?= __('Login') ?></a>
        </p>
    </form>
</div>