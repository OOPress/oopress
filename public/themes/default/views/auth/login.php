<?php $this->layout('layouts/app') ?>

<div class="auth-logo">
        <h1><span style="color: #FF8C00;">OO</span><span style="color: #707070;">Press</span></h1>
    </div>
    <h2><?= __('Login') ?></h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $this->e($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="/login">
        <div class="form-group">
            <label for="username"><?= __('Username or Email') ?></label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password"><?= __('Password') ?></label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary"><?= __('Login') ?></button>
        
        <p class="auth-links">
            <?= __('Don\'t have an account?') ?> 
            <a href="/register"><?= __('Register') ?></a>
        </p>
    </form>
</div>