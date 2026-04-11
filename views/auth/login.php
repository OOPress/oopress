<?php $this->layout('layouts/app') ?>

<h1>Login</h1>

<?php if ($error): ?>
    <div style="color: red; padding: 10px; background: #fee; margin-bottom: 20px;">
        <?= $this->e($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="/login">
    <div>
        <label for="username">Username or Email:</label>
        <input type="text" id="username" name="username" required>
    </div>
    
    <div style="margin-top: 10px;">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <button type="submit" style="margin-top: 20px;">Login</button>
</form>