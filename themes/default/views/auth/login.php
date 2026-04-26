<?php $this->layout('layouts/auth') ?>

<div class="auth-container">
    <div class="auth-logo">
        <img src="<?= theme_asset('images/logo-dark.svg') ?>" alt="OOPress" class="auth-logo-img">
    </div>
    
    <div class="auth-form-container">
        <h2><?= __('Login') ?></h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $this->e($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/login" class="auth-form">
            <div class="form-group">
                <label for="username"><?= __('Username or Email') ?></label>
                <input type="text" id="username" name="username" required autofocus class="form-control">
            </div>
            
            <div class="form-group">
                <label for="password"><?= __('Password') ?></label>
                <input type="password" id="password" name="password" required class="form-control">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block"><?= __('Login') ?></button>
            
            <p class="auth-links">
                <?= __('Don\'t have an account?') ?> 
                <a href="/register"><?= __('Register') ?></a>
            </p>
        </form>
    </div>
</div>