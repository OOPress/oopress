<?php $this->layout('layouts/auth') ?>

<div class="auth-container">
    <div class="auth-logo">
        <img src="<?= theme_asset('images/logo-dark.svg') ?>" alt="OOPress" class="auth-logo-img">
    </div>
    
    <div class="auth-form-container">
        <h2><?= __('Register') ?></h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/register" class="auth-form">
            <div class="form-group">
                <label for="username"><?= __('Username') ?> *</label>
                <input type="text" id="username" name="username" 
                       value="<?= $this->e($old['username'] ?? '') ?>" 
                       required autofocus class="form-control">
                <small class="form-help"><?= __('Letters, numbers, and underscores only. 3+ characters.') ?></small>
            </div>
            
            <div class="form-group">
                <label for="email"><?= __('Email') ?> *</label>
                <input type="email" id="email" name="email" 
                       value="<?= $this->e($old['email'] ?? '') ?>" 
                       required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="password"><?= __('Password') ?> *</label>
                <input type="password" id="password" name="password" required class="form-control">
                <small class="form-help"><?= __('At least 6 characters') ?></small>
            </div>
            
            <div class="form-group">
                <label for="password_confirm"><?= __('Confirm Password') ?> *</label>
                <input type="password" id="password_confirm" name="password_confirm" required class="form-control">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block"><?= __('Register') ?></button>
            
            <p class="auth-links">
                <?= __('Already have an account?') ?> 
                <a href="/login"><?= __('Login') ?></a>
            </p>
        </form>
    </div>
</div>