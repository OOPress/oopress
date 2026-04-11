<?php $this->layout('layouts/app') ?>

<h1>Dashboard</h1>

<p>Welcome, <?= $this->e($user->display_name ?? $user->username) ?>!</p>
<p>Email: <?= $this->e($user->email) ?></p>
<p>Role: <?= $this->e($user->role) ?></p>

<p><a href="/logout">Logout</a></p>