<?php $this->layout('install/layout', ['step' => 1]) ?>

<h2>Welcome to OOPress</h2>
<p>Thank you for choosing OOPress! Before we begin, please verify that your server meets the following requirements.</p>

<div class="requirements-list">
    <?php foreach ($requirements as $req): ?>
        <div class="requirement <?= $req['passed'] ? 'passed' : 'failed' ?>">
            <span class="requirement-name"><?= $req['name'] ?></span>
            <span><?= $req['current'] ?> (Required: <?= $req['required'] ?>)</span>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($all_passed): ?>
    <div class="alert alert-success">✓ All requirements are met! You can proceed with the installation.</div>
    <a href="/install/database" class="btn btn-primary">Continue →</a>
<?php else: ?>
    <div class="alert alert-error">✗ Please fix the requirements above before continuing.</div>
<?php endif; ?>