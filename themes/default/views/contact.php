<?php $this->layout('layouts/app') ?>

<div class="contact-page">
    <h1><?= __('Contact Us') ?></h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if ($errors && isset($errors['form'])): ?>
        <div class="alert alert-error"><?= $errors['form'] ?></div>
    <?php endif; ?>
    
    <div class="contact-info">
        <p><?= __('Have a question or comment? Fill out the form below and we will get back to you as soon as possible.') ?></p>
    </div>
    
    <form method="POST" action="/contact/submit" class="contact-form">
        <!-- Honeypot field (hidden from real users) -->
        <div class="honeypot" style="display: none;">
            <label for="website">Website</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>
        
        <div class="form-group">
            <label for="name"><?= __('Name') ?> *</label>
            <input type="text" id="name" name="name" value="<?= $this->e($old['name'] ?? '') ?>" required>
            <?php if ($errors && isset($errors['name'])): ?>
                <span class="error"><?= $errors['name'] ?></span>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="email"><?= __('Email') ?> *</label>
            <input type="email" id="email" name="email" value="<?= $this->e($old['email'] ?? '') ?>" required>
            <?php if ($errors && isset($errors['email'])): ?>
                <span class="error"><?= $errors['email'] ?></span>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="subject"><?= __('Subject') ?> *</label>
            <input type="text" id="subject" name="subject" value="<?= $this->e($old['subject'] ?? '') ?>" required>
            <?php if ($errors && isset($errors['subject'])): ?>
                <span class="error"><?= $errors['subject'] ?></span>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="message"><?= __('Message') ?> *</label>
            <textarea id="message" name="message" rows="6" required><?= $this->e($old['message'] ?? '') ?></textarea>
            <?php if ($errors && isset($errors['message'])): ?>
                <span class="error"><?= $errors['message'] ?></span>
            <?php endif; ?>
        </div>
        
        <button type="submit" class="btn btn-primary"><?= __('Send Message') ?></button>
    </form>
</div>

<style>
.contact-page {
    max-width: 800px;
    margin: 0 auto;
}

.contact-page h1 {
    font-size: 2rem;
    margin-bottom: 1.5rem;
}

.contact-info {
    background: #ebf8ff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border-left: 4px solid #4299e1;
}

.contact-form {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2d3748;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 16px;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.error {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #e53e3e;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #c6f6d5;
    color: #22543d;
    border: 1px solid #9ae6b4;
}

.alert-error {
    background: #fed7d7;
    color: #742a2a;
    border: 1px solid #feb2b2;
}

.btn-primary {
    background: #4299e1;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: #3182ce;
}
</style>